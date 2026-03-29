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
        $model = $this->findModel($brand->id, $modelSlug);

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

    private function findModel(int $brandId, string $modelSlug): ?CarModel
    {
        // 1. Exact slug match
        $model = CarModel::where('brand_id', $brandId)->where('slug', $modelSlug)->first();
        if ($model) {
            return $model;
        }

        // 2. Local slug starts with ACARA slug (CC → passat-cc, saveiro → saveiro-pick-up)
        $model = CarModel::where('brand_id', $brandId)->where('slug', 'LIKE', "$modelSlug%")->first();
        if ($model) {
            return $model;
        }

        // 3. Strip hyphens and compare (Q2/q2 → Q 2/q-2)
        $stripped = str_replace('-', '', $modelSlug);
        $model = CarModel::where('brand_id', $brandId)
            ->get()
            ->first(fn (CarModel $m) => str_replace('-', '', $m->slug) === $stripped);

        return $model;
    }

    private function scoreBestMatch(string $acaraName, Collection $candidates): ?Version
    {
        $acaraDisplacement = $this->extractDisplacement($acaraName);
        $acaraHp = $this->extractHorsepower($acaraName);
        $acaraTokens = $this->tokenize($acaraName);

        $bestMatch = null;
        $bestScore = -1;

        foreach ($candidates as $candidate) {
            $localDisplacement = $this->extractDisplacement($candidate->name);
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

        // Split and filter
        $tokens = preg_split('/[\s\/\-]+/', $normalized);
        $tokens = array_filter($tokens, fn (string $t) => strlen($t) >= 2);

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
}
