<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\SubcategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\GoldPriceController;


Route::get('/', fn() => redirect('/admin/dashboard'));

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('gold-price',  [GoldPriceController::class, 'index'])->name('admin.gold-price.index');
    Route::post('gold-price', [GoldPriceController::class, 'update'])->name('admin.gold-price.update');
});

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('admin.dashboard');
    
    Route::resource('products', ProductController::class)
         ->names('admin.products');

    Route::resource('categories', CategoryController::class)
         ->names('admin.categories')
         ->except(['show']);

    Route::resource('subcategories', SubcategoryController::class)
         ->names('admin.subcategories')
         ->except(['show']);

    Route::resource('product-details', ProductDetailController::class)
         ->names('admin.product-details')
         ->only(['index', 'edit', 'update']);
    
    // CSV Import routes

   // routes/web.php — TEMPORARY



    Route::get('/products-import/form', [ProductController::class, 'showImportForm'])
         ->name('admin.products.import.form');
    Route::post('/products-import', [ProductController::class, 'import'])
         ->name('admin.products.import');
    Route::get('/products-import/template', [ProductController::class, 'downloadTemplate'])
         ->name('admin.products.download-template');
});

require __DIR__.'/auth.php';
