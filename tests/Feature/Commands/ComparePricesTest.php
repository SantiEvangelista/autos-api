<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Valuation;
use App\Models\Version;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $brand = Brand::create(['name' => 'BMW', 'slug' => 'bmw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => '116', 'slug' => '116']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '5P 1,6 i']);
    Valuation::create(['version_id' => $version->id, 'year' => 2015, 'price' => 20000.00]);
    Valuation::create(['version_id' => $version->id, 'year' => 2014, 'price' => 18000.00]);

    $brand2 = Brand::create(['name' => 'AUDI', 'slug' => 'audi']);
    $model2 = CarModel::create(['brand_id' => $brand2->id, 'name' => 'A3', 'slug' => 'a3']);
    $version2 = Version::create(['car_model_id' => $model2->id, 'name' => '3P 1,4 TFSI']);
    Valuation::create(['version_id' => $version2->id, 'year' => 2015, 'price' => 15000.00]);
});

afterEach(function () {
    foreach (glob(storage_path('app/test_*.csv')) as $file) {
        File::delete($file);
    }
});

function createCsv(string $name, array $rows): string
{
    $path = storage_path("app/test_{$name}.csv");
    $fp = fopen($path, 'w');
    fputcsv($fp, ['brand', 'model', 'year', 'version', 'currency', 'price']);

    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }

    fclose($fp);

    return $path;
}

it('fails when no source files are provided', function () {
    $this->artisan('compare:prices')
        ->expectsOutputToContain('No external price data found')
        ->assertExitCode(1);
});

it('compares primary source prices against DB', function () {
    $csv = createCsv('primary', [
        ['BMW', '116', 2015, '5P 1,6 i', 'u$s', 20000],
        ['BMW', '116', 2014, '5P 1,6 i', 'u$s', 18000],
    ]);

    $this->artisan("compare:prices --primary={$csv}")
        ->expectsOutputToContain('Loaded: 2 primary')
        ->expectsOutputToContain('Matching')
        ->assertExitCode(0);
});

it('detects mismatches above threshold', function () {
    $csv = createCsv('primary', [
        ['BMW', '116', 2015, '5P 1,6 i', 'u$s', 25000], // +25% vs DB 20000
    ]);

    $this->artisan("compare:prices --primary={$csv} --threshold=10")
        ->expectsOutputToContain('Mismatched')
        ->assertExitCode(0);
});

it('reports matching prices within threshold', function () {
    $csv = createCsv('primary', [
        ['BMW', '116', 2015, '5P 1,6 i', 'u$s', 20500], // +2.5% vs DB 20000
    ]);

    $this->artisan("compare:prices --primary={$csv} --threshold=10")
        ->expectsOutputToContain('Matching')
        ->assertExitCode(0);
});

it('supports both primary and secondary sources simultaneously', function () {
    $primary = createCsv('primary', [
        ['BMW', '116', 2015, '5P 1,6 i', 'u$s', 20000],
    ]);
    $secondary = createCsv('secondary', [
        ['BMW', '116', 2015, '5P 1,6 i', 'u$s', 21000],
    ]);

    $this->artisan("compare:prices --primary={$primary} --secondary={$secondary}")
        ->expectsOutputToContain('1 primary, 1 secondary')
        ->assertExitCode(0);
});

it('skips diff calculation for non-USD currencies', function () {
    $csv = createCsv('primary', [
        ['BMW', '116', 2015, '5P 1,6 i', '$', 28000000], // ARS price, should not diff
    ]);

    // Should not flag as mismatch since ARS prices are not compared
    $this->artisan("compare:prices --primary={$csv} --threshold=10")
        ->assertExitCode(0);
});

it('tracks entries not found in the database', function () {
    $csv = createCsv('primary', [
        ['NONEXISTENT', 'MODEL', 2015, 'VERSION', 'u$s', 10000],
    ]);

    $this->artisan("compare:prices --primary={$csv}")
        ->expectsOutputToContain('Not found primary')
        ->assertExitCode(0);
});

it('exports results to a CSV file', function () {
    $source = createCsv('primary', [
        ['BMW', '116', 2015, '5P 1,6 i', 'u$s', 20000],
    ]);
    $exportPath = storage_path('app/test_export.csv');

    $this->artisan("compare:prices --primary={$source} --export={$exportPath}")
        ->expectsOutputToContain('Results exported')
        ->assertExitCode(0);

    expect(file_exists($exportPath))->toBeTrue();

    $lines = file($exportPath);
    expect(count($lines))->toBeGreaterThan(1);
    expect($lines[0])->toContain('type');
    expect($lines[0])->toContain('brand');
});

it('handles CSV with missing optional columns gracefully', function () {
    // CSV without currency column — should default to USD
    $path = storage_path('app/test_nocurrency.csv');
    $fp = fopen($path, 'w');
    fputcsv($fp, ['brand', 'model', 'year', 'version', 'price']);
    fputcsv($fp, ['BMW', '116', 2015, '5P 1,6 i', 20000]);
    fclose($fp);

    $this->artisan("compare:prices --primary={$path}")
        ->expectsOutputToContain('Loaded: 1 primary')
        ->assertExitCode(0);

    File::delete($path);
});
