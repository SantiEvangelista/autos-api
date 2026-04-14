<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    private const CACHE_KEY_OFICIAL = 'exchange_rate:usd_ars_oficial';
    private const CACHE_KEY_BLUE = 'exchange_rate:usd_ars_blue';
    private const CACHE_TTL_OFICIAL = 900; // 15 minutes
    private const CACHE_TTL_BLUE = 604800; // 1 week
    private const API_URL = 'https://api.bluelytics.com.ar/v2/latest';

    public function getOfficialSellRate(): ?float
    {
        $cached = Cache::get(self::CACHE_KEY_OFICIAL);

        if ($cached !== null) {
            return $cached;
        }

        $rates = $this->fetchRates();

        if ($rates && $rates['oficial'] !== null) {
            Cache::put(self::CACHE_KEY_OFICIAL, $rates['oficial'], self::CACHE_TTL_OFICIAL);

            // Also cache blue if available and not already cached
            if ($rates['blue'] !== null && !Cache::has(self::CACHE_KEY_BLUE)) {
                Cache::put(self::CACHE_KEY_BLUE, $rates['blue'], self::CACHE_TTL_BLUE);
            }

            return $rates['oficial'];
        }

        return null;
    }

    public function getBlueSellRate(): ?float
    {
        $cached = Cache::get(self::CACHE_KEY_BLUE);

        if ($cached !== null) {
            return $cached;
        }

        $rates = $this->fetchRates();

        if ($rates && $rates['blue'] !== null) {
            Cache::put(self::CACHE_KEY_BLUE, $rates['blue'], self::CACHE_TTL_BLUE);

            // Also cache oficial if available and not already cached
            if ($rates['oficial'] !== null && !Cache::has(self::CACHE_KEY_OFICIAL)) {
                Cache::put(self::CACHE_KEY_OFICIAL, $rates['oficial'], self::CACHE_TTL_OFICIAL);
            }

            return $rates['blue'];
        }

        return null;
    }

    /**
     * @return array{oficial: float|null, blue: float|null}
     */
    public function getAllRates(): array
    {
        return [
            'oficial' => $this->getOfficialSellRate(),
            'blue' => $this->getBlueSellRate(),
        ];
    }

    /**
     * @return array{oficial: float|null, blue: float|null}|null
     */
    private function fetchRates(): ?array
    {
        try {
            $response = Http::connectTimeout(2)
                ->timeout(5)
                ->retry(3, 100)
                ->get(self::API_URL);

            if ($response->failed()) {
                return null;
            }

            return [
                'oficial' => $response->json('oficial.value_sell'),
                'blue' => $response->json('blue.value_sell'),
            ];
        } catch (\Throwable $e) {
            Log::warning('Exchange rate fetch failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
