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
        ])->where('published', 1)->where('in_stock', true);

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
            $query->whereHas('productType',
                fn($q) =>
                $q->where('slug', $request->input('product_type_slug'))
            );
        }

        if ($request->filled('product_category_id')) {
            $query->where('product_category_id', $request->integer('product_category_id'));
        }

        if ($request->filled('product_category_slug')) {
            $query->whereHas('productCategory',
                fn($q) =>
                $q->where('slug', $request->input('product_category_slug'))
            );
        }

        if ($request->filled('jewellery_id')) {
            $query->where('jewellery_id', $request->integer('jewellery_id'));
        }

        if ($request->filled('jewellery_slug')) {
            $query->whereHas('jewellery',
                fn($q) =>
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
                    ->orWhere('sku', 'like', "%{$s}%")
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

        $perPage = min($request->integer('per_page', 20), 100);
        $products = $query->latest('record_id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
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
            ->where('in_stock', true)
            ->latest('record_id')
            ->get();

        return response()->json([
            'success' => true,
            'total' => $products->count(),
            'data' => $products,
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
    private const GOLD_SINGLE_CATEGORIES = [
        'Bands',
        'Bangles',
        'Beaded Necklaces',
        'Cuban Chains',
        'Lockets',
    ];

    private const GOLD_EARRING_SUBCATEGORIES = [
        'Dangle Earrings',
        'Drop Earrings',
        'Hoop Earrings',
    ];

    private const GOLD_RING_SUBCATEGORIES = [
        'Solitaire Rings',
        'Multi-Stone Rings',
        'Engagement Rings',
        'Cocktail Rings',
        'Diamond Rings',
        'Wedding Rings',
        'Eternity Rings',
    ];

    private const METAL_CATEGORY_MAP = [
        'gold' => [
            'Bands',
            'Bangles',
            'Beaded Necklaces',
            'Cocktail Rings',
            'Cuban Chains',
            'Dangle Earrings',
            'Diamond Rings',
            'Drop Earrings',
            'Engagement Rings',
            'Eternity Rings',
            'Hoop Earrings',
            'Lockets',
            'Multi-Stone Rings',
            'Rings',
            'Solitaire Rings',
            'Wedding Rings',
        ],
        'diamond' => ['Diamond Rings', 'Wedding Rings'],
        'rings' => ['Cocktail Rings', 'Diamond Rings', 'Engagement Rings', 'Eternity Rings', 'Multi-Stone Rings', 'Solitaire Rings', 'Wedding Rings'],
        'earrings' => ['Hoop Earrings', 'Drop Earrings', 'Dangle Earrings'],
        'necklaces' => ['Beaded Necklaces'],
        'wedding' => ['Wedding Rings'],
        'collections' => ['Bands', 'Bangles', 'Beaded Necklaces', 'Cocktail Rings', 'Diamond Rings', 'Engagement Rings', 'Eternity Rings', 'Multi-Stone Rings', 'Solitaire Rings', 'Wedding Rings'],
        'gifts' => ['Bangles', 'Cocktail Rings', 'Diamond Rings', 'Engagement Rings', 'Eternity Rings', 'Multi-Stone Rings', 'Solitaire Rings', 'Wedding Rings', 'Hoop Earrings', 'Drop Earrings'],
    ];

    private const GENDER_CATEGORY_MAP = [
        'gold' => [
            'for_him' => ['Rings', 'Bands'],
            'for_her' => ['Bands', 'Bangles', 'Beaded Necklaces', 'Cuban Chains', 'Lockets', 'Rings', 'Earrings'],
            'kids' => ['Lockets', 'Earrings', 'Bands', 'Bangles'],
        ],
        'diamond' => [
            'for_him' => ['Diamond Rings', 'Engagement Rings'],
            'for_her' => ['Diamond Rings', 'Engagement Rings'],
            'kids' => ['Diamond Rings'],
        ],
        'rings' => [
            'for_him' => ['Diamond Rings', 'Solitaire Rings', 'Engagement Rings', 'Wedding Rings'],
            'for_her' => ['Diamond Rings', 'Engagement Rings', 'Wedding Rings', 'Multi-Stone Rings', 'Solitaire Rings', 'Eternity Rings', 'Cocktail Rings'],
            'kids' => ['Diamond Rings', 'Solitaire Rings'],
        ],
        'earrings' => [
            'for_him' => [],
            'for_her' => ['Dangle Earrings', 'Drop Earrings', 'Hoop Earrings'],
            'kids' => ['Dangle Earrings', 'Drop Earrings', 'Hoop Earrings'],
        ],
        'necklaces' => [
            'for_him' => [],
            'for_her' => ['Beaded Necklaces'],
            'kids' => ['Beaded Necklaces'],
        ],
        'wedding' => [
            'for_him' => ['Wedding Rings'],
            'for_her' => ['Wedding Rings'],
            'kids' => ['Wedding Rings'],
        ],
        'collections' => [
            'for_him' => ['Rings'],
            'for_her' => ['Bands', 'Bangles', 'Beaded Necklaces', 'Rings'],
            'kids' => ['Rings', 'Bands', 'Bangles'],
        ],
        'gifts' => [
            'for_him' => ['Rings'],
            'for_her' => ['Hoop Earrings', 'Drop Earrings', 'Bangles', 'Rings'],
            'kids' => ['Drop Earrings', 'Bangles', 'Rings'],
        ],
    ];

    public function byGender(Request $request): JsonResponse
    {
        $metalType = strtolower(trim($request->input('metal_type', '')));
        $gender = strtolower(trim($request->input('gender', '')));

        if ($metalType === '') {
            return response()->json(['success' => false, 'message' => 'metal_type is required.'], 422);
        }

        if ($gender === '') {
            return response()->json(['success' => false, 'message' => 'gender is required (for_him, for_her, kids).'], 422);
        }

        if (!array_key_exists($metalType, self::GENDER_CATEGORY_MAP)) {
            return response()->json(['success' => false, 'message' => "Unknown metal_type \"{$metalType}\"."], 422);
        }

        if (!array_key_exists($gender, self::GENDER_CATEGORY_MAP[$metalType])) {
            return response()->json(['success' => false, 'message' => "Unknown gender \"{$gender}\". Use for_him, for_her, or kids."], 422);
        }

        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;

        $data = collect(self::GENDER_CATEGORY_MAP[$metalType][$gender])->map(function (string $cat) use ($minPrice, $maxPrice) {
            $query = ProductDetail::where('published', 1)->where('in_stock', true);

            if ($cat === 'Rings') {
                $query->whereIn('categories', self::GOLD_RING_SUBCATEGORIES);
            } elseif ($cat === 'Earrings') {
                $query->whereIn('categories', self::GOLD_EARRING_SUBCATEGORIES);
            } else {
                $query->where('categories', $cat);
            }

            if ($minPrice !== null) {
                $query->where('regular_price', '>=', $minPrice);
            }

            if ($maxPrice !== null) {
                $query->where('regular_price', '<=', $maxPrice);
            }

            $products = $query->orderBy('regular_price')->get();

            return [
                'category' => $cat,
                'slug' => \Illuminate\Support\Str::slug($cat),
                'product_count' => $products->count(),
                'products' => $products,
            ];
        })->filter(fn($item) => $item['product_count'] > 0)->values();

        return response()->json([
            'success' => true,
            'metal_type' => $metalType,
            'gender' => $gender,
            'total_categories' => $data->count(),
            'total_products' => $data->sum('product_count'),
            'data' => $data,
        ]);
    }

    public function byMetalAndPrice(Request $request): JsonResponse
    {
        $metalType = strtolower(trim($request->input('metal_type', '')));

        if ($metalType === '') {
            return response()->json(['success' => false, 'message' => 'metal_type is required.'], 422);
        }

        if (!array_key_exists($metalType, self::METAL_CATEGORY_MAP)) {
            return response()->json(['success' => false, 'message' => "Unknown metal_type \"{$metalType}\"."], 422);
        }

        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;

        $data = collect(self::METAL_CATEGORY_MAP[$metalType])->map(function (string $cat) use ($minPrice, $maxPrice) {
            // "Rings" is a virtual aggregate across all ring subcategories
            $query = ProductDetail::where('published', 1)->where('in_stock', true);

            if ($cat === 'Rings') {
                $query->whereIn('categories', self::GOLD_RING_SUBCATEGORIES);
            } else {
                $query->where('categories', $cat);
            }

            if ($minPrice !== null) {
                $query->where('regular_price', '>=', $minPrice);
            }

            if ($maxPrice !== null) {
                $query->where('regular_price', '<=', $maxPrice);
            }

            $products = $query->orderBy('regular_price')->get();

            return [
                'category' => $cat,
                'slug' => \Illuminate\Support\Str::slug($cat),
                'product_count' => $products->count(),
                'products' => $products,
            ];
        })->filter(fn($item) => $item['product_count'] > 0)->values();

        return response()->json([
            'success' => true,
            'metal_type' => $metalType,
            'total_categories' => $data->count(),
            'total_products' => $data->sum('product_count'),
            'data' => $data,
        ]);
    }

    private const OFFERS_CATEGORIES = ['Rings', 'Earrings', 'Lockets', 'Bands'];

    public function offers(Request $request): JsonResponse
    {
        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;

        $data = collect(self::OFFERS_CATEGORIES)->map(function (string $cat) use ($minPrice, $maxPrice) {
            $query = ProductDetail::where('published', 1)->where('in_stock', true);

            if ($cat === 'Rings') {
                $query->whereIn('categories', self::GOLD_RING_SUBCATEGORIES);
            } elseif ($cat === 'Earrings') {
                $query->whereIn('categories', self::GOLD_EARRING_SUBCATEGORIES);
            } else {
                $query->where('categories', $cat);
            }

            if ($minPrice !== null) {
                $query->where('regular_price', '>=', $minPrice);
            }

            if ($maxPrice !== null) {
                $query->where('regular_price', '<=', $maxPrice);
            }

            $products = $query->orderBy('regular_price')->get();

            return [
                'category' => $cat,
                'slug' => \Illuminate\Support\Str::slug($cat),
                'product_count' => $products->count(),
                'products' => $products,
            ];
        })->filter(fn($item) => $item['product_count'] > 0)->values();

        return response()->json([
            'success' => true,
            'total_categories' => $data->count(),
            'total_products' => $data->sum('product_count'),
            'data' => $data,
        ]);
    }

    public function categoriesByMetal(Request $request): JsonResponse
    {
        $metalType = strtolower(trim($request->input('metal_type', '')));

        if ($metalType === '') {
            return response()->json(['success' => false, 'message' => 'metal_type is required.'], 422);
        }

        if ($metalType === 'gold') {
            return $this->goldCategories();
        }

        if ($metalType === 'rings') {
            $categories = ProductDetail::whereIn('categories', self::GOLD_RING_SUBCATEGORIES)
                ->where('categories', '!=', '')
                ->selectRaw('categories, MIN(images) as image, COUNT(*) as product_count')
                ->groupBy('categories')
                ->orderByRaw('FIELD(categories, ' . implode(',', array_fill(0, count(self::GOLD_RING_SUBCATEGORIES), '?')) . ')', self::GOLD_RING_SUBCATEGORIES)
                ->get()
                ->map(fn($row) => [
                    'categories' => $row->categories,
                    'slug' => \Illuminate\Support\Str::slug($row->categories),
                    'image' => $row->image,
                    'product_count' => $row->product_count,
                ]);

            return response()->json([
                'success' => true,
                'metal_type' => 'rings',
                'categories' => $categories,
            ]);
        }

        // Keywords mapped to LIKE patterns searched in the categories column.
        $categoryKeywordMap = [
            'necklaces' => ['%necklace%', '%necklaces%'],
            'wedding' => ['%wedding%'],
            'earrings' => ['%earrings%'],
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

        $categories = $query->get()
            ->map(fn($row) => [
                'categories' => $row->categories,
                'slug' => \Illuminate\Support\Str::slug($row->categories),
                'image' => $row->image,
                'product_count' => $row->product_count,
            ]);

        return response()->json([
            'success' => true,
            'metal_type' => $metalType,
            'categories' => $categories,
        ]);
    }

    private function goldCategories(): JsonResponse
    {
        $data = collect();

        foreach (self::GOLD_SINGLE_CATEGORIES as $cat) {
            $row = ProductDetail::where('published', 1)
                // ->where('meta_metal', 'gold')
                ->where('categories', $cat)
                ->selectRaw('MIN(images) as image, COUNT(*) as product_count')
                ->first();

            if ($row && $row->product_count > 0) {
                $data->push([
                    'categories' => $cat,
                    'slug' => \Illuminate\Support\Str::slug($cat),
                    'image' => $row->image,
                    'product_count' => $row->product_count,
                ]);
            }
        }

        // Earrings: aggregate across Dangle, Drop, Hoop
        foreach (self::GOLD_EARRING_SUBCATEGORIES as $cat) {
            $row = ProductDetail::where('published', 1)
                // ->where('meta_metal', 'gold')
                ->where('categories', $cat)
                ->selectRaw('MIN(images) as image, COUNT(*) as product_count')
                ->first();

            if ($row && $row->product_count > 0) {
                $data->push([
                    'categories' => $cat,
                    'slug' => \Illuminate\Support\Str::slug($cat),
                    'image' => $row->image,
                    'product_count' => $row->product_count,
                ]);
            }
        }

        // Rings: aggregate across all ring subcategories
        foreach (self::GOLD_RING_SUBCATEGORIES as $cat) {
            $row = ProductDetail::where('published', 1)
                // ->where('meta_metal', 'gold')
                ->where('categories', $cat)
                ->selectRaw('MIN(images) as image, COUNT(*) as product_count')
                ->first();

            if ($row && $row->product_count > 0) {
                $data->push([
                    'categories'    => $cat,
                    'slug'          => \Illuminate\Support\Str::slug($cat),
                    'image'         => $row->image,
                    'product_count' => $row->product_count,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'metal_type' => 'gold',
            'categories' => $data->values(),
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
        $perPage = min($request->integer('per_page', 50), 100);
        $jewelleryType = strtolower(trim($request->input('jewellery_type', '')));

        $query = ProductDetail::where('published', 1)->where('in_stock', true);

        if ($jewelleryType !== '') {
            $query->where('name', 'like', "%{$jewelleryType}%");
        }

        $products = $query->latest('record_id')
            ->paginate($perPage, ['*'], 'page', $request->integer('page', 1));

        return response()->json([
            'success' => true,
            'total' => $products->total(),
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'has_more' => $products->hasMorePages(),
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
        $query = ProductDetail::where('published', 1)->where('in_stock', true);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                    ->orWhere('categories', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('meta_metal', 'like', "%{$q}%")
                    ->orWhere('regular_price', 'like', "%{$q}%");
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

        $perPage = min($request->integer('per_page', 20), 100);
        $products = $query->latest('record_id')
            ->paginate($perPage, ['*'], 'page', $request->integer('page', 1));

        return response()->json([
            'success' => true,
            'total' => $products->total(),
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'has_more' => $products->hasMorePages(),
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
