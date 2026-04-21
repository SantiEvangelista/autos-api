<?php

namespace App\Services;

class RulesEngineService
{
    private array $stripSuffixes;

    private array $replace;

    private array $brandAliases;

    private array $modelAliases;

    public function __construct(?array $rules = null)
    {
        $rules ??= config('mapper_rules');
        $this->stripSuffixes = $rules['strip_suffixes'] ?? [];
        $this->replace = $rules['replace'] ?? [];
        $this->brandAliases = $rules['brand_aliases'] ?? [];
        $this->modelAliases = $rules['model_aliases'] ?? [];
    }

    /**
     * Lowercase, collapse multiple spaces, trim.
     */
    public function normalize(string $name): string
    {
        $name = mb_strtolower($name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return trim($name);
    }

    /**
     * Apply exact string replacements from config.
     */
    public function applyReplacements(string $name): string
    {
        foreach ($this->replace as $search => $replacement) {
            $name = str_replace($search, $replacement, $name);
        }

        return $name;
    }

    /**
     * Strip known suffixes from the end of a string, iteratively.
     */
    public function stripSuffixes(string $name): string
    {
        $changed = true;

        while ($changed) {
            $changed = false;
            $trimmed = rtrim($name);

            foreach ($this->stripSuffixes as $suffix) {
                $suffixWithSpace = ' ' . $suffix;

                if (str_ends_with(strtoupper($trimmed), strtoupper($suffixWithSpace))) {
                    $name = rtrim(substr($trimmed, 0, -strlen($suffixWithSpace)));
                    $changed = true;

                    break;
                }
            }
        }

        return $name;
    }

    /**
     * Strip the model/group name prefix from a version description.
     */
    public function stripModelPrefix(string $versionDescription, string $groupName): string
    {
        $pattern = '/^' . preg_quote($groupName, '/') . '\s+/i';
        $result = preg_replace($pattern, '', $versionDescription, 1);

        return $result ?? $versionDescription;
    }

    /**
     * Resolve InfoAuto brand name to CCA brand name via aliases.
     */
    public function resolveBrand(string $infoautoBrand): string
    {
        return $this->brandAliases[$infoautoBrand] ?? $infoautoBrand;
    }

    /**
     * Resolve InfoAuto group name to CCA model name via aliases.
     * Returns null if the model should be skipped.
     */
    public function resolveModel(string $brand, string $infoautoGroup): ?string
    {
        $brandAliases = $this->modelAliases[$brand] ?? [];

        if (array_key_exists($infoautoGroup, $brandAliases)) {
            return $brandAliases[$infoautoGroup];
        }

        return $infoautoGroup;
    }

    /**
     * Convert dot-decimal displacement to comma format (InfoAuto → CCA).
     * "1.0T" → "1,0T", "2.8 TDI" → "2,8 TDI"
     */
    public function normalizeDisplacement(string $name): string
    {
        return preg_replace('/(\d)\.(\d)/', '$1,$2', $name) ?? $name;
    }
}
