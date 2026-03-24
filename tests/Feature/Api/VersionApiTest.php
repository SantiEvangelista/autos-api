<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Valuation;
use App\Models\Version;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    $this->version = Version::create(['car_model_id' => $model->id, 'name' => '4P 2.0 SEG CVT']);
    Valuation::create(['version_id' => $this->version->id, 'year' => 2025, 'price' => 40000.00]);
    Valuation::create(['version_id' => $this->version->id, 'year' => 2024, 'price' => 35000.00]);
    Valuation::create(['version_id' => $this->version->id, 'year' => 0, 'price' => 42000.00]);
});

it('returns valuations in USD by default', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk()
        ->assertJsonPath('meta.currency', 'USD')
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.year', 2025)
        ->assertJsonPath('data.0.price', '40000.00')
        ->assertJsonMissing(['exchange_rate']);
});

it('returns valuations ordered by year descending with 0km last', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk();
    $years = collect($response->json('data'))->pluck('year')->toArray();
    expect($years)->toBe([2025, 2024, 0]);
});

it('returns valuations with correct fields', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'version_id', 'year', 'price']],
            'meta' => ['currency'],
        ]);
});

it('converts valuations to ARS when currency=ars', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1400.0, 'value_buy' => 1350.0],
            'blue' => ['value_sell' => 1500.0, 'value_buy' => 1450.0],
        ]),
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?currency=ars");

    $response->assertOk()
        ->assertJsonPath('meta.currency', 'ARS')
        ->assertJsonPath('meta.exchange_rate.source', 'bluelytics')
        ->assertJsonPath('meta.exchange_rate.type', 'oficial_sell')
        ->assertJsonPath('meta.exchange_rate.ars_per_usd', 1400)
        ->assertJsonPath('data.0.price', '56000000.00'); // 40000 * 1400
});

it('is case insensitive for currency param', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1400.0],
        ]),
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?currency=Ars");

    $response->assertOk()
        ->assertJsonPath('meta.currency', 'ARS');
});

it('returns 422 for unsupported currency', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?currency=EUR");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('currency');
});

it('returns 503 when exchange rate api is unavailable', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response(null, 500),
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?currency=ars");

    $response->assertStatus(503)
        ->assertJsonPath('error', 'Exchange rate temporarily unavailable');
});

it('returns 404 for non-existent version', function () {
    $response = $this->getJson('/api/v1/versions/99999/valuations');

    $response->assertNotFound();
});

it('includes price_formatted in USD when format_price=true', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?format_price=true");

    $response->assertOk()
        ->assertJsonPath('data.0.price_formatted', 'US$40.000,00')
        ->assertJsonPath('data.1.price_formatted', 'US$35.000,00')
        ->assertJsonPath('data.0.price', '40000.00'); // raw price still present
});

it('includes price_formatted in ARS when format_price=true and currency=ars', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1000.0],
        ]),
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?currency=ars&format_price=true");

    $response->assertOk()
        ->assertJsonPath('data.0.price_formatted', '$40.000.000,00') // 40000 * 1000
        ->assertJsonPath('meta.currency', 'ARS');
});

it('does not include price_formatted when format_price is absent', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk()
        ->assertJsonMissingPath('data.0.price_formatted');
});

it('does not include price_formatted when format_price=false', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?format_price=false");

    $response->assertOk()
        ->assertJsonMissingPath('data.0.price_formatted');
});

it('includes version, model and brand context when relations[]=version&model&brand', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?relations[]=version&relations[]=model&relations[]=brand");

    $response->assertOk()
        ->assertJsonPath('meta.version', '4P 2.0 SEG CVT')
        ->assertJsonPath('meta.model.name', 'COROLLA')
        ->assertJsonPath('meta.model.slug', 'corolla')
        ->assertJsonPath('meta.brand.name', 'TOYOTA')
        ->assertJsonPath('meta.brand.slug', 'toyota');
});

it('does not include context in meta by default', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk()
        ->assertJsonMissingPath('meta.version')
        ->assertJsonMissingPath('meta.model')
        ->assertJsonMissingPath('meta.brand');
});

it('includes only requested relations in meta', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?relations[]=brand");

    $response->assertOk()
        ->assertJsonPath('meta.brand.name', 'TOYOTA')
        ->assertJsonMissingPath('meta.version')
        ->assertJsonMissingPath('meta.model');
});

it('returns 422 for invalid format_price value', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?format_price=yes");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('format_price');
});

it('returns empty data for version with no valuations', function () {
    $brand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'KA', 'slug' => 'ka']);
    $emptyVersion = Version::create(['car_model_id' => $model->id, 'name' => 'EMPTY']);

    $response = $this->getJson("/api/v1/versions/{$emptyVersion->id}/valuations");

    $response->assertOk()
        ->assertJsonCount(0, 'data');
});
