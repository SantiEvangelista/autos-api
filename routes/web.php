<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class);

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!docs/).*');
