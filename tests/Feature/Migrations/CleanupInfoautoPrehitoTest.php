<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\PriceSnapshot;
use App\Models\Version;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'HILUX', 'slug' => 'hilux']);
    $this->version = Version::create(['car_model_id' => $model->id, 'name' => 'SRX']);
});

it('deletes only pre-feat infoauto snapshots with year > 0', function () {
    $preHito0km = PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 0,
        'price' => 80000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-13',
    ]);

    $preHitoUsed = PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 55000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-01',
    ]);

    $postHitoUsed = PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 50000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $postHito0km = PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 0,
        'price' => 85000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    DB::table('price_snapshots')
        ->where('source', 'infoauto')
        ->where('recorded_at', '<', '2026-04-17')
        ->where('year', '>', 0)
        ->delete();

    expect(PriceSnapshot::find($preHito0km->id))->not->toBeNull();
    expect(PriceSnapshot::find($preHitoUsed->id))->toBeNull();
    expect(PriceSnapshot::find($postHitoUsed->id))->not->toBeNull();
    expect(PriceSnapshot::find($postHito0km->id))->not->toBeNull();
});

it('does not touch snapshots from other sources', function () {
    $caraPreHito = PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 40000.00,
        'source' => 'cca',
        'recorded_at' => '2026-04-01',
    ]);

    $acaraPreHito = PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-04-01',
    ]);

    $predictedPreHito = PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 36000.00,
        'source' => 'infoauto_predicted',
        'recorded_at' => '2026-04-01',
    ]);

    DB::table('price_snapshots')
        ->where('source', 'infoauto')
        ->where('recorded_at', '<', '2026-04-17')
        ->where('year', '>', 0)
        ->delete();

    expect(PriceSnapshot::find($caraPreHito->id))->not->toBeNull();
    expect(PriceSnapshot::find($acaraPreHito->id))->not->toBeNull();
    expect(PriceSnapshot::find($predictedPreHito->id))->not->toBeNull();
});
