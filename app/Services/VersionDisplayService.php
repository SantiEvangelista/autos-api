<?php

namespace App\Services;

class VersionDisplayService
{
    private static ?array $config = null;

    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = config('version-display');
        }

        return self::$config;
    }

    public static function humanize(string $name): string
    {
        $config = self::config();

        // 1. Diesel tokens → "Diesel"
        foreach ($config['diesel_tokens'] as $token) {
            $name = preg_replace('/\b' . preg_quote($token, '/') . '\b/i', 'Diesel', $name);
        }

        // 2. Abbreviations → reemplazo directo
        foreach ($config['abbreviations'] as $abbr => $replacement) {
            $name = preg_replace('/\b' . preg_quote($abbr, '/') . '\b/i', $replacement, $name);
        }

        // 3. HIGH → Highline (contextual)
        //    NO expandir cuando: HIGHLINE, HIGH COUNTRY, HIGH TECH, HIGH SECURITY, HIGH-POWER, PASSION HIGH
        $name = preg_replace(
            '/(?<!PASSION\s)\bHIGH\b(?!\s*(?:LINE|COUNTRY|TECH|SECURITY))(?!-)/i',
            'Highline',
            $name
        );

        // 4. SE → Serie Especial (solo cuando Gen.2 está presente, solo al final)
        if (str_contains($name, 'Gen.2')) {
            $name = preg_replace('/\bSE\s+(\d{4})\s*$/', 'Serie Especial $1', $name);
            $name = preg_replace('/\bSE\s*$/', 'Serie Especial', $name);
        }

        // 5. Title Case
        $name = self::applyTitleCase($name, $config['preserve_case']);

        return $name;
    }

    private static function applyTitleCase(string $name, array $preserveTokens): string
    {
        $preserveMap = [];
        foreach ($preserveTokens as $token) {
            $preserveMap[mb_strtolower($token)] = $token;
        }

        $tokens = preg_split('/(\s+)/', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = [];

        foreach ($tokens as $token) {
            if (trim($token) === '') {
                $result[] = $token;
                continue;
            }

            $lower = mb_strtolower($token);

            if (isset($preserveMap[$lower])) {
                // Token en la lista de preserve → mantener casing definido
                $result[] = $preserveMap[$lower];
            } elseif (preg_match('/^\d+[.,]\d+$/', $token)) {
                // Cilindrada: 1,8 / 2.0 / 3,0
                $result[] = $token;
            } elseif (preg_match('/^\d+CV$/i', $token)) {
                // Potencia: 180CV, 258CV
                $result[] = strtoupper($token);
            } elseif (preg_match('/^\d{4}$/', $token)) {
                // Año: 2023, 2026
                $result[] = $token;
            } elseif (str_contains($token, '-')) {
                // Tokens con guión: DI-D, GR-S, F-TRUCK
                $result[] = implode('-', array_map(
                    fn ($part) => ucfirst(mb_strtolower($part)),
                    explode('-', $token)
                ));
            } else {
                // Default: Title Case
                $result[] = ucfirst(mb_strtolower($token));
            }
        }

        return implode('', $result);
    }
}
