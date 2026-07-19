<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductDetailCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductDetailCategoryApiController extends Controller
{
    public function __construct(private readonly ProductDetailCategoryService $service) {}

    /**
     * GET /api/jewellery-categories
     *
     * Returns every unique category found in product_detail.categories,
     * ordered by product count (highest first).
     *
     * Query params:
     *   include_products  (bool, default false) – embed up to `per_category`
     *                     products inside each category entry.
     *   per_category      (int,  default 10)    – max products per category
     *                     when include_products=true.
     *
     * Example responses:
     *
     * Without products (default):
     * {
     *   "success": true,
     *   "total_categories": 17,
     *   "total_products": 553,
     *   "data": [
     *     { "category": "Solitaire Rings", "slug": "solitaire-rings", "product_count": 116 },
     *     { "category": "Multi-Stone Rings","slug": "multi-stone-rings","product_count": 83 },
     *     ...
     *   ]
     * }
     *
     * With products (?include_products=true&per_category=5):
     * {
     *   "success": true,
     *   "total_categories": 17,
     *   "total_products": 553,
     *   "data": [
     *     {
     *       "category": "Solitaire Rings",
     *       "slug": "solitaire-rings",
     *       "product_count": 116,
     *       "products": [ { "record_id": ..., "name": ..., ... }, ... ]
     *     },
     *     ...
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $withProducts = filter_var($request->input('include_products', false), FILTER_VALIDATE_BOOLEAN);
        $perCategory  = min((int) $request->input('per_category', 10), 50);

        $categories = $this->service->uniqueCategories($withProducts, $perCategory);

        return response()->json([
            'success'          => true,
            'total_categories' => $categories->count(),
            'total_products'   => $categories->sum('product_count'),
            'data'             => $categories->values(),
        ]);
    }

    /**
     * GET /api/jewellery-categories/{category?}
     *
     * Returns paginated complete product_detail records.
     * At least one of {category} or ?metal_type must be provided.
     *
     * Params:
     *   {category}   (path, optional)  – category name or slug
     *   metal_type   (query, optional) – value of meta_metal column (e.g. "gold")
     *   per_page     (query, default 20, max 100)
     *   page         (query, default 1)
     *
     * Examples:
     *   GET /api/jewellery-categories/Bands
     *   GET /api/jewellery-categories?metal_type=gold
     *   GET /api/jewellery-categories/Bands?metal_type=gold
     */
    public function show(Request $request, string $category = null): JsonResponse
    {
        
        $metalType =  null;
        // $metalType = $request->input('metal_type') ?: null;

        if ($category === null && $metalType === null) {
            return response()->json([
                'success' => false,
                'message' => 'Provide at least one filter: category (path) or metal_type (query param).',
            ], 422);
        }

        $resolvedCategory = null;

        if ($category !== null) {
            $resolvedCategory = $this->resolveCategory($category);

            if (!$resolvedCategory) {
                return response()->json([
                    'success' => false,
                    'message' => "Category \"{$category}\" not found.",
                ], 404);
            }
        }
        $result = $this->service->forFilter($resolvedCategory, $metalType);

        return response()->json(['success' => true, 'data' => $result]);
    }

    // ── private ───────────────────────────────────────────────────────────

    /**
     * Resolve a URL segment to the exact category string stored in the DB.
     * Accepts the raw name OR a slugified version.
     */
    private function resolveCategory(string $input): ?string
    {
        // Try exact match first (e.g. "Solitaire Rings" passed as-is)
        $exact = \App\Models\ProductDetail::where('categories', $input)
            ->value('categories');

        if ($exact) {
            return $exact;
        }

        // Fallback: match by slug
        $all = \App\Models\ProductDetail::whereNotNull('categories')
            ->where('categories', '!=', '')
            ->distinct()
            ->pluck('categories');

        return $all->first(fn ($c) => \Illuminate\Support\Str::slug($c) === \Illuminate\Support\Str::slug($input));
    }
}
