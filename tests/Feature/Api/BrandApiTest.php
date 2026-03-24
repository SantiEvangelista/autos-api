<?php

use App\Models\Brand;
use App\Models\CarModel;

it('responds at the v1 prefixed route', function () {
    Brand::create(['name' => 'TEST', 'slug' => 'test']);
    $this->getJson('/api/v1/brands')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('returns all brands ordered by name', function () {
    Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    Brand::create(['name' => 'AUDI', 'slug' => 'audi']);

    $response = $this->getJson('/api/v1/brands');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.name', 'AUDI')
        ->assertJsonPath('data.1.name', 'FORD')
        ->assertJsonPath('data.2.name', 'TOYOTA');
});

it('returns brand fields: id, name, slug', function () {
    Brand::create(['name' => 'BMW', 'slug' => 'bmw']);

    $response = $this->getJson('/api/v1/brands');

    $response->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug']]]);
});

it('returns empty array when no brands exist', function () {
    $response = $this->getJson('/api/v1/brands');

    $response->assertOk()->assertJsonPath('data', []);
});

it('returns models for a brand by id with pagination', function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    CarModel::create(['brand_id' => $brand->id, 'name' => 'HILUX', 'slug' => 'hilux']);
    CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);

    $response = $this->getJson("/api/v1/brands/{$brand->id}/models");

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'COROLLA')
        ->assertJsonPath('data.1.name', 'HILUX')
        ->assertJsonStructure(['data', 'links', 'meta' => ['current_page', 'per_page']]);
});

it('returns models with correct fields', function () {
    $brand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    CarModel::create(['brand_id' => $brand->id, 'name' => 'RANGER', 'slug' => 'ranger']);

    $response = $this->getJson("/api/v1/brands/{$brand->id}/models");

    $response->assertOk()
        ->assertJsonStructure(['data' => [['id', 'brand_id', 'name', 'slug']]]);
});

it('returns 404 for non-existent brand id', function () {
    $response = $this->getJson('/api/v1/brands/99999/models');

    $response->assertNotFound();
});

it('returns empty array for brand with no models', function () {
    $brand = Brand::create(['name' => 'EMPTY', 'slug' => 'empty']);

    $response = $this->getJson("/api/v1/brands/{$brand->id}/models");

    $response->assertOk()->assertJsonPath('data', []);
});

it('includes brand info when relations[]=brand', function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);

    $response = $this->getJson("/api/v1/brands/{$brand->id}/models?relations[]=brand");

    $response->assertOk()
        ->assertJsonPath('data.0.brand.name', 'TOYOTA')
        ->assertJsonPath('data.0.brand.slug', 'toyota');
});

it('does not include brand info by default', function () {
    $brand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    CarModel::create(['brand_id' => $brand->id, 'name' => 'RANGER', 'slug' => 'ranger']);

    $response = $this->getJson("/api/v1/brands/{$brand->id}/models");

    $response->assertOk()
        ->assertJsonMissingPath('data.0.brand');
});

it('does not return models from other brands', function () {
    $toyota = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $ford = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    CarModel::create(['brand_id' => $toyota->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    CarModel::create(['brand_id' => $ford->id, 'name' => 'RANGER', 'slug' => 'ranger']);

    $response = $this->getJson("/api/v1/brands/{$toyota->id}/models");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'COROLLA');
});

it('paginates models with default per_page of 25', function () {
    $brand = Brand::create(['name' => 'TEST', 'slug' => 'test']);
    for ($i = 0; $i < 30; $i++) {
        CarModel::create(['brand_id' => $brand->id, 'name' => "MODEL {$i}", 'slug' => "model-{$i}"]);
    }

    $response = $this->getJson("/api/v1/brands/{$brand->id}/models");

    $response->assertOk()
        ->assertJsonCount(25, 'data')
        ->assertJsonStructure(['data', 'links', 'meta' => ['current_page', 'per_page']]);
});

it('respects custom per_page param for models', function () {
    $brand = Brand::create(['name' => 'TEST', 'slug' => 'test']);
    for ($i = 0; $i < 10; $i++) {
        CarModel::create(['brand_id' => $brand->id, 'name' => "MODEL {$i}", 'slug' => "model-{$i}"]);
    }

    $response = $this->getJson("/api/v1/brands/{$brand->id}/models?per_page=5");

    $response->assertOk()->assertJsonCount(5, 'data');
});

it('rejects per_page greater than 100 for models', function () {
    $brand = Brand::create(['name' => 'TEST', 'slug' => 'test']);

    $response = $this->getJson("/api/v1/brands/{$brand->id}/models?per_page=200");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('per_page');
});
