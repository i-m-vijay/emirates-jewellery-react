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
     *
     * Filter params:
     *   Legacy  : category_id, subcategory_id
     *   New     : product_type_id, product_category_id, jewellery_id
     *             collection_category_id, collection_subcategory_id
     *   General : search, in_stock, min_price, max_price, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductDetail::with([
            'category:id,category_name',
            'subcategory:id,name,image,category_id',
            'productType:id,name,slug',
            'productCategory:id,name,slug,product_type_id',
            'jewellery:id,name,slug',
            'collectionCategory:id,name,slug',
            'collectionSubcategory:id,name,slug,collection_category_id',
        ])->where('published', 1);

        // ── Legacy filters ─────────────────────────────────────────────
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->integer('subcategory_id'));
        }

        // ── New taxonomy filters ────────────────────────────────────────
        if ($request->filled('product_type_id')) {
            $query->where('product_type_id', $request->integer('product_type_id'));
        }

        if ($request->filled('product_type_slug')) {
            $query->whereHas('productType', fn($q) =>
                $q->where('slug', $request->input('product_type_slug'))
            );
        }

        if ($request->filled('product_category_id')) {
            $query->where('product_category_id', $request->integer('product_category_id'));
        }

        if ($request->filled('product_category_slug')) {
            $query->whereHas('productCategory', fn($q) =>
                $q->where('slug', $request->input('product_category_slug'))
            );
        }

        if ($request->filled('jewellery_id')) {
            $query->where('jewellery_id', $request->integer('jewellery_id'));
        }

        if ($request->filled('jewellery_slug')) {
            $query->whereHas('jewellery', fn($q) =>
                $q->where('slug', $request->input('jewellery_slug'))
            );
        }

        if ($request->filled('collection_category_id')) {
            $query->where('collection_category_id', $request->integer('collection_category_id'));
        }

        if ($request->filled('collection_subcategory_id')) {
            $query->where('collection_subcategory_id', $request->integer('collection_subcategory_id'));
        }

        // ── General filters ────────────────────────────────────────────
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku',  'like', "%{$s}%")
                  ->orWhere('brands', 'like', "%{$s}%")
                  ->orWhere('short_description', 'like', "%{$s}%");
            });
        }

        if ($request->filled('in_stock')) {
            $query->where('in_stock', filter_var($request->input('in_stock'), FILTER_VALIDATE_BOOLEAN));
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

    /**
     * All published products (no pagination).
     */
    public function all(): JsonResponse
    {
        $products = ProductDetail::with([
            'category:id,category_name',
            'subcategory:id,name,image,category_id',
            'productType:id,name,slug',
            'productCategory:id,name,slug,product_type_id',
            'jewellery:id,name,slug',
            'collectionCategory:id,name,slug',
            'collectionSubcategory:id,name,slug,collection_category_id',
        ])
            ->where('published', 1)
            ->latest('record_id')
            ->get();

        return response()->json([
            'success' => true,
            'total'   => $products->count(),
            'data'    => $products,
        ]);
    }

    /**
     * Unique categories for a given metal type.
     *
     * GET /api/jewellery/categories-by-metal?metal_type=gold
     * GET /api/jewellery/categories-by-metal?metal_type=rings
     * GET /api/jewellery/categories-by-metal?metal_type=necklace
     * GET /api/jewellery/categories-by-metal?metal_type=wedding
     *
     * For rings / necklace / wedding the categories column is searched
     * using keyword patterns instead of the meta_metal column.
     */
    public function categoriesByMetal(Request $request): JsonResponse
    {
        
        $metalType = strtolower(trim($request->input('metal_type', '')));

        if ($metalType === '') {
            return response()->json(['success' => false, 'message' => 'metal_type is required.'], 422);
        }

        // Keywords mapped to LIKE patterns searched in the categories column.
        $categoryKeywordMap = [
            'rings'    => ['%ring%', '%rings%'],
            'necklaces' => ['%necklace%', '%necklaces%'],
            'wedding'  => ['%wedding%'],
            'earrings'  => ['%earrings%'],
        ];

        $query = ProductDetail::whereNotNull('categories')
            ->where('categories', '!=', '')
            ->selectRaw('categories, MIN(images) as image, COUNT(*) as product_count')
            ->groupBy('categories')
            ->orderBy('categories');

        if (isset($categoryKeywordMap[$metalType])) {
            $patterns = $categoryKeywordMap[$metalType];
            $query->where(function ($q) use ($patterns) {
                foreach ($patterns as $pattern) {
                    $q->orWhere('categories', 'like', $pattern);
                }
            });
        } else {
            $query->where('meta_metal', $metalType);
        }

        $categories = $query->get();

        return response()->json([
            'success'    => true,
            'metal_type' => $metalType,
            'categories' => $categories,
        ]);
    }

    /**
     * Paginated product list designed for "view more" on the React frontend.
     *
     * GET /api/jewellery/browse?page=1&per_page=50
     *
     * Query params:
     *   page      (int, default 1)  – page number
     *   per_page  (int, default 50) – records per page (max 100)
     *
     * Response:
     * {
     *   "success": true,
     *   "data": [...],
     *   "pagination": {
     *     "current_page": 1,
     *     "per_page": 50,
     *     "total": 553,
     *     "last_page": 12,
     *     "has_more": true
     *   }
     * }
     */
    public function browse(Request $request): JsonResponse
    {
        $perPage       = min($request->integer('per_page', 50), 100);
        $jewelleryType = strtolower(trim($request->input('jewellery_type', '')));

        $query = ProductDetail::where('published', 1);

        if ($jewelleryType !== '') {
            $query->where('name', 'like', "%{$jewelleryType}%");
        }

        $products = $query->latest('record_id')
            ->paginate($perPage, ['*'], 'page', $request->integer('page', 1));

        return response()->json([
            'success' => true,
            'total'   => $products->total(),
            'data'    => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
                'last_page'    => $products->lastPage(),
                'has_more'     => $products->hasMorePages(),
            ],
        ]);
    }

    /**
     * Global search across name, categories, price, meta_metal, description.
     *
     * GET /api/jewellery/search
     *
     * Query params:
     *   q            – keyword searched across name, categories, description, meta_metal
     *   name         – filter by product name (partial match)
     *   categories   – filter by categories text (partial match)
     *   meta_metal   – filter by metal type (partial match)
     *   description  – filter by description (partial match)
     *   min_price    – minimum regular_price
     *   max_price    – maximum regular_price
     *   page         – page number (default 1)
     *   per_page     – records per page (default 20, max 100)
     */
    public function search(Request $request): JsonResponse
    {
        $query = ProductDetail::where('published', 1);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sq) use ($q) {
                $sq->where('name',        'like', "%{$q}%")
                   ->orWhere('categories',  'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%")
                   ->orWhere('meta_metal',  'like', "%{$q}%")   
                   ->orWhere('regular_price',  'like', "%{$q}%");
            });
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('categories')) {
            $query->where('categories', 'like', '%' . $request->input('categories') . '%');
        }

        if ($request->filled('meta_metal')) {
            $query->where('meta_metal', 'like', '%' . $request->input('meta_metal') . '%');
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->input('description') . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('regular_price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('regular_price', '<=', $request->input('max_price'));
        }

        $perPage  = min($request->integer('per_page', 20), 100);
        $products = $query->latest('record_id')
            ->paginate($perPage, ['*'], 'page', $request->integer('page', 1));

        return response()->json([
            'success' => true,
            'total'   => $products->total(),
            'data'    => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
                'last_page'    => $products->lastPage(),
                'has_more'     => $products->hasMorePages(),
            ],
        ]);
    }

    /**
     * Single product detail.
     */
    public function show(int $id): JsonResponse
    {
        $product = ProductDetail::with([
            'category:id,category_name',
            'subcategory:id,name,image,category_id',
            'productType:id,name,slug',
            'productCategory:id,name,slug,product_type_id',
            'jewellery:id,name,slug',
            'collectionCategory:id,name,slug',
            'collectionSubcategory:id,name,slug,collection_category_id',
        ])->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }
}
