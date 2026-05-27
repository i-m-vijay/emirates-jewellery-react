<?php

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ProductDetailApiController;
use App\Http\Controllers\ProductDetailImportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes (React frontend)
|--------------------------------------------------------------------------
*/

Route::prefix('products')->group(function () {
    Route::get('/', [ProductApiController::class, 'index']);
    Route::get('/{product}', [ProductApiController::class, 'show']);
});

Route::prefix('product-details')->group(function () {
    Route::post('/import', [ProductDetailImportController::class, 'import']);
    Route::get('/statistics', [ProductDetailImportController::class, 'statistics']);
    Route::get('/list', [ProductDetailImportController::class, 'list']);
    Route::get('/search-sku', [ProductDetailImportController::class, 'searchBySku']);
    Route::get('/{id}', [ProductDetailImportController::class, 'show']);
    Route::delete('/clear', [ProductDetailImportController::class, 'clear']);
});

// Public: categories & subcategories
Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/categories/{category}/subcategories', [CategoryApiController::class, 'subcategories']);

// Helper: subcategories filtered by category (used by admin edit form)
Route::get('/subcategories-by-category/{categoryId}', [CategoryApiController::class, 'subcategoriesByCategory']);

// Public: product details for React (with category/subcategory filtering)
Route::get('/jewellery', [ProductDetailApiController::class, 'index']);
Route::get('/jewellery/{id}', [ProductDetailApiController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Admin API Routes (protected by API token)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware('api.auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::apiResource('categories', CategoryController::class);
    });
});
