<?php

namespace App\Services;

use App\Models\CollectionCategory;
use App\Models\Jewellery;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Collection;

class ProductTypeService
{
    /**
     * All active product types (Gold, Diamond, All Jewellery, Collections, Gifting).
     */
    public function all(): Collection
    {
        return ProductType::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'icon', 'sort_order']);
    }

    /**
     * Single product type with its flat categories.
     */
    public function withCategories(ProductType $productType): array
    {
        $categories = $productType
            ->productCategories()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'product_type_id', 'name', 'slug', 'image', 'sort_order']);

        return [
            'product_type' => $productType->only('id', 'name', 'slug'),
            'categories'   => $categories,
        ];
    }

    /**
     * Full Collections hierarchy:
     *   CollectionCategory → CollectionSubcategory[].
     */
    public function collections(): Collection
    {
        return CollectionCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->with([
                'collectionSubcategories' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->select('id', 'collection_category_id', 'name', 'slug', 'image', 'sort_order'),
            ])
            ->get(['id', 'name', 'slug', 'image', 'sort_order']);
    }

    /**
     * Top-level jewellery buckets
     * (Gold Jewellery, Diamond Jewellery, All Jewellery).
     */
    public function jewelleryTypes(): Collection
    {
        return Jewellery::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'image', 'sort_order']);
    }
}
