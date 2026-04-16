<?php

namespace App\Services;

use App\Models\Version;
use Illuminate\Support\Str;

class PricePredictionService
{
    public function __construct(
        private readonly ?array $config = null,
    ) {}

    /**
     * Check if a version name matches a cluster's match rules.
     *
     * @param  array{all: string[], any: string[], none: string[]}  $matchRules
     */
    public function versionMatchesCluster(string $versionName, array $matchRules): bool
    {
        $normalizedName = $this->normalizeForMatching($versionName);

        foreach ($matchRules['all'] as $token) {
            if (! $this->containsMatchToken($normalizedName, $token)) {
                return false;
            }
        }

        if (! empty($matchRules['any'])) {
            $found = false;

            foreach ($matchRules['any'] as $token) {
                if ($this->containsMatchToken($normalizedName, $token)) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                return false;
            }
        }

        foreach ($matchRules['none'] as $token) {
            if ($this->containsMatchToken($normalizedName, $token)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Round a value (in ARS thousands) to the nearest 100.
     */
    public function roundTo100(float $value): float
    {
        return round($value / 100) * 100;
    }

    /**
     * Calculate predicted used car prices.
     *
     * @param  float  $price0kmArsK  The 0km price in ARS thousands
     * @param  float  $factor  The patentado depreciation factor
     * @param  int[]  $drops  Drop amounts per year in ARS thousands
     * @param  int  $currentModelYear  e.g. 2026
     * @param  int  $yearsBack  Number of years to generate
     * @param  int|null  $lineYear  Model line year extracted from version name
     * @param  int  $lineYearPremiumPerYear  Discount for old lines vs current used year
     * @param  int  $priceOffset  Fixed ARS-thousands offset applied to all predictions
     * @return array<int, float> [calendarYear => priceArsThousands]
     */
    public function calculatePrices(
        float $price0kmArsK,
        float $factor,
        array $drops,
        int $currentModelYear,
        int $yearsBack,
        ?int $lineYear = null,
        int $lineYearPremiumPerYear = 0,
        int $priceOffset = 0,
    ): array {
        $firstUsedYear = $currentModelYear - 1;
        $patentado = $this->roundTo100($price0kmArsK * $factor) + $priceOffset;

        if ($lineYear !== null && $firstUsedYear < $lineYear) {
            return [];
        }

        if ($lineYear !== null && $lineYearPremiumPerYear > 0) {
            $modelYearGap = max(0, $firstUsedYear - $lineYear);
            $patentado -= $modelYearGap * $lineYearPremiumPerYear;
        }

        if ($patentado <= 0) {
            return [];
        }

        $prices = [];
        $accumulated = 0;
        $lastDrop = end($drops) ?: 0;

        for ($n = 0; $n < $yearsBack; $n++) {
            $year = $firstUsedYear - $n;

            if ($lineYear !== null && $year < $lineYear) {
                break;
            }

            $price = $patentado - $accumulated;

            if ($price <= 0) {
                break;
            }

            $prices[$year] = $price;

            $drop = $drops[$n] ?? $lastDrop;
            $accumulated += $drop;
        }

        return $prices;
    }

    /**
     * Find the first exact matching cluster for a version name.
     *
     * @param  string  $versionName  The version name from DB
     * @param  array<string, array<string, mixed>>  $modelClusters
     * @return array{key: string, config: array<string, mixed>}|null
     */
    public function findMatchingCluster(string $versionName, array $modelClusters): ?array
    {
        foreach ($modelClusters as $clusterKey => $clusterConfig) {
            if ($this->versionMatchesCluster($versionName, $clusterConfig['match'])) {
                return ['key' => $clusterKey, 'config' => $clusterConfig];
            }
        }

        return null;
    }

    /**
     * Find the best partial cluster for intra-family fallback.
     *
     * @param  array<string, array<string, mixed>>  $modelClusters
     * @return array{key: string, config: array<string, mixed>}|null
     */
    public function findPartialCluster(string $versionName, array $modelClusters): ?array
    {
        $bestKey = null;
        $bestConfig = null;
        $bestScore = 0;
        $normalizedName = $this->normalizeForMatching($versionName);

        foreach ($modelClusters as $clusterKey => $clusterConfig) {
            $noneMatched = collect($clusterConfig['match']['none'] ?? [])
                ->contains(fn (string $token) => $this->containsMatchToken($normalizedName, $token));

            if ($noneMatched) {
                continue;
            }

            $score = 0;

            foreach ($clusterConfig['match']['all'] ?? [] as $token) {
                if ($this->containsMatchToken($normalizedName, $token)) {
                    $score += 2;
                }
            }

            foreach ($clusterConfig['match']['any'] ?? [] as $token) {
                if ($this->containsMatchToken($normalizedName, $token)) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestKey = $clusterKey;
                $bestConfig = $clusterConfig;
            }
        }

        if ($bestKey === null) {
            return null;
        }

        return ['key' => $bestKey, 'config' => $bestConfig];
    }

    /**
     * @return array{
     *   predictions: array<int, float>,
     *   confidence: string,
     *   cluster_key: string,
     *   formula_version: string
     * }|null
     */
    public function predict(Version $version, float $price0kmArsK): ?array
    {
        $match = $this->matchCluster($version);

        if ($match === null) {
            return null;
        }

        $config = $this->getConfig();
        $lineYear = $this->extractLineYear($version->name);
        $priceOffset = $this->resolvePriceOffset(
            versionName: $version->name,
            clusterConfig: $match['config'],
            confidence: $match['confidence'],
        );

        $predictions = $this->calculatePrices(
            price0kmArsK: $price0kmArsK,
            factor: $match['config']['factor'],
            drops: $match['config']['drops'],
            currentModelYear: $config['current_model_year'],
            yearsBack: $config['years_back'],
            lineYear: $lineYear,
            lineYearPremiumPerYear: (int) ($match['config']['line_year_premium_per_year'] ?? $config['line_year_premium_per_year'] ?? 0),
            priceOffset: $priceOffset,
        );

        return [
            'predictions' => $predictions,
            'confidence' => $match['confidence'],
            'cluster_key' => $match['cluster_key'],
            'formula_version' => $config['formula_version'],
        ];
    }

    /**
     * @return array{
     *   confidence: string,
     *   cluster_key: string,
     *   config: array<string, mixed>
     * }|null
     */
    public function matchCluster(Version $version): ?array
    {
        $config = $this->getConfig();
        $brand = $version->carModel->brand;
        $model = $version->carModel;
        $brandName = mb_strtoupper($brand->name);

        if (! in_array($brandName, $config['mercosur_brands'], true)) {
            return null;
        }

        $brandClusters = $config['calibrated'][$brand->slug] ?? [];
        $modelClusters = $brandClusters[$model->slug] ?? null;

        if ($modelClusters !== null) {
            $exactMatch = $this->findMatchingCluster($version->name, $modelClusters);

            if ($exactMatch !== null) {
                return [
                    'confidence' => 'high',
                    'cluster_key' => "calibrated:{$brand->slug}:{$model->slug}:{$exactMatch['key']}",
                    'config' => $exactMatch['config'],
                ];
            }

            $partialMatch = $this->findPartialCluster($version->name, $modelClusters);

            if ($partialMatch !== null) {
                return [
                    'confidence' => 'medium',
                    'cluster_key' => "intra:{$brand->slug}:{$model->slug}:{$partialMatch['key']}",
                    'config' => $partialMatch['config'],
                ];
            }
        }

        $segment = $this->detectSegment($version);
        $tier = $this->resolveTier($brandName);

        return [
            'confidence' => 'low',
            'cluster_key' => "tier:{$tier}:{$segment}",
            'config' => [
                'factor' => $config['tiers'][$tier]['factor'],
                'drops' => $config['drop_tables'][$segment],
                'segment' => $segment,
                'at_over_mt' => $config['offsets']['at_over_mt'] ?? 0,
                'hev_over_ice' => $config['offsets']['hev_over_ice'] ?? 0,
            ],
        ];
    }

    public function extractLineYear(string $name): ?int
    {
        if (preg_match('/l\/(\d{2})\b/i', $name, $matches)) {
            return 2000 + (int) $matches[1];
        }

        if (preg_match('/\b(20\d{2})\b/', $name, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function getConfig(): array
    {
        return $this->config ?? config('infoauto-predictions');
    }

    private function normalizeForMatching(string $value): string
    {
        $value = Str::upper(Str::ascii($value));
        $value = preg_replace('/[^A-Z0-9]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return $value;
    }

    private function containsMatchToken(string $normalizedName, string $token): bool
    {
        $normalizedToken = $this->normalizeForMatching($token);

        if ($normalizedToken === '') {
            return false;
        }

        if (str_contains($normalizedToken, ' ')) {
            return str_contains(" {$normalizedName} ", " {$normalizedToken} ");
        }

        $words = explode(' ', $normalizedName);

        foreach ($words as $word) {
            if ($word === $normalizedToken) {
                return true;
            }

            if (strlen($normalizedToken) >= 4 && str_starts_with($word, $normalizedToken)) {
                return true;
            }
        }

        return false;
    }

    private function detectSegment(Version $version): string
    {
        $haystack = $this->normalizeForMatching(implode(' ', [
            $version->carModel->brand->name,
            $version->carModel->name,
            $version->name,
        ]));

        foreach ($this->getConfig()['segment_keywords'] as $segment => $keywords) {
            foreach ($keywords as $keyword) {
                if ($this->containsMatchToken($haystack, $keyword)) {
                    return $segment;
                }
            }
        }

        return 'sedan_chico';
    }

    private function resolveTier(string $brandName): string
    {
        foreach ($this->getConfig()['tiers'] as $tier => $data) {
            if (in_array($brandName, $data['brands'], true)) {
                return $tier;
            }
        }

        return 'commodity';
    }

    private function resolvePriceOffset(string $versionName, array $clusterConfig, string $confidence): int
    {
        $offset = 0;
        $offsetSource = $confidence === 'low' ? $this->getConfig()['offsets'] : $clusterConfig;
        $normalizedName = $this->normalizeForMatching($versionName);

        if (($offsetSource['at_over_mt'] ?? 0) > 0 && $this->isAutomatic($normalizedName)) {
            $offset += (int) $offsetSource['at_over_mt'];
        }

        if (($offsetSource['hev_over_ice'] ?? 0) > 0 && $this->isHev($normalizedName)) {
            $offset += (int) $offsetSource['hev_over_ice'];
        }

        return $offset;
    }

    private function isAutomatic(string $normalizedName): bool
    {
        return preg_match('/\b(CVT|AT|AT6|AT8|AT10|TIPT|TIPTRONIC|AUT|DSG|EDC|EDCT|E CVT|ECVT)\b/', $normalizedName) === 1;
    }

    private function isHev(string $normalizedName): bool
    {
        return preg_match('/\b(HEV|HYBRID|HIBRID)\b/', $normalizedName) === 1;
    }
}
