<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Version;
use App\Services\PricePredictionService;

beforeEach(function () {
    $this->service = new PricePredictionService();
});

function makeVersion(string $brandSlug, string $brandName, string $modelSlug, string $modelName, string $versionName): Version
{
    $brand = Brand::create(['name' => $brandName, 'slug' => $brandSlug]);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => $modelName, 'slug' => $modelSlug]);

    return Version::create(['car_model_id' => $model->id, 'name' => $versionName]);
}

it('matches normalized tokens with exact any and none rules', function () {
    $rules = ['all' => ['SR'], 'any' => ['AT6', 'AT'], 'none' => ['SRV', 'SRX']];

    expect($this->service->versionMatchesCluster('2,8 DC 4x4 TDI SR AT6', $rules))->toBeTrue()
        ->and($this->service->versionMatchesCluster('2,8 DC 4x4 TDI SRV AT6', $rules))->toBeFalse();
});

it('matches long abbreviations as prefixes but avoids false positives on short tokens', function () {
    $rules = ['all' => [], 'any' => ['COMF'], 'none' => []];
    $shortTokenRules = ['all' => ['LT'], 'any' => [], 'none' => []];

    expect($this->service->versionMatchesCluster('1.0 TSI COMFORTLINE AUT', $rules))->toBeTrue()
        ->and($this->service->versionMatchesCluster('1.0 LTZ AT', $shortTokenRules))->toBeFalse();
});

it('rounds to the nearest hundred', function () {
    expect($this->service->roundTo100(25312.8))->toBe(25300.0)
        ->and($this->service->roundTo100(25350.0))->toBe(25400.0);
});

it('generates used rows from current minus one and stops at line year', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 31120.0,
        factor: 0.813,
        drops: [1700, 1000, 1000, 1000, 1000, 1000, 1000],
        currentModelYear: 2026,
        yearsBack: 7,
        lineYear: 2025,
    );

    expect($prices)->toBe([
        2025 => 25300.0,
    ]);
});

it('applies model year premium before generating years', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 41200.0,
        factor: 0.80,
        drops: [1500, 1500, 1500],
        currentModelYear: 2026,
        yearsBack: 3,
        lineYear: 2024,
        lineYearPremiumPerYear: 1000,
    );

    expect($prices)->toBe([
        2025 => 32000.0,
        2024 => 30500.0,
    ]);
});

it('repeats last drop when the drops array is shorter than years back', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 40000.0,
        factor: 0.80,
        drops: [2000, 1500],
        currentModelYear: 2026,
        yearsBack: 5,
    );

    expect($prices)->toBe([
        2025 => 32000.0,
        2024 => 30000.0,
        2023 => 28500.0,
        2022 => 27000.0,
        2021 => 25500.0,
    ]);
});

it('returns no prices when a line year is newer than the first used year', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 40000.0,
        factor: 0.80,
        drops: [2000, 1500],
        currentModelYear: 2026,
        yearsBack: 5,
        lineYear: 2026,
    );

    expect($prices)->toBe([]);
});

it('finds the best partial cluster for intra family fallback', function () {
    $clusters = [
        'base_like' => [
            'match' => ['all' => ['LIKE'], 'any' => [], 'none' => []],
            'factor' => 0.813,
            'drops' => [1700],
            'segment' => 'sedan_chico',
        ],
        'medio_drive' => [
            'match' => ['all' => ['DRIVE'], 'any' => [], 'none' => ['LIKE', 'PRECISION']],
            'factor' => 0.721,
            'drops' => [1200],
            'segment' => 'sedan_chico',
        ],
        'full_precision' => [
            'match' => ['all' => ['PRECISION'], 'any' => ['CVT'], 'none' => []],
            'factor' => 0.786,
            'drops' => [2000],
            'segment' => 'sedan_chico',
        ],
    ];

    $match = $this->service->findPartialCluster('4P 1,3 DRIVE PACK PLUS CVT', $clusters);

    expect($match)->not->toBeNull()
        ->and($match['key'])->toBe('medio_drive');
});

it('matches an exact calibrated cluster with high confidence', function () {
    $version = makeVersion('toyota', 'TOYOTA', 'hilux-pick-up', 'HILUX PICK - UP', '2.4 DC 4X4 TDI DX');

    $result = $this->service->matchCluster($version);

    expect($result)->not->toBeNull()
        ->and($result['confidence'])->toBe('high')
        ->and($result['cluster_key'])->toBe('calibrated:toyota:hilux-pick-up:base_dx');
});

it('matches cronos drive pack plus directly after the expanded calibrated rules', function () {
    $version = makeVersion('fiat', 'FIAT', 'cronos', 'CRONOS', '4P 1,3 DRIVE PACK PLUS CVT');

    $result = $this->service->matchCluster($version);

    expect($result)->not->toBeNull()
        ->and($result['confidence'])->toBe('high')
        ->and($result['cluster_key'])->toBe('calibrated:fiat:cronos:medio_drive');
});

it('falls back to tier strategy with low confidence for non calibrated mercosur models', function () {
    $version = makeVersion('ford', 'FORD', 'territory', 'TERRITORY', '1.8 TITANIUM AT');

    $result = $this->service->matchCluster($version);

    expect($result)->not->toBeNull()
        ->and($result['confidence'])->toBe('low')
        ->and($result['cluster_key'])->toBe('tier:popular:suv')
        ->and($result['config']['factor'])->toBe(0.80);
});

it('returns null for non mercosur brands', function () {
    $version = makeVersion('bmw', 'BMW', 'serie-3', 'SERIE 3', '320I AT');

    expect($this->service->matchCluster($version))->toBeNull();
});

it('extracts line year from l slash year or full year', function () {
    expect($this->service->extractLineYear('CRONOS 1.3 LIKE L/25'))->toBe(2025)
        ->and($this->service->extractLineYear('5P 1,0 T IMPETUS CVT 2024'))->toBe(2024)
        ->and($this->service->extractLineYear('2.0 XEI CVT'))->toBeNull();
});

it('predicts metadata and prices for a calibrated version with line year awareness', function () {
    $version = makeVersion('fiat', 'FIAT', 'pulse', 'PULSE', '5P 1,0 T IMPETUS CVT L/25');

    $prediction = $this->service->predict($version, 42670.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['confidence'])->toBe('high')
        ->and($prediction['cluster_key'])->toBe('calibrated:fiat:pulse:alto_impetus_l25')
        ->and($prediction['predictions'])->toBe([
            2025 => 34500.0,
        ]);
});

it('predicts the legacy pulse impetus curve from the latest magazine sample', function () {
    $version = makeVersion('fiat', 'FIAT', 'pulse', 'PULSE', '5P 1,0 T IMPETUS CVT');

    $prediction = $this->service->predict($version, 42670.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['cluster_key'])->toBe('calibrated:fiat:pulse:alto_impetus')
        ->and($prediction['predictions'])->toBe([
            2025 => 32500.0,
            2024 => 31000.0,
            2023 => 29600.0,
            2022 => 28300.0,
            2021 => 27000.0,
            2020 => 25700.0,
            2019 => 24400.0,
        ]);
});

it('predicts cronos drive pack plus cvt close to the revista values', function () {
    $version = makeVersion('fiat', 'FIAT', 'cronos', 'CRONOS', '4P 1,3 DRIVE GSE PACK PLUS CVT 2025');

    $prediction = $this->service->predict($version, 37430.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['predictions'])->toBe([
            2025 => 27000.0,
        ]);
});

it('predicts frontier x-gear closer to the calibrated revista sample', function () {
    $version = makeVersion('nissan', 'NISSAN', 'frontier-pick-up', 'FRONTIER PICK - UP', 'D/C 2,3 TD 4X4 X-GEAR AT 190CV 2024');

    $prediction = $this->service->predict($version, 60421.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['predictions'])->toBe([
            2025 => 42000.0,
            2024 => 39000.0,
        ]);
});

it('predicts hilux srv without applying the global line premium heuristic', function () {
    $version = makeVersion('toyota', 'TOYOTA', 'hilux-pick-up', 'HILUX PICK - UP', 'D/C 2,8 TDi 4X4 SRV 6AT 2024');

    $prediction = $this->service->predict($version, 76381.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['predictions'])->toBe([
            2025 => 56500.0,
            2024 => 54500.0,
        ]);
});

it('predicts hilux srx l slash 24 with the recalibrated srx curve', function () {
    $version = makeVersion('toyota', 'TOYOTA', 'hilux-pick-up', 'HILUX PICK - UP', 'D/C 2,8 TDi 4X4 SRX 6AT 2024');

    $prediction = $this->service->predict($version, 77921.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['predictions'])->toBe([
            2025 => 57000.0,
            2024 => 55000.0,
        ]);
});

it('predicts kicks exclusive 1.6 with the new exclusive curve', function () {
    $version = makeVersion('nissan', 'NISSAN', 'kicks', 'KICKS', '5P 1,6 EXCLUSIVE CVT');

    $prediction = $this->service->predict($version, 46300.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['predictions'])->toBe([
            2025 => 38200.0,
            2024 => 36900.0,
            2023 => 36100.0,
            2022 => 34500.0,
            2021 => 33300.0,
            2020 => 32600.0,
            2019 => 31900.0,
        ]);
});

it('predicts fastback 1.3t closer to the revista sample after recalibration', function () {
    $version = makeVersion('fiat', 'FIAT', 'fastback', 'FASTBACK', '5P 1,3 TURBO 270 AT6');

    $prediction = $this->service->predict($version, 45540.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['predictions'])->toBe([
            2025 => 34000.0,
            2024 => 32000.0,
            2023 => 30000.0,
            2022 => 28000.0,
            2021 => 26000.0,
            2020 => 24000.0,
            2019 => 22000.0,
        ]);
});

it('predicts nivus highline with the positive offset validated by the magazine', function () {
    $version = makeVersion('volkswagen', 'VOLKSWAGEN', 'nivus', 'NIVUS', '5P 1,0 TSI 200 6AT HIGHLINE');

    $prediction = $this->service->predict($version, 51994.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['predictions'])->toBe([
            2025 => 36500.0,
            2024 => 35000.0,
            2023 => 33800.0,
            2022 => 32600.0,
            2021 => 31400.0,
            2020 => 30200.0,
            2019 => 29000.0,
        ]);
});

it('predicts hr-v lx l slash 25 with the recalibrated lx curve', function () {
    $version = makeVersion('honda', 'HONDA', 'hr-v', 'HR-V', '5P 1,5 LX CVT 2025');

    $prediction = $this->service->predict($version, 45890.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['predictions'])->toBe([
            2025 => 41900.0,
        ]);
});

it('predicts pulse abarth l slash 25 from the scanned revista sample', function () {
    $version = makeVersion('fiat', 'FIAT', 'pulse', 'PULSE', '5P 270 TURBO ABARTH 6AT L/25');

    $prediction = $this->service->predict($version, 44750.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['predictions'])->toBe([
            2025 => 37100.0,
        ]);
});

it('predicts zr-v touring style trim through the trg cluster', function () {
    $version = makeVersion('honda', 'HONDA', 'zr-v', 'ZR-V', '5P 2,0 CVT TRG');

    $prediction = $this->service->predict($version, 59990.0);

    expect($prediction)->not->toBeNull()
        ->and($prediction['cluster_key'])->toBe('calibrated:honda:zr-v:alto_trg')
        ->and($prediction['predictions'])->toBe([
            2025 => 48500.0,
            2024 => 46500.0,
            2023 => 44500.0,
            2022 => 42500.0,
            2021 => 40500.0,
            2020 => 38500.0,
            2019 => 36500.0,
        ]);
});
