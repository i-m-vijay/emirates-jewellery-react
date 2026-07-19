<?php

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ProductDetailApiController;
use App\Http\Controllers\Api\ProductDetailCategoryApiController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\ProductTypeApiController;
use App\Http\Controllers\ProductDetailImportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes (React frontend)
|--------------------------------------------------------------------------
*/

// ── Basic products ────────────────────────────────────────────────────────
Route::prefix('products')->group(function () {
    Route::get('/', [ProductApiController::class, 'index']);
    Route::get('/{product}', [ProductApiController::class, 'show']);
});

// ── Product-detail import / management ───────────────────────────────────
Route::prefix('product-details')->group(function () {
    Route::post('/import',        [ProductDetailImportController::class, 'import']);
    Route::get('/statistics',     [ProductDetailImportController::class, 'statistics']);
    Route::get('/list',           [ProductDetailImportController::class, 'list']);
    Route::get('/search-sku',     [ProductDetailImportController::class, 'searchBySku']);
    Route::get('/{id}',           [ProductDetailImportController::class, 'show']);
    Route::delete('/clear',       [ProductDetailImportController::class, 'clear']);
});

// ── Legacy categories & subcategories ────────────────────────────────────
Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/categories/{category}/subcategories', [CategoryApiController::class, 'subcategories']);
Route::get('/subcategories-by-category/{categoryId}', [CategoryApiController::class, 'subcategoriesByCategory']);

// ── Jewellery product listing (main frontend endpoint) ───────────────────
// Filter params: product_type_id | product_type_slug
//                product_category_id | product_category_slug
//                jewellery_id | jewellery_slug
//                collection_category_id | collection_subcategory_id
//                category_id | subcategory_id  (legacy)
//                search | in_stock | min_price | max_price | per_page
Route::get('/jewellery',                       [ProductDetailApiController::class, 'index']);
Route::get('/jewellery/all',                   [ProductDetailApiController::class, 'all']);
Route::get('/jewellery/browse',                [ProductDetailApiController::class, 'browse']);
Route::get('/jewellery/search',                [ProductDetailApiController::class, 'search']);
Route::get('/jewellery/categories-by-metal',   [ProductDetailApiController::class, 'categoriesByMetal']);
Route::get('/jewellery/{id}',                  [ProductDetailApiController::class, 'show']);

// ── Jewellery taxonomy navigation ────────────────────────────────────────

// Top-level buckets: Gold Jewellery / Diamond Jewellery / All Jewellery
Route::get('/jewellery-types', [ProductTypeApiController::class, 'jewelleryTypes']);

// Product types: Gold / Diamond / All Jewellery / Collections / Gifting
Route::get('/product-types',                         [ProductTypeApiController::class, 'index']);
Route::get('/product-types/{productType}/categories',[ProductTypeApiController::class, 'categories']);

// Collections hierarchy: category + sub-collections
Route::get('/collections', [ProductTypeApiController::class, 'collections']);

// ── Unique categories extracted from product_detail.categories column ───
// GET /api/jewellery-categories                 → all unique categories + counts
// GET /api/jewellery-categories/{category}      → products inside one category
Route::get('/jewellery-categories',               [ProductDetailCategoryApiController::class, 'index']);
Route::get('/jewellery-categories/{category?}',  [ProductDetailCategoryApiController::class, 'show']);

/*
|--------------------------------------------------------------------------
| User Authentication Routes
|--------------------------------------------------------------------------
| Token is returned on login and should be stored in sessionStorage
| on the frontend so it is cleared automatically when the browser closes.
|
| Public  : POST /api/user/register   → create account
|           POST /api/user/login      → get Bearer token
|
| Protected (Authorization: Bearer <token>):
|           POST /api/user/logout     → invalidate token
|           GET  /api/user/profile    → authenticated user info
|--------------------------------------------------------------------------
*/

Route::prefix('user')->group(function () {
    // Public
    Route::post('/register',    [UserAuthController::class, 'register']);
    Route::post('/login',       [UserAuthController::class, 'login']);
    Route::post('/request-otp', [UserAuthController::class, 'requestOtp']);
    Route::post('/verify-otp',  [UserAuthController::class, 'verifyOtp']);

    // Protected — requires valid user Bearer token
    Route::middleware('user.api.auth')->group(function () {
        Route::post('/logout',  [UserAuthController::class, 'logout']);
        Route::get('/profile',  [UserAuthController::class, 'profile']);
    });
});

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
