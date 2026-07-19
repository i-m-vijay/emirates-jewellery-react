<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\ProductType;
use App\Models\ProductCategory;
use App\Models\CollectionCategory;
use App\Models\CollectionSubcategory;
use App\Models\Jewellery;

class JewelleryTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Jewellery top-level buckets ────────────────────────────────
        $jewelleryTypes = [
            ['name' => 'Gold Jewellery',    'slug' => 'gold-jewellery',    'sort_order' => 1],
            ['name' => 'Diamond Jewellery', 'slug' => 'diamond-jewellery', 'sort_order' => 2],
            ['name' => 'All Jewellery',     'slug' => 'all-jewellery',     'sort_order' => 3],
        ];

        foreach ($jewelleryTypes as $jt) {
            Jewellery::updateOrCreate(['slug' => $jt['slug']], $jt);
        }

        // ── 2. Product Types ──────────────────────────────────────────────
        $types = [
            ['name' => 'Gold',         'slug' => 'gold',          'sort_order' => 1],
            ['name' => 'Diamond',      'slug' => 'diamond',       'sort_order' => 2],
            ['name' => 'All Jewellery','slug' => 'all-jewellery', 'sort_order' => 3],
            ['name' => 'Collections',  'slug' => 'collections',   'sort_order' => 4],
            ['name' => 'Gifting',      'slug' => 'gifting',       'sort_order' => 5],
        ];

        foreach ($types as $t) {
            ProductType::updateOrCreate(['slug' => $t['slug']], $t);
        }

        // ── 3. Product Categories per type ───────────────────────────────
        $typeCategories = [
            'gold' => [
                'Earrings', 'Pendants', 'Rings', 'Nosepins',
                'Bracelets', 'Sets', 'Mangalsutras', 'Necklaces', 'Coins',
            ],
            'diamond' => [
                'Earrings', 'Pendants', 'Rings', 'Nosepins',
                'Bracelets', 'Sets', 'Mangalsutras', 'Necklaces',
            ],
            'all-jewellery' => [
                'Earrings', 'Rings', 'Necklaces', 'Mangalsutras',
                'Bracelets', 'Sets', 'Kids', 'Others', 'Coins',
            ],
            // Collections gets its own hierarchy (see below), no flat categories
            'gifting' => [
                'Jewelry Gifting', 'Message Gifting', 'Corporate Gifting',
            ],
        ];

        foreach ($typeCategories as $typeSlug => $categories) {
            $productType = ProductType::where('slug', $typeSlug)->first();
            if (!$productType) {
                continue;
            }

            foreach ($categories as $idx => $catName) {
                $catSlug = Str::slug($catName);
                ProductCategory::updateOrCreate(
                    ['product_type_id' => $productType->id, 'slug' => $catSlug],
                    [
                        'product_type_id' => $productType->id,
                        'name'            => $catName,
                        'slug'            => $catSlug,
                        'sort_order'      => $idx + 1,
                    ]
                );
            }
        }

        // ── 4. Collection Categories & Sub-categories ─────────────────────
        $collections = [
            'Gold' => [
                'Apurva', 'Veda', 'Yuva', 'Zenina',
                "Joy18", "Li'l Joy", 'Sita Kalyanam', 'Krishna Leela',
            ],
            'Diamond' => [
                'Eleganza', 'Pride', 'Bella', 'Evora', 'Blossom', 'Padmalakshmi',
            ],
            'Precious Stone' => [
                'Ratna',
            ],
            'Platinum' => [
                'Perfekt Platino', 'MS Dhoni Signature Edition',
            ],
            'Pearl' => [
                'Massaki',
            ],
        ];

        foreach ($collections as $catName => $subNames) {
            $catSlug = Str::slug($catName);

            $collectionCat = CollectionCategory::updateOrCreate(
                ['slug' => $catSlug],
                ['name' => $catName, 'slug' => $catSlug, 'sort_order' => array_search($catName, array_keys($collections)) + 1]
            );

            foreach ($subNames as $idx => $subName) {
                $subSlug = Str::slug($subName);
                CollectionSubcategory::updateOrCreate(
                    ['collection_category_id' => $collectionCat->id, 'slug' => $subSlug],
                    [
                        'collection_category_id' => $collectionCat->id,
                        'name'                   => $subName,
                        'slug'                   => $subSlug,
                        'sort_order'             => $idx + 1,
                    ]
                );
            }
        }

        $this->command->info('✔  Jewellery taxonomy seeded successfully.');
    }
}
