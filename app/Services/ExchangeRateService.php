<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    private const CACHE_KEY = 'exchange_rate:usd_ars_oficial';
    private const CACHE_TTL_SECONDS = 900; // 15 minutes
    private const API_URL = 'https://api.bluelytics.com.ar/v2/latest';

    public function getOfficialSellRate(): ?float
    {
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached !== null) {
            return $cached;
        }

        $rate = $this->fetchRate();

        if ($rate !== null) {
            Cache::put(self::CACHE_KEY, $rate, self::CACHE_TTL_SECONDS);
        }

        return $rate;
    }

    private function fetchRate(): ?float
    {
        try {
            $response = Http::connectTimeout(2)
                ->timeout(5)
                ->retry(3, 100)
                ->get(self::API_URL);

            if ($response->failed()) {
                return null;
            }

            return $response->json('oficial.value_sell');
        } catch (\Exception) {
            return null;
        }
    }
}
