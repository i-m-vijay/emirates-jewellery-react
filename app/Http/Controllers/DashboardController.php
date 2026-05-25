<?php

namespace App\Http\Controllers;

use App\Models\Product;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->count();
        $outOfStock = Product::where('stock', 0)->count();
        $recentProducts = Product::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'activeProducts',
            'outOfStock',
            'recentProducts'
        ));
    }
}
