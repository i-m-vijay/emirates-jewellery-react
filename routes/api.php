<?php

use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\ProductDetailImportController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function () {
    Route::get('/', [ProductApiController::class, 'index']);
    Route::get('/{product}', [ProductApiController::class, 'show']);
});

// Product Detail Import Routes
Route::prefix('product-details')->group(function () {
    Route::post('/import', [ProductDetailImportController::class, 'import']);
    Route::get('/statistics', [ProductDetailImportController::class, 'statistics']);
    Route::get('/list', [ProductDetailImportController::class, 'list']);
    Route::get('/search-sku', [ProductDetailImportController::class, 'searchBySku']);
    Route::get('/{id}', [ProductDetailImportController::class, 'show']);
    Route::delete('/clear', [ProductDetailImportController::class, 'clear']);
});
