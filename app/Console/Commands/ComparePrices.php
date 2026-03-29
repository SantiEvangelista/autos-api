<?php

namespace App\Console\Commands;

use App\Models\Valuation;
use App\Services\ExchangeRateService;
use App\Services\VersionMatcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ComparePrices extends Command
{
    protected $signature = 'compare:prices
        {--primary= : Path to primary source CSV (columns: brand, model, year, version, currency, price)}
        {--secondary= : Path to secondary source CSV (same format)}
        {--threshold=10 : Percentage difference threshold to flag as mismatch}
        {--exchange-rate= : USD/ARS exchange rate to normalize ARS prices to USD for comparison. Falls back to ExchangeRateService}
        {--export= : Export comparison results to a CSV file}';

    protected $description = 'Compare external price sources against database valuations to detect discrepancies';

    private const USD_CURRENCY_MARKER = 'u$s';

    private const ARS_CURRENCY_MARKERS = ['$', 'ars', 'ARS'];

    private ?float $exchangeRate = null;

    public function handle(ExchangeRateService $exchangeRateService): int
    {
        $threshold = (float) $this->option('threshold');
        $exportPath = $this->option('export');

        $this->exchangeRate = $this->resolveExchangeRate($exchangeRateService);

        $primaryEntries = $this->loadSource('primary');
        $secondaryEntries = $this->loadSource('secondary');

        if (empty($primaryEntries) && empty($secondaryEntries)) {
            $this->error('No external price data found. Provide at least one source CSV via --primary or --secondary.');
            return self::FAILURE;
        }

        $this->info('Loaded: ' . count($primaryEntries) . ' primary, ' . count($secondaryEntries) . ' secondary entries');
        $this->info("Threshold: {$threshold}%");
        $this->newLine();

        $matched = [];
        $notFoundPrimary = [];
        $notFoundSecondary = [];

        $this->matchEntries('primary', $primaryEntries, $matched, $notFoundPrimary);
        $this->matchEntries('secondary', $secondaryEntries, $matched, $notFoundSecondary);

        $this->newLine();

        [$stats, $mismatches] = $this->classify($matched, $threshold, count($notFoundPrimary), count($notFoundSecondary));

        $this->printResults($stats, $mismatches, $threshold);

        if ($exportPath) {
            $this->exportResults($exportPath, $matched, $mismatches);
            $this->info("Results exported to: {$exportPath}");
        }

        return self::SUCCESS;
    }

    private function loadSource(string $name): array
    {
        $path = $this->option($name);

        if (!$path) {
            return [];
        }

        if (!file_exists($path)) {
            $this->warn("Source file not found: {$path}");
            return [];
        }

        $this->info("Loading {$name} source from: {$path}");

        return $this->parseCsv($path);
    }

    /**
     * Parse a standardized CSV with columns: brand, model, year, version, currency, price.
     *
     * @return array<int, array{brand: string, model: string, version: string, year: int, price: float, currency: string, brand_slug: string, model_slug: string}>
     */
    private function parseCsv(string $path): array
    {
        $entries = [];
        $fp = fopen($path, 'r');
        $headers = fgetcsv($fp);

        if (!$headers) {
            fclose($fp);
            return [];
        }

        $headers = array_map(fn ($h) => strtolower(trim($h)), $headers);

        // Detect header/data column mismatch by peeking at first data row
        $firstDataPos = ftell($fp);
        $firstRow = fgetcsv($fp);
        fseek($fp, $firstDataPos);

        if ($firstRow && count($headers) > count($firstRow)) {
            // Header has more columns than data — trim trailing headers to match data width
            $headers = array_slice($headers, 0, count($firstRow));
        }

        $map = array_flip($headers);

        $brandCol = $map['brand'] ?? null;
        $modelCol = $map['model'] ?? null;
        $yearCol = $map['year'] ?? null;
        $versionCol = $map['version'] ?? null;
        $currencyCol = $map['currency'] ?? null;
        $priceCol = $map['price'] ?? $map['price_numeric'] ?? null;

        // If currency column ended up as last col after trimming, the actual price
        // may be in what was labeled as currency. Detect by checking if the value is numeric.
        $detectCurrencyFromValue = false;
        if ($priceCol === null && $currencyCol !== null) {
            $priceCol = $currencyCol;
            $currencyCol = null;
            $detectCurrencyFromValue = true;
        }

        if ($brandCol === null || $priceCol === null) {
            $this->warn("CSV missing required columns (brand, price). Found: " . implode(', ', $headers));
            fclose($fp);
            return [];
        }

        while (($row = fgetcsv($fp)) !== false) {
            $brand = trim($row[$brandCol] ?? '');
            $model = trim($row[$modelCol] ?? '');
            $year = (int) ($row[$yearCol] ?? 0);
            $version = trim($row[$versionCol] ?? '');
            $rawPrice = (float) ($row[$priceCol] ?? 0);

            // Determine currency
            if ($currencyCol !== null) {
                $currency = trim($row[$currencyCol] ?? self::USD_CURRENCY_MARKER);
            } elseif ($detectCurrencyFromValue) {
                // Infer from price_raw column if available (contains "$" or "u$s" prefix)
                $priceRawCol = $map['price_raw'] ?? null;
                $priceRawValue = $priceRawCol !== null ? trim($row[$priceRawCol] ?? '') : '';
                $currency = str_starts_with($priceRawValue, 'u$s') ? self::USD_CURRENCY_MARKER : '$';
            } else {
                $currency = self::USD_CURRENCY_MARKER;
            }

            if ($rawPrice <= 0 || $year <= 0 || $brand === '') {
                continue;
            }

            $entries[] = [
                'brand' => $brand,
                'model' => $model,
                'version' => $version,
                'year' => $year,
                'price' => round($rawPrice, 2),
                'currency' => $currency,
                'brand_slug' => Str::slug($brand),
                'model_slug' => Str::slug($model),
            ];
        }

        fclose($fp);

        return $entries;
    }

    private function matchEntries(string $sourceName, array $entries, array &$matched, array &$notFound): void
    {
        if (empty($entries)) {
            return;
        }

        $priceKey = "{$sourceName}_price";
        $currencyKey = "{$sourceName}_currency";

        $this->info("Matching {$sourceName} entries against DB...");
        $bar = $this->output->createProgressBar(count($entries));

        foreach ($entries as $entry) {
            $valuation = $this->findValuation($entry['brand'], $entry['model'], $entry['version'], $entry['year']);
            $bar->advance();

            if (!$valuation) {
                $notFound[] = $entry;
                continue;
            }

            $vid = $valuation->id;

            if (!isset($matched[$vid])) {
                $matched[$vid] = [
                    'brand' => $entry['brand'],
                    'model' => $entry['model'],
                    'version_db' => $valuation->version->name,
                    'year' => $entry['year'],
                    'db_price' => (float) $valuation->price,
                    'primary_price' => null,
                    'primary_currency' => null,
                    'secondary_price' => null,
                    'secondary_currency' => null,
                ];
            }

            $matched[$vid][$priceKey] = $entry['price'];
            $matched[$vid][$currencyKey] = $entry['currency'];
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * @return array{0: array, 1: array}
     */
    private function classify(array $matched, float $threshold, int $notFoundPrimary, int $notFoundSecondary): array
    {
        $stats = [
            'total' => 0,
            'matching' => 0,
            'mismatched' => 0,
            'not_found_primary' => $notFoundPrimary,
            'not_found_secondary' => $notFoundSecondary,
        ];
        $mismatches = [];

        foreach ($matched as $row) {
            $stats['total']++;
            $dbPrice = $row['db_price'];

            $primaryDiff = $this->calculateDiff($row['primary_price'], $row['primary_currency'], $dbPrice);
            $secondaryDiff = $this->calculateDiff($row['secondary_price'], $row['secondary_currency'], $dbPrice);

            $hasMismatch = ($primaryDiff !== null && abs($primaryDiff) > $threshold)
                || ($secondaryDiff !== null && abs($secondaryDiff) > $threshold);

            if ($hasMismatch) {
                $stats['mismatched']++;
                $mismatches[] = array_merge($row, [
                    'primary_diff' => $primaryDiff,
                    'secondary_diff' => $secondaryDiff,
                ]);
            } else {
                $stats['matching']++;
            }
        }

        return [$stats, $mismatches];
    }

    private function calculateDiff(?float $sourcePrice, ?string $currency, float $dbPrice): ?float
    {
        if ($sourcePrice === null || $dbPrice <= 0) {
            return null;
        }

        $normalizedPrice = $this->normalizeToUsd($sourcePrice, $currency);

        if ($normalizedPrice === null) {
            return null;
        }

        return round((($normalizedPrice - $dbPrice) / $dbPrice) * 100, 2);
    }

    private function normalizeToUsd(float $price, ?string $currency): ?float
    {
        if ($currency === null || $currency === self::USD_CURRENCY_MARKER) {
            return $price;
        }

        if (in_array($currency, self::ARS_CURRENCY_MARKERS) && $this->exchangeRate) {
            return round($price / $this->exchangeRate, 2);
        }

        return null;
    }

    private function resolveExchangeRate(ExchangeRateService $exchangeRateService): ?float
    {
        $manualRate = $this->option('exchange-rate');

        if ($manualRate !== null) {
            $rate = (float) $manualRate;

            if ($rate <= 0) {
                $this->error('Exchange rate must be a positive number.');
                return null;
            }

            $this->info("Using manual exchange rate: {$rate}");

            return $rate;
        }

        $this->info('Fetching exchange rate from Bluelytics...');
        $rate = $exchangeRateService->getOfficialSellRate();

        if ($rate) {
            $this->info("Using Bluelytics official sell rate: {$rate}");
        }

        return $rate;
    }

    private function findValuation(string $brandName, string $modelName, string $versionName, int $year): ?Valuation
    {
        $matcher = app(VersionMatcherService::class);

        $version = $matcher->findVersion($brandName, $modelName, $versionName);

        if (!$version) {
            return null;
        }

        return Valuation::where('version_id', $version->id)->where('year', $year)->first();
    }

    private function printResults(array $stats, array $mismatches, float $threshold): void
    {
        $this->info('=== Comparison Results ===');
        $this->table(
            ['Metric', 'Count'],
            collect($stats)->map(fn ($v, $k) => [str_replace('_', ' ', ucfirst($k)), $v])->values()->toArray()
        );

        if (empty($mismatches)) {
            return;
        }

        $this->newLine();
        $this->warn("=== Mismatches (>{$threshold}% difference) ===");
        $this->table(
            ['Brand', 'Model', 'Version (DB)', 'Year', 'DB (USD)', 'Primary (USD)', 'Pri Diff', 'Secondary (USD)', 'Sec Diff'],
            collect($mismatches)->map(fn ($r) => [
                $r['brand'],
                Str::limit($r['model'], 15),
                Str::limit($r['version_db'], 25),
                $r['year'],
                number_format($r['db_price'], 2, ',', '.'),
                $r['primary_price'] !== null ? number_format($r['primary_price'], 2, ',', '.') : '-',
                $this->formatSourceDiff($r['primary_diff'] ?? null, $r['primary_currency'] ?? null),
                $r['secondary_price'] !== null ? number_format($r['secondary_price'], 2, ',', '.') : '-',
                $this->formatSourceDiff($r['secondary_diff'] ?? null, $r['secondary_currency'] ?? null),
            ])->toArray()
        );
    }

    private function formatSourceDiff(?float $diff, ?string $currency): string
    {
        if ($currency !== null && $currency !== self::USD_CURRENCY_MARKER) {
            return 'ARS';
        }

        if ($diff === null) {
            return '-';
        }

        return ($diff > 0 ? '+' : '') . $diff . '%';
    }

    private function exportResults(string $path, array $matched, array $mismatches): void
    {
        $fp = fopen($path, 'w');
        fputcsv($fp, ['type', 'brand', 'model', 'version_db', 'year', 'db_price', 'primary_price', 'primary_diff_pct', 'secondary_price', 'secondary_currency', 'secondary_diff_pct']);

        foreach ($matched as $r) {
            $dbPrice = $r['db_price'];
            $primaryDiff = $this->calculateDiff($r['primary_price'], $r['primary_currency'], $dbPrice);
            $secondaryDiff = $this->calculateDiff($r['secondary_price'], $r['secondary_currency'], $dbPrice);

            fputcsv($fp, [
                in_array($r, $mismatches) ? 'mismatch' : 'match',
                $r['brand'],
                $r['model'],
                $r['version_db'],
                $r['year'],
                $r['db_price'],
                $r['primary_price'] ?? '',
                $primaryDiff !== null ? $primaryDiff : '',
                $r['secondary_price'] ?? '',
                $r['secondary_currency'] ?? '',
                $secondaryDiff !== null ? $secondaryDiff : '',
            ]);
        }

        fclose($fp);
    }
}
