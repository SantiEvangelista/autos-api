<?php

namespace App\Services;

use App\Models\CarModel;
use App\Models\Version;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VersionMatcherService
{
    private const BRAND_ALIASES = [
        'b-y-d' => 'byd',
        'ds' => 'ds-automobiles',
        'mini' => 'mini-cooper',
    ];

    /**
     * Find a local Version by fuzzy matching on brand, model, and version name.
     */
    public function findVersion(string $brandName, string $modelName, string $versionName, ?int $year = null): ?Version
    {
        $brandSlug = $this->resolveBrandSlug($brandName);
        $modelSlug = Str::slug($modelName);

        // Find brand
        $brand = \App\Models\Brand::where('slug', $brandSlug)->first();
        if (!$brand) {
            return null;
        }

        // Find model with fuzzy matching
        $model = $this->findModel($brand->id, $modelSlug, $versionName);

        // Get candidate versions (eager loaded to avoid lazy loading violation)
        if ($model) {
            $candidates = Version::where('car_model_id', $model->id)->get();
        } else {
            // Fallback: search across ALL versions in the brand
            $candidates = Version::whereHas('carModel', fn ($q) => $q->where('brand_id', $brand->id))->get();
        }

        if ($candidates->isEmpty()) {
            return null;
        }

        return $this->scoreBestMatch($versionName, $candidates);
    }

    /**
     * Normalize a price to USD given the currency string.
     */
    public static function normalizeToUsd(float $price, string $currency, float $exchangeRate): float
    {
        $currency = strtolower(trim($currency));

        if ($currency === 'u$s' || $currency === 'usd') {
            return round($price, 2);
        }

        return round($price / $exchangeRate, 2);
    }

    private function resolveBrandSlug(string $brandName): string
    {
        $slug = Str::slug($brandName);

        return self::BRAND_ALIASES[$slug] ?? $slug;
    }

    private function findModel(int $brandId, string $modelSlug, ?string $versionName = null): ?CarModel
    {
        // 1. Exact slug match
        $model = CarModel::where('brand_id', $brandId)->where('slug', $modelSlug)->first();
        if ($model) {
            return $model;
        }

        // 2. Local slug starts with source slug (CC → passat-cc, saveiro → saveiro-pick-up)
        $model = CarModel::where('brand_id', $brandId)->where('slug', 'LIKE', "$modelSlug%")->first();
        if ($model) {
            return $model;
        }

        // 3. Strip hyphens and compare (Q2/q2 → Q 2/q-2)
        $stripped = str_replace('-', '', $modelSlug);
        $model = CarModel::where('brand_id', $brandId)
            ->get()
            ->first(fn (CarModel $m) => str_replace('-', '', $m->slug) === $stripped);

        if ($model) {
            return $model;
        }

        if ($versionName === null || trim($versionName) === '') {
            return null;
        }

        // 4. Fallback: infer model from tokens embedded in the version name (e.g. PORSCHE 718 BOXSTER)
        $context = trim($modelSlug . ' ' . Str::slug($versionName));
        $bestModel = null;
        $bestScore = 0;

        foreach (CarModel::where('brand_id', $brandId)->get() as $candidate) {
            $score = 0;
            $candidateSlug = $candidate->slug;

            if (str_contains($context, $candidateSlug)) {
                $score += 3;
            }

            $candidateTokens = array_filter(explode('-', $candidateSlug), fn (string $token) => strlen($token) >= 2);
            foreach ($candidateTokens as $token) {
                if (str_contains($context, $token)) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestModel = $candidate;
            }
        }

        return $bestScore > 0 ? $bestModel : null;
    }

    private function scoreBestMatch(string $acaraName, Collection $candidates): ?Version
    {
        $acaraDisplacement = $this->extractDisplacement($acaraName);
        $acaraHp = $this->extractHorsepower($acaraName);
        $acaraYear = $this->extractModelYear($acaraName);
        $acaraTransmission = $this->extractTransmission($acaraName);
        $acaraTokens = $this->tokenize($acaraName);

        $bestMatch = null;
        $bestScore = -1;

        foreach ($candidates as $candidate) {
            $localDisplacement = $this->extractDisplacement($candidate->name);
            $localYear = $this->extractModelYear($candidate->name);
            $localTransmission = $this->extractTransmission($candidate->name);
            $score = 0;

            // Displacement matching
            if ($acaraDisplacement !== null && $localDisplacement !== null) {
                if ($acaraDisplacement !== $localDisplacement) {
                    continue; // Different displacement = skip
                }
                $score += 3; // Displacement match bonus
            } elseif ($acaraDisplacement !== null && $localDisplacement === null) {
                continue; // ACARA has displacement, local doesn't
            }
            // If ACARA has no displacement (e.g., EV), allow token-only matching

            if ($acaraYear !== null && $localYear !== null) {
                if ($acaraYear !== $localYear) {
                    continue;
                }
                $score += 3;
            }

            if ($acaraTransmission !== null && $localTransmission !== null) {
                if ($acaraTransmission['family'] !== $localTransmission['family']) {
                    continue;
                }

                if ($acaraTransmission['code'] === $localTransmission['code']) {
                    $score += 2;
                } else {
                    $score += 1;
                }
            }

            $localTokens = $this->tokenize($candidate->name);

            // Horsepower match
            $localHp = $this->extractHorsepower($candidate->name);
            if ($acaraHp !== null && $localHp !== null && $acaraHp === $localHp) {
                $score += 2;
            }

            // Token overlap (exact + partial substring matching)
            $score += $this->scoreTokenOverlap($acaraTokens, $localTokens);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $candidate;
            }
        }

        // Minimum threshold: need at least 1 point of evidence
        return $bestScore >= 1 ? $bestMatch : null;
    }

    /**
     * Score token overlap between ACARA and local tokens.
     * Supports exact matches and partial substring matches (tiptronic → tipt).
     */
    private function scoreTokenOverlap(array $acaraTokens, array $localTokens): int
    {
        $score = 0;
        $matchedLocal = [];

        foreach ($acaraTokens as $aToken) {
            foreach ($localTokens as $i => $lToken) {
                if (isset($matchedLocal[$i])) {
                    continue;
                }

                if ($aToken === $lToken) {
                    $score += 1;
                    $matchedLocal[$i] = true;
                    break;
                }

                // Partial: one contains the other (tiptronic/tipt, cuero/cro)
                if (strlen($aToken) >= 3 && strlen($lToken) >= 3) {
                    if (str_contains($aToken, $lToken) || str_contains($lToken, $aToken)) {
                        $score += 1;
                        $matchedLocal[$i] = true;
                        break;
                    }
                }
            }
        }

        return $score;
    }

    /**
     * Extract displacement from version name. Handles both "1.8" and "1,8" formats.
     * Also handles "1.8T" (no space after number).
     */
    private function extractDisplacement(string $name): ?string
    {
        $normalized = str_replace(',', '.', $name);

        if (preg_match('/(\d+\.\d+)/', $normalized, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract horsepower from version name. Handles "200cv", "200CV", "(200cv)".
     */
    private function extractHorsepower(string $name): ?string
    {
        if (preg_match('/(\d{2,3})\s*cv\b/i', $name, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Tokenize a version name for comparison.
     * Normalizes and returns significant tokens.
     */
    private function tokenize(string $name): array
    {
        // Normalize: lowercase, comma→dot, strip parens, strip door prefix
        $normalized = strtolower($name);
        $normalized = str_replace(',', '.', $normalized);
        $normalized = preg_replace('/\([^)]*\)/', '', $normalized);
        $normalized = preg_replace('/^\d+p\s+/', '', $normalized); // Strip "4P " prefix
        $normalized = preg_replace('/l\/(\d{2})\b/i', ' ', $normalized); // Strip "L/25" hints handled separately

        // Split and filter
        $tokens = preg_split('/[\s\/\-]+/', $normalized);
        $tokens = array_filter($tokens, fn (string $t) => strlen($t) >= 2);
        $tokens = array_map(fn (string $token) => $this->normalizeTransmissionToken($token), $tokens);

        // Remove displacement and pure numbers (already matched separately)
        $tokens = array_filter($tokens, function (string $t) {
            if (preg_match('/^\d+\.\d+[a-z]?$/', $t)) {
                return false;
            } // "1.8", "1.8t"
            if (preg_match('/^\d+cv$/i', $t)) {
                return false;
            } // "200cv"
            if (preg_match('/^\d+$/', $t)) {
                return false;
            } // pure numbers

            return true;
        });

        return array_values(array_unique($tokens));
    }

    private function extractModelYear(string $name): ?int
    {
        if (preg_match('/l\/(\d{2})\b/i', $name, $matches)) {
            return 2000 + (int) $matches[1];
        }

        if (preg_match('/\b(20\d{2})\b/', $name, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * @return array{code: string, family: string}|null
     */
    private function extractTransmission(string $name): ?array
    {
        $normalized = strtolower(str_replace([',', ' '], ['', ''], $name));

        foreach ([
            'edct' => 'auto',
            'cvt' => 'auto',
            'dsg' => 'auto',
            'tipt' => 'auto',
            'tiptronic' => 'auto',
            'stronic' => 'auto',
        ] as $needle => $family) {
            if (str_contains($normalized, $needle)) {
                return ['code' => $needle === 'tiptronic' ? 'tipt' : $needle, 'family' => $family];
            }
        }

        if (preg_match('/(?:at|a)(\d)\b/', $normalized, $matches) || preg_match('/\b(\d)at\b/', $normalized, $matches)) {
            return ['code' => $matches[1] . 'at', 'family' => 'auto'];
        }

        if (preg_match('/(?:mt|m)(\d)\b/', $normalized, $matches) || preg_match('/\b(\d)mt\b/', $normalized, $matches)) {
            return ['code' => $matches[1] . 'mt', 'family' => 'manual'];
        }

        if (preg_match('/\baut\b/', $normalized) || preg_match('/\bat\b/', $normalized)) {
            return ['code' => 'at', 'family' => 'auto'];
        }

        if (preg_match('/\bman\b/', $normalized) || preg_match('/\bmt\b/', $normalized)) {
            return ['code' => 'mt', 'family' => 'manual'];
        }

        return null;
    }

    private function normalizeTransmissionToken(string $token): string
    {
        $token = strtolower($token);

        if (preg_match('/^(?:at|a)(\d)$/', $token, $matches)) {
            return $matches[1] . 'at';
        }

        if (preg_match('/^(?:mt|m)(\d)$/', $token, $matches)) {
            return $matches[1] . 'mt';
        }

        if ($token === 'aut') {
            return 'at';
        }

        if ($token === 'tiptronic') {
            return 'tipt';
        }

        if ($token === 's-tronic') {
            return 'stronic';
        }

        return $token;
    }
}
