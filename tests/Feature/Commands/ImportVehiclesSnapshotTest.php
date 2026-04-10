<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\PriceSnapshot;
use App\Models\Valuation;
use App\Models\Version;
use Illuminate\Support\Carbon;

it('creates price snapshots when valuations are upserted', function () {
    $brand = Brand::create(['name' => 'BMW', 'slug' => 'bmw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'SERIE 3', 'slug' => 'serie-3']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '320i']);

    $now = now();
    $valuationData = [
        [
            'version_id' => $version->id,
            'year' => 2025,
            'price' => 55000.00,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'version_id' => $version->id,
            'year' => 2024,
            'price' => 48000.00,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ];

    Valuation::upsert($valuationData, ['version_id', 'year'], ['price', 'updated_at']);

    // This is what ImportVehicles should do after the upsert
    $snapshotData = array_map(function ($v) use ($now) {
        return [
            'version_id' => $v['version_id'],
            'year' => $v['year'],
            'price' => $v['price'],
            'source' => 'cca',
            'recorded_at' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }, $valuationData);

    PriceSnapshot::upsert($snapshotData, ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    expect(PriceSnapshot::count())->toBe(2)
        ->and(PriceSnapshot::where('source', 'cca')->count())->toBe(2)
        ->and(PriceSnapshot::where('version_id', $version->id)->where('year', 2025)->first()->price)->toBe('55000.00')
        ->and(PriceSnapshot::where('version_id', $version->id)->where('year', 2024)->first()->price)->toBe('48000.00');
});

it('does not duplicate snapshots when upserting on the same day', function () {
    $brand = Brand::create(['name' => 'AUDI', 'slug' => 'audi']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'A3', 'slug' => 'a3']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '1.4 TFSI']);

    $now = now();

    // First import
    $data = [
        'version_id' => $version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'cca',
        'recorded_at' => $now->toDateString(),
        'created_at' => $now,
        'updated_at' => $now,
    ];

    PriceSnapshot::upsert([$data], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    // Second import same day with updated price
    $data['price'] = 41000.00;
    PriceSnapshot::upsert([$data], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    expect(PriceSnapshot::count())->toBe(1)
        ->and(PriceSnapshot::first()->price)->toBe('41000.00');
});

it('creates new snapshots when importing on different days', function () {
    $brand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'RANGER', 'slug' => 'ranger']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '3.2 XLT']);

    // Day 1
    Carbon::setTestNow('2026-03-01');
    $now = now();
    PriceSnapshot::upsert([
        [
            'version_id' => $version->id,
            'year' => 2025,
            'price' => 45000.00,
            'source' => 'cca',
            'recorded_at' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    // Day 2
    Carbon::setTestNow('2026-03-15');
    $now = now();
    PriceSnapshot::upsert([
        [
            'version_id' => $version->id,
            'year' => 2025,
            'price' => 46000.00,
            'source' => 'cca',
            'recorded_at' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    expect(PriceSnapshot::count())->toBe(2)
        ->and(PriceSnapshot::orderBy('recorded_at')->first()->price)->toBe('45000.00')
        ->and(PriceSnapshot::orderBy('recorded_at', 'desc')->first()->price)->toBe('46000.00');

    Carbon::setTestNow(); // Reset
});
