<?php

it('has all required top-level keys', function () {
    $config = config('infoauto-predictions');

    expect($config)->toHaveKeys([
        'formula_version',
        'current_model_year',
        'years_back',
        'mercosur_brands',
        'calibrated',
        'tiers',
        'drop_tables',
        'offsets',
        'line_year_premium_per_year',
        'segment_keywords',
    ]);
});

it('mercosur_brands contains exactly 9 brands', function () {
    $brands = config('infoauto-predictions.mercosur_brands');

    expect($brands)->toHaveCount(9)
        ->and($brands)->toContain('TOYOTA', 'VOLKSWAGEN', 'FIAT', 'FORD', 'CHEVROLET', 'PEUGEOT', 'RENAULT', 'HONDA', 'NISSAN');
});

it('all calibrated factors are between 0.5 and 1.0', function () {
    $calibrated = config('infoauto-predictions.calibrated');

    foreach ($calibrated as $brand => $models) {
        foreach ($models as $model => $clusters) {
            foreach ($clusters as $clusterKey => $cluster) {
                expect($cluster['factor'])
                    ->toBeGreaterThanOrEqual(0.5, "Factor too low: {$brand}.{$model}.{$clusterKey}")
                    ->toBeLessThanOrEqual(1.0, "Factor too high: {$brand}.{$model}.{$clusterKey}");
            }
        }
    }
});

it('all calibrated drops are positive arrays', function () {
    $calibrated = config('infoauto-predictions.calibrated');

    foreach ($calibrated as $brand => $models) {
        foreach ($models as $model => $clusters) {
            foreach ($clusters as $clusterKey => $cluster) {
                expect($cluster['drops'])->toBeArray()
                    ->not->toBeEmpty();

                foreach ($cluster['drops'] as $drop) {
                    expect($drop)->toBeGreaterThan(0, "Negative drop in {$brand}.{$model}.{$clusterKey}");
                }
            }
        }
    }
});

it('all calibrated clusters have valid match rules', function () {
    $calibrated = config('infoauto-predictions.calibrated');

    foreach ($calibrated as $brand => $models) {
        foreach ($models as $model => $clusters) {
            foreach ($clusters as $clusterKey => $cluster) {
                expect($cluster)->toHaveKey('match');
                expect($cluster['match'])->toHaveKeys(['all', 'any', 'none']);
                expect($cluster['match']['all'])->toBeArray("{$brand}.{$model}.{$clusterKey}: 'all' must be array");
                expect($cluster['match']['any'])->toBeArray("{$brand}.{$model}.{$clusterKey}: 'any' must be array");
                expect($cluster['match']['none'])->toBeArray("{$brand}.{$model}.{$clusterKey}: 'none' must be array");
            }
        }
    }
});

it('all calibrated clusters have a segment', function () {
    $validSegments = array_keys(config('infoauto-predictions.drop_tables'));
    $calibrated = config('infoauto-predictions.calibrated');

    foreach ($calibrated as $brand => $models) {
        foreach ($models as $model => $clusters) {
            foreach ($clusters as $clusterKey => $cluster) {
                expect($cluster)->toHaveKey('segment');
                expect($validSegments)->toContain($cluster['segment']);
            }
        }
    }
});

it('formula_version is a non-empty string', function () {
    expect(config('infoauto-predictions.formula_version'))
        ->toBeString()
        ->not->toBeEmpty();
});

it('all tier factors are between 0.5 and 1.0', function () {
    foreach (config('infoauto-predictions.tiers') as $tier => $data) {
        expect($data['factor'])
            ->toBeGreaterThanOrEqual(0.5, "Tier {$tier} factor too low")
            ->toBeLessThanOrEqual(1.0, "Tier {$tier} factor too high");
    }
});

it('drop tables have at least 1 entry per segment', function () {
    foreach (config('infoauto-predictions.drop_tables') as $segment => $drops) {
        expect($drops)->toBeArray()->not->toBeEmpty("Empty drop table for {$segment}");
    }
});
