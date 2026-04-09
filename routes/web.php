<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class);

Route::post('/contact', ContactController::class)
    ->middleware('throttle:5,1')
    ->name('contact');

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!docs/).*');
