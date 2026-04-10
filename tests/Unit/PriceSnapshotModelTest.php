<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\PriceSnapshot;
use App\Models\Version;
use Illuminate\Database\QueryException;

it('price snapshot belongs to version', function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '1.8 XEI CVT']);

    $snapshot = PriceSnapshot::create([
        'version_id' => $version->id,
        'year' => 2025,
        'price' => 35000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-28',
    ]);

    expect($snapshot->version->id)->toBe($version->id);
});

it('version has many price snapshots', function () {
    $brand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'RANGER', 'slug' => 'ranger']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '3.2 XLT']);

    PriceSnapshot::create([
        'version_id' => $version->id,
        'year' => 2025,
        'price' => 45000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-01',
    ]);

    PriceSnapshot::create([
        'version_id' => $version->id,
        'year' => 2025,
        'price' => 46000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-15',
    ]);

    expect($version->priceSnapshots)->toHaveCount(2);
});

it('price snapshot casts price year and recorded_at correctly', function () {
    $brand = Brand::create(['name' => 'VW', 'slug' => 'vw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'GOL', 'slug' => 'gol']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => 'TREND']);

    $snapshot = PriceSnapshot::create([
        'version_id' => $version->id,
        'year' => '2024',
        'price' => '28000.50',
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    $snapshot->refresh();

    expect($snapshot->year)->toBeInt()
        ->and($snapshot->price)->toBe('28000.50')
        ->and($snapshot->recorded_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($snapshot->getCasts())->toHaveKey('price', 'decimal:2')
        ->and($snapshot->getCasts())->toHaveKey('year', 'integer')
        ->and($snapshot->getCasts())->toHaveKey('recorded_at', 'date');
});

it('enforces unique constraint on version_id year source recorded_at', function () {
    $brand = Brand::create(['name' => 'BMW', 'slug' => 'bmw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'SERIE 3', 'slug' => 'serie-3']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '320i']);

    PriceSnapshot::create([
        'version_id' => $version->id,
        'year' => 2025,
        'price' => 55000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-28',
    ]);

    PriceSnapshot::create([
        'version_id' => $version->id,
        'year' => 2025,
        'price' => 56000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-28',
    ]);
})->throws(QueryException::class);

it('allows same version year and date with different source', function () {
    $brand = Brand::create(['name' => 'AUDI', 'slug' => 'audi']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'A3', 'slug' => 'a3']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '1.4 TFSI']);

    PriceSnapshot::create([
        'version_id' => $version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-28',
    ]);

    $acara = PriceSnapshot::create([
        'version_id' => $version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    expect($acara)->toBeInstanceOf(PriceSnapshot::class)
        ->and(PriceSnapshot::where('version_id', $version->id)->count())->toBe(2);
});

it('cascades delete when version is deleted', function () {
    $brand = Brand::create(['name' => 'FIAT', 'slug' => 'fiat']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'CRONOS', 'slug' => 'cronos']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '1.3 DRIVE']);

    PriceSnapshot::create([
        'version_id' => $version->id,
        'year' => 2025,
        'price' => 15000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-28',
    ]);

    $version->delete();

    expect(PriceSnapshot::count())->toBe(0);
});
