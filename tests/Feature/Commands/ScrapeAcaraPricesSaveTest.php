<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\PriceSnapshot;
use App\Models\Version;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Create local DB data that matches what ACARA API will return
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    Version::create(['car_model_id' => $model->id, 'name' => '1.8 XEI CVT']);

    $brand2 = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $model2 = CarModel::create(['brand_id' => $brand2->id, 'name' => 'RANGER', 'slug' => 'ranger']);
    Version::create(['car_model_id' => $model2->id, 'name' => '3.2 XLT']);
});

function fakeAcaraApi(array $brands, array $models, array $versions, array $prices): void
{
    Http::fake([
        '*/brand-list*' => Http::response(['data' => $brands]),
        '*/model-list*' => Http::response(['data' => $models]),
        '*/version-list*' => Http::response(['data' => $versions]),
        '*/get-vehicules*' => Http::response(['data' => $prices]),
    ]);
}

it('saves ACARA prices to price_snapshots when --save flag is used', function () {
    fakeAcaraApi(
        brands: [['id' => 1, 'name' => 'TOYOTA']],
        models: [['id' => 10, 'name' => 'COROLLA']],
        versions: [['id' => 100, 'name' => '1.8 XEI CVT']],
        prices: [[
            'version' => '1.8 XEI CVT',
            'moneda' => 'u$s',
            'precios_por_año' => [2025 => 30000, 2024 => 27000],
        ]]
    );

    $this->artisan('scrape:acara-prices', [
        '--save' => true,
        '--delay' => 0,
        '--exchange-rate' => 1400,
        '--output' => storage_path('app/test_acara.csv'),
    ])->assertSuccessful();

    expect(PriceSnapshot::where('source', 'acara')->count())->toBe(2);

    $snapshot2025 = PriceSnapshot::where('source', 'acara')
        ->whereHas('version', fn ($q) => $q->where('name', '1.8 XEI CVT'))
        ->where('year', 2025)
        ->first();

    expect($snapshot2025)->not->toBeNull()
        ->and($snapshot2025->price)->toBe('30000.00')
        ->and($snapshot2025->recorded_at->toDateString())->toBe(now()->toDateString());
});

it('does not save to DB when --save flag is absent', function () {
    fakeAcaraApi(
        brands: [['id' => 1, 'name' => 'TOYOTA']],
        models: [['id' => 10, 'name' => 'COROLLA']],
        versions: [['id' => 100, 'name' => '1.8 XEI CVT']],
        prices: [[
            'version' => '1.8 XEI CVT',
            'moneda' => 'u$s',
            'precios_por_año' => [2025 => 30000],
        ]]
    );

    $this->artisan('scrape:acara-prices', [
        '--delay' => 0,
        '--output' => storage_path('app/test_acara_nosave.csv'),
    ])->assertSuccessful();

    expect(PriceSnapshot::count())->toBe(0);
});

it('normalizes ARS prices to USD when saving', function () {
    fakeAcaraApi(
        brands: [['id' => 1, 'name' => 'TOYOTA']],
        models: [['id' => 10, 'name' => 'COROLLA']],
        versions: [['id' => 100, 'name' => '1.8 XEI CVT']],
        prices: [[
            'version' => '1.8 XEI CVT',
            'moneda' => '$',
            'precios_por_año' => [2025 => 42000000],
        ]]
    );

    $this->artisan('scrape:acara-prices', [
        '--save' => true,
        '--delay' => 0,
        '--exchange-rate' => 1400,
        '--output' => storage_path('app/test_acara_ars.csv'),
    ])->assertSuccessful();

    $snapshot = PriceSnapshot::where('source', 'acara')->first();

    expect($snapshot)->not->toBeNull()
        ->and((float) $snapshot->price)->toBe(30000.00); // 42000000 / 1400
});

it('skips unmatched ACARA entries when saving', function () {
    fakeAcaraApi(
        brands: [['id' => 1, 'name' => 'UNKNOWN BRAND']],
        models: [['id' => 10, 'name' => 'UNKNOWN MODEL']],
        versions: [['id' => 100, 'name' => 'UNKNOWN VERSION']],
        prices: [[
            'version' => 'UNKNOWN VERSION',
            'moneda' => 'u$s',
            'precios_por_año' => [2025 => 50000],
        ]]
    );

    $this->artisan('scrape:acara-prices', [
        '--save' => true,
        '--delay' => 0,
        '--exchange-rate' => 1400,
        '--output' => storage_path('app/test_acara_unmatched.csv'),
    ])->assertSuccessful();

    expect(PriceSnapshot::count())->toBe(0);
});

it('does not duplicate snapshots on re-scrape same day', function () {
    fakeAcaraApi(
        brands: [['id' => 1, 'name' => 'TOYOTA']],
        models: [['id' => 10, 'name' => 'COROLLA']],
        versions: [['id' => 100, 'name' => '1.8 XEI CVT']],
        prices: [[
            'version' => '1.8 XEI CVT',
            'moneda' => 'u$s',
            'precios_por_año' => [2025 => 30000],
        ]]
    );

    // First scrape
    $this->artisan('scrape:acara-prices', [
        '--save' => true,
        '--delay' => 0,
        '--exchange-rate' => 1400,
        '--output' => storage_path('app/test_acara_dup1.csv'),
    ])->assertSuccessful();

    // Second scrape same day
    $this->artisan('scrape:acara-prices', [
        '--save' => true,
        '--delay' => 0,
        '--exchange-rate' => 1400,
        '--output' => storage_path('app/test_acara_dup2.csv'),
    ])->assertSuccessful();

    expect(PriceSnapshot::where('source', 'acara')->count())->toBe(1);
});

it('keeps USD prices as-is when saving', function () {
    fakeAcaraApi(
        brands: [['id' => 1, 'name' => 'FORD']],
        models: [['id' => 20, 'name' => 'RANGER']],
        versions: [['id' => 200, 'name' => '3.2 XLT']],
        prices: [[
            'version' => '3.2 XLT',
            'moneda' => 'u$s',
            'precios_por_año' => [2025 => 55000],
        ]]
    );

    $this->artisan('scrape:acara-prices', [
        '--save' => true,
        '--delay' => 0,
        '--exchange-rate' => 1400,
        '--output' => storage_path('app/test_acara_usd.csv'),
    ])->assertSuccessful();

    $snapshot = PriceSnapshot::where('source', 'acara')->first();

    expect($snapshot)->not->toBeNull()
        ->and($snapshot->price)->toBe('55000.00');
});
