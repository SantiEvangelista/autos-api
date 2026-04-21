<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CarModelController;
use App\Http\Controllers\Api\ExchangeRateController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\InfoautoCatalogController;
use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\PriceExplorerController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\VersionController;
use App\Http\Middleware\AdminToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('brands/{brand}/models', [BrandController::class, 'models'])->name('brands.models');
    Route::get('models/{carModel}/versions', [CarModelController::class, 'versions'])->name('models.versions');
    Route::get('versions/{version}/valuations', [VersionController::class, 'valuations'])->name('versions.valuations');
    Route::get('search', SearchController::class)->name('search');
    Route::get('infoauto/catalog/{externalId}/prices', [InfoautoCatalogController::class, 'prices'])
        ->name('infoauto.catalog.prices');
    Route::get('price-explorer', PriceExplorerController::class)->name('price-explorer');
    Route::get('stats', StatsController::class)->name('stats');
    Route::get('exchange-rates', ExchangeRateController::class)->name('exchange-rates');
    Route::get('health', HealthController::class)->name('health');

    Route::get('admin/metrics', MetricsController::class)
        ->middleware(AdminToken::class)
        ->name('admin.metrics');
});
