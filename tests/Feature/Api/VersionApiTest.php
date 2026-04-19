<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\PriceSnapshot;
use App\Models\Valuation;
use App\Models\Version;
use App\Services\ExchangeRateService;
use Illuminate\Support\Carbon;
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
        ->assertJsonPath('meta.version', '4P 2.0 Seg CVT')
        ->assertJsonPath('meta.version_raw', '4P 2.0 SEG CVT')
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

// =============================================
// Ciclo 4: Parámetro sources (precio ACARA)
// =============================================

it('shows acara_price as null when sources=acara but version has no ACARA data', function () {
    // No ACARA snapshots exist for this version
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=acara");

    $response->assertOk();

    // The key 'acara_price' MUST exist in the response (not just be missing)
    $data = $response->json('data.0');
    expect(array_key_exists('acara_price', $data))->toBeTrue('acara_price key should be present in response');
    expect($data['acara_price'])->toBeNull();
});

it('returns valuations without acara_price by default', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk()
        ->assertJsonMissingPath('data.0.acara_price');
});

it('includes acara_price when sources includes acara', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 33000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=acara");

    $response->assertOk()
        ->assertJsonPath('data.0.acara_price', '38000.00')
        ->assertJsonPath('data.1.acara_price', '33000.00');
});

it('returns null acara_price when no ACARA data exists for a year', function () {
    // Only create ACARA snapshot for 2025, not for 2024 or 0km
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=acara");

    $response->assertOk()
        ->assertJsonPath('data.0.acara_price', '38000.00')
        ->assertJsonPath('data.1.acara_price', null);
});

it('uses latest ACARA snapshot when multiple exist', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 36000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-01',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 39000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=acara");

    $response->assertOk()
        ->assertJsonPath('data.0.acara_price', '39000.00');
});

it('combines sources=acara with currency=ARS', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1400.0],
        ]),
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=acara&currency=ars");

    $response->assertOk()
        ->assertJsonPath('data.0.price', '56000000.00')       // 40000 * 1400
        ->assertJsonPath('data.0.acara_price', '53200000.00'); // 38000 * 1400
});

it('combines sources=acara with format_price', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=acara&format_price=true");

    $response->assertOk()
        ->assertJsonPath('data.0.price_formatted', 'US$40.000,00')
        ->assertJsonPath('data.0.acara_price_formatted', 'US$38.000,00');
});

it('returns 422 for invalid source value', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=invalid");

    $response->assertStatus(422);
});

it('supports multiple sources via comma-separated string', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-28',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto,acara");

    $response->assertOk()
        ->assertJsonPath('data.0.acara_price', '38000.00');
});

// =============================================
// Ciclo 5: Evolución de precios (history)
// =============================================

it('returns current valuations when history param is absent', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonMissingPath('data.0.source')
        ->assertJsonMissingPath('data.0.recorded_at');
});

it('returns price evolution when history=true', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 39000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-01',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-15',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?history=true&from=2026-03-01&to=2026-03-28");

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure(['data' => [['version_id', 'year', 'price', 'source', 'recorded_at']]]);
});

it('filters history by from and to dates', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'cca',
        'recorded_at' => '2026-02-01',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 39000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-01',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-15',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?history=true&from=2026-03-01&to=2026-03-20");

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('defaults from to 30 days ago when history=true', function () {
    Carbon::setTestNow('2026-03-28');

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 37000.00,
        'source' => 'cca',
        'recorded_at' => '2026-02-01', // > 30 days ago
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-15', // within 30 days
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?history=true");

    $response->assertOk()
        ->assertJsonCount(1, 'data');

    Carbon::setTestNow();
});

it('filters history by source parameter', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-15',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-15',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?history=true&source=acara&from=2026-03-01&to=2026-03-28");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.source', 'acara');
});

it('applies currency conversion to history prices', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1400.0],
        ]),
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 40000.00,
        'source' => 'cca',
        'recorded_at' => '2026-03-15',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?history=true&currency=ars&from=2026-03-01&to=2026-03-28");

    $response->assertOk()
        ->assertJsonPath('data.0.price', '56000000.00'); // 40000 * 1400
});

it('returns 422 for invalid date format in history', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?history=true&from=invalid");

    $response->assertStatus(422);
});

it('returns empty data when no snapshots exist in date range', function () {
    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?history=true&from=2020-01-01&to=2020-12-31");

    $response->assertOk()
        ->assertJsonCount(0, 'data');
});

// =============================================
// Ciclo 6: Parámetro sources=infoauto
// =============================================

it('does not include infoauto_price by default', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 45000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk()
        ->assertJsonMissingPath('data.0.infoauto_price')
        ->assertJsonMissingPath('data.0.infoauto_price_origin');
});

it('includes infoauto_price with origin=real when sources=infoauto and post-feat snapshot exists', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 45000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto");

    $response->assertOk()
        ->assertJsonPath('data.0.infoauto_price', '45000.00')
        ->assertJsonPath('data.0.infoauto_price_origin', 'real');
});

it('falls back to infoauto_predicted with origin=predicted when no real snapshot exists', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2024,
        'price' => 32000.00,
        'source' => 'infoauto_predicted',
        'recorded_at' => '2026-04-16',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto");

    $response->assertOk()
        ->assertJsonPath('data.1.year', 2024)
        ->assertJsonPath('data.1.infoauto_price', '32000.00')
        ->assertJsonPath('data.1.infoauto_price_origin', 'predicted');
});

it('ignores infoauto snapshots recorded before the feat date', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 99999.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-10',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto");

    $response->assertOk()
        ->assertJsonPath('data.0.year', 2025)
        ->assertJsonPath('data.0.infoauto_price', null)
        ->assertJsonPath('data.0.infoauto_price_origin', null);
});

it('returns null infoauto_price when sources=infoauto but no data covers a year', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 45000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto");

    $data2024 = collect($response->json('data'))->firstWhere('year', 2024);

    expect(array_key_exists('infoauto_price', $data2024))->toBeTrue();
    expect($data2024['infoauto_price'])->toBeNull();
    expect($data2024['infoauto_price_origin'])->toBeNull();
});

it('coexists with sources=acara when both requested', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 45000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 38000.00,
        'source' => 'acara',
        'recorded_at' => '2026-04-01',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=acara,infoauto");

    $response->assertOk()
        ->assertJsonPath('data.0.price', '40000.00')
        ->assertJsonPath('data.0.acara_price', '38000.00')
        ->assertJsonPath('data.0.infoauto_price', '45000.00')
        ->assertJsonPath('data.0.infoauto_price_origin', 'real');
});

it('converts infoauto_price to ARS when currency=ars', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1400.0],
        ]),
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 45000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto&currency=ars");

    $response->assertOk()
        ->assertJsonPath('data.0.infoauto_price', '63000000.00'); // 45000 * 1400
});

it('formats infoauto_price when format_price=true', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 45000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto&format_price=true");

    $response->assertOk()
        ->assertJsonPath('data.0.infoauto_price_formatted', 'US$45.000,00');
});

it('exposes infoauto_price_raw_ars with the absolute ARS value when snapshot has raw_price_ars_thousands', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 26983.32,
        'raw_price_ars_thousands' => 37210.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto");

    $response->assertOk()
        ->assertJsonPath('data.0.infoauto_price_raw_ars', '37210000.00');
});

it('sets infoauto_price_raw_ars to null when snapshot has no raw_price_ars_thousands', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 26983.32,
        'raw_price_ars_thousands' => null,
        'source' => 'infoauto_predicted',
        'recorded_at' => '2026-04-16',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto");

    $data2025 = collect($response->json('data'))->firstWhere('year', 2025);

    expect(array_key_exists('infoauto_price_raw_ars', $data2025))->toBeTrue();
    expect($data2025['infoauto_price_raw_ars'])->toBeNull();
});

it('returns infoauto_price_raw_ars unchanged by currency=ars (always absolute ARS)', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1389.0],
        ]),
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 26983.32,
        'raw_price_ars_thousands' => 37210.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto&currency=ars");

    $response->assertOk()
        ->assertJsonPath('data.0.infoauto_price_raw_ars', '37210000.00');
});

it('does not include infoauto_price_raw_ars when sources=infoauto is absent', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 26983.32,
        'raw_price_ars_thousands' => 37210.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk()
        ->assertJsonMissingPath('data.0.infoauto_price_raw_ars');
});

it('executes a single query for infoauto resolution regardless of year count', function () {
    foreach ([2020, 2021, 2022, 2023, 2024, 2025] as $year) {
        \App\Models\Valuation::firstOrCreate(
            ['version_id' => $this->version->id, 'year' => $year],
            ['price' => 10000 + $year]
        );
        PriceSnapshot::create([
            'version_id' => $this->version->id,
            'year' => $year,
            'price' => 20000 + $year,
            'source' => 'infoauto',
            'recorded_at' => '2026-04-17',
        ]);
    }

    $infoautoQueryCount = 0;
    \Illuminate\Support\Facades\DB::listen(function ($query) use (&$infoautoQueryCount) {
        if (str_contains($query->sql, 'price_snapshots')
            && (str_contains($query->sql, "'infoauto'") || in_array('infoauto', $query->bindings))
            && (str_contains($query->sql, "'infoauto_predicted'") || in_array('infoauto_predicted', $query->bindings))) {
            $infoautoQueryCount++;
        }
    });

    $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto")->assertOk();

    expect($infoautoQueryCount)->toBe(1);
});

// =============================================
// Ciclo 7: Row para años con infoauto pero sin valuation CCA
// =============================================

it('surfaces rows for years with infoauto snapshots but no CCA valuation', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2021,
        'price' => 25000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto");

    $response->assertOk();
    $years = collect($response->json('data'))->pluck('year')->all();
    expect($years)->toBe([2025, 2024, 2021, 0]);

    $row2021 = collect($response->json('data'))->firstWhere('year', 2021);
    expect($row2021['price'])->toBeNull();
    expect($row2021['infoauto_price'])->toBe('25000.00');
    expect($row2021['infoauto_price_origin'])->toBe('real');
});

it('surfaces a 0km row when infoauto has 0km but CCA does not', function () {
    Valuation::where('version_id', $this->version->id)->where('year', 0)->delete();

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 0,
        'price' => 48000.00,
        'raw_price_ars_thousands' => 66672.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto");

    $response->assertOk();
    $years = collect($response->json('data'))->pluck('year')->all();
    expect($years)->toBe([2025, 2024, 0]);

    $row0 = collect($response->json('data'))->firstWhere('year', 0);
    expect($row0['price'])->toBeNull();
    expect($row0['infoauto_price'])->toBe('48000.00');
    expect($row0['infoauto_price_raw_ars'])->toBe('66672000.00');
});

it('keeps null price on rows when currency=ars (no corruption)', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1400.0],
        ]),
    ]);

    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2021,
        'price' => 25000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto&currency=ars");

    $response->assertOk();
    $row2021 = collect($response->json('data'))->firstWhere('year', 2021);
    expect($row2021['price'])->toBeNull();
    expect($row2021['infoauto_price'])->toBe('35000000.00');
});

it('omits price_formatted on rows when format_price=true', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2021,
        'price' => 25000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto&format_price=true");

    $response->assertOk();
    $row2021 = collect($response->json('data'))->firstWhere('year', 2021);
    expect(array_key_exists('price_formatted', $row2021))->toBeFalse();
    expect($row2021['infoauto_price_formatted'])->toBe('US$25.000,00');
});

it('does not add rows when sources=infoauto is not requested', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2021,
        'price' => 25000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-17',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations");

    $response->assertOk();
    $years = collect($response->json('data'))->pluck('year')->all();
    expect($years)->toBe([2025, 2024, 0]);
});

it('does not add rows for infoauto snapshots recorded before the feat date', function () {
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2021,
        'price' => 25000.00,
        'source' => 'infoauto',
        'recorded_at' => '2026-04-10',
    ]);

    $response = $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto");

    $response->assertOk();
    $years = collect($response->json('data'))->pluck('year')->all();
    expect($years)->toBe([2025, 2024, 0]);
});

it('keeps single infoauto query when adding rows', function () {
    foreach ([2020, 2021, 2022] as $year) {
        PriceSnapshot::create([
            'version_id' => $this->version->id,
            'year' => $year,
            'price' => 20000 + $year,
            'source' => 'infoauto',
            'recorded_at' => '2026-04-17',
        ]);
    }

    $infoautoQueryCount = 0;
    \Illuminate\Support\Facades\DB::listen(function ($query) use (&$infoautoQueryCount) {
        if (str_contains($query->sql, 'price_snapshots')
            && (str_contains($query->sql, "'infoauto'") || in_array('infoauto', $query->bindings))
            && (str_contains($query->sql, "'infoauto_predicted'") || in_array('infoauto_predicted', $query->bindings))) {
            $infoautoQueryCount++;
        }
    });

    $this->getJson("/api/v1/versions/{$this->version->id}/valuations?sources=infoauto")->assertOk();

    expect($infoautoQueryCount)->toBe(1);
});
