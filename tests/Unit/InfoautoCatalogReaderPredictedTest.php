<?php

use App\Models\InfoautoCatalog;
use App\Models\InfoautoPriceHistory;
use App\Services\InfoautoCatalogReader;

it('prefers real over predicted when both exist for same year', function () {
    $catalog = InfoautoCatalog::factory()->create();

    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $catalog->id,
        'year' => 2023,
        'price_ars_thousands' => 15000,
        'origin' => 'predicted',
        'source' => 'predicted',
        'recorded_at' => '2026-04-16',
    ]);
    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $catalog->id,
        'year' => 2023,
        'price_ars_thousands' => 17000,
        'origin' => 'real',
        'source' => 'test',
        'recorded_at' => '2026-04-21',
    ]);

    $reader = app(InfoautoCatalogReader::class);
    $prices = $reader->getPricesFor("ia_{$catalog->id}");

    // Ordenados por year ASC; el mismo año aparece dos veces — real primero
    $year2023 = $prices->where('year', 2023);
    expect($year2023)->toHaveCount(2)
        ->and($year2023->first()->origin)->toBe('real');
});

it('returns only real rows when no predicted exists', function () {
    $catalog = InfoautoCatalog::factory()->create();
    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $catalog->id,
        'year' => 2020,
        'price_ars_thousands' => 12000,
        'origin' => 'real',
        'source' => 'test',
        'recorded_at' => '2026-04-21',
    ]);

    $reader = app(InfoautoCatalogReader::class);
    $prices = $reader->getPricesFor("ia_{$catalog->id}");

    expect($prices)->toHaveCount(1)
        ->and($prices->first()->origin)->toBe('real');
});
