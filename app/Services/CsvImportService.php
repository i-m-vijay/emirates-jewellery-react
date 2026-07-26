<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CsvImportService
{
    /**
     * Import product details from CSV file
     *
     * @param string $filePath Path to the CSV file
     * @return array Result with statistics
     */


    public static function debugImport(string $filePath): void
{
    $file = fopen($filePath, 'r');
    $header = fgetcsv($file);
    
    // Show raw headers from CSV
    dump('=== RAW CSV HEADERS ===');
    dump($header);
    
    // Show what mapHeader produces
    $headerMap = self::mapHeader($header);
    dump('=== MAPPED HEADERS ===');
    dump($headerMap);
    
    // Read first data row
    $row = fgetcsv($file);
    dump('=== RAW FIRST ROW ===');
    dump($row);
    
    // Show what parseProductDetailRow builds
    try {
        $parsed = self::parseProductDetailRow($row, $headerMap, 2);
        dump('=== PARSED PRODUCT DATA ===');
        dump($parsed);
    } catch (\Exception $e) {
        dump('=== PARSE ERROR ===');
        dump($e->getMessage());
    }
    
    fclose($file);
}


   public static function importProducts(string $filePath): array
{
    $results = [
        'success'  => 0,
        'failed'   => 0,
        'errors'   => [],
        'warnings' => [],
    ];

    $file = null;

    try {
        $file = fopen($filePath, 'r');
        if ($file === false) {
            throw new \Exception('Unable to open the CSV file.');
        }

        $header = fgetcsv($file);
        if ($header === false) {
            throw new \Exception('CSV file is empty or invalid.');
        }

        $headerMap = self::mapHeader($header);

        $emptyRow = [
            'product_id'                   => null,
            'sku'                          => null,
            'type'                         => null,
            'gtin'                         => null,
            'name'                         => null,
            'published'                    => 0,
            'is_featured'                  => 0,
            'visibility_in_catalog'        => null,
            'short_description'            => null,
            'description'                  => null,
            'date_sale_price_starts'       => null,
            'date_sale_price_ends'         => null,
            'tax_status'                   => null,
            'tax_class'                    => null,
            'in_stock'                     => 0,
            'stock'                        => null,
            'low_stock_amount'             => null,
            'backorders_allowed'           => 0,
            'sold_individually'            => 0,
            'weight_kg'                    => null,
            'length_cm'                    => null,
            'width_cm'                     => null,
            'height_cm'                    => null,
            'allow_customer_reviews'       => 1,
            'purchase_note'                => null,
            'sale_price'                   => null,
            'regular_price'                => null,
            'categories'                   => null,
            'tags'                         => null,
            'shipping_class'               => null,
            'images'                       => null,
            'download_limit'               => null,
            'download_expiry_days'         => null,
            'parent'                       => null,
            'grouped_products'             => null,
            'upsells'                      => null,
            'cross_sells'                  => null,
            'external_url'                 => null,
            'button_text'                  => null,
            'position'                     => null,
            'brands'                       => null,
            'attribute_1_name'             => null,
            'attribute_1_value'            => null,
            'attribute_1_visible'          => 0,
            'attribute_1_global'           => 0,
            'attribute_2_name'             => null,
            'attribute_2_value'            => null,
            'attribute_2_visible'          => 0,
            'attribute_2_global'           => 0,
            'attribute_3_name'             => null,
            'attribute_3_value'            => null,
            'attribute_3_visible'          => 0,
            'attribute_3_global'           => 0,
            'attribute_4_name'             => null,
            'attribute_4_value'            => null,
            'attribute_4_visible'          => 0,
            'attribute_4_global'           => 0,
            'attribute_5_name'             => null,
            'attribute_5_value'            => null,
            'attribute_5_visible'          => 0,
            'attribute_5_global'           => 0,
            'meta_wp_page_template'        => null,
            'meta_metal'                   => null,
            'meta_metal_alt'               => null,
            'meta_gemhub_media_link'       => null,
            'meta_gemhub_video_urls'       => null,
            'meta_gold_type'               => null,
            'meta_gold_purity'             => null,
            'meta_gold_weight'             => null,
            'meta_gold_weight_grams'       => null,
            'meta_total_diamond_weight'    => null,
            'meta_centre_diamond_weight'   => null,
            'meta_diamond_clarity'         => null,
            'meta_diamond_color'           => null,
            'meta_gold_weight_alt'         => null,
            'meta_gold_purity_alt'         => null,
            'meta_elementor_edit_mode'     => 0,
            'meta_elementor_template_type' => null,
            'meta_elementor_version'       => null,
            'meta_weight_in_gram'          => null,
            'meta_product_material_type'   => null,
            'meta_woosw_count'             => null,
            'meta_woosw_add'               => null,
            'meta_gold_price_per_gram'     => null,
            'meta_gold_price_per_gram_alt' => null,
            'meta_product_video_url'       => null,
            'meta_product_video_url_alt'   => null,
            'meta_wcfv_source'             => null,
            'meta_wcfv_local_video'        => null,
            'meta_wcfv_poster_image'       => null,
            'meta_wcfv_youtube_video'      => null,
            'meta_wcfv_youtube_image'      => null,
            'meta_wcfv_vimeo_video'        => null,
            'meta_wcfv_vimeo_image'        => null,
            'imported_at'                  => null,
            'created_at'                   => null,
            'updated_at'                   => null,
        ];

        $rowNumber        = 1;
        $productsToInsert = [];
        $now              = now(); // ✅ compute once, reuse for all rows

        while (($row = fgetcsv($file)) !== false) {
            $rowNumber++;

            // Skip completely empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            try {
                $productData = self::parseProductDetailRow($row, $headerMap, $rowNumber);

                if ($productData) {
                    // Merge into emptyRow so every row has identical keys
                    $productsToInsert[] = array_merge($emptyRow, $productData, [
                        'imported_at' => $now,  // ✅ always set timestamps here
                        'created_at'  => $now,  //    not inside parseProductDetailRow
                        'updated_at'  => $now,
                    ]);
                    $results['success']++;
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Row {$rowNumber}: {$e->getMessage()}";
                continue;
            }

            // Batch insert every 100 rows
            if (count($productsToInsert) >= 100) {
                try {
                    \DB::table('product_detail')->insert($productsToInsert);
                } catch (\Exception $e) {
                    $results['failed']  += count($productsToInsert);
                    $results['success'] -= count($productsToInsert);
                    $results['errors'][] = "Batch insert failed near row {$rowNumber}: {$e->getMessage()}";
                }
                $productsToInsert = [];
            }
        }

        // Insert any remaining rows
        if (!empty($productsToInsert)) {
            try {
                \DB::table('product_detail')->insert($productsToInsert);
            } catch (\Exception $e) {
                $results['failed']  += count($productsToInsert);
                $results['success'] -= count($productsToInsert);
                $results['errors'][] = "Final batch insert failed: {$e->getMessage()}";
            }
        }

    } catch (\Exception $e) {
        $results['errors'][] = $e->getMessage();

    } finally {
        // ✅ Always close the file handle even if an exception occurs
        if ($file && is_resource($file)) {
            fclose($file);
        }
        // ✅ Only delete if it's a real uploaded temp file (not storage path)
        if (str_starts_with($filePath, sys_get_temp_dir())) {
            @unlink($filePath);
        }
    }

    return $results;
}

    /**
     * Map CSV header to database columns
     *
     * @param array $header CSV header row
     * @return array Mapped header
     */
   

    public static function mapHeader(array $header): array
{
    // Maps exact WooCommerce CSV header → your DB column name
    $csvToColumn = [
        'id'                              => 'product_id',
        'type'                            => 'type',
        'sku'                             => 'sku',
        'gtin, upc, ean, or isbn'         => 'gtin',
        'name'                            => 'name',
        'published'                       => 'published',
        'is featured?'                    => 'is_featured',
        'visibility in catalog'           => 'visibility_in_catalog',
        'short description'               => 'short_description',
        'description'                     => 'description',
        'date sale price starts'          => 'date_sale_price_starts',
        'date sale price ends'            => 'date_sale_price_ends',
        'tax status'                      => 'tax_status',
        'tax class'                       => 'tax_class',
        'in stock?'                       => 'in_stock',
        'stock'                           => 'stock',
        'low stock amount'                => 'low_stock_amount',
        'backorders allowed?'             => 'backorders_allowed',
        'sold individually?'              => 'sold_individually',
        'weight (kg)'                     => 'weight_kg',
        'length (cm)'                     => 'length_cm',
        'width (cm)'                      => 'width_cm',
        'height (cm)'                     => 'height_cm',
        'allow customer reviews?'         => 'allow_customer_reviews',
        'purchase note'                   => 'purchase_note',
        'sale price'                      => 'sale_price',
        'regular price'                   => 'regular_price',
        'categories'                      => 'categories',
        'tags'                            => 'tags',
        'shipping class'                  => 'shipping_class',
        'images'                          => 'images',
        'download limit'                  => 'download_limit',
        'download expiry days'            => 'download_expiry_days',
        'parent'                          => 'parent',
        'grouped products'                => 'grouped_products',
        'upsells'                         => 'upsells',
        'cross-sells'                     => 'cross_sells',
        'external url'                    => 'external_url',
        'button text'                     => 'button_text',
        'position'                        => 'position',
        'brands'                          => 'brands',

        // Attributes
        'attribute 1 name'                => 'attribute_1_name',
        'attribute 1 value(s)'            => 'attribute_1_value',
        'attribute 1 visible'             => 'attribute_1_visible',
        'attribute 1 global'              => 'attribute_1_global',
        'attribute 2 name'                => 'attribute_2_name',
        'attribute 2 value(s)'            => 'attribute_2_value',
        'attribute 2 visible'             => 'attribute_2_visible',
        'attribute 2 global'              => 'attribute_2_global',
        'attribute 3 name'                => 'attribute_3_name',
        'attribute 3 value(s)'            => 'attribute_3_value',
        'attribute 3 visible'             => 'attribute_3_visible',
        'attribute 3 global'              => 'attribute_3_global',
        'attribute 4 name'                => 'attribute_4_name',
        'attribute 4 value(s)'            => 'attribute_4_value',
        'attribute 4 visible'             => 'attribute_4_visible',
        'attribute 4 global'              => 'attribute_4_global',
        'attribute 5 name'                => 'attribute_5_name',
        'attribute 5 value(s)'            => 'attribute_5_value',
        'attribute 5 visible'             => 'attribute_5_visible',
        'attribute 5 global'              => 'attribute_5_global',

        // Meta fields
        'meta: _wp_page_template'         => 'meta_wp_page_template',
        'meta: metal'                     => 'meta_metal',
        'meta: _metal'                    => 'meta_metal_alt',
        'meta: gemhub_media_link'         => 'meta_gemhub_media_link',
        'meta: gemhub_video_urls'         => 'meta_gemhub_video_urls',
        'meta: gold_type'                 => 'meta_gold_type',
        'meta: gold_purity'               => 'meta_gold_purity',
        'meta: gold_weight'               => 'meta_gold_weight',
        'meta: gold_weight_grams'         => 'meta_gold_weight_grams',
        'meta: total_diamond_weight'      => 'meta_total_diamond_weight',
        'meta: centre_diamond_weight'     => 'meta_centre_diamond_weight',
        'meta: diamond_clarity'           => 'meta_diamond_clarity',
        'meta: diamond_color'             => 'meta_diamond_color',
        'meta: _gold_weight'              => 'meta_gold_weight_alt',
        'meta: _gold_purity'              => 'meta_gold_purity_alt',
        'meta: _elementor_edit_mode'      => 'meta_elementor_edit_mode',
        'meta: _elementor_template_type'  => 'meta_elementor_template_type',
        'meta: _elementor_version'        => 'meta_elementor_version',
        'meta: _weight_in_gram'           => 'meta_weight_in_gram',
        'meta: _product_material_type'    => 'meta_product_material_type',
        'meta: woosw_count'               => 'meta_woosw_count',
        'meta: woosw_add'                 => 'meta_woosw_add',
        'meta: gold_price_per_gram'       => 'meta_gold_price_per_gram',
        'meta: _gold_price_per_gram'      => 'meta_gold_price_per_gram_alt',
        'meta: product_video_url'         => 'meta_product_video_url',
        'meta: _product_video_url'        => 'meta_product_video_url_alt',
        'meta: wcfv_source'               => 'meta_wcfv_source',
        'meta: wcfv_local_video'          => 'meta_wcfv_local_video',
        'meta: wcfv_poster_image'         => 'meta_wcfv_poster_image',
        'meta: wcfv_youtube_video'        => 'meta_wcfv_youtube_video',
        'meta: wcfv_youtube_image'        => 'meta_wcfv_youtube_image',
        'meta: wcfv_vimeo_video'          => 'meta_wcfv_vimeo_video',
        'meta: wcfv_vimeo_image'          => 'meta_wcfv_vimeo_image',
    ];

    $headerMap = [];
    foreach ($header as $index => $column) {
        $normalized = strtolower(trim($column));
        // Map to DB column name if known, otherwise skip (null = ignore column)
        $headerMap[$index] = $csvToColumn[$normalized] ?? null;
    }

    return $headerMap;
}

    /**
     * Parse a CSV row into product detail data
     *
     * @param array $row CSV row data
     * @param array $headerMap Header mapping
     * @param int $rowNumber Current row number (for error messages)
     * @return array|null Product detail data or null if invalid
     */
    public static function parseProductDetailRow(array $row, array $headerMap, int $rowNumber): ?array
    {
        $productData = [
    'created_at'  => now(),
    'updated_at'  => now(),
    'imported_at' => now(),
];

        // Map of CSV column names to database field names with type casting
        $fieldTypeMap = [
            // Core fields
            'product_id' => 'int',
            'sku' => 'string',
            'type' => 'string',
            'gtin' => 'string',
            'name' => 'string',
            'published' => 'boolean',
            'is_featured' => 'boolean',
            'visibility_in_catalog' => 'string',
            'short_description' => 'string',
            'description' => 'string',
            'date_sale_price_starts' => 'date',
            'date_sale_price_ends' => 'date',
            'tax_status' => 'string',
            'tax_class' => 'string',
            'in_stock' => 'boolean',
            'stock' => 'int',
            'low_stock_amount' => 'int',
            'backorders_allowed' => 'boolean',
            'sold_individually' => 'boolean',
            'weight_kg' => 'float',
            'length_cm' => 'float',
            'width_cm' => 'float',
            'height_cm' => 'float',
            'allow_customer_reviews' => 'boolean',
            'purchase_note' => 'string',
            'sale_price' => 'float',
            'regular_price' => 'float',
            'categories' => 'string',
            'tags' => 'string',
            'shipping_class' => 'string',
            'images' => 'string',
            'download_limit' => 'int',
            'download_expiry_days' => 'int',
            'parent' => 'int',
            'grouped_products' => 'string',
            'upsells' => 'string',
            'cross_sells' => 'string',
            'external_url' => 'string',
            'button_text' => 'string',
            'position' => 'int',
            'brands' => 'string',
            // Attributes
            'attribute_1_name' => 'string',
            'attribute_1_value' => 'string',
            'attribute_1_visible' => 'boolean',
            'attribute_1_global' => 'boolean',
            'attribute_2_name' => 'string',
            'attribute_2_value' => 'string',
            'attribute_2_visible' => 'boolean',
            'attribute_2_global' => 'boolean',
            'attribute_3_name' => 'string',
            'attribute_3_value' => 'string',
            'attribute_3_visible' => 'boolean',
            'attribute_3_global' => 'boolean',
            'attribute_4_name' => 'string',
            'attribute_4_value' => 'string',
            'attribute_4_visible' => 'boolean',
            'attribute_4_global' => 'boolean',
            'attribute_5_name' => 'string',
            'attribute_5_value' => 'string',
            'attribute_5_visible' => 'boolean',
            'attribute_5_global' => 'boolean',
            // Meta fields
            'meta_wp_page_template' => 'string',
            'meta_metal' => 'string',
            'meta_metal_alt' => 'string',
            'meta_gemhub_media_link' => 'string',
            'meta_gemhub_video_urls' => 'string',
            'meta_gold_type' => 'string',
            'meta_gold_purity' => 'string',
            'meta_gold_weight' => 'float',
            'meta_gold_weight_grams' => 'float',
            'meta_total_diamond_weight' => 'float',
            'meta_centre_diamond_weight' => 'float',
            'meta_diamond_clarity' => 'string',
            'meta_diamond_color' => 'string',
            'meta_gold_weight_alt' => 'float',
            'meta_gold_purity_alt' => 'string',
            'meta_elementor_edit_mode' => 'boolean',
            'meta_elementor_template_type' => 'string',
            'meta_elementor_version' => 'string',
            'meta_weight_in_gram' => 'float',
            'meta_product_material_type' => 'string',
            'meta_woosw_count' => 'int',
            'meta_woosw_add' => 'string',
            'meta_gold_price_per_gram' => 'float',
            'meta_gold_price_per_gram_alt' => 'float',
            'meta_product_video_url' => 'string',
            'meta_product_video_url_alt' => 'string',
            'meta_wcfv_source' => 'string',
            'meta_wcfv_local_video' => 'string',
            'meta_wcfv_poster_image' => 'string',
            'meta_wcfv_youtube_video' => 'string',
            'meta_wcfv_youtube_image' => 'string',
            'meta_wcfv_vimeo_video' => 'string',
            'meta_wcfv_vimeo_image' => 'string',
        ];

        // Extract values based on header mapping
        // foreach ($headerMap as $index => $column) {
        //     $value = $row[$index] ?? '';
            
        //     if (empty($value) || $value === '') {
        //         continue; // Skip empty values
        //     }

        //     if (isset($fieldTypeMap[$column])) {
        //         $type = $fieldTypeMap[$column];
        //         $productData[$column] = self::castValue($value, $type);
        //     }
        // }


        // Replace this block in parseProductDetailRow():

foreach ($headerMap as $index => $column) {
    // ← ADD THIS: skip columns not in our map
    if ($column === null) {
        continue;
    }

    $value = $row[$index] ?? '';

    if ($value === '' || $value === null) {
        continue;
    }

    if (isset($fieldTypeMap[$column])) {
        $type = $fieldTypeMap[$column];
        $productData[$column] = self::castValue($value, $type);
    }
}

        // Validate required fields
        if (empty($productData['name'] ?? null)) {
            throw new \Exception('Product name is required.');
        }

        // At least one of product_id or sku should be provided
        if (empty($productData['product_id'] ?? null) && empty($productData['sku'] ?? null)) {
            throw new \Exception('Either product_id or SKU is required.');
        }

        if (isset($productData['sku']) && ProductDetail::where('sku', $productData['sku'])->exists()) {
            throw new \Exception('Duplicate SKU: ' . $productData['sku']);
        }

        if (isset($productData['product_id']) && ProductDetail::where('product_id', $productData['product_id'])->exists()) {
            throw new \Exception('Duplicate product_id: ' . $productData['product_id']);
        }

        return $productData;
    }

    /**
     * Cast value to appropriate type
     *
     * @param mixed $value The value to cast
     * @param string $type The target type
     * @return mixed The casted value
     */
    private static function castValue($value, string $type)
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'date' => ($value && strtotime($value)) ? date('Y-m-d', strtotime($value)) : null,
            'string' => trim($value) ?: null,
            default => $value,
        };
    }

    /**
     * Generate CSV template for ProductDetail import
     *
     * @return string CSV template content
     */
    public static function generateTemplate(): string
    {
        // Essential headers for ProductDetail import
        $headers = [
            'product_id',
            'sku',
            'type',
            'name',
            'published',
            'is_featured',
            'short_description',
            'description',
            'regular_price',
            'sale_price',
            'tax_status',
            'in_stock',
            'stock',
            'weight_kg',
            'length_cm',
            'width_cm',
            'height_cm',
            'categories',
            'brands',
            'meta_gold_type',
            'meta_gold_purity',
            'meta_gold_weight_grams',
            'meta_diamond_clarity',
            'meta_diamond_color',
        ];
        
        $template = implode(',', $headers) . "\n";

        // Add sample rows
        $samples = [
            ['1', 'GOLD-RING-001', 'simple', 'Gold Ring 18K', '1', '1', 'Beautiful 18K gold ring', 'Elegant gold ring with 18K purity', '5000', '4500', 'taxable', '1', '10', '5.5', '2', '2', '1.5', 'Rings', 'Brand A', '18K Gold', '750', '5.5', 'VS1', 'G'],
            ['2', 'DIAMOND-EARR-002', 'simple', 'Diamond Earrings', '1', '0', 'Elegant diamond earrings', 'Premium diamond earrings with VS clarity', '8000', '7200', 'taxable', '1', '5', '3.2', '1.5', '1.5', '1', 'Earrings', 'Brand B', 'White Gold', '750', '3.2', 'VS1', 'D'],
            ['3', 'SILVER-NECK-003', 'simple', 'Silver Necklace', '1', '0', 'Delicate silver necklace', 'Pure silver necklace with elegant design', '3000', '2700', 'taxable', '1', '15', '2.1', '1', '0.5', '0.8', 'Necklace', 'Brand C', 'Sterling Silver', '925', '2.1', '', ''],
        ];

        foreach ($samples as $sample) {
            $template .= '"' . implode('","', $sample) . '"' . "\n";
        }

        return $template;
    }
}
