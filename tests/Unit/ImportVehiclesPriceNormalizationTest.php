<?php

use App\Console\Commands\ImportVehicles;

describe('normalizePrice', function () {
    // ARS brands: values are ALWAYS in thousands, then converted to USD
    it('converts ARS brand prices from thousands to USD', function () {
        // VW Bora 1.8T Highline 2015: XLS=14728, real=ARS $14,728,000
        // CCA API says ~$13,991,000 ARS for this version
        $price = ImportVehicles::normalizePrice(14728.09, brandIsUsd: false, exchangeRate: 1403.0);

        // 14728.09 * 1000 / 1403 ≈ 10,497 USD
        expect($price)->toBeGreaterThan(10000);
        expect($price)->toBeLessThan(11000);
    });

    it('converts small ARS brand prices from thousands to USD', function () {
        // Chevrolet Classic LT Spirit Pack 2013: XLS=8792, real=ARS $8,792,000
        // CCA API says ~$8,352,000 ARS
        $price = ImportVehicles::normalizePrice(8791.95, brandIsUsd: false, exchangeRate: 1403.0);

        // 8791.95 * 1000 / 1403 ≈ 6,267 USD
        expect($price)->toBeGreaterThan(6000);
        expect($price)->toBeLessThan(7000);
    });

    // USD brands with large values: already in direct USD units
    it('keeps USD brand prices that are already in units', function () {
        // BMW 116 5P 1,6i 2012: XLS=20508, real=USD 20,508
        $price = ImportVehicles::normalizePrice(20508.0, brandIsUsd: true, exchangeRate: 1403.0);

        expect($price)->toBe(20508.0);
    });

    it('keeps large USD brand prices unchanged', function () {
        // BMW XDRIVE M Sport Pro 2025: XLS=175890, real=USD 175,890
        $price = ImportVehicles::normalizePrice(175890.0, brandIsUsd: true, exchangeRate: 1403.0);

        expect($price)->toBe(175890.0);
    });

    // USD luxury brands with small values: prices in thousands of USD
    it('scales up USD luxury brand prices from thousands', function () {
        // Porsche Cayenne E-Hybrid 2023: XLS=139.81, real=USD 139,810
        // CCA API says u$s 139,000
        $price = ImportVehicles::normalizePrice(139.81, brandIsUsd: true, exchangeRate: 1403.0);

        expect($price)->toBeGreaterThan(139000);
        expect($price)->toBeLessThan(140000);
    });

    it('scales up Ferrari prices from thousands', function () {
        // Ferrari 4.6 2014: XLS=414.13, real=USD 414,130
        $price = ImportVehicles::normalizePrice(414.13, brandIsUsd: true, exchangeRate: 1403.0);

        expect($price)->toBe(414130.0);
    });

    // Mixed brands: ARS model within a mixed brand must be treated as ARS
    it('converts mixed brand ARS model prices from thousands to USD', function () {
        // Peugeot RCZ Coupe 2013: XLS=29720, CCA API=$28,234,000 ARS
        // RCZ is NOT in KNOWN_USD_MODELS for Peugeot, so it's ARS
        $price = ImportVehicles::normalizePrice(29720.09, brandIsUsd: false, exchangeRate: 1403.0);

        // 29720.09 * 1000 / 1403 ≈ 21,180 USD
        expect($price)->toBeGreaterThan(21000);
        expect($price)->toBeLessThan(22000);
    });

    it('keeps mixed brand USD model prices as USD', function () {
        // Honda Accord (USD model within mixed Honda)
        // USD values >= 1000 are kept as-is
        $price = ImportVehicles::normalizePrice(18432.48, brandIsUsd: true, exchangeRate: 1403.0);

        expect($price)->toBe(18432.48);
    });

    // Critical acceptance criteria: no price should be 2 digits or less
    it('never produces a price with 2 digits or less', function () {
        $testCases = [
            // [rawPrice, brandIsUsd, description]
            [14728.09, false, 'VW Bora ARS'],
            [8791.95, false, 'Chevrolet Classic ARS'],
            [5819.25, false, 'Cheapest ARS brand car'],
            [29720.09, false, 'Peugeot RCZ ARS (mixed brand)'],
            [139.81, true, 'Porsche USD thousands'],
            [20508.0, true, 'BMW USD direct'],
            [414.13, true, 'Ferrari USD thousands'],
            [27.33, true, 'Porsche cheapest USD thousands'],
            [600.0, true, 'McLaren USD thousands'],
            [18432.48, true, 'Honda Accord USD (mixed brand)'],
        ];

        foreach ($testCases as [$rawPrice, $brandIsUsd, $description]) {
            $price = ImportVehicles::normalizePrice($rawPrice, $brandIsUsd, 1403.0);

            expect($price)
                ->toBeGreaterThan(100, "Failed for {$description}: price={$price}");
        }
    });

    // Edge case: exchange rate is null (no CSV provided, legacy mode)
    it('does not convert when exchange rate is null', function () {
        $price = ImportVehicles::normalizePrice(20508.0, brandIsUsd: true, exchangeRate: null);

        expect($price)->toBe(20508.0);
    });
});

describe('isModelUsd — mixed brand currency detection', function () {
    // Toyota YARIS CROSS is ARS-priced (locally available), NOT USD
    // Bug: str_contains("YARIS CROSS", "YARIS") was returning true
    it('treats Toyota YARIS CROSS as ARS, not USD', function () {
        expect(ImportVehicles::isModelUsd('TOYOTA', 'YARIS CROSS'))->toBeFalse();
    });

    // Toyota YARIS (non-GR) versions have ARS 0km prices
    it('treats Toyota YARIS as ARS', function () {
        expect(ImportVehicles::isModelUsd('TOYOTA', 'YARIS'))->toBeFalse();
    });

    // Toyota 86 and other actual USD models remain USD
    it('treats Toyota 86 as USD', function () {
        expect(ImportVehicles::isModelUsd('TOYOTA', '86'))->toBeTrue();
    });

    it('treats Toyota LAND CRUISER as USD', function () {
        expect(ImportVehicles::isModelUsd('TOYOTA', 'LAND CRUISER'))->toBeTrue();
    });

    it('treats Toyota RAV - 4 as USD', function () {
        expect(ImportVehicles::isModelUsd('TOYOTA', 'RAV - 4'))->toBeTrue();
    });

    it('treats Toyota HIACE as USD', function () {
        expect(ImportVehicles::isModelUsd('TOYOTA', 'HIACE'))->toBeTrue();
    });

    it('treats Toyota CROWN as USD', function () {
        expect(ImportVehicles::isModelUsd('TOYOTA', 'CROWN'))->toBeTrue();
    });

    // Fiat 600 is ARS-priced (0km = 49340 ARS thousands, confirmed by InfoAuto)
    it('treats Fiat 600 - 800 as ARS, not USD', function () {
        expect(ImportVehicles::isModelUsd('FIAT', '600 - 800'))->toBeFalse();
    });

    // Toyota COROLLA should remain ARS (not in USD list)
    it('treats Toyota COROLLA as ARS', function () {
        expect(ImportVehicles::isModelUsd('TOYOTA', 'COROLLA'))->toBeFalse();
    });

    // Peugeot 3008 and 5008 should remain USD
    it('treats Peugeot 3008 as USD', function () {
        expect(ImportVehicles::isModelUsd('PEUGEOT', '3008'))->toBeTrue();
    });

    // Honda ACCORD should remain USD
    it('treats Honda ACCORD as USD', function () {
        expect(ImportVehicles::isModelUsd('HONDA', 'ACCORD'))->toBeTrue();
    });
});
