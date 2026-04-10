<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\PriceSnapshot;
use App\Models\Valuation;
use App\Models\Version;
use Illuminate\Support\Carbon;

beforeEach(function () {
    // Simulate March 2026 import: existing data in DB
    Carbon::setTestNow('2026-03-07');

    $this->brand = Brand::create(['name' => 'VOLKSWAGEN', 'slug' => 'volkswagen']);
    $this->model = CarModel::create(['brand_id' => $this->brand->id, 'name' => 'BORA', 'slug' => 'bora']);
    $this->version = Version::create(['car_model_id' => $this->model->id, 'name' => '4P 1,8 T HIGHLINE 180CV TIPT']);

    Valuation::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 10497.57,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'version_id' => $this->version->id,
            'year' => 2024,
            'price' => 9200.00,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year'], ['price', 'updated_at']);

    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 10497.57,
            'source' => 'cca',
            'recorded_at' => '2026-03-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'version_id' => $this->version->id,
            'year' => 2024,
            'price' => 9200.00,
            'source' => 'cca',
            'recorded_at' => '2026-03-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    Carbon::setTestNow();
});

afterEach(function () {
    Carbon::setTestNow();
});

// =============================================
// firstOrCreate: no duplicates on re-import
// =============================================

it('does not duplicate brands on re-import with firstOrCreate', function () {
    $existing = Brand::firstOrCreate(['slug' => 'volkswagen'], ['name' => 'VOLKSWAGEN']);

    expect(Brand::where('slug', 'volkswagen')->count())->toBe(1)
        ->and($existing->id)->toBe($this->brand->id);
});

it('does not duplicate models on re-import with firstOrCreate', function () {
    $existing = CarModel::firstOrCreate(
        ['brand_id' => $this->brand->id, 'slug' => 'bora'],
        ['name' => 'BORA'],
    );

    expect(CarModel::where('brand_id', $this->brand->id)->where('slug', 'bora')->count())->toBe(1)
        ->and($existing->id)->toBe($this->model->id);
});

it('does not duplicate versions on re-import with firstOrCreate', function () {
    $existing = Version::firstOrCreate(
        ['car_model_id' => $this->model->id, 'name' => '4P 1,8 T HIGHLINE 180CV TIPT'],
    );

    expect(Version::where('car_model_id', $this->model->id)
        ->where('name', '4P 1,8 T HIGHLINE 180CV TIPT')
        ->count())->toBe(1)
        ->and($existing->id)->toBe($this->version->id);
});

// =============================================
// Valuations: upsert updates price, no duplicates
// =============================================

it('updates valuation prices on re-import without duplicating rows', function () {
    Carbon::setTestNow('2026-04-07');

    $newPrice = 11200.00;

    Valuation::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => $newPrice,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year'], ['price', 'updated_at']);

    expect(Valuation::where('version_id', $this->version->id)->where('year', 2025)->count())->toBe(1)
        ->and(Valuation::where('version_id', $this->version->id)->where('year', 2025)->first()->price)->toBe('11200.00');
});

// =============================================
// Snapshots: monthly accumulation (core feature)
// =============================================

it('preserves march snapshots when importing april data', function () {
    Carbon::setTestNow('2026-04-07');

    // Simulate April import snapshot
    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 11200.00,
            'source' => 'cca',
            'recorded_at' => '2026-04-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    // March snapshots must survive
    $marchSnapshots = PriceSnapshot::where('source', 'cca')
        ->whereDate('recorded_at', '2026-03-07')
        ->get();

    $aprilSnapshots = PriceSnapshot::where('source', 'cca')
        ->whereDate('recorded_at', '2026-04-07')
        ->get();

    expect($marchSnapshots)->toHaveCount(2)
        ->and($aprilSnapshots)->toHaveCount(1)
        ->and(PriceSnapshot::where('source', 'cca')->count())->toBe(3);
});

it('creates april snapshots with correct prices separate from march', function () {
    Carbon::setTestNow('2026-04-07');

    $marchPrice = 10497.57;
    $aprilPrice = 11200.00;

    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => $aprilPrice,
            'source' => 'cca',
            'recorded_at' => '2026-04-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    $march = PriceSnapshot::where('version_id', $this->version->id)
        ->where('year', 2025)
        ->where('source', 'cca')
        ->where('recorded_at', '2026-03-07')
        ->first();

    $april = PriceSnapshot::where('version_id', $this->version->id)
        ->where('year', 2025)
        ->where('source', 'cca')
        ->where('recorded_at', '2026-04-07')
        ->first();

    expect($march->price)->toBe('10497.57')
        ->and($april->price)->toBe('11200.00');
});

it('accumulates three months of snapshots correctly', function () {
    // April
    Carbon::setTestNow('2026-04-07');
    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 11200.00,
            'source' => 'cca',
            'recorded_at' => '2026-04-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    // May
    Carbon::setTestNow('2026-05-07');
    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 11800.00,
            'source' => 'cca',
            'recorded_at' => '2026-05-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    $snapshots = PriceSnapshot::where('version_id', $this->version->id)
        ->where('year', 2025)
        ->where('source', 'cca')
        ->orderBy('recorded_at')
        ->get();

    expect($snapshots)->toHaveCount(3)
        ->and($snapshots[0]->price)->toBe('10497.57')
        ->and($snapshots[0]->recorded_at->toDateString())->toBe('2026-03-07')
        ->and($snapshots[1]->price)->toBe('11200.00')
        ->and($snapshots[1]->recorded_at->toDateString())->toBe('2026-04-07')
        ->and($snapshots[2]->price)->toBe('11800.00')
        ->and($snapshots[2]->recorded_at->toDateString())->toBe('2026-05-07');
});

// =============================================
// ACARA snapshots survive CCA re-import
// =============================================

it('preserves acara snapshots when re-importing CCA data', function () {
    // Add ACARA snapshot from March
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 10500.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-10',
    ]);

    Carbon::setTestNow('2026-04-07');

    // April CCA import
    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 11200.00,
            'source' => 'cca',
            'recorded_at' => '2026-04-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    $acaraMarch = PriceSnapshot::where('source', 'acara')->where('recorded_at', '2026-03-10')->first();
    $ccaMarch = PriceSnapshot::where('source', 'cca')->where('recorded_at', '2026-03-07')->where('year', 2025)->first();
    $ccaApril = PriceSnapshot::where('source', 'cca')->where('recorded_at', '2026-04-07')->first();

    expect($acaraMarch)->not->toBeNull()
        ->and($acaraMarch->price)->toBe('10500.00')
        ->and($ccaMarch)->not->toBeNull()
        ->and($ccaMarch->price)->toBe('10497.57')
        ->and($ccaApril)->not->toBeNull()
        ->and($ccaApril->price)->toBe('11200.00');
});

it('accumulates both sources across months', function () {
    // March ACARA
    PriceSnapshot::create([
        'version_id' => $this->version->id,
        'year' => 2025,
        'price' => 10500.00,
        'source' => 'acara',
        'recorded_at' => '2026-03-10',
    ]);

    Carbon::setTestNow('2026-04-07');

    // April CCA
    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 11200.00,
            'source' => 'cca',
            'recorded_at' => '2026-04-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    // April ACARA
    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 11300.00,
            'source' => 'acara',
            'recorded_at' => '2026-04-10',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    $all = PriceSnapshot::where('version_id', $this->version->id)
        ->where('year', 2025)
        ->orderBy('recorded_at')
        ->orderBy('source')
        ->get();

    // March CCA + March acara + April CCA + April acara = 4
    expect($all)->toHaveCount(4)
        ->and($all->where('source', 'cca')->values())->toHaveCount(2)
        ->and($all->where('source', 'acara')->values())->toHaveCount(2);
});

// =============================================
// Same-day re-import: update, not duplicate
// =============================================

it('updates snapshot price on same-day re-import without duplicating', function () {
    Carbon::setTestNow('2026-04-07');

    // First April import
    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 11200.00,
            'source' => 'cca',
            'recorded_at' => '2026-04-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    // Second April import same day (corrected price)
    PriceSnapshot::upsert([
        [
            'version_id' => $this->version->id,
            'year' => 2025,
            'price' => 11350.00,
            'source' => 'cca',
            'recorded_at' => '2026-04-07',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    $aprilSnapshots = PriceSnapshot::where('source', 'cca')
        ->where('recorded_at', '2026-04-07')
        ->where('version_id', $this->version->id)
        ->where('year', 2025)
        ->get();

    // March still has 2 snapshots (2025 + 2024)
    $marchCount = PriceSnapshot::where('recorded_at', '2026-03-07')->count();

    expect($aprilSnapshots)->toHaveCount(1)
        ->and($aprilSnapshots->first()->price)->toBe('11350.00')
        ->and($marchCount)->toBe(2);
});

// =============================================
// Full re-import cycle: entities + valuations + snapshots
// =============================================

it('simulates full april re-import cycle preserving all march data', function () {
    Carbon::setTestNow('2026-04-07');

    $now = now();

    // --- Step 1: firstOrCreate entities (what ImportVehicles now does) ---
    $brand = Brand::firstOrCreate(['slug' => 'volkswagen'], ['name' => 'VOLKSWAGEN']);
    $model = CarModel::firstOrCreate(
        ['brand_id' => $brand->id, 'slug' => 'bora'],
        ['name' => 'BORA'],
    );
    $version = Version::firstOrCreate(
        ['car_model_id' => $model->id, 'name' => '4P 1,8 T HIGHLINE 180CV TIPT'],
    );

    // Entities must reuse existing IDs
    expect($brand->id)->toBe($this->brand->id)
        ->and($model->id)->toBe($this->model->id)
        ->and($version->id)->toBe($this->version->id)
        ->and(Brand::count())->toBe(1)
        ->and(CarModel::count())->toBe(1)
        ->and(Version::count())->toBe(1);

    // --- Step 2: Upsert valuations with April prices ---
    $aprilPrice2025 = 11200.00;
    $aprilPrice2024 = 9800.00;

    Valuation::upsert([
        [
            'version_id' => $version->id,
            'year' => 2025,
            'price' => $aprilPrice2025,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'version_id' => $version->id,
            'year' => 2024,
            'price' => $aprilPrice2024,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ], ['version_id', 'year'], ['price', 'updated_at']);

    // Valuations updated, not duplicated
    expect(Valuation::count())->toBe(2)
        ->and(Valuation::where('year', 2025)->first()->price)->toBe('11200.00')
        ->and(Valuation::where('year', 2024)->first()->price)->toBe('9800.00');

    // --- Step 3: Upsert April snapshots ---
    $snapshotData = [
        [
            'version_id' => $version->id,
            'year' => 2025,
            'price' => $aprilPrice2025,
            'source' => 'cca',
            'recorded_at' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'version_id' => $version->id,
            'year' => 2024,
            'price' => $aprilPrice2024,
            'source' => 'cca',
            'recorded_at' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ];

    PriceSnapshot::upsert($snapshotData, ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    // --- Assertions: both months coexist ---
    $allSnapshots = PriceSnapshot::where('version_id', $version->id)
        ->orderBy('recorded_at')
        ->orderBy('year', 'desc')
        ->get();

    // 2 from March + 2 from April = 4
    expect($allSnapshots)->toHaveCount(4);

    // March snapshots intact with original prices
    $marchSnapshots = $allSnapshots->where('recorded_at', Carbon::parse('2026-03-07'));
    expect($marchSnapshots)->toHaveCount(2)
        ->and($marchSnapshots->firstWhere('year', 2025)->price)->toBe('10497.57')
        ->and($marchSnapshots->firstWhere('year', 2024)->price)->toBe('9200.00');

    // April snapshots with new prices
    $aprilSnapshots = $allSnapshots->where('recorded_at', Carbon::parse('2026-04-07'));
    expect($aprilSnapshots)->toHaveCount(2)
        ->and($aprilSnapshots->firstWhere('year', 2025)->price)->toBe('11200.00')
        ->and($aprilSnapshots->firstWhere('year', 2024)->price)->toBe('9800.00');
});

// =============================================
// New entities in April (new brand/model/version)
// =============================================

it('adds new brands and versions in april alongside existing march data', function () {
    Carbon::setTestNow('2026-04-07');

    $now = now();

    // Re-create existing VW
    $vw = Brand::firstOrCreate(['slug' => 'volkswagen'], ['name' => 'VOLKSWAGEN']);

    // New brand in April
    $byd = Brand::firstOrCreate(['slug' => 'byd'], ['name' => 'BYD']);
    $seal = CarModel::firstOrCreate(['brand_id' => $byd->id, 'slug' => 'seal'], ['name' => 'SEAL']);
    $sealVersion = Version::firstOrCreate(['car_model_id' => $seal->id, 'name' => 'EV 530KM']);

    expect(Brand::count())->toBe(2)
        ->and($vw->id)->toBe($this->brand->id);

    // April snapshot for new vehicle
    PriceSnapshot::upsert([
        [
            'version_id' => $sealVersion->id,
            'year' => 0,
            'price' => 45000.00,
            'source' => 'cca',
            'recorded_at' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ], ['version_id', 'year', 'source', 'recorded_at'], ['price', 'updated_at']);

    // March VW snapshots still intact
    $marchVw = PriceSnapshot::where('version_id', $this->version->id)
        ->where('recorded_at', '2026-03-07')
        ->count();

    // April BYD snapshot exists
    $aprilByd = PriceSnapshot::where('version_id', $sealVersion->id)
        ->where('recorded_at', '2026-04-07')
        ->first();

    expect($marchVw)->toBe(2)
        ->and($aprilByd)->not->toBeNull()
        ->and($aprilByd->price)->toBe('45000.00');
});

// =============================================
// warmCaches + firstOrCreate integration
// =============================================

it('warm caches pre-populate from DB so firstOrCreate hits cache first', function () {
    // Simulate warmCaches behavior from ImportVehicles
    $brandCache = Brand::all()->keyBy('slug');
    $modelCache = CarModel::all()->keyBy(fn (CarModel $m) => "{$m->brand_id}:{$m->slug}");
    $versionCache = Version::all()->keyBy(fn (Version $v) => "{$v->car_model_id}:{$v->name}");

    // Cache hit: existing brand
    $slug = 'volkswagen';
    $brand = $brandCache[$slug] ?? null;

    expect($brand)->not->toBeNull()
        ->and($brand->id)->toBe($this->brand->id);

    // Cache miss + firstOrCreate: new brand
    $newSlug = 'byd';
    $newBrand = $brandCache[$newSlug] ?? Brand::firstOrCreate(['slug' => $newSlug], ['name' => 'BYD']);

    expect($newBrand->name)->toBe('BYD')
        ->and(Brand::count())->toBe(2);
});
