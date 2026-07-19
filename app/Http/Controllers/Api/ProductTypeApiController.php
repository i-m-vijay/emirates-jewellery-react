<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use App\Services\ProductTypeService;
use Illuminate\Http\JsonResponse;

class ProductTypeApiController extends Controller
{
    public function __construct(private readonly ProductTypeService $service) {}

    /**
     * GET /api/product-types
     *
     * Returns all active product types.
     *
     * Response:
     * {
     *   "success": true,
     *   "data": [
     *     { "id": 1, "name": "Gold",         "slug": "gold",          "icon": null, "sort_order": 1 },
     *     { "id": 2, "name": "Diamond",      "slug": "diamond",       "icon": null, "sort_order": 2 },
     *     { "id": 3, "name": "All Jewellery","slug": "all-jewellery", "icon": null, "sort_order": 3 },
     *     { "id": 4, "name": "Collections",  "slug": "collections",   "icon": null, "sort_order": 4 },
     *     { "id": 5, "name": "Gifting",      "slug": "gifting",       "icon": null, "sort_order": 5 }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->all(),
        ]);
    }

    /**
     * GET /api/product-types/{productType}/categories
     *
     * Returns flat categories that belong to the given product type.
     * Works for Gold / Diamond / All Jewellery / Gifting.
     * Collections has its own hierarchy — use /api/collections instead.
     *
     * Response:
     * {
     *   "success": true,
     *   "product_type": { "id": 1, "name": "Gold", "slug": "gold" },
     *   "data": [
     *     { "id": 1, "name": "Earrings", "slug": "earrings", "image": null, "sort_order": 1 },
     *     ...
     *   ]
     * }
     */
    public function categories(ProductType $productType): JsonResponse
    {
        ['product_type' => $type, 'categories' => $cats] =
            $this->service->withCategories($productType);

        return response()->json([
            'success'      => true,
            'product_type' => $type,
            'data'         => $cats,
        ]);
    }

    /**
     * GET /api/collections
     *
     * Returns the Collections hierarchy with sub-collections.
     *
     * Response:
     * {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1, "name": "Gold", "slug": "gold", ...,
     *       "collection_subcategories": [
     *         { "id": 1, "name": "Apurva", "slug": "apurva", ... },
     *         ...
     *       ]
     *     },
     *     ...
     *   ]
     * }
     */
    public function collections(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->collections(),
        ]);
    }

    /**
     * GET /api/jewellery-types
     *
     * Returns top-level jewellery buckets.
     *
     * Response:
     * {
     *   "success": true,
     *   "data": [
     *     { "id": 1, "name": "Gold Jewellery",    "slug": "gold-jewellery"    },
     *     { "id": 2, "name": "Diamond Jewellery", "slug": "diamond-jewellery" },
     *     { "id": 3, "name": "All Jewellery",     "slug": "all-jewellery"     }
     *   ]
     * }
     */
    public function jewelleryTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->jewelleryTypes(),
        ]);
    }
}
