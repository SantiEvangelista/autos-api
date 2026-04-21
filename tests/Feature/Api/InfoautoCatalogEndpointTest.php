<?php

use App\Models\InfoautoCatalog;
use App\Models\InfoautoPriceHistory;

function seedCatalogWithPrices(): InfoautoCatalog
{
    $catalog = InfoautoCatalog::factory()->create([
        'codia' => 'codia-endpoint',
        'brand_name' => 'PEUGEOT',
        'model_name' => '2008',
        'version_name_raw' => '2008 1.0 T 200 ACTIVE CVT',
    ]);

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

it('returns prices for external id', function () {
    $catalog = seedCatalogWithPrices();

    $response = $this->getJson('/api/v1/infoauto/catalog/' . $catalog->external_id . '/prices');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.year', 0)
        ->assertJsonPath('data.0.origin', 'real');
});

it('returns 404 for unknown external id', function () {
    $response = $this->getJson('/api/v1/infoauto/catalog/ia_99999999/prices');

    $response->assertNotFound();
});

it('returns 404 when external id has invalid prefix', function () {
    $response = $this->getJson('/api/v1/infoauto/catalog/xx_123/prices');

    $response->assertNotFound();
});

it('returns 404 when external id has no prefix', function () {
    $response = $this->getJson('/api/v1/infoauto/catalog/123/prices');

    $response->assertNotFound();
});

it('returns 404 when external id suffix is non-numeric', function () {
    $response = $this->getJson('/api/v1/infoauto/catalog/ia_abc/prices');

    $response->assertNotFound();
});

it('includes price_ars_thousands in each entry', function () {
    $catalog = seedCatalogWithPrices();

    $response = $this->getJson('/api/v1/infoauto/catalog/' . $catalog->external_id . '/prices');

    $response->assertOk()
        ->assertJsonPath('data.0.price_ars_thousands', '44740.00');
});

it('includes meta with source_refs', function () {
    $catalog = seedCatalogWithPrices();

    $response = $this->getJson('/api/v1/infoauto/catalog/' . $catalog->external_id . '/prices');

    $response->assertOk()
        ->assertJsonPath('meta.external_id', $catalog->external_id)
        ->assertJsonPath('meta.brand', 'PEUGEOT')
        ->assertJsonPath('meta.model', '2008')
        ->assertJsonPath('meta.source_refs.codia', 'codia-endpoint')
        ->assertJsonPath('meta.source_refs.product_id', null);
});
