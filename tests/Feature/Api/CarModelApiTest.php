<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Version;

it('returns versions for a model', function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    Version::create(['car_model_id' => $model->id, 'name' => '4P 2.0 XEI CVT']);
    Version::create(['car_model_id' => $model->id, 'name' => '4P 1.8 XLI']);

    $response = $this->getJson("/api/v1/models/{$model->id}/versions");

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', '4P 1.8 Xli')
        ->assertJsonPath('data.0.name_raw', '4P 1.8 XLI')
        ->assertJsonPath('data.1.name', '4P 2.0 Xei CVT')
        ->assertJsonPath('data.1.name_raw', '4P 2.0 XEI CVT');
});

it('returns version fields: id, car_model_id, name', function () {
    $brand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'RANGER', 'slug' => 'ranger']);
    Version::create(['car_model_id' => $model->id, 'name' => 'D/C 3.2 XLT 4X4']);

    $response = $this->getJson("/api/v1/models/{$model->id}/versions");

    $response->assertOk()
        ->assertJsonStructure(['data' => [['id', 'car_model_id', 'name', 'name_raw']]]);
});

it('returns 404 for non-existent model', function () {
    $response = $this->getJson('/api/v1/models/99999/versions');

    $response->assertNotFound();
});

it('includes model and brand context when relations[]=model&relations[]=brand', function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    Version::create(['car_model_id' => $model->id, 'name' => '4P 2.0 XEI']);

    $response = $this->getJson("/api/v1/models/{$model->id}/versions?relations[]=model&relations[]=brand");

    $response->assertOk()
        ->assertJsonPath('data.0.model.name', 'COROLLA')
        ->assertJsonPath('data.0.model.slug', 'corolla')
        ->assertJsonPath('data.0.brand.name', 'TOYOTA')
        ->assertJsonPath('data.0.brand.slug', 'toyota');
});

it('does not include context by default', function () {
    $brand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'RANGER', 'slug' => 'ranger']);
    Version::create(['car_model_id' => $model->id, 'name' => 'XLT 4X4']);

    $response = $this->getJson("/api/v1/models/{$model->id}/versions");

    $response->assertOk()
        ->assertJsonMissingPath('data.0.model')
        ->assertJsonMissingPath('data.0.brand');
});

it('returns empty array for model with no versions', function () {
    $brand = Brand::create(['name' => 'BMW', 'slug' => 'bmw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'SERIE 3', 'slug' => 'serie-3']);

    $response = $this->getJson("/api/v1/models/{$model->id}/versions");

    $response->assertOk()->assertJsonPath('data', []);
});

it('paginates versions with default per_page of 25', function () {
    $brand = Brand::create(['name' => 'TEST', 'slug' => 'test']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'BULK', 'slug' => 'bulk']);
    for ($i = 0; $i < 30; $i++) {
        Version::create(['car_model_id' => $model->id, 'name' => "VERSION {$i}"]);
    }

    $response = $this->getJson("/api/v1/models/{$model->id}/versions");

    $response->assertOk()
        ->assertJsonCount(25, 'data')
        ->assertJsonStructure(['data', 'links', 'meta' => ['current_page', 'per_page']]);
});

it('respects custom per_page param', function () {
    $brand = Brand::create(['name' => 'TEST', 'slug' => 'test']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'BULK', 'slug' => 'bulk']);
    for ($i = 0; $i < 10; $i++) {
        Version::create(['car_model_id' => $model->id, 'name' => "VERSION {$i}"]);
    }

    $response = $this->getJson("/api/v1/models/{$model->id}/versions?per_page=5");

    $response->assertOk()->assertJsonCount(5, 'data');
});
