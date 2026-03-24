<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Valuation;
use App\Models\Version;

it('brand has many car models', function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    CarModel::create(['brand_id' => $brand->id, 'name' => 'HILUX', 'slug' => 'hilux']);

    expect($brand->carModels)->toHaveCount(2);
});

it('brand uses id as route key', function () {
    $brand = new Brand();
    expect($brand->getRouteKeyName())->toBe('id');
});

it('car model belongs to brand', function () {
    $brand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'RANGER', 'slug' => 'ranger']);

    expect($model->brand->id)->toBe($brand->id);
});

it('car model has many versions', function () {
    $brand = Brand::create(['name' => 'BMW', 'slug' => 'bmw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'SERIE 3', 'slug' => 'serie-3']);
    Version::create(['car_model_id' => $model->id, 'name' => '320i']);
    Version::create(['car_model_id' => $model->id, 'name' => '330i']);

    expect($model->versions)->toHaveCount(2);
});

it('version belongs to car model', function () {
    $brand = Brand::create(['name' => 'AUDI', 'slug' => 'audi']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'A3', 'slug' => 'a3']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '1.4 TFSI']);

    expect($version->carModel->id)->toBe($model->id);
});

it('version has many valuations', function () {
    $brand = Brand::create(['name' => 'FIAT', 'slug' => 'fiat']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'CRONOS', 'slug' => 'cronos']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '1.3 DRIVE']);
    Valuation::create(['version_id' => $version->id, 'year' => 2025, 'price' => 15000]);
    Valuation::create(['version_id' => $version->id, 'year' => 2024, 'price' => 13000]);

    expect($version->valuations)->toHaveCount(2);
});

it('valuation belongs to version', function () {
    $brand = Brand::create(['name' => 'VW', 'slug' => 'vw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'GOL', 'slug' => 'gol']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => 'TREND']);
    $valuation = Valuation::create(['version_id' => $version->id, 'year' => 2023, 'price' => 12000]);

    expect($valuation->version->id)->toBe($version->id);
});

it('valuation casts price to decimal and year to integer', function () {
    $brand = Brand::create(['name' => 'VW', 'slug' => 'vw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'GOL', 'slug' => 'gol']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => 'TREND']);
    $valuation = Valuation::create(['version_id' => $version->id, 'year' => '2023', 'price' => '15000.50']);

    $valuation->refresh();

    expect($valuation->year)->toBeInt()
        ->and($valuation->price)->toBe('15000.50')
        ->and($valuation->getCasts())->toHaveKey('price', 'decimal:2')
        ->and($valuation->getCasts())->toHaveKey('year', 'integer');
});
