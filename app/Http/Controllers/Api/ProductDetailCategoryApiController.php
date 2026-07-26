<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductDetailCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductDetailCategoryApiController extends Controller
{
    private const RINGS_CATEGORY_ORDER = [
        'Solitaire Rings',
        'Multi-Stone Rings',
        'Engagement Rings',
        'Cocktail Rings',
        'Diamond Rings',
        'Wedding Rings',
        'Eternity Rings',
    ];

    private const EARRINGS_CATEGORY_ORDER = [
        'Hoop Earrings',
        'Dangle Earrings',
        'Drop Earrings',
    ];

    public function __construct(private readonly ProductDetailCategoryService $service) {}

   
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
            if (\Illuminate\Support\Str::slug($category) === 'rings') {
                return $this->showRings();
            }

            if (\Illuminate\Support\Str::slug($category) === 'earrings') {
                return $this->showEarrings();
            }

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

    private function showRings(): JsonResponse
    {
        $data = collect(self::RINGS_CATEGORY_ORDER)->map(function (string $cat) {
            $result = $this->service->forFilter($cat, null, false);
            return [
                'category'      => $cat,
                'slug'          => \Illuminate\Support\Str::slug($cat),
                'product_count' => $result['product_count'],
                'products'      => $result['products'],
            ];
        })->filter(fn ($item) => $item['product_count'] > 0)->values();

        return response()->json([
            'success'          => true,
            'total_categories' => $data->count(),
            'total_products'   => $data->sum('product_count'),
            'data'             => $data,
        ]);
    }

    private function showEarrings(): JsonResponse
    {
        $data = collect(self::EARRINGS_CATEGORY_ORDER)->map(function (string $cat) {
            $result = $this->service->forFilter($cat, null, false);
            return [
                'category'      => $cat,
                'slug'          => \Illuminate\Support\Str::slug($cat),
                'product_count' => $result['product_count'],
                'products'      => $result['products'],
            ];
        })->filter(fn ($item) => $item['product_count'] > 0)->values();

        return response()->json([
            'success'          => true,
            'total_categories' => $data->count(),
            'total_products'   => $data->sum('product_count'),
            'data'             => $data,
        ]);
    }

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
