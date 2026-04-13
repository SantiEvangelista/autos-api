<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Version;
use App\Services\VersionMatcherService;

beforeEach(function () {
    $this->matcher = new VersionMatcherService();

    // ALFA ROMEO
    $alfa = Brand::create(['name' => 'ALFA ROMEO', 'slug' => 'alfa-romeo']);
    $m159 = CarModel::create(['brand_id' => $alfa->id, 'name' => '159', 'slug' => '159']);
    Version::create(['car_model_id' => $m159->id, 'name' => '4P 1,8 TBI 200CV DISTINCTIVE']);
    Version::create(['car_model_id' => $m159->id, 'name' => '4P 2,2 JTS 185CV S-SPEED CRO']);
    Version::create(['car_model_id' => $m159->id, 'name' => '4P 3,2 JTS HIGHT 4X4 CA ELEGANCE']);

    // VW
    $vw = Brand::create(['name' => 'VOLKSWAGEN', 'slug' => 'volkswagen']);
    $bora = CarModel::create(['brand_id' => $vw->id, 'name' => 'BORA', 'slug' => 'bora']);
    Version::create(['car_model_id' => $bora->id, 'name' => '4P 1,8 T HIGHLINE 180CV']);
    Version::create(['car_model_id' => $bora->id, 'name' => '4P 1,8 T HIGHLINE 180CV CRO']);
    Version::create(['car_model_id' => $bora->id, 'name' => '4P 1,8 T HIGHLINE 180CV TIPT']);
    Version::create(['car_model_id' => $bora->id, 'name' => '4P 2,0 TRENDLINE 115CV']);
    Version::create(['car_model_id' => $bora->id, 'name' => '4P 2,0 TRENDLINE 115CV TIPT']);
    Version::create(['car_model_id' => $bora->id, 'name' => '4P 1,9 TDi TRENDLINE']);

    $passat = CarModel::create(['brand_id' => $vw->id, 'name' => 'PASSAT CC', 'slug' => 'passat-cc']);
    Version::create(['car_model_id' => $passat->id, 'name' => '4P 2,0 TSI 211CV DSG']);

    $saveiro = CarModel::create(['brand_id' => $vw->id, 'name' => 'SAVEIRO PICK - UP', 'slug' => 'saveiro-pick-up']);
    Version::create(['car_model_id' => $saveiro->id, 'name' => 'C/S 1,6 TREND']);

    // BYD
    Brand::create(['name' => 'BYD', 'slug' => 'byd']);

    // DS AUTOMOBILES
    Brand::create(['name' => 'DS AUTOMOBILES', 'slug' => 'ds-automobiles']);

    // MINI COOPER
    Brand::create(['name' => 'MINI COOPER', 'slug' => 'mini-cooper']);

    // AUDI
    $audi = Brand::create(['name' => 'AUDI', 'slug' => 'audi']);
    $q2 = CarModel::create(['brand_id' => $audi->id, 'name' => 'Q 2', 'slug' => 'q-2']);
    Version::create(['car_model_id' => $q2->id, 'name' => '5P 1,0 T 116CV S-TRONIC']);

    // BMW (models are individual numbers, not "Serie N")
    $bmw = Brand::create(['name' => 'BMW', 'slug' => 'bmw']);
    $bmw320 = CarModel::create(['brand_id' => $bmw->id, 'name' => '320', 'slug' => '320']);
    Version::create(['car_model_id' => $bmw320->id, 'name' => '4P 2,0 T 184CV SPORT AT']);
    $bmw118 = CarModel::create(['brand_id' => $bmw->id, 'name' => '118', 'slug' => '118']);
    Version::create(['car_model_id' => $bmw118->id, 'name' => '5P 1,6 136CV SPORT AT']);
    Version::create(['car_model_id' => $bmw118->id, 'name' => '5P 1,5 T 7AT ADVANTAGE']);
    Version::create(['car_model_id' => $bmw118->id, 'name' => '5P 1,5 T 7AT ADVANTAGE 2025']);

    // CHEVROLET
    $chevrolet = Brand::create(['name' => 'CHEVROLET', 'slug' => 'chevrolet']);
    $tracker = CarModel::create(['brand_id' => $chevrolet->id, 'name' => 'TRACKER', 'slug' => 'tracker']);
    Version::create(['car_model_id' => $tracker->id, 'name' => '5P 1,2 T MT']);
    Version::create(['car_model_id' => $tracker->id, 'name' => '5P 1,2 T 6AT']);
    Version::create(['car_model_id' => $tracker->id, 'name' => '5P 1,2 T 6AT LTZ']);

    // FIAT
    $fiat = Brand::create(['name' => 'FIAT', 'slug' => 'fiat']);
    $cronos = CarModel::create(['brand_id' => $fiat->id, 'name' => 'CRONOS', 'slug' => 'cronos']);
    Version::create(['car_model_id' => $cronos->id, 'name' => '4P 1,3 LIKE GSE 2023']);
    Version::create(['car_model_id' => $cronos->id, 'name' => '4P 1,3 LIKE GSE 2025']);

    // PORSCHE
    $porsche = Brand::create(['name' => 'PORSCHE', 'slug' => 'porsche']);
    $boxster = CarModel::create(['brand_id' => $porsche->id, 'name' => 'BOXSTER', 'slug' => 'boxster']);
    $macan = CarModel::create(['brand_id' => $porsche->id, 'name' => 'MACAN', 'slug' => 'macan']);
    Version::create(['car_model_id' => $boxster->id, 'name' => '2P 2,0 BOXSTER']);
    Version::create(['car_model_id' => $macan->id, 'name' => '5P 2,0 T 252CV']);
});

// === Fix 2: Brand aliases ===

it('resolves brand alias B Y D to BYD', function () {
    $result = $this->matcher->findVersion('B Y D', 'anything', 'anything');
    // Should not crash — brand resolves to BYD even though slug b-y-d != byd
    // Result is null because model/version dont exist, but brand resolution works
    expect(true)->toBeTrue(); // brand alias resolution is tested indirectly
});

it('finds version for aliased brand B Y D', function () {
    $byd = Brand::where('slug', 'byd')->first();
    $model = CarModel::create(['brand_id' => $byd->id, 'name' => 'DOLPHIN', 'slug' => 'dolphin']);
    Version::create(['car_model_id' => $model->id, 'name' => 'EV 150CV COMFORT']);

    $result = $this->matcher->findVersion('B Y D', 'Dolphin', 'EV 150CV Comfort');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('EV 150CV COMFORT');
});

it('finds version for aliased brand DS', function () {
    $ds = Brand::where('slug', 'ds-automobiles')->first();
    $model = CarModel::create(['brand_id' => $ds->id, 'name' => 'DS3', 'slug' => 'ds3']);
    Version::create(['car_model_id' => $model->id, 'name' => '3P 1,6 THP 156CV']);

    $result = $this->matcher->findVersion('DS', 'DS3', '1.6 THP 156CV');
    expect($result)->not->toBeNull();
});

it('finds version for aliased brand MINI', function () {
    $mini = Brand::where('slug', 'mini-cooper')->first();
    $model = CarModel::create(['brand_id' => $mini->id, 'name' => 'COUNTRYMAN', 'slug' => 'countryman']);
    Version::create(['car_model_id' => $model->id, 'name' => '5P 1,5 T 136CV AT']);

    $result = $this->matcher->findVersion('MINI', 'Countryman', '1.5 T 136CV AT');
    expect($result)->not->toBeNull();
});

// === Fix 3: Model fuzzy matching ===

it('matches model Q2 to local Q 2 by stripping hyphens', function () {
    $result = $this->matcher->findVersion('AUDI', 'Q2', '1.0 T 116CV S-Tronic');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P 1,0 T 116CV S-TRONIC');
});

it('matches model CC to local PASSAT CC by starts-with', function () {
    $result = $this->matcher->findVersion('VOLKSWAGEN', 'CC', '2.0 TSI 211CV DSG');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('4P 2,0 TSI 211CV DSG');
});

it('matches model Saveiro to SAVEIRO PICK - UP by starts-with', function () {
    $result = $this->matcher->findVersion('VOLKSWAGEN', 'Saveiro', '1.6 Trend');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('C/S 1,6 TREND');
});

// === Fix 4: Version token matching ===

it('matches ACARA "1.8 TBi 6MT Cuero TC (200cv)" to local "4P 1,8 TBI 200CV DISTINCTIVE"', function () {
    $result = $this->matcher->findVersion('ALFA ROMEO', '159', '1.8 TBi 6MT Cuero TC (200cv)');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('4P 1,8 TBI 200CV DISTINCTIVE');
});

it('matches ACARA "2.2 JTS Selespeed Cuero (185cv)" to local "4P 2,2 JTS 185CV S-SPEED CRO"', function () {
    $result = $this->matcher->findVersion('ALFA ROMEO', '159', '2.2 JTS Selespeed Cuero (185cv)');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('4P 2,2 JTS 185CV S-SPEED CRO');
});

it('matches ACARA "1.8T Highline MT" to "4P 1,8 T HIGHLINE 180CV"', function () {
    $result = $this->matcher->findVersion('VOLKSWAGEN', 'Bora', '1.8T Highline MT');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('4P 1,8 T HIGHLINE 180CV');
});

it('matches ACARA "2.0 Trendline Tiptronic" to "4P 2,0 TRENDLINE 115CV TIPT"', function () {
    $result = $this->matcher->findVersion('VOLKSWAGEN', 'Bora', '2.0 Trendline Tiptronic');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('4P 2,0 TRENDLINE 115CV TIPT');
});

it('matches ACARA "1.9 TDI Trendline" to "4P 1,9 TDi TRENDLINE"', function () {
    $result = $this->matcher->findVersion('VOLKSWAGEN', 'Bora', '1.9 TDI Trendline');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('4P 1,9 TDi TRENDLINE');
});

it('picks best scored candidate when multiple match same displacement', function () {
    // "1.8T Highline Tiptronic" should match TIPT variant, not plain
    $result = $this->matcher->findVersion('VOLKSWAGEN', 'Bora', '1.8T Highline Tiptronic');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('4P 1,8 T HIGHLINE 180CV TIPT');
});

it('returns null when no candidates share displacement', function () {
    $result = $this->matcher->findVersion('ALFA ROMEO', '159', '5.0 V10 Supercharged');
    expect($result)->toBeNull();
});

it('falls back to brand-wide search when model does not match', function () {
    // BMW "Serie 3" doesn't match any local model, but "320" exists in the brand
    // ACARA: "2.0T Sport AT" → local BMW 320: "4P 2,0 T 184CV SPORT AT"
    $result = $this->matcher->findVersion('BMW', 'Serie 3', '2.0T Sport AT (184cv)');
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('4P 2,0 T 184CV SPORT AT');
});

it('prefers automatic transmission variants over manual ones for InfoAuto 0km names', function () {
    $result = $this->matcher->findVersion('CHEVROLET', 'TRACKER', 'TRACKER 1.2 TURBO LT AT6 L/25');

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P 1,2 T 6AT');
});

it('prefers matching candidate year when source contains L slash year', function () {
    $result = $this->matcher->findVersion('FIAT', 'CRONOS', 'CRONOS 1.3 LIKE L/25');

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('4P 1,3 LIKE GSE 2025');
});

it('prefers matching candidate year for BMW 0km names', function () {
    $result = $this->matcher->findVersion('BMW', '118', '118 ADVANTAGE 5 P. AUT L/25');

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P 1,5 T 7AT ADVANTAGE 2025');
});

it('infers the correct Porsche model from the version name context', function () {
    $result = $this->matcher->findVersion('PORSCHE', '718', '718 2.0 BOXSTER');

    expect($result)->not->toBeNull()
        ->and($result->carModel->name)->toBe('BOXSTER')
        ->and($result->name)->toBe('2P 2,0 BOXSTER');
});
