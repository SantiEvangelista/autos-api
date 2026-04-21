<?php

use App\Models\InfoautoCatalog;
use App\Models\InfoautoPriceHistory;
use App\Services\InfoautoCatalogReader;

it('returns empty collection when search has no matches', function () {
    InfoautoCatalog::factory()->create();

    $reader = app(InfoautoCatalogReader::class);
    $results = $reader->search('ferrari');

    expect($results->items())->toBeEmpty();
});

it('matches by brand name ilike', function () {
    InfoautoCatalog::factory()->create(['brand_name' => 'TOYOTA', 'model_name' => 'COROLLA']);

    $reader = app(InfoautoCatalogReader::class);
    $results = $reader->search('toyota');

    expect($results->items())->toHaveCount(1);
});

it('matches by model name ilike', function () {
    InfoautoCatalog::factory()->create(['brand_name' => 'PEUGEOT', 'model_name' => '2008']);

    $reader = app(InfoautoCatalogReader::class);
    $results = $reader->search('2008');

    expect($results->items())->toHaveCount(1);
});

it('matches by version name ilike', function () {
    InfoautoCatalog::factory()->create(['version_name_raw' => 'SPECIAL EDITION GT']);

    $reader = app(InfoautoCatalogReader::class);
    $results = $reader->search('special edition');

    expect($results->items())->toHaveCount(1);
});

it('returns one row per codia not consolidated', function () {
    // Anti-regresión Peugeot 2008
    InfoautoCatalog::factory()->create(['codia' => 'c1', 'brand_name' => 'PEUGEOT', 'model_name' => '2008', 'version_name_raw' => '2008 1.0 T 200 ACTIVE CVT']);
    InfoautoCatalog::factory()->create(['codia' => 'c2', 'brand_name' => 'PEUGEOT', 'model_name' => '2008', 'version_name_raw' => '2008 1.0 T 200 ALLURE CVT']);
    InfoautoCatalog::factory()->create(['codia' => 'c3', 'brand_name' => 'PEUGEOT', 'model_name' => '2008', 'version_name_raw' => '2008 1.0 T 200 GT CVT']);

    $reader = app(InfoautoCatalogReader::class);
    $results = $reader->search('peugeot 2008');

    expect($results->items())->toHaveCount(3);
    $codias = collect($results->items())->pluck('codia')->all();
    expect($codias)->toContain('c1', 'c2', 'c3');
});

it('preserves raw version text from infoauto', function () {
    InfoautoCatalog::factory()->create(['brand_name' => 'PEUGEOT', 'model_name' => '208', 'version_name_raw' => '208 L/24 1.6 ALLURE AT']);

    $reader = app(InfoautoCatalogReader::class);
    $results = $reader->search('208 allure');

    expect($results->items())->toHaveCount(1)
        ->and($results->items()[0]->version_name_raw)->toBe('208 L/24 1.6 ALLURE AT');
});

it('returns null when external id not found', function () {
    $reader = app(InfoautoCatalogReader::class);

    expect($reader->findByExternalId('ia_99999999'))->toBeNull();
});

it('returns null when external id has invalid prefix', function () {
    $reader = app(InfoautoCatalogReader::class);

    expect($reader->findByExternalId('xx_123'))->toBeNull();
    expect($reader->findByExternalId('ia_abc'))->toBeNull();
    expect($reader->findByExternalId('123'))->toBeNull();
});

it('finds catalog entry by valid external id', function () {
    $catalog = InfoautoCatalog::factory()->create();

    $reader = app(InfoautoCatalogReader::class);
    $found = $reader->findByExternalId("ia_{$catalog->id}");

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($catalog->id);
});

it('returns price history entries for an external id', function () {
    $catalog = InfoautoCatalog::factory()->create();
    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $catalog->id,
        'year' => 0,
        'price_ars_thousands' => 44740,
        'origin' => 'real',
        'source' => 'autazo',
        'recorded_at' => '2026-04-10',
    ]);

    $reader = app(InfoautoCatalogReader::class);
    $prices = $reader->getPricesFor("ia_{$catalog->id}");

    expect($prices)->toHaveCount(1)
        ->and($prices[0]->year)->toBe(0);
});
