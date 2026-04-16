<?php

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Version;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    Redis::flushdb();
    config(['app.admin_api_token' => 'test-secret-token']);
});

// --- Auth ---

it('returns 401 without admin token header', function () {
    $this->getJson('/api/v1/admin/metrics')
        ->assertStatus(401)
        ->assertJsonPath('message', 'Unauthorized.');
});

it('returns 401 with wrong admin token', function () {
    $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'wrong-token'])
        ->assertStatus(401);
});

it('returns 200 with valid admin token', function () {
    $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token'])
        ->assertOk();
});

// --- Unique visitors (IPs) ---

it('tracks unique visitors per day via HyperLogLog', function () {
    // Simulate 3 requests from 2 different IPs
    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->getJson('/api/v1/brands');
    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->getJson('/api/v1/brands');
    $this->withServerVariables(['REMOTE_ADDR' => '5.6.7.8'])->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    $response->assertOk();
    $totals = $response->json('totals');
    expect($totals['unique_visitors'])->toBe(2);
});

it('tracks unique visitors in daily breakdown', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->getJson('/api/v1/brands');
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])->getJson('/api/v1/brands');
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->getJson('/api/v1/search?q=corolla');

    $response = $this->getJson('/api/v1/admin/metrics?days=1', ['X-Admin-Token' => 'test-secret-token']);

    $today = now()->toDateString();
    $daily = $response->json("daily.{$today}");
    expect($daily['unique_visitors'])->toBe(2);
});

it('counts frontend unique visitors separately', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])->get('/');
    $this->withServerVariables(['REMOTE_ADDR' => '2.2.2.2'])->get('/');
    $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])->get('/');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.unique_visitors'))->toBe(2);
});

// --- Popular brands/models ---

it('tracks most consulted brands', function () {
    $brand1 = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $brand2 = Brand::create(['name' => 'BMW', 'slug' => 'bmw']);

    $this->getJson("/api/v1/brands/{$brand1->id}/models");
    $this->getJson("/api/v1/brands/{$brand1->id}/models");
    $this->getJson("/api/v1/brands/{$brand2->id}/models");

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    $brands = $response->json('totals.popular_brands');
    expect($brands)->toHaveKey('TOYOTA')
        ->and($brands['TOYOTA'])->toBe(2)
        ->and($brands)->toHaveKey('BMW')
        ->and($brands['BMW'])->toBe(1);
});

it('sorts popular brands by hit count descending', function () {
    $brand1 = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $brand2 = Brand::create(['name' => 'CHEVROLET', 'slug' => 'chevrolet']);

    $this->getJson("/api/v1/brands/{$brand1->id}/models");
    $this->getJson("/api/v1/brands/{$brand2->id}/models");
    $this->getJson("/api/v1/brands/{$brand2->id}/models");
    $this->getJson("/api/v1/brands/{$brand2->id}/models");

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    $brands = $response->json('totals.popular_brands');
    $keys = array_keys($brands);
    expect($keys[0])->toBe('CHEVROLET')
        ->and($keys[1])->toBe('FORD');
});

it('tracks most consulted models from versions endpoint', function () {
    $brand = Brand::create(['name' => 'TOYOTA', 'slug' => 'toyota']);
    $model1 = CarModel::create(['brand_id' => $brand->id, 'name' => 'COROLLA', 'slug' => 'corolla']);
    $model2 = CarModel::create(['brand_id' => $brand->id, 'name' => 'HILUX', 'slug' => 'hilux']);

    $this->getJson("/api/v1/models/{$model1->id}/versions");
    $this->getJson("/api/v1/models/{$model1->id}/versions");
    $this->getJson("/api/v1/models/{$model2->id}/versions");

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    $models = $response->json('totals.popular_models');
    expect($models)->toHaveKey('TOYOTA COROLLA')
        ->and($models['TOYOTA COROLLA'])->toBe(2)
        ->and($models)->toHaveKey('TOYOTA HILUX')
        ->and($models['TOYOTA HILUX'])->toBe(1);
});

it('tracks brands from valuations endpoint via version', function () {
    $brand = Brand::create(['name' => 'BMW', 'slug' => 'bmw']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'X3', 'slug' => 'x3']);
    $version = Version::create(['car_model_id' => $model->id, 'name' => '2.0 XDRIVE']);

    $this->getJson("/api/v1/versions/{$version->id}/valuations");

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.popular_brands'))->toHaveKey('BMW')
        ->and($response->json('totals.popular_models'))->toHaveKey('BMW X3');
});

it('includes popular brands and models in daily breakdown', function () {
    $brand = Brand::create(['name' => 'FORD', 'slug' => 'ford']);
    $model = CarModel::create(['brand_id' => $brand->id, 'name' => 'RANGER', 'slug' => 'ranger']);

    $this->getJson("/api/v1/brands/{$brand->id}/models");
    $this->getJson("/api/v1/models/{$model->id}/versions");

    $response = $this->getJson('/api/v1/admin/metrics?days=1', ['X-Admin-Token' => 'test-secret-token']);

    $today = now()->toDateString();
    $daily = $response->json("daily.{$today}");
    expect($daily['popular_brands'])->toHaveKey('FORD')
        ->and($daily['popular_models'])->toHaveKey('FORD RANGER');
});

// --- IP Exclusion ---

it('does not track requests from excluded IPs', function () {
    config(['app.metrics_excluded_ips' => ['9.9.9.9']]);

    $this->withServerVariables(['REMOTE_ADDR' => '9.9.9.9'])->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.api_requests'))->toBe(0)
        ->and($response->json('totals.unique_visitors'))->toBe(0);
});

it('tracks requests from non-excluded IPs normally', function () {
    config(['app.metrics_excluded_ips' => ['9.9.9.9']]);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.api_requests'))->toBeGreaterThan(0);
});

// --- Traffic Breakdown ---

it('classifies browser requests by Mozilla User-Agent', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'])
        ->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.traffic_breakdown.browser'))->toBe(1);
});

it('classifies known crawler User-Agents as bot', function () {
    $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])
        ->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.traffic_breakdown.bot'))->toBe(1);
});

it('classifies empty User-Agent as bot', function () {
    $this->withHeaders(['User-Agent' => ''])
        ->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.traffic_breakdown.bot'))->toBe(1);
});

it('classifies non-Mozilla API requests as api_client', function () {
    $this->withHeaders(['User-Agent' => 'PostmanRuntime/7.36.0'])
        ->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.traffic_breakdown.api_client'))->toBe(1);
});

it('classifies reconnaissance paths as bot regardless of User-Agent', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 Chrome/120.0.0.0'])
        ->get('/api/.env');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.traffic_breakdown.bot'))->toBe(1);
});

it('records bot paths for reconnaissance requests', function () {
    $this->withHeaders(['User-Agent' => 'curl/7.88.0'])
        ->get('/api/.git/config');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.top_bot_paths'))->toHaveKey('api/.git/config');
});

it('includes traffic breakdown in daily metrics', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 Chrome/120.0.0.0'])
        ->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics?days=1', ['X-Admin-Token' => 'test-secret-token']);

    $today = now()->toDateString();
    expect($response->json("daily.{$today}.traffic_breakdown.browser"))->toBe(1);
});

// --- Referrer Tracking ---

it('tracks referrer domain from Referer header', function () {
    $this->withHeaders(['Referer' => 'https://google.com/search?q=autos'])
        ->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.referrers'))->toHaveKey('google.com');
});

it('tracks direct when no Referer header is present', function () {
    $this->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.referrers'))->toHaveKey('direct');
});

it('tracks same-site referrer as direct', function () {
    config(['app.url' => 'https://argautos.com']);

    $this->withHeaders(['Referer' => 'https://argautos.com/some-page'])
        ->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.referrers.direct'))->toBeGreaterThanOrEqual(1);
});

it('falls back to Origin header when Referer is absent', function () {
    $this->withHeaders(['Origin' => 'https://external-app.com'])
        ->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.referrers'))->toHaveKey('external-app.com');
});

it('includes referrers in daily metrics', function () {
    $this->withHeaders(['Referer' => 'https://reddit.com/r/argentina'])
        ->getJson('/api/v1/brands');

    $response = $this->getJson('/api/v1/admin/metrics?days=1', ['X-Admin-Token' => 'test-secret-token']);

    $today = now()->toDateString();
    expect($response->json("daily.{$today}.referrers"))->toHaveKey('reddit.com');
});

// --- Response structure ---

it('returns complete metrics structure with daily default 1 day', function () {
    $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token'])
        ->assertOk()
        ->assertJsonStructure([
            'totals' => [
                'api_requests',
                'frontend_visits',
                'unique_visitors',
                'endpoints',
                'popular_brands',
                'popular_models',
                'traffic_breakdown',
                'top_bot_paths',
                'referrers',
            ],
            'daily',
        ]);
});

it('defaults to 1 day when days parameter is absent', function () {
    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    $response->assertOk();
    expect(count($response->json('daily')))->toBe(1);
});

it('respects days parameter count', function () {
    $response = $this->getJson('/api/v1/admin/metrics?days=5', ['X-Admin-Token' => 'test-secret-token']);

    $response->assertOk();
    expect(count($response->json('daily')))->toBe(5);
});

it('clamps days between 1 and 90', function () {
    $response = $this->getJson('/api/v1/admin/metrics?days=200', ['X-Admin-Token' => 'test-secret-token']);
    expect(count($response->json('daily')))->toBe(90);

    $response = $this->getJson('/api/v1/admin/metrics?days=0', ['X-Admin-Token' => 'test-secret-token']);
    expect(count($response->json('daily')))->toBe(1);
});

// --- Exclusions ---

it('does not track admin metrics endpoint itself', function () {
    $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);
    $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    $response = $this->getJson('/api/v1/admin/metrics', ['X-Admin-Token' => 'test-secret-token']);

    expect($response->json('totals.api_requests'))->toBe(0);
});
