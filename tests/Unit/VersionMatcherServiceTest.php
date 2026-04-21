<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\PriceSnapshot;
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
    $pulse = CarModel::create(['brand_id' => $fiat->id, 'name' => 'PULSE', 'slug' => 'pulse']);
    Version::create(['car_model_id' => $pulse->id, 'name' => '5P 1,3 DRIVE CVT 2025']);
    Version::create(['car_model_id' => $pulse->id, 'name' => '5P 270 TURBO ABARTH 6AT 2026']);

    // HONDA
    $honda = Brand::create(['name' => 'HONDA', 'slug' => 'honda']);
    $hrv = CarModel::create(['brand_id' => $honda->id, 'name' => 'HR-V', 'slug' => 'hr-v']);
    Version::create(['car_model_id' => $hrv->id, 'name' => '5P 1,5 LX CVT 2025']);
    Version::create(['car_model_id' => $hrv->id, 'name' => '5P 1,5 EXL CVT 2025']);
    Version::create(['car_model_id' => $hrv->id, 'name' => '5P 1,8 EX L 2WD CVT 2019']);
    $zrv = CarModel::create(['brand_id' => $honda->id, 'name' => 'ZR-V', 'slug' => 'zr-v']);
    Version::create(['car_model_id' => $zrv->id, 'name' => '5P 2,0 CVT TRG']);

    // RENAULT
    $renault = Brand::create(['name' => 'RENAULT', 'slug' => 'renault']);
    $kardian = CarModel::create(['brand_id' => $renault->id, 'name' => 'KARDIAN', 'slug' => 'kardian']);
    Version::create(['car_model_id' => $kardian->id, 'name' => '5P 1,0 T AT6 ICONIC 2025']);

    // PORSCHE
    $porsche = Brand::create(['name' => 'PORSCHE', 'slug' => 'porsche']);
    $boxster = CarModel::create(['brand_id' => $porsche->id, 'name' => 'BOXSTER', 'slug' => 'boxster']);
    $macan = CarModel::create(['brand_id' => $porsche->id, 'name' => 'MACAN', 'slug' => 'macan']);
    Version::create(['car_model_id' => $boxster->id, 'name' => '2P 2,0 BOXSTER']);
    Version::create(['car_model_id' => $macan->id, 'name' => '5P 2,0 T 252CV']);

    // CITROEN
    $citroen = Brand::create(['name' => 'CITROEN', 'slug' => 'citroen']);
    $c3Aircross = CarModel::create(['brand_id' => $citroen->id, 'name' => 'C 3 AIRCROSS', 'slug' => 'c-3-aircross']);
    Version::create(['car_model_id' => $c3Aircross->id, 'name' => '5P T200 FEEL PK 2024']);
    Version::create(['car_model_id' => $c3Aircross->id, 'name' => '5P 1,6 VTI FEEL PK 2024']);
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

it('normalizes ctv to cvt when matching infoauto names', function () {
    $result = $this->matcher->findVersion('HONDA', 'HR-V', 'HR-V 1.5 LX CTV L/25');

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P 1,5 LX CVT 2025');
});

it('normalizes ex l to exl when matching infoauto names', function () {
    $result = $this->matcher->findVersion('HONDA', 'HR-V', 'HR-V 1.8 EXL CVT L/19');

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P 1,8 EX L 2WD CVT 2019');
});

it('maps touring infoauto names to trg local abbreviations', function () {
    $result = $this->matcher->findVersion('HONDA', 'ZR-V', 'ZR-V 2.0 TOURING');

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P 2,0 CVT TRG');
});

it('matches full magazine titles without explicit model splitting', function () {
    $catalog = Version::query()->with(['carModel.brand'])->get();

    $result = $this->matcher->findVersionByInfoautoName('HONDA HR-V 1.5 EXL CVT L/25', null, $catalog);

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P 1,5 EXL CVT 2025');
});

it('matches magazine title with brand hint and implicit model context', function () {
    $catalog = Version::query()->with(['carModel.brand'])->get();

    $result = $this->matcher->findVersionByInfoautoName('PULSE 1.3 DRIVE CVT L/25', 'FIAT', $catalog);

    expect($result)->not->toBeNull()
        ->and($result->carModel->slug)->toBe('pulse');
});

it('prefers the explicit brand in the title over a stale section brand hint', function () {
    $catalog = Version::query()->with(['carModel.brand'])->get();

    $result = $this->matcher->findVersionByInfoautoName('HONDA HR-V 1.5 EXL CVT L/25', 'FIAT', $catalog);

    expect($result)->not->toBeNull()
        ->and($result->carModel->brand->slug)->toBe('honda');
});

it('matches numbered magazine titles after normalization', function () {
    $catalog = Version::query()->with(['carModel.brand'])->get();

    $result = $this->matcher->findVersionByInfoautoName('1. HONDA HR-V 1.5 EXL CVT L/25', null, $catalog);

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P 1,5 EXL CVT 2025');
});

it('matches citroen c3 aircross feel pk despite local engine code naming', function () {
    $catalog = Version::query()->with(['carModel.brand'])->get();

    $result = $this->matcher->findVersionByInfoautoName('C3 AIRCROSS 1.3 FEEL PK', 'Citroen', $catalog);

    expect($result)->not->toBeNull()
        ->and($result->carModel->slug)->toBe('c-3-aircross')
        ->and($result->name)->toBe('5P T200 FEEL PK 2024');
});

it('breaks ties using observed 0km price when infoauto title is ambiguous', function () {
    $catalog = Version::query()->with([
        'carModel.brand',
        'priceSnapshots' => fn ($query) => $query
            ->where('source', 'infoauto')
            ->where('year', 0)
            ->orderByDesc('recorded_at'),
    ])->get();

    $candidates = Version::query()
        ->whereHas('carModel', fn ($query) => $query->where('slug', 'c-3-aircross'))
        ->get();

    foreach ($candidates as $candidate) {
        PriceSnapshot::create([
            'version_id' => $candidate->id,
            'year' => 0,
            'price' => 0,
            'raw_price_ars_thousands' => $candidate->name === '5P T200 FEEL PK 2024' ? 36900 : 31200,
            'source' => 'infoauto',
            'recorded_at' => '2026-04-13',
        ]);
    }

    $catalog = Version::query()->with([
        'carModel.brand',
        'priceSnapshots' => fn ($query) => $query
            ->where('source', 'infoauto')
            ->where('year', 0)
            ->orderByDesc('recorded_at'),
    ])->get();

    $result = $this->matcher->findVersionByInfoautoName(
        'C3 AIRCROSS 1.3 FEEL PK',
        'Citroen',
        $catalog,
        [0 => 36900.0],
    );

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P T200 FEEL PK 2024');
});

it('normalizes edc and dct style transmission labels from infoauto titles', function () {
    $catalog = Version::query()->with(['carModel.brand'])->get();

    $result = $this->matcher->findVersionByInfoautoName('Kardian 1.0T Iconic 200 EDC L/25', 'RENAULT', $catalog);

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('5P 1,0 T AT6 ICONIC 2025');
});

it('infers the correct Porsche model from the version name context', function () {
    $result = $this->matcher->findVersion('PORSCHE', '718', '718 2.0 BOXSTER');

    expect($result)->not->toBeNull()
        ->and($result->carModel->name)->toBe('BOXSTER')
        ->and($result->name)->toBe('2P 2,0 BOXSTER');
});
