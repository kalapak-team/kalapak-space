<?php

use App\Http\Controllers\PublicApi\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Kalapak Code Team API',
        'version' => '1.0.0',
    ]);
});

// SEO: Sitemap & Robots.txt
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap']);
Route::get('/robots.txt', [SitemapController::class, 'robots']);
