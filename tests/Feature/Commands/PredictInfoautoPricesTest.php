<?php

use App\Models\InfoautoCatalog;
use App\Models\InfoautoPriceHistory;
use App\Models\PriceSnapshot;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->catalog = InfoautoCatalog::factory()->create([
        'source_system' => 'test_source_system',
        'codia' => 'codia-hilux-dx',
        'brand_name' => 'TOYOTA',
        'model_name' => 'HILUX PICK UP',
        'version_name_raw' => '2.4 DC 4X4 TDI DX',
    ]);

    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $this->catalog->id,
        'year' => 0,
        'price_ars_thousands' => 57769.00,
        'origin' => 'real',
        'source' => 'test_source_system',
        'recorded_at' => '2026-04-16',
    ]);
});

it('generates predictions for a calibrated catalog entry using used years only', function () {
    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--recorded-at' => '2026-04-16',
    ])->assertSuccessful();

    $predictions = InfoautoPriceHistory::where('origin', 'predicted')
        ->where('infoauto_catalog_id', $this->catalog->id)
        ->orderBy('year', 'desc')
        ->get();

    expect($predictions)->toHaveCount(7);

    $year2025 = $predictions->firstWhere('year', 2025);
    expect($year2025)->not->toBeNull()
        ->and((float) $year2025->price_ars_thousands)->toBe(43000.0)
        ->and($year2025->source)->toBe('predicted')
        ->and($year2025->origin)->toBe('predicted');

    expect($predictions->firstWhere('year', 2026))->toBeNull();
});

it('is idempotent for the same recorded_at value', function () {
    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--recorded-at' => '2026-04-16',
    ])->assertSuccessful();

    $countAfterFirst = InfoautoPriceHistory::where('origin', 'predicted')->count();

    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--recorded-at' => '2026-04-16',
    ])->assertSuccessful();

    $countAfterSecond = InfoautoPriceHistory::where('origin', 'predicted')->count();

    expect($countAfterSecond)->toBe($countAfterFirst);
});

it('creates a new prediction vintage when recorded_at changes', function () {
    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--recorded-at' => '2026-04-16',
    ])->assertSuccessful();

    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--recorded-at' => '2026-05-01',
    ])->assertSuccessful();

    expect(InfoautoPriceHistory::where('origin', 'predicted')->count())->toBe(14);
});

it('never touches real price history rows', function () {
    $original = InfoautoPriceHistory::where('infoauto_catalog_id', $this->catalog->id)
        ->where('year', 0)
        ->first();

    $this->artisan('predict:infoauto-prices', ['--exchange-rate' => 1400])->assertSuccessful();

    $after = InfoautoPriceHistory::where('infoauto_catalog_id', $this->catalog->id)
        ->where('year', 0)
        ->first();

    expect((float) $after->price_ars_thousands)->toBe((float) $original->price_ars_thousands)
        ->and($after->id)->toBe($original->id)
        ->and($after->origin)->toBe('real');
});

it('does not predict for catalog entries from test_source_system_2 (no 0km base)', function () {
    InfoautoCatalog::factory()->create([
        'source_system' => 'test_source_system_2',
        'product_id' => 9999,
        'brand_name' => 'TOYOTA',
        'model_name' => 'HILUX PICK UP',
        'version_name_raw' => 'SR 2.8 TDI',
    ]);

    $this->artisan('predict:infoauto-prices', ['--exchange-rate' => 1400])->assertSuccessful();

    $predictionsForTestSourceSystem2 = InfoautoPriceHistory::where('origin', 'predicted')
        ->whereHas('catalog', fn ($q) => $q->where('product_id', 9999))
        ->count();

    expect($predictionsForTestSourceSystem2)->toBe(0);
});

it('applies filters by brand and model', function () {
    $polo = InfoautoCatalog::factory()->create([
        'source_system' => 'test_source_system',
        'codia' => 'codia-polo-track',
        'brand_name' => 'VOLKSWAGEN',
        'model_name' => 'POLO',
        'version_name_raw' => '1.6 MSI TRACK',
    ]);
    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $polo->id,
        'year' => 0,
        'price_ars_thousands' => 37567.00,
        'origin' => 'real',
        'source' => 'test_source_system',
        'recorded_at' => '2026-04-16',
    ]);

    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--brand' => 'toyota',
        '--model' => 'hilux-pick-up',
    ])->assertSuccessful();

    expect(InfoautoPriceHistory::where('origin', 'predicted')
        ->where('infoauto_catalog_id', $this->catalog->id)
        ->count())->toBeGreaterThan(0)
        ->and(InfoautoPriceHistory::where('origin', 'predicted')
            ->where('infoauto_catalog_id', $polo->id)
            ->count())->toBe(0);
});

it('uses tier fallback for mercosur models without calibrated rules', function () {
    $territory = InfoautoCatalog::factory()->create([
        'source_system' => 'test_source_system',
        'codia' => 'codia-territory',
        'brand_name' => 'FORD',
        'model_name' => 'TERRITORY',
        'version_name_raw' => '1.8 TITANIUM AT',
    ]);
    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $territory->id,
        'year' => 0,
        'price_ars_thousands' => 42000.00,
        'origin' => 'real',
        'source' => 'test_source_system',
        'recorded_at' => '2026-04-16',
    ]);

    $this->artisan('predict:infoauto-prices', ['--exchange-rate' => 1400])->assertSuccessful();

    $prediction = InfoautoPriceHistory::where('origin', 'predicted')
        ->where('infoauto_catalog_id', $territory->id)
        ->where('year', 2025)
        ->first();

    expect($prediction)->not->toBeNull();
});

it('respects line year and does not generate invalid used rows for l slash 25 versions', function () {
    $pulse = InfoautoCatalog::factory()->create([
        'source_system' => 'test_source_system',
        'codia' => 'codia-pulse-l25',
        'brand_name' => 'FIAT',
        'model_name' => 'PULSE',
        'version_name_raw' => '5P 1,0 T IMPETUS CVT L/25',
    ]);
    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $pulse->id,
        'year' => 0,
        'price_ars_thousands' => 42670.00,
        'origin' => 'real',
        'source' => 'test_source_system',
        'recorded_at' => '2026-04-16',
    ]);

    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--brand' => 'fiat',
        '--model' => 'pulse',
    ])->assertSuccessful();

    $predictions = InfoautoPriceHistory::where('origin', 'predicted')
        ->where('infoauto_catalog_id', $pulse->id)
        ->orderBy('year', 'desc')
        ->get();

    expect($predictions)->toHaveCount(1)
        ->and($predictions->first()->year)->toBe(2025);
});

it('does not generate predicted years that already exist as real infoauto observations for same catalog', function () {
    InfoautoPriceHistory::create([
        'infoauto_catalog_id' => $this->catalog->id,
        'year' => 2025,
        'price_ars_thousands' => 42000.00,
        'origin' => 'real',
        'source' => 'test_source_system_2',
        'recorded_at' => '2026-04-01',
    ]);

    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--recorded-at' => '2026-04-16',
    ])->assertSuccessful();

    $predictedYears = InfoautoPriceHistory::where('origin', 'predicted')
        ->where('infoauto_catalog_id', $this->catalog->id)
        ->pluck('year')
        ->all();

    expect($predictedYears)->not->toContain(2025)
        ->and($predictedYears)->toHaveCount(6);
});

it('uses Bluelytics exchange rate when not provided manually', function () {
    Http::fake([
        'api.bluelytics.com.ar/*' => Http::response([
            'oficial' => ['value_sell' => 1400.0, 'value_buy' => 1350.0],
            'blue' => ['value_sell' => 1500.0, 'value_buy' => 1450.0],
        ]),
    ]);

    $this->artisan('predict:infoauto-prices')->assertSuccessful();

    expect(InfoautoPriceHistory::where('origin', 'predicted')->count())->toBe(7);
});

it('does not write to the database when dry run is enabled', function () {
    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--dry-run' => true,
    ])->assertSuccessful();

    expect(InfoautoPriceHistory::where('origin', 'predicted')->count())->toBe(0);
});

it('does not write to price_snapshots anymore', function () {
    $this->artisan('predict:infoauto-prices', [
        '--exchange-rate' => 1400,
        '--recorded-at' => '2026-04-16',
    ])->assertSuccessful();

    expect(PriceSnapshot::where('source', 'infoauto_predicted')->count())->toBe(0);
});
