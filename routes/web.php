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
});

require __DIR__.'/auth.php';
