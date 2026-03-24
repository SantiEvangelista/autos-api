<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Valuation;
use App\Models\Version;

it('returns correct counts from the database', function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '2.0 SEG']);
    Valuation::create(['version_id' => $version->id, 'year' => 2025, 'price' => 40000.00]);

    $response = $this->getJson('/api/v1/stats');

    $response->assertOk()
        ->assertJsonPath('brands', 1)
        ->assertJsonPath('models', 1)
        ->assertJsonPath('versions', 1);
});

it('returns correct JSON structure', function () {
    $response = $this->getJson('/api/v1/stats');

    $response->assertOk()
        ->assertJsonStructure(['brands', 'models', 'versions', 'last_updated']);
});

it('returns zero counts when database is empty', function () {
    $response = $this->getJson('/api/v1/stats');

    $response->assertOk()
        ->assertJsonPath('brands', 0)
        ->assertJsonPath('models', 0)
        ->assertJsonPath('versions', 0)
        ->assertJsonPath('last_updated', null);
});

it('returns last_updated from most recent valuation', function () {
    $brand = Brand::create(['name' => 'BMW', 'slug' => 'bmw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'X3', 'slug' => 'x3']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '2.0']);
    Valuation::create(['version_id' => $version->id, 'year' => 2025, 'price' => 50000.00]);

    $response = $this->getJson('/api/v1/stats');

    $response->assertOk();
    expect($response->json('last_updated'))->not->toBeNull();
});

it('returns 200 status code', function () {
    $this->getJson('/api/v1/stats')->assertOk();
});
