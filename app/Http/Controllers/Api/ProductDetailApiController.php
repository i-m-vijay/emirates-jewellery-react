<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductDetailApiController extends Controller
{
    /**
     * Fetch product details for the React frontend.
     * Supports filtering by category_id, subcategory_id, search, in_stock.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductDetail::with(['category:id,category_name', 'subcategory:id,name,image,category_id'])
            ->where('published', 1);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->integer('subcategory_id'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
                  ->orWhere('brands', 'like', "%{$s}%")
                  ->orWhere('short_description', 'like', "%{$s}%");
            });
        }

        if ($request->filled('in_stock')) {
            $query->where('in_stock', (bool) $request->input('in_stock'));
        }

        if ($request->filled('min_price')) {
            $query->where('regular_price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('regular_price', '<=', $request->input('max_price'));
        }

        $perPage  = min($request->integer('per_page', 20), 100);
        $products = $query->latest('record_id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = ProductDetail::with([
            'category:id,category_name',
            'subcategory:id,name,image,category_id',
        ])->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }
}
