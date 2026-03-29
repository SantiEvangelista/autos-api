<?php

namespace App\Console\Commands;

use App\Models\PriceSnapshot;
use App\Services\ExchangeRateService;
use App\Services\VersionMatcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ScrapeAcaraPrices extends Command
{
    protected $signature = 'scrape:acara-prices
        {--output=storage/app/acara_prices.csv : Path for the output CSV file}
        {--brand=* : Scrape only specific brands (by name). If empty, scrape all}
        {--delay=1500 : Delay in milliseconds between API requests}
        {--retries=3 : Number of retries on rate limit (429)}
        {--save : Also persist scraped prices to the price_snapshots table}
        {--exchange-rate= : USD/ARS exchange rate for normalizing ARS prices (required with --save if ACARA returns ARS)}';

    protected $description = 'Scrape vehicle prices from ACARA API and export to CSV';

    private string $baseUrl = 'https://api.acara.org.ar/api/v1/prices';

    /** @var array<int, array{brand: string, model: string, version: string, year: int, currency: string, price: float}> */
    private array $scrapedEntries = [];

    public function handle(VersionMatcherService $versionMatcher, ExchangeRateService $exchangeRateService): int
    {
        $outputPath = $this->option('output');
        $brandFilter = array_filter(array_map(fn($b) => strtolower(trim($b)), $this->option('brand')));
        $delay = (int) $this->option('delay');

        $this->info('Fetching brands from ACARA...');
        $brands = $this->fetchData("{$this->baseUrl}/brand-list?vehiculeType=1");

        if (empty($brands)) {
            $this->error('Could not fetch brands from ACARA API.');
            return self::FAILURE;
        }

        if (!empty($brandFilter)) {
            $brands = array_filter($brands, fn($b) => in_array(strtolower(trim($b['name'] ?? '')), $brandFilter));
        }

        $this->info(count($brands) . ' brands to process.');

        $fp = fopen($outputPath, 'w');
        fputcsv($fp, ['brand', 'brand_id', 'model', 'model_id', 'version', 'version_id', 'year', 'currency', 'price']);

        $stats = ['brands' => 0, 'models' => 0, 'versions' => 0, 'prices' => 0, 'errors' => 0];

        foreach ($brands as $brand) {
            $brandName = trim($brand['name'] ?? '');
            $brandId = $brand['id'] ?? null;

            if (!$brandId || $brandName === '') {
                continue;
            }

            $this->info("Processing brand: {$brandName} (ID: {$brandId})");

            $models = $this->fetchData("{$this->baseUrl}/model-list?vehiculeType=1&vehiculeBrandId={$brandId}");
            $this->delay($delay);

            if (empty($models)) {
                $this->warn("  No models found for {$brandName}");
                continue;
            }

            $stats['brands']++;

            // Group models by name to unify duplicates (same model, different ID ranges)
            $modelsByName = [];
            foreach ($models as $model) {
                $name = trim($model['name'] ?? '');
                $id = $model['id'] ?? null;
                if ($id && $name !== '') {
                    $modelsByName[$name][] = $id;
                }
            }

            foreach ($modelsByName as $modelName => $modelIds) {
                // Collect versions from all IDs for this model name, dedup by version name
                $allVersions = [];
                foreach ($modelIds as $modelId) {
                    $versions = $this->fetchData("{$this->baseUrl}/version-list?vehiculeType=1&vehiculeBrandId={$brandId}&vehiculeModelId={$modelId}");
                    $this->delay($delay);

                    foreach ($versions as $v) {
                        $vName = trim($v['name'] ?? '');
                        $vId = $v['id'] ?? null;
                        if ($vId && $vName !== '' && !isset($allVersions[$vName])) {
                            $allVersions[$vName] = ['id' => $vId, 'model_id' => $modelId];
                        }
                    }
                }

                if (empty($allVersions)) {
                    continue;
                }

                $stats['models']++;
                $idsStr = implode(',', $modelIds);
                $this->line("  Model: {$modelName} (IDs: {$idsStr}) - " . count($allVersions) . ' unique versions');

                foreach ($allVersions as $versionName => $versionInfo) {
                    $versionId = $versionInfo['id'];
                    $modelId = $versionInfo['model_id'];

                    $priceData = $this->fetchData("{$this->baseUrl}/get-vehicules?vehiculeType=1&vehiculeBrandId={$brandId}&vehiculeModelId={$modelId}&vehiculeVersionId={$versionId}");
                    $this->delay($delay);

                    if (empty($priceData)) {
                        continue;
                    }

                    $stats['versions']++;

                    $this->line("    Fetched {$versionName}: " . count($priceData) . ' row(s) from API');

                    foreach ($priceData as $item) {
                        $currency = $item['moneda'] ?? '$';
                        $yearPrices = $item['precios_por_año'] ?? [];
                        // Use version name from price response if available, fallback to version list name
                        $itemVersion = trim($item['version'] ?? $versionName);

                        $validPrices = array_filter($yearPrices, fn($p) => $p !== null && $p > 0);
                        $this->line("      Version: {$itemVersion} | Moneda: {$currency} | Prices: " . count($validPrices) . '/' . count($yearPrices) . ' years');

                        if (!empty($validPrices)) {
                            $sample = collect($validPrices)->take(3)->map(fn($p, $y) => "{$y}={$p}")->implode(', ');
                            $this->line("      Sample: {$sample}");
                        }

                        foreach ($yearPrices as $year => $price) {
                            if ($price === null || $price <= 0 || $year === 0) {
                                continue;
                            }

                            fputcsv($fp, [
                                $brandName,
                                $brandId,
                                $modelName,
                                $modelId,
                                $itemVersion,
                                $versionId,
                                (int) $year,
                                $currency,
                                $price,
                            ]);

                            $this->scrapedEntries[] = [
                                'brand' => $brandName,
                                'model' => $modelName,
                                'version' => $itemVersion,
                                'year' => (int) $year,
                                'currency' => $currency,
                                'price' => (float) $price,
                            ];

                            $stats['prices']++;
                        }
                    }
                }
            }
        }

        fclose($fp);

        $this->newLine();
        $this->info("CSV saved to: {$outputPath}");
        $this->table(
            ['Metric', 'Count'],
            collect($stats)->map(fn($v, $k) => [ucfirst($k), $v])->values()->toArray()
        );

        if ($this->option('save')) {
            $this->persistToDatabase($versionMatcher, $exchangeRateService);
        }

        return self::SUCCESS;
    }

    private function fetchData(string $url): array
    {
        $response = $this->fetchWithRetry($url);
        if ($response === null) {
            return [];
        }

        // Try JSON first
        $json = $response->json();
        if ($json && isset($json['data'])) {
            return $json['data'];
        }

        // Fallback: parse HTML table (get-vehicules endpoint returns HTML)
        $body = $response->body();
        if (str_contains($body, '<table')) {
            return $this->parseHtmlTable($body);
        }

        return [];
    }

    private function fetchWithRetry(string $url): ?\Illuminate\Http\Client\Response
    {
        $maxRetries = (int) $this->option('retries');

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    return $response;
                }

                if ($response->status() === 429 && $attempt < $maxRetries) {
                    $wait = (int) ($response->header('Retry-After') ?: ($attempt + 1) * 3);
                    $this->warn("  Rate limited (429), waiting {$wait}s before retry " . ($attempt + 1) . "/{$maxRetries}...");
                    sleep($wait);
                    continue;
                }

                $this->warn("  HTTP {$response->status()} for: {$url}");
                return null;
            } catch (\Throwable $e) {
                $this->warn("  Error fetching {$url}: {$e->getMessage()}");
                return null;
            }
        }

        return null;
    }

    /**
     * Parse the HTML table returned by get-vehicules into structured data.
     * Table format: Modelo | Version | Moneda | 0km | 2025 | 2024 | ... | 2011
     */
    private function parseHtmlTable(string $html): array
    {
        $results = [];

        // Extract year headers from <thead>
        preg_match('/<thead>(.*?)<\/thead>/s', $html, $headMatch);
        if (empty($headMatch)) {
            return [];
        }

        preg_match_all('/<th[^>]*>(.*?)<\/th>/s', $headMatch[1], $headers);
        $headerValues = array_map('strip_tags', $headers[1] ?? []);
        // Headers: [Modelo, Version, Moneda, 0km, 2025, 2024, ...]

        // Extract rows from <tbody>
        preg_match('/<tbody>(.*?)<\/tbody>/s', $html, $bodyMatch);
        if (empty($bodyMatch)) {
            return [];
        }

        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/s', $bodyMatch[1], $rows);

        foreach ($rows[1] ?? [] as $rowHtml) {
            preg_match_all('/<td[^>]*>(.*?)<\/td>/s', $rowHtml, $cells);
            $values = array_map('strip_tags', $cells[1] ?? []);

            if (count($values) < 4) {
                continue;
            }

            $modelo = trim($values[0] ?? '');
            $version = trim($values[1] ?? '');
            $moneda = trim($values[2] ?? '$');

            $preciosPorAnio = [];
            for ($i = 3; $i < count($values); $i++) {
                $yearHeader = $headerValues[$i] ?? null;
                $val = trim($values[$i]);

                if ($yearHeader === null || $val === '-' || $val === '') {
                    continue;
                }

                $numericVal = (float) str_replace(['.', ','], ['', '.'], $val);
                if ($numericVal > 0) {
                    $year = $yearHeader === '0km' ? 0 : (int) $yearHeader;
                    $preciosPorAnio[$year] = $numericVal;
                }
            }

            $results[] = [
                'modelo' => $modelo,
                'version' => $version,
                'moneda' => $moneda,
                'precios_por_año' => $preciosPorAnio,
            ];
        }

        return $results;
    }

    private function delay(int $ms): void
    {
        if ($ms > 0) {
            usleep($ms * 1000);
        }
    }

    private function persistToDatabase(VersionMatcherService $versionMatcher, ExchangeRateService $exchangeRateService): void
    {
        $exchangeRate = $this->resolveExchangeRateForSave($exchangeRateService);
        $today = now()->toDateString();
        $now = now();
        $saved = 0;
        $skipped = 0;
        $snapshotBatch = [];

        foreach ($this->scrapedEntries as $entry) {
            $version = $versionMatcher->findVersion($entry['brand'], $entry['model'], $entry['version']);

            if (!$version) {
                $skipped++;
                continue;
            }

            $isArs = !in_array(strtolower(trim($entry['currency'])), ['u$s', 'usd']);

            if ($isArs && !$exchangeRate) {
                $skipped++;
                continue;
            }

            $price = VersionMatcherService::normalizeToUsd($entry['price'], $entry['currency'], $exchangeRate ?? 1.0);

            $snapshotBatch[] = [
                'version_id' => $version->id,
                'year' => $entry['year'],
                'price' => $price,
                'source' => 'acara',
                'recorded_at' => $today,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Flush in batches of 500
            if (count($snapshotBatch) >= 500) {
                $snapshotBatch = $this->deduplicateBatch($snapshotBatch);
                PriceSnapshot::upsert($snapshotBatch, ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);
                $saved += count($snapshotBatch);
                $snapshotBatch = [];
            }
        }

        if (!empty($snapshotBatch)) {
            $snapshotBatch = $this->deduplicateBatch($snapshotBatch);
            PriceSnapshot::upsert($snapshotBatch, ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);
            $saved += count($snapshotBatch);
        }

        $this->newLine();
        $this->info("Saved to DB: {$saved} price snapshots, {$skipped} unmatched entries skipped.");
    }

    private function deduplicateBatch(array $batch): array
    {
        $unique = [];

        foreach ($batch as $row) {
            $key = "{$row['version_id']}:{$row['year']}:{$row['source']}:{$row['recorded_at']}";
            $unique[$key] = $row; // Last one wins
        }

        return array_values($unique);
    }

    private function resolveExchangeRateForSave(ExchangeRateService $exchangeRateService): ?float
    {
        $manualRate = $this->option('exchange-rate');

        if ($manualRate !== null) {
            $this->info("Using manual exchange rate: {$manualRate}");
            return (float) $manualRate;
        }

        $this->info('Fetching exchange rate from Bluelytics...');
        $rate = $exchangeRateService->getOfficialSellRate();

        if ($rate) {
            $this->info("Using Bluelytics official sell rate: {$rate}");
            return $rate;
        }

        $this->error('Could not fetch exchange rate. ARS prices will not be saved. Use --exchange-rate=<value> as fallback.');
        return null;
    }
}
