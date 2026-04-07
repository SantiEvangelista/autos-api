<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Version;

beforeEach(function () {
    $toyota = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $corolla = CarModel::create(['brand_id' => $toyota->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    Version::create(['car_model_id' => $corolla->id, 'name' => '4P 2.0 XEI CVT']);
    Version::create(['car_model_id' => $corolla->id, 'name' => '4P 1.8 XLI']);

    $hilux = CarModel::create(['brand_id' => $toyota->id, 'name' => 'HILUX', 'slug' => 'hilux']);
    Version::create(['car_model_id' => $hilux->id, 'name' => 'D/C 2.8 SRX 4X4 AT']);

    $ford = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $ranger = CarModel::create(['brand_id' => $ford->id, 'name' => 'RANGER', 'slug' => 'ranger']);
    Version::create(['car_model_id' => $ranger->id, 'name' => 'D/C 3.2 XLT 4X4']);
});

it('searches by version name', function () {
    $response = $this->getJson('/api/v1/search?q=XEI');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.version', '4P 2.0 Xei CVT')
        ->assertJsonPath('data.0.version_raw', '4P 2.0 XEI CVT');
});

it('searches by model name', function () {
    $response = $this->getJson('/api/v1/search?q=corolla');

    $response->assertOk()->assertJsonCount(2, 'data');
    $response->assertJsonPath('data.0.model', 'COROLLA');
    $response->assertJsonPath('data.1.model', 'COROLLA');
});

it('searches by brand name', function () {
    $response = $this->getJson('/api/v1/search?q=ford');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.brand', 'FORD');
});

it('is case insensitive', function () {
    $response = $this->getJson('/api/v1/search?q=TOYOTA');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(3);
});

it('returns correct result structure', function () {
    $response = $this->getJson('/api/v1/search?q=corolla');

    $response->assertOk()
        ->assertJsonStructure(['data' => [['version_id', 'brand', 'brand_slug', 'model', 'model_slug', 'version', 'version_raw']]]);
});

it('returns 422 when query is less than 2 characters', function () {
    $response = $this->getJson('/api/v1/search?q=a');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('q');
});

it('returns 422 when query is empty', function () {
    $response = $this->getJson('/api/v1/search');

    $response->assertStatus(422);
});

it('searches across brand and model with multi-word query', function () {
    $response = $this->getJson('/api/v1/search?q=toyota corolla');

    $response->assertOk()->assertJsonCount(2, 'data');
    $response->assertJsonPath('data.0.brand', 'TOYOTA');
    $response->assertJsonPath('data.0.model', 'COROLLA');
});

it('returns empty array for no matches', function () {
    $response = $this->getJson('/api/v1/search?q=lamborghini');

    $response->assertOk()->assertJsonPath('data', []);
});

it('paginates results with default per_page of 25', function () {
    $brand = Brand::create(['name' => 'TEST', 'slug' => 'test']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'BULK', 'slug' => 'bulk']);
    for ($i = 0; $i < 30; $i++) {
        Version::create(['car_model_id' => $model->id, 'name' => "SEARCHABLE {$i}"]);
    }

    $response = $this->getJson('/api/v1/search?q=SEARCHABLE');

    $response->assertOk()
        ->assertJsonCount(25, 'data')
        ->assertJsonStructure(['data', 'links', 'meta' => ['current_page', 'per_page']]);
});

it('respects custom per_page for search', function () {
    $response = $this->getJson('/api/v1/search?q=TOYOTA&per_page=2');

    $response->assertOk()->assertJsonCount(2, 'data');
});
