<?php

use App\Models\InfoautoCatalog;
use App\Models\InfoautoPriceHistory;

function seedCatalog(array $attrs = []): InfoautoCatalog
{
    $catalog = InfoautoCatalog::factory()->create(array_merge([
        'brand_name' => 'PEUGEOT',
        'model_name' => '2008',
        'version_name_raw' => '2008 1.0 T 200 ACTIVE CVT',
    ], $attrs));

    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $catalog->id,
        'year' => 0,
        'price_ars_thousands' => 44740,
        'origin' => 'real',
        'source' => 'test',
        'recorded_at' => '2026-04-10',
    ]);

    return $catalog;
}

it('returns infoauto results from catalog read model', function () {
    seedCatalog();

    $response = $this->getJson('/api/v1/search?q=peugeot&source=infoauto');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.brand', 'PEUGEOT');

    expect($response->json('data.0'))->not->toHaveKey('version_id')
        ->and($response->json('data.0'))->toHaveKey('external_id')
        ->and($response->json('data.0.external_id'))->toStartWith('ia_');
});

it('returns separate rows for peugeot 2008 variants', function () {
    // Anti-regresión bug apilado del matcher legacy
    seedCatalog(['codia' => 'c1', 'version_name_raw' => '2008 1.0 T 200 ACTIVE CVT']);
    seedCatalog(['codia' => 'c2', 'version_name_raw' => '2008 1.0 T 200 ALLURE CVT']);
    seedCatalog(['codia' => 'c3', 'version_name_raw' => '2008 1.0 T 200 GT CVT']);

    $response = $this->getJson('/api/v1/search?q=peugeot 2008&source=infoauto');

    $response->assertOk()->assertJsonCount(3, 'data');
    $externalIds = collect($response->json('data'))->pluck('external_id')->all();
    expect(count(array_unique($externalIds)))->toBe(3);
});

it('shows exact raw text for version', function () {
    seedCatalog(['version_name_raw' => '208 L/24 1.6 ALLURE AT']);

    $response = $this->getJson('/api/v1/search?q=208 allure&source=infoauto');

    $response->assertOk()
        ->assertJsonPath('data.0.version', '208 L/24 1.6 ALLURE AT');
});

it('returns source_refs with codia when available', function () {
    seedCatalog(['codia' => 'c-refs-1']);

    $response = $this->getJson('/api/v1/search?q=peugeot&source=infoauto');

    $response->assertOk()
        ->assertJsonPath('data.0.source_refs.codia', 'c-refs-1')
        ->assertJsonPath('data.0.source_refs.product_id', null);
});

it('returns price in USD computed on-the-fly when price_usd is null and currency=USD', function () {
    $catalog = InfoautoCatalog::factory()->create([
        'codia' => 'codia-search-usd',
        'brand_name' => 'VOLKSWAGEN',
        'model_name' => 'Gol',
        'version_name_raw' => 'GOL 1.4 3 P POWER',
    ]);
    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $catalog->id,
        'year' => 2015,
        'price_ars_thousands' => 10200,
        'origin' => 'real',
        'source' => 'test',
        'recorded_at' => '2026-04-27',
    ]);

    \Illuminate\Support\Facades\Cache::put('exchange_rate:usd_ars_oficial', 1000.0, 60);

    $response = $this->getJson('/api/v1/search?q=GOL+POWER&source=infoauto&currency=USD');

    $response->assertOk()
        ->assertJsonPath('data.0.price', 10200)
        ->assertJsonPath('data.0.price_raw_ars', 10200000);
});

it('returns price in ARS literal when currency=ARS', function () {
    $catalog = InfoautoCatalog::factory()->create([
        'codia' => 'codia-search-ars',
        'version_name_raw' => 'GOL 1.4 3 P POWER',
    ]);
    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $catalog->id,
        'year' => 2015,
        'price_ars_thousands' => 10200,
        'origin' => 'real',
        'source' => 'test',
        'recorded_at' => '2026-04-27',
    ]);

    $response = $this->getJson('/api/v1/search?q=GOL+POWER&source=infoauto&currency=ARS');

    $response->assertOk()
        ->assertJsonPath('data.0.price', 10200000)
        ->assertJsonPath('data.0.price_raw_ars', 10200000);
});
