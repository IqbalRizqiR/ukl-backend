<?php

use App\Http\Controllers\Api\V1\Discovery\BrandController;
use App\Http\Controllers\Api\V1\Discovery\CategoryController;
use App\Http\Controllers\Api\V1\Discovery\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/provinces', function() {
        $provinces = \App\Models\Province::all(['id', 'name']);
        return response()->json($provinces);
    });
    Route::get('/cities', function() {
        $cities = \App\Models\City::all(['id', 'province_id', 'name']);
        return response()->json($cities);
    });
});
