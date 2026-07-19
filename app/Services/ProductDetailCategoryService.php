<?php

namespace App\Services;

use App\Models\ProductDetail;
use Illuminate\Support\Collection;

class ProductDetailCategoryService
{
    /**
     * Curated category list in display order.
     * db_categories lists the exact values stored in product_detail.categories.
     * Multi-value entries (Earrings, Rings) act as virtual aggregate categories.
     */
    private const CURATED_CATEGORIES = [
        ['display' => 'Bands',             'slug' => 'bands',             'db_categories' => ['Bands']],
        ['display' => 'Bangles',           'slug' => 'bangles',           'db_categories' => ['Bangles']],
        ['display' => 'Beaded Necklaces',  'slug' => 'beaded-necklaces',  'db_categories' => ['Beaded Necklaces']],
        ['display' => 'Rings',             'slug' => 'rings',             'db_categories' => ['Solitaire Rings', 'Multi-Stone Rings', 'Engagement Rings', 'Cocktail Rings', 'Diamond Rings', 'Wedding Rings', 'Eternity Rings']],
        ['display' => 'Cuban Chains',      'slug' => 'cuban-chains',      'db_categories' => ['Cuban Chains']],
        ['display' => 'Lockets',           'slug' => 'lockets',           'db_categories' => ['Lockets']],
        ['display' => 'Earrings',          'slug' => 'earrings',          'db_categories' => ['Hoop Earrings', 'Dangle Earrings', 'Drop Earrings']],
        ['display' => 'Solitaire Rings',   'slug' => 'solitaire-rings',   'db_categories' => ['Solitaire Rings']],
        ['display' => 'Multi-Stone Rings', 'slug' => 'multi-stone-rings', 'db_categories' => ['Multi-Stone Rings']],
        ['display' => 'Hoop Earrings',     'slug' => 'hoop-earrings',     'db_categories' => ['Hoop Earrings']],
        ['display' => 'Dangle Earrings',   'slug' => 'dangle-earrings',   'db_categories' => ['Dangle Earrings']],
        ['display' => 'Drop Earrings',     'slug' => 'drop-earrings',     'db_categories' => ['Drop Earrings']],
    ];

    /**
     * Return the curated jewellery categories in fixed display order,
     * with an optional product sample per category.
     */
    public function uniqueCategories(bool $withProducts = false, int $perCategory = 10): Collection
    {
        return collect(self::CURATED_CATEGORIES)->map(function (array $cat) use ($withProducts, $perCategory) {
            $count = ProductDetail::whereIn('categories', $cat['db_categories'])
                ->where('published', 1)
                ->count();

            $entry = [
                'category'      => $cat['display'],
                'slug'          => $cat['slug'],
                'product_count' => $count,
            ];

            if ($withProducts) {
                $entry['products'] = $this->productsForCategories($cat['db_categories'], $perCategory);
            }

            return $entry;
        })->filter(fn ($e) => $e['product_count'] > 0)->values();
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

    private function productsForCategories(array $categories, int $limit): array
    {
        return ProductDetail::whereIn('categories', $categories)
            ->where('published', 1)
            ->limit($limit)
            ->orderBy('record_id')
            ->get()
            ->toArray();
    }
}
