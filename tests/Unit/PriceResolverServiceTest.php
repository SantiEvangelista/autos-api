<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\PriceSnapshot;
use App\Models\Version;
use App\Services\PriceResolverService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'HILUX', 'slug' => 'hilux']);
    $this->version = Version::create(['car_model_id' => $model->id, 'name' => 'D/C 2.8 TDi SRX']);
    $this->service = app(PriceResolverService::class);
});

it('returns empty collection when no snapshots exist', function () {
    $result = $this->service->resolveInfoautoPrices($this->version->id, [2024, 2025]);

    expect($result)->toBeEmpty();
});

it('prefers real infoauto post-feat over predicted for the same year', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 45000.00,
        'raw_price_ars_thousands' => 64000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 42000.00,
        'raw_price_ars_thousands' => 58800.00,
        'source' => 'infoauto_predicted',
        'recorded_at' => '2026-04-16',
    ]);

    $result = $this->service->resolveInfoautoPrices($this->version->id, [2025]);

    expect($result->get(2025))->not->toBeNull();
    expect((float) $result->get(2025)->price)->toBe(45000.00);
    expect($result->get(2025)->origin)->toBe('real');
});

it('falls back to predicted when no post-feat real snapshot exists', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 30000.00,
        'raw_price_ars_thousands' => 42000.00,
        'source' => 'infoauto_predicted',
        'recorded_at' => '2026-04-16',
    ]);

    $result = $this->service->resolveInfoautoPrices($this->version->id, [2024]);

    expect((float) $result->get(2024)->price)->toBe(30000.00);
    expect($result->get(2024)->origin)->toBe('predicted');
});

it('ignores infoauto snapshots with recorded_at before the feat date', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 99999.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-10',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 30000.00,
        'source' => 'infoauto_predicted',
        'recorded_at' => '2026-04-16',
    ]);

    $result = $this->service->resolveInfoautoPrices($this->version->id, [2024]);

    expect((float) $result->get(2024)->price)->toBe(30000.00);
    expect($result->get(2024)->origin)->toBe('predicted');
});

it('uses the most recent real snapshot when multiple post-feat snapshots exist for the same year', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 46000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-05-01',
    ]);

    $result = $this->service->resolveInfoautoPrices($this->version->id, [2025]);

    expect((float) $result->get(2025)->price)->toBe(46000.00);
    expect($result->get(2025)->origin)->toBe('real');
});

it('returns origin and metadata for each resolved year', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 0,
        'price' => 60000.00,
        'raw_price_ars_thousands' => 84000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2020,
        'price' => 20000.00,
        'source' => 'infoauto_predicted',
        'recorded_at' => '2026-04-16',
    ]);

    $result = $this->service->resolveInfoautoPrices($this->version->id, [0, 2020]);

    expect($result->get(0)->origin)->toBe('real');
    expect((float) $result->get(0)->raw_price_ars_thousands)->toBe(84000.00);
    expect($result->get(0)->recorded_at)->not->toBeNull();
    expect($result->get(2020)->origin)->toBe('predicted');
});

it('resolves many years in a single database query (no N+1)', function () {
    foreach ([0, 2020, 2021, 2022, 2023, 2024, 2025, 2026] as $year) {
        PriceSnapshot::create([
            'version_id' => $this->version->id,
            'year' => $year,
            'price' => 10000 + $year,
            'source' => $year % 2 === 0 ? 'infoauto' : 'infoauto_predicted',
            'recorded_at' => $year % 2 === 0 ? '2026-04-17' : '2026-04-16',
        ]);
    }

    $queryCount = 0;
    DB::listen(function () use (&$queryCount) {
        $queryCount++;
    });

    $result = $this->service->resolveInfoautoPrices(
        $this->version->id,
        [0, 2020, 2021, 2022, 2023, 2024, 2025, 2026]
    );

    expect($queryCount)->toBe(1);
    expect($result)->toHaveCount(8);
});

it('ignores years not requested', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 35000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $result = $this->service->resolveInfoautoPrices($this->version->id, [2025]);

    expect($result)->toHaveCount(1);
    expect($result->has(2025))->toBeTrue();
    expect($result->has(2024))->toBeFalse();
});

it('does not mix snapshots from other versions', function () {
    $otherBrand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $otherModel = CarModel::create(['brand_id' => $otherBrand->id, 'name' => 'RANGER', 'slug' => 'ranger']);
    $otherVersion = Version::create(['car_model_id' => $otherModel->id, 'name' => 'XLT']);

    PriceSnapshot::create([
        'version_id' => $otherVersion->id,
        'year' => 2025,
        'price' => 77777.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $result = $this->service->resolveInfoautoPrices($this->version->id, [2025]);

    expect($result)->toBeEmpty();
});

it('returns empty collection when years array is empty', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $result = $this->service->resolveInfoautoPrices($this->version->id, []);

    expect($result)->toBeEmpty();
});
