<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\InfoautoCatalog;
use App\Models\SourceVersionLink;
use App\Models\Version;

beforeEach(function () {
    $toyota = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $hilux = CarModel::create(['brand_id' => $toyota->id, 'name' => 'HILUX PICK UP', 'slug' => 'hilux-pick-up']);
    $this->version = Version::create(['car_model_id' => $hilux->id, 'name' => '2.8 DC 4X4 TDI SR']);
});

it('creates a suggested link for a matched catalog entry', function () {
    $catalog = InfoautoCatalog::factory()->create([
        'brand_name' => 'TOYOTA',
        'model_name' => 'HILUX PICK UP',
        'version_name_raw' => '2.8 DC 4X4 TDI SR',
    ]);

    $this->artisan('link:infoauto-products')->assertSuccessful();

    $link = SourceVersionLink::where('external_id', $catalog->external_id)->first();
    expect($link)->not->toBeNull()
        ->and($link->source_family)->toBe('infoauto')
        ->and($link->version_id)->toBe($this->version->id)
        ->and($link->status)->toBe('suggested');
});

it('skips persistence with --dry-run but reports matches', function () {
    InfoautoCatalog::factory()->create([
        'brand_name' => 'TOYOTA',
        'model_name' => 'HILUX PICK UP',
        'version_name_raw' => '2.8 DC 4X4 TDI SR',
    ]);

    $this->artisan('link:infoauto-products', ['--dry-run' => true])->assertSuccessful();

    expect(SourceVersionLink::count())->toBe(0);
});

it('is idempotent on re-run', function () {
    InfoautoCatalog::factory()->create([
        'brand_name' => 'TOYOTA',
        'model_name' => 'HILUX PICK UP',
        'version_name_raw' => '2.8 DC 4X4 TDI SR',
    ]);

    $this->artisan('link:infoauto-products')->assertSuccessful();
    $firstCount = SourceVersionLink::count();

    $this->artisan('link:infoauto-products')->assertSuccessful();

    expect(SourceVersionLink::count())->toBe($firstCount);
});

it('respects validated status and does not downgrade', function () {
    $catalog = InfoautoCatalog::factory()->create([
        'brand_name' => 'TOYOTA',
        'model_name' => 'HILUX PICK UP',
        'version_name_raw' => '2.8 DC 4X4 TDI SR',
    ]);

    SourceVersionLink::create([
        'source_family' => 'infoauto',
        'source_system' => $catalog->source_system,
        'external_id' => $catalog->external_id,
        'version_id' => $this->version->id,
        'status' => 'validated',
        'confidence' => 'high',
    ]);

    $this->artisan('link:infoauto-products')->assertSuccessful();

    $link = SourceVersionLink::where('external_id', $catalog->external_id)->first();
    expect($link->status)->toBe('validated');
});

it('filters by source_system option', function () {
    InfoautoCatalog::factory()->create([
        'source_system' => 'test_source_system',
        'brand_name' => 'TOYOTA',
        'model_name' => 'HILUX PICK UP',
        'version_name_raw' => '2.8 DC 4X4 TDI SR',
    ]);
    InfoautoCatalog::factory()->create([
        'source_system' => 'test_source_system_2',
        'brand_name' => 'TOYOTA',
        'model_name' => 'HILUX PICK UP',
        'version_name_raw' => '2.8 DC 4X4 TDI SR FAKE',
        'codia' => null,
        'product_id' => 9999,
    ]);

    $this->artisan('link:infoauto-products', ['--source-system' => 'test_source_system'])->assertSuccessful();

    $links = SourceVersionLink::all();
    $sources = $links->pluck('source_system')->unique()->values()->all();
    expect($sources)->toBe(['test_source_system', 'test_source_system_2']);
});
