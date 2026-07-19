<?php

namespace App\Services;

use App\Models\ProductDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductDetailCategoryService
{
    /**
     * Return every unique category from the product_detail.categories column,
     * ordered by product count (descending), with an optional products list.
     *
     * @param  bool  $withProducts  Include paginated product records per category.
     * @param  int   $perCategory   Max products returned when $withProducts = true.
     */
    public function uniqueCategories(bool $withProducts = false, int $perCategory = 10): Collection
    {
        // Aggregate counts in one efficient GROUP BY query
        $aggregates = ProductDetail::whereNotNull('categories')
            ->where('categories', '!=', '')
            ->where('published', 1)
            ->selectRaw('categories, COUNT(*) as product_count')
            ->groupBy('categories')
            ->orderByDesc('product_count')
            ->get();

        return $aggregates->map(function ($row) use ($withProducts, $perCategory) {
            $entry = [
                'category'      => $row->categories,
                'slug'          => Str::slug($row->categories),
                'product_count' => (int) $row->product_count,
            ];

            if ($withProducts) {
                $entry['products'] = $this->productsForCategory(
                    $row->categories,
                    $perCategory
                );
            }

            return $entry;
        });
    }

    /**
     * Products filtered by category, metal_type, or both (paginated).
     * Returns complete product_detail columns.
     *
     * @param  string|null  $category   Exact category name (product_detail.categories).
     * @param  string|null  $metalType  Value of product_detail.meta_metal.
     * @param  int          $perPage
     * @param  int          $page
     */
    public function forFilter(?string $category, ?string $metalType): array
    {
        $query = ProductDetail::where('published', 1);

        if ($category !== null) {
            $query->where('categories', $category);
        }

        if ($metalType !== null) {
            $query->where('meta_metal', $metalType);
        }

        $products = $query->orderBy('record_id')->get();

        return [
            'filters' => array_filter([
                'category'   => $category,
                'metal_type' => $metalType,
            ]),
            'product_count' => $products->count(),
            'products'      => $products,
        ];
    }

    // ── private ───────────────────────────────────────────────────────────

    private function productsForCategory(string $category, int $limit): array
    {
        return ProductDetail::where('categories', $category)
            ->where('published', 1)
            ->limit($limit)
            ->orderBy('record_id')
            ->get()
            ->toArray();
    }
}
