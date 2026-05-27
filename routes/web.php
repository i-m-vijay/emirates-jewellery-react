<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;



Route::get('/', fn() => redirect('/admin/dashboard'));



Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('admin.dashboard');
    
    Route::resource('products', ProductController::class)
         ->names('admin.products');
    
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
