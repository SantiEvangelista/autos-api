<?php

use App\Services\VersionDisplayService;

// --- Diesel unificado ---

it('replaces TDi with Diesel', function () {
    expect(VersionDisplayService::humanize('4P 1,9 TDi TRENDLINE'))
        ->toBe('4P 1,9 Diesel Trendline');
});

it('replaces TDI uppercase with Diesel', function () {
    expect(VersionDisplayService::humanize('4P 2,0 TDI MULTI ATTRACTION'))
        ->toBe('4P 2,0 Diesel Multi Attraction');
});

it('replaces DSL with Diesel', function () {
    expect(VersionDisplayService::humanize('4P 2,0 DSL LTZ AT'))
        ->toBe('4P 2,0 Diesel Ltz AT');
});

it('replaces HDI with Diesel', function () {
    expect(VersionDisplayService::humanize('FURGON 1,6 HDI BUSINESS'))
        ->toBe('Furgon 1,6 Diesel Business');
});

it('replaces CDI with Diesel', function () {
    expect(VersionDisplayService::humanize('4P 2,2 CDI AVANTGARDE AT'))
        ->toBe('4P 2,2 Diesel Avantgarde AT');
});

it('replaces TDCI with Diesel', function () {
    expect(VersionDisplayService::humanize('5P 1,4 TDCI XLS'))
        ->toBe('5P 1,4 Diesel Xls');
});

// --- VW trims ---

it('expands COMF to Comfortline', function () {
    expect(VersionDisplayService::humanize('D/C 2,0 TDi 180CV 4X2 COMF 2017'))
        ->toBe('D/C 2,0 Diesel 180CV 4X2 Comfortline 2017');
});

it('expands HIGH to Highline in Amarok context', function () {
    expect(VersionDisplayService::humanize('D/C 3,0 TDi V6 4X4 8AT HIGH 258CV 2023'))
        ->toBe('D/C 3,0 Diesel V6 4X4 8AT Highline 258CV 2023');
});

// --- HIGH NO se expande en otros contextos ---

it('does not expand HIGH in HIGH COUNTRY', function () {
    expect(VersionDisplayService::humanize('D/C V8 AT 4X4 HIGH COUNTRY'))
        ->toBe('D/C V8 AT 4X4 High Country');
});

it('does not expand HIGH in HIGH TECH', function () {
    expect(VersionDisplayService::humanize('5P 1,6 16V SX HIGH TECH'))
        ->toBe('5P 1,6 16V Sx High Tech');
});

it('does not expand HIGH in HIGH SECURITY', function () {
    expect(VersionDisplayService::humanize('5P 1,4 16V ACTIVE HIGH SECURITY'))
        ->toBe('5P 1,4 16V Active High Security');
});

it('does not expand PASSION HIGH', function () {
    expect(VersionDisplayService::humanize('COUPE 1,0 PASSION HIGH'))
        ->toBe('Coupe 1,0 Passion High');
});

it('does not expand HIGH-POWER (hyphenated)', function () {
    $result = VersionDisplayService::humanize('D/C 2,4 DI-D HIGH-POWER 4WD 6MT');
    expect($result)->not->toContain('Highline');
});

it('does not expand HIGHLINE (already complete)', function () {
    expect(VersionDisplayService::humanize('4P 1,6 MSI HIGHLINE'))
        ->toBe('4P 1,6 MSI Highline');
});

// --- G2 ---

it('expands G2 to Gen.2', function () {
    expect(VersionDisplayService::humanize('D/C 2,0 TDi 180CV 4X2 COMF G2 2025'))
        ->toBe('D/C 2,0 Diesel 180CV 4X2 Comfortline Gen.2 2025');
});

// --- SE → Serie Especial (solo con Gen.2) ---

it('expands SE to Serie Especial when Gen.2 is present', function () {
    expect(VersionDisplayService::humanize('D/C 2,0 TDi 180CV 4X2 COMF G2 SE 2026'))
        ->toBe('D/C 2,0 Diesel 180CV 4X2 Comfortline Gen.2 Serie Especial 2026');
});

it('expands SE to Serie Especial with AT before SE', function () {
    expect(VersionDisplayService::humanize('D/C 2,0 TDi 180CV 4X2 HIGH G2 AT SE 2026'))
        ->toBe('D/C 2,0 Diesel 180CV 4X2 Highline Gen.2 AT Serie Especial 2026');
});

it('does not expand SE when Gen.2 is not present (Ford trim)', function () {
    expect(VersionDisplayService::humanize('5P 1,6 SE 2018'))
        ->toBe('5P 1,6 Se 2018');
});

it('does not expand SE mid-string even with year', function () {
    expect(VersionDisplayService::humanize('5P 1,6 SE PLUS POWERSHIFT 2018'))
        ->toBe('5P 1,6 Se Plus Powershift 2018');
});

// --- Title Case + preserve ---

it('applies title case while preserving known tokens', function () {
    expect(VersionDisplayService::humanize('4P 2.0 SEG CVT'))
        ->toBe('4P 2.0 Seg CVT');
});

it('preserves transmission tokens', function () {
    expect(VersionDisplayService::humanize('5P 2,0 TSI 211CV DSG'))
        ->toBe('5P 2,0 TSI 211CV DSG');
});

it('preserves traction tokens', function () {
    expect(VersionDisplayService::humanize('D/C 2,8 SRX 4X4 AT'))
        ->toBe('D/C 2,8 Srx 4X4 AT');
});

it('preserves compound transmission tokens', function () {
    expect(VersionDisplayService::humanize('D/C 3,0 TDi V6 4X4 8AT EXTREME 258CV'))
        ->toBe('D/C 3,0 Diesel V6 4X4 8AT Extreme 258CV');
});

it('handles hyphenated tokens with title case', function () {
    $result = VersionDisplayService::humanize('4X4 CD GR-S II 2.8 TDI 6AT');
    expect($result)->toContain('Gr-S');
});

// --- Caso completo canónico ---

it('transforms the canonical Amarok V6 example', function () {
    expect(VersionDisplayService::humanize('D/C 3,0 TDi V6 4X4 8AT BLACK STYLE G2 258CV SE 2026'))
        ->toBe('D/C 3,0 Diesel V6 4X4 8AT Black Style Gen.2 258CV Serie Especial 2026');
});
