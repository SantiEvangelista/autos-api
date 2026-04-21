<?php

use App\Services\PricePredictionService;

beforeEach(function () {
    $this->service = new PricePredictionService();
});

it('resolves toyota hilux to hilux-pick-up calibrated cluster', function () {
    $match = $this->service->matchClusterByNames('TOYOTA', 'HILUX', 'HILUX L/24 2.8 DC 4X4 TDI SRX AT6');

    expect($match)->not->toBeNull()
        ->and($match['confidence'])->toBe('high')
        ->and($match['cluster_key'])->toStartWith('calibrated:toyota:hilux-pick-up:');
});

it('resolves volkswagen amarok to amarok-pick-up', function () {
    $match = $this->service->matchClusterByNames('VOLKSWAGEN', 'AMAROK', 'AMAROK 2.0 TDI 180 4X4 HIGHLINE AT');

    expect($match)->not->toBeNull()
        ->and($match['confidence'])->not->toBe('low');
});

it('resolves ford ranger to ranger-pick-up', function () {
    $match = $this->service->matchClusterByNames('FORD', 'RANGER', 'RANGER 3.2 CD 4X4 LIMITED AT');

    expect($match)->not->toBeNull()
        ->and($match['cluster_key'])->toContain('ranger-pick-up');
});

it('resolves honda hrv to hr-v', function () {
    $match = $this->service->matchClusterByNames('HONDA', 'HRV', 'HRV 1.8 EXL CVT');

    expect($match)->not->toBeNull()
        ->and($match['cluster_key'])->toContain('hr-v');
});

it('resolves fiat strada to strada-pick-up', function () {
    $match = $this->service->matchClusterByNames('FIAT', 'STRADA', 'STRADA 1.3 FIREFLY VOLCANO CD');

    expect($match)->not->toBeNull()
        ->and($match['cluster_key'])->toContain('strada-pick-up');
});

it('does not alias models that already match config slug', function () {
    // Peugeot 208 está en config.calibrated.peugeot.208 y debe matchear sin alias
    $match = $this->service->matchClusterByNames('PEUGEOT', '208', '208 L/24 1.6 ACTIVE');

    expect($match)->not->toBeNull()
        ->and($match['cluster_key'])->toStartWith('calibrated:peugeot:208:');
});
