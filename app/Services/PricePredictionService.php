<?php

namespace App\Services;

class PricePredictionService
{
    /**
     * Check if a version name matches a cluster's match rules.
     *
     * @param  array{all: string[], any: string[], none: string[]}  $matchRules
     */
    public function versionMatchesCluster(string $versionName, array $matchRules): bool
    {
        $name = mb_strtoupper($versionName);

        foreach ($matchRules['all'] as $token) {
            if (! str_contains($name, mb_strtoupper($token))) {
                return false;
            }
        }

        if (! empty($matchRules['any'])) {
            $found = false;
            foreach ($matchRules['any'] as $token) {
                if (str_contains($name, mb_strtoupper($token))) {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                return false;
            }
        }

        foreach ($matchRules['none'] as $token) {
            if (str_contains($name, mb_strtoupper($token))) {
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
     * Parse the line year (L/XX or trailing YYYY) from a version name.
     *
     * Patterns: "L/25", "L/2025", trailing "2025", trailing "2024".
     * Two-digit years are normalized assuming 2000s.
     *
     * @return int|null The 4-digit line year, or null if not found.
     */
    public function parseLineYear(string $versionName): ?int
    {
        // Match L/XX or L/XXXX anywhere in the name
        if (preg_match('/L\/(\d{2,4})\b/i', $versionName, $m)) {
            $raw = (int) $m[1];

            return $raw < 100 ? 2000 + $raw : $raw;
        }

        // Match a trailing 4-digit year (2019-2039 range to avoid false positives)
        if (preg_match('/\b(20[1-3]\d)$/', trim($versionName), $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Calculate predicted used car prices.
     *
     * Generates prices from currentModelYear down to max(lineYear, currentModelYear - yearsBack + 1).
     * When lineYear < currentModelYear, applies line_year_premium adjustment to the patentado.
     *
     * @param  float  $price0kmArsK  The 0km price in ARS thousands
     * @param  float  $factor  The patentado depreciation factor
     * @param  int[]  $drops  Drop amounts per year in ARS thousands
     * @param  int  $currentModelYear  e.g. 2026
     * @param  int  $yearsBack  Number of years to generate (e.g. 7)
     * @param  int|null  $lineYear  The version's line year (e.g. 2025 for L/25), null = currentModelYear
     * @param  float  $lineYearPremium  Premium to subtract per year of line age difference
     * @return array<int, float> [calendarYear => priceArsThousands]
     */
    public function calculatePrices(
        float $price0kmArsK,
        float $factor,
        array $drops,
        int $currentModelYear,
        int $yearsBack,
        ?int $lineYear = null,
        float $lineYearPremium = 0,
    ): array {
        $patentado = $this->roundTo100($price0kmArsK * $factor);

        // Apply line year premium: older lines are valued less
        if ($lineYear !== null) {
            $lineYearDiff = $currentModelYear - $lineYear;

            if ($lineYearDiff > 0 && $lineYearPremium > 0) {
                $patentado -= $lineYearDiff * $lineYearPremium;
                $patentado = $this->roundTo100($patentado);
            }
        }

        if ($patentado <= 0) {
            return [];
        }

        // Don't generate years before the line year (if known)
        $minYear = $lineYear ?? ($currentModelYear - $yearsBack);
        $prices = [];
        $accumulated = 0;
        $lastDrop = end($drops) ?: 0;

        for ($n = 0; $n < $yearsBack; $n++) {
            $year = $currentModelYear - $n;

            if ($year < $minYear) {
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
     * Find the first matching cluster for a version name.
     *
     * @param  string  $versionName  The version name from DB
     * @param  array  $modelClusters  Clusters config for this model
     * @return array{key: string, config: array}|null
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
}
