<?php

use App\Services\PricePredictionService;

beforeEach(function () {
    $this->service = new PricePredictionService();
});


it('matches when all tokens are present', function () {
    $rules = ['all' => ['SRX'], 'any' => [], 'none' => []];

    expect($this->service->versionMatchesCluster('2.8 DC 4X4 SRX AT6', $rules))->toBeTrue();
});

it('fails when an all token is missing', function () {
    $rules = ['all' => ['SRX', 'TURBO'], 'any' => [], 'none' => []];

    expect($this->service->versionMatchesCluster('2.8 DC 4X4 SRX AT6', $rules))->toBeFalse();
});

it('matches when at least one any token is present', function () {
    $rules = ['all' => [], 'any' => ['GR-S', 'GR S', 'GRS'], 'none' => []];

    expect($this->service->versionMatchesCluster('2.8 DC 4X4 GR-S AT6', $rules))->toBeTrue();
});

it('fails when no any token is present', function () {
    $rules = ['all' => [], 'any' => ['GR-S', 'GR S', 'GRS'], 'none' => []];

    expect($this->service->versionMatchesCluster('2.8 DC 4X4 SRX AT6', $rules))->toBeFalse();
});

it('fails when a none token is present', function () {
    $rules = ['all' => ['SR'], 'any' => [], 'none' => ['SRV', 'SRX']];

    expect($this->service->versionMatchesCluster('2.8 DC 4X4 SRV AT6', $rules))->toBeFalse();
});

it('passes combined all+any+none rules', function () {
    $rules = ['all' => ['DX'], 'any' => ['2.4', '2,4'], 'none' => ['SRV']];

    expect($this->service->versionMatchesCluster('2.4 DC 4X4 TDI DX', $rules))->toBeTrue();
});

it('passes when all and any arrays are empty', function () {
    $rules = ['all' => [], 'any' => [], 'none' => ['ABARTH']];

    expect($this->service->versionMatchesCluster('1.3 TURBO 270 CVT', $rules))->toBeTrue();
});

it('matches case insensitively', function () {
    $rules = ['all' => ['SRX'], 'any' => [], 'none' => []];

    expect($this->service->versionMatchesCluster('2.8 dc 4x4 srx at6', $rules))->toBeTrue();
});

it('matches tokens as substrings', function () {
    $rules = ['all' => [], 'any' => ['COMFORTLINE', 'COMF'], 'none' => []];

    expect($this->service->versionMatchesCluster('1.0 TSI COMFORTLINE AUT', $rules))->toBeTrue();
});


it('rounds down to nearest 100', function () {
    expect($this->service->roundTo100(25312.8))->toBe(25300.0);
});

it('rounds up to nearest 100', function () {
    expect($this->service->roundTo100(25350.0))->toBe(25400.0);
});

it('keeps exact multiples of 100 unchanged', function () {
    expect($this->service->roundTo100(25000.0))->toBe(25000.0);
    expect($this->service->roundTo100(100.0))->toBe(100.0);
});


it('parses L/25 format as 2025', function () {
    expect($this->service->parseLineYear('2.0 XEI CVT Safety L/25'))->toBe(2025);
});

it('parses L/2025 format as 2025', function () {
    expect($this->service->parseLineYear('2.0 XEI CVT Safety L/2025'))->toBe(2025);
});

it('parses trailing 4-digit year', function () {
    expect($this->service->parseLineYear('4P 1,3 LIKE GSE 2025'))->toBe(2025);
});

it('returns null when no line year is found', function () {
    expect($this->service->parseLineYear('2.4 DC 4X4 TDI DX'))->toBeNull();
});

it('parses L/21 format as 2021', function () {
    expect($this->service->parseLineYear('2.4 DC 4X4 TDI DX L/21'))->toBe(2021);
});

it('does not match non-year numbers as line year', function () {
    // "1.3" should not be parsed as a year
    expect($this->service->parseLineYear('1.3 LIKE GSE'))->toBeNull();
});


it('calculates Cronos LIKE prices correctly', function () {    
    $prices = $this->service->calculatePrices(
        price0kmArsK: 31120.0,
        factor: 0.813,
        drops: [1700, 1000, 1000, 1000, 1000, 1000, 1000],
        currentModelYear: 2026,
        yearsBack: 7,
    );

    expect($prices)->toBe([
        2026 => 25300.0,
        2025 => 23600.0,
        2024 => 22600.0,
        2023 => 21600.0,
        2022 => 20600.0,
        2021 => 19600.0,
        2020 => 18600.0,
    ]);
});

it('calculates Hilux SRX prices correctly', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 84349.0,
        factor: 0.783,
        drops: [2000, 2000, 2000, 2000, 2000, 2000, 2000],
        currentModelYear: 2026,
        yearsBack: 7,
    );

    expect($prices[2026])->toBe(66000.0)  
        ->and($prices[2025])->toBe(64000.0)  
        ->and($prices[2024])->toBe(62000.0); 
});

it('repeats last drop when drops array is shorter than years_back', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 40000.0,
        factor: 0.80,
        drops: [2000, 1500],
        currentModelYear: 2026,
        yearsBack: 5,
    );

    expect($prices)->toBe([
        2026 => 32000.0,
        2025 => 30000.0,
        2024 => 28500.0,
        2023 => 27000.0,
        2022 => 25500.0,
    ]);
});

it('stops when price would go to zero or negative', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 5000.0,
        factor: 0.80,
        drops: [2000, 2000, 2000, 2000],
        currentModelYear: 2026,
        yearsBack: 4,
    );

    expect($prices)->toBe([
        2026 => 4000.0,
        2025 => 2000.0,
    ]);
});

it('returns empty array when patentado itself is zero', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 0.0,
        factor: 0.80,
        drops: [1000],
        currentModelYear: 2026,
        yearsBack: 3,
    );

    expect($prices)->toBe([]);
});

it('limits years to line year for L/25 version', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 31120.0,
        factor: 0.813,
        drops: [1700, 1000, 1000, 1000, 1000, 1000, 1000],
        currentModelYear: 2026,
        yearsBack: 7,
        lineYear: 2025,
    );

    expect($prices)->toHaveCount(2)
        ->and(array_keys($prices))->toBe([2026, 2025])
        ->and($prices[2026])->toBe(25300.0)
        ->and($prices[2025])->toBe(23600.0);
});

it('generates more years for older line years', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 40000.0,
        factor: 0.80,
        drops: [2000, 2000, 2000, 2000, 2000, 2000, 2000],
        currentModelYear: 2026,
        yearsBack: 7,
        lineYear: 2023,
    );

    expect($prices)->toHaveCount(4)
        ->and(min(array_keys($prices)))->toBe(2023)
        ->and(max(array_keys($prices)))->toBe(2026);
});

it('applies line year premium for older lines', function () {
    $prices = $this->service->calculatePrices(
        price0kmArsK: 40000.0,
        factor: 0.80,
        drops: [2000, 2000, 2000],
        currentModelYear: 2026,
        yearsBack: 7,
        lineYear: 2024,
        lineYearPremium: 1000,
    );

    expect($prices[2026])->toBe(30000.0)
        ->and($prices[2025])->toBe(28000.0)
        ->and($prices[2024])->toBe(26000.0);
});

it('does not apply premium when line year equals current model year', function () {
    $withPremium = $this->service->calculatePrices(
        price0kmArsK: 40000.0,
        factor: 0.80,
        drops: [2000],
        currentModelYear: 2026,
        yearsBack: 3,
        lineYear: 2026,
        lineYearPremium: 1000,
    );

    $withoutPremium = $this->service->calculatePrices(
        price0kmArsK: 40000.0,
        factor: 0.80,
        drops: [2000],
        currentModelYear: 2026,
        yearsBack: 3,
    );

    expect($withPremium[2026])->toBe($withoutPremium[2026]);
});

it('treats null line year same as current model year', function () {
    $withNull = $this->service->calculatePrices(
        price0kmArsK: 40000.0,
        factor: 0.80,
        drops: [2000, 2000, 2000],
        currentModelYear: 2026,
        yearsBack: 4,
        lineYear: null,
    );

    expect($withNull)->toHaveCount(4)
        ->and(min(array_keys($withNull)))->toBe(2023);
});



it('finds the correct cluster for a matching version', function () {
    $modelClusters = [
        'base_dx' => [
            'match' => ['all' => ['DX'], 'any' => ['2.4', '2,4'], 'none' => []],
            'factor' => 0.744,
            'drops' => [2000, 2000, 1500, 1500, 1500, 1500, 1500],
            'segment' => 'pickup_mediana',
        ],
        'full_srx' => [
            'match' => ['all' => ['SRX'], 'any' => [], 'none' => []],
            'factor' => 0.783,
            'drops' => [2000, 2000, 2000, 2000, 2000, 2000, 2000],
            'segment' => 'pickup_mediana',
        ],
    ];

    $result = $this->service->findMatchingCluster('2.4 DC 4X4 TDI DX', $modelClusters);

    expect($result)->not->toBeNull()
        ->and($result['key'])->toBe('base_dx')
        ->and($result['config']['factor'])->toBe(0.744);
});

it('returns null when no cluster matches', function () {
    $modelClusters = [
        'base_dx' => [
            'match' => ['all' => ['DX'], 'any' => ['2.4'], 'none' => []],
            'factor' => 0.744,
            'drops' => [2000],
            'segment' => 'pickup_mediana',
        ],
    ];

    $result = $this->service->findMatchingCluster('COMPLETELY UNKNOWN VERSION', $modelClusters);

    expect($result)->toBeNull();
});

it('returns first matching cluster when multiple could match', function () {
    $modelClusters = [
        'base_sr' => [
            'match' => ['all' => ['SR'], 'any' => [], 'none' => ['SRV', 'SRX']],
            'factor' => 0.745,
            'drops' => [1500],
            'segment' => 'pickup_mediana',
        ],
        'medio_srv' => [
            'match' => ['all' => ['SRV'], 'any' => [], 'none' => []],
            'factor' => 0.766,
            'drops' => [2000],
            'segment' => 'pickup_mediana',
        ],
    ];

    $result = $this->service->findMatchingCluster('2.8 DC 4X4 SRV AT6', $modelClusters);

    expect($result['key'])->toBe('medio_srv');
});

it('respects none exclusion to skip first cluster', function () {
    $modelClusters = [
        'base_sr' => [
            'match' => ['all' => ['SR'], 'any' => [], 'none' => ['SRX']],
            'factor' => 0.745,
            'drops' => [1500],
            'segment' => 'pickup_mediana',
        ],
        'full_srx' => [
            'match' => ['all' => ['SRX'], 'any' => [], 'none' => []],
            'factor' => 0.783,
            'drops' => [2000],
            'segment' => 'pickup_mediana',
        ],
    ];

    $result = $this->service->findMatchingCluster('2.8 DC 4X4 SRX AT6', $modelClusters);

    expect($result['key'])->toBe('full_srx');
});
