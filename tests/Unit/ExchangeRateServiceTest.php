<?php

use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('returns official sell rate from bluelytics', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1417.0, 'value_buy' => 1366.0, 'value_avg' => 1391.5],
            'blue' => ['value_sell' => 1430.0, 'value_buy' => 1410.0],
        ]),
    ]);

    $service = new ExchangeRateService();
    $rate = $service->getOfficialSellRate();

    expect($rate)->toBe(1417.0);
    Http::assertSentCount(1);
});

it('returns null when api fails', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response(null, 500),
    ]);

    Cache::flush();
    $service = new ExchangeRateService();
    $rate = $service->getOfficialSellRate();

    expect($rate)->toBeNull();
});

it('caches the rate', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1400.0],
        ]),
    ]);

    Cache::flush();
    $service = new ExchangeRateService();

    $service->getOfficialSellRate();
    $service->getOfficialSellRate();

    Http::assertSentCount(1);
});
