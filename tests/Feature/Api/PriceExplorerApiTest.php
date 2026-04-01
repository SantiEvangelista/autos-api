<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Valuation;
use App\Models\Version;

beforeEach(function () {
    $toyota = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $corolla = CarModel::create(['brand_id' => $toyota->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    $corollaVersion = Version::create(['car_model_id' => $corolla->id, 'name' => '4P 2.0 XEI CVT']);
    Valuation::create(['version_id' => $corollaVersion->id, 'year' => 0, 'price' => 42000]);
    Valuation::create(['version_id' => $corollaVersion->id, 'year' => 2025, 'price' => 38000]);
    Valuation::create(['version_id' => $corollaVersion->id, 'year' => 2024, 'price' => 33000]);

    $hilux = CarModel::create(['brand_id' => $toyota->id, 'name' => 'HILUX', 'slug' => 'hilux']);
    $hiluxVersion = Version::create(['car_model_id' => $hilux->id, 'name' => 'D/C 2.8 SRX 4X4 AT']);
    Valuation::create(['version_id' => $hiluxVersion->id, 'year' => 0, 'price' => 65000]);
    Valuation::create(['version_id' => $hiluxVersion->id, 'year' => 2025, 'price' => 58000]);

    $ford = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $ranger = CarModel::create(['brand_id' => $ford->id, 'name' => 'RANGER', 'slug' => 'ranger']);
    $rangerVersion = Version::create(['car_model_id' => $ranger->id, 'name' => 'D/C 3.2 XLT 4X4']);
    Valuation::create(['version_id' => $rangerVersion->id, 'year' => 0, 'price' => 55000]);
    Valuation::create(['version_id' => $rangerVersion->id, 'year' => 2024, 'price' => 48000]);

    $fiat = Brand::create(['name' => 'FIAT', 'slug' => 'fiat']);
    $cronos = CarModel::create(['brand_id' => $fiat->id, 'name' => 'CRONOS', 'slug' => 'cronos']);
    $cronosVersion = Version::create(['car_model_id' => $cronos->id, 'name' => '1.3 DRIVE']);
    Valuation::create(['version_id' => $cronosVersion->id, 'year' => 0, 'price' => 18000]);
    Valuation::create(['version_id' => $cronosVersion->id, 'year' => 2025, 'price' => 15000]);
});

it('returns vehicles within a max_price budget', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=20000');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.brand', 'FIAT')
        ->assertJsonPath('data.0.model', 'CRONOS');
});

it('returns vehicles within a min-max price range', function () {
    $response = $this->getJson('/api/v1/price-explorer?min_price=30000&max_price=50000');

    $response->assertOk();
    $brands = collect($response->json('data'))->pluck('brand')->sort()->values()->all();
    expect($brands)->toContain('TOYOTA')
        ->toContain('FORD');
});

it('returns correct result structure', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=100000');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['version_id', 'brand', 'brand_slug', 'model', 'model_slug', 'version', 'price', 'price_year']],
        ]);
});

it('returns results ordered by price ascending', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=100000');

    $response->assertOk();
    $prices = collect($response->json('data'))->pluck('price')->map(fn ($p) => (float) $p)->all();
    $sorted = $prices;
    sort($sorted);
    expect($prices)->toBe($sorted);
});

it('filters by year=0 for 0km vehicles only', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=100000&year=0');

    $response->assertOk();
    $years = collect($response->json('data'))->pluck('price_year')->unique()->all();
    expect($years)->toBe([0]);
});

it('filters by specific year', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=100000&year=2024');

    $response->assertOk();
    // Only Corolla ($33k in 2024) and Ranger ($48k in 2024) have 2024 valuations
    $response->assertJsonCount(2, 'data');
    $years = collect($response->json('data'))->pluck('price_year')->unique()->all();
    expect($years)->toBe([2024]);
});

it('returns empty array when no vehicles match the budget', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=1000');

    $response->assertOk()
        ->assertJsonPath('data', []);
});

it('returns 422 when max_price is missing', function () {
    $response = $this->getJson('/api/v1/price-explorer');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('max_price');
});

it('returns 422 for negative max_price', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=-100');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('max_price');
});

it('returns 422 when min_price exceeds max_price', function () {
    $response = $this->getJson('/api/v1/price-explorer?min_price=50000&max_price=10000');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('min_price');
});

it('defaults min_price to 0 when not provided', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=20000');

    $response->assertOk();
    // FIAT CRONOS has $15k (2025) and $18k (0km) — both within 0-20k
    $response->assertJsonCount(1, 'data');
});

it('paginates results with default per_page of 25', function () {
    $brand = Brand::create(['name' => 'BULK', 'slug' => 'bulk']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'BULKMODEL', 'slug' => 'bulkmodel']);
    for ($i = 0; $i < 30; $i++) {
        $v = Version::create(['car_model_id' => $model->id, 'name' => "VERSION {$i}"]);
        Valuation::create(['version_id' => $v->id, 'year' => 0, 'price' => 5000 + ($i * 100)]);
    }

    $response = $this->getJson('/api/v1/price-explorer?max_price=100000');

    $response->assertOk()
        ->assertJsonCount(25, 'data')
        ->assertJsonStructure(['data', 'links', 'meta' => ['current_page', 'per_page']]);
});

it('respects custom per_page', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=100000&per_page=2');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('returns pagination structure', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=100000');

    $response->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta' => ['current_page', 'per_page']]);
});

it('returns prices within the requested range', function () {
    $response = $this->getJson('/api/v1/price-explorer?min_price=30000&max_price=45000');

    $response->assertOk();
    $prices = collect($response->json('data'))->pluck('price');
    foreach ($prices as $price) {
        expect((float) $price)->toBeGreaterThanOrEqual(30000)
            ->toBeLessThanOrEqual(45000);
    }
});

it('handles year=0 filtering correctly for 0km only', function () {
    $response = $this->getJson('/api/v1/price-explorer?max_price=20000&year=0');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.brand', 'FIAT')
        ->assertJsonPath('data.0.price_year', 0);
});
