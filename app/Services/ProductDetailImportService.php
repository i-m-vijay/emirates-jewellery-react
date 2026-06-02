<?php

namespace App\Services;

use App\Models\ProductDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductDetailImportService
{
    /**
     * CSV Header to Database Column Mapping
     * Maps CSV headers to database column names in order
     */
    private static array $columnMapping = [
        'ID' => 'product_id',
        'Type' => 'type',
        'SKU' => 'sku',
        'GTIN, UPC, EAN, or ISBN' => 'gtin',
        'Name' => 'name',
        'Published' => 'published',
        'Is featured?' => 'is_featured',
        'Visibility in catalog' => 'visibility_in_catalog',
        'Short description' => 'short_description',
        'Description' => 'description',
        'Date sale price starts' => 'date_sale_price_starts',
        'Date sale price ends' => 'date_sale_price_ends',
        'Tax status' => 'tax_status',
        'Tax class' => 'tax_class',
        'In stock?' => 'in_stock',
        'Stock' => 'stock',
        'Low stock amount' => 'low_stock_amount',
        'Backorders allowed?' => 'backorders_allowed',
        'Sold individually?' => 'sold_individually',
        'Weight (kg)' => 'weight_kg',
        'Length (cm)' => 'length_cm',
        'Width (cm)' => 'width_cm',
        'Height (cm)' => 'height_cm',
        'Allow customer reviews?' => 'allow_customer_reviews',
        'Purchase note' => 'purchase_note',
        'Sale price' => 'sale_price',
        'Regular price' => 'regular_price',
        'Categories' => 'categories',
        'Tags' => 'tags',
        'Shipping class' => 'shipping_class',
        'Images' => 'images',
        'Download limit' => 'download_limit',
        'Download expiry days' => 'download_expiry_days',
        'Parent' => 'parent',
        'Grouped products' => 'grouped_products',
        'Upsells' => 'upsells',
        'Cross-sells' => 'cross_sells',
        'External URL' => 'external_url',
        'Button text' => 'button_text',
        'Position' => 'position',
        'Brands' => 'brands',
        'Attribute 1 name' => 'attribute_1_name',
        'Attribute 1 value(s)' => 'attribute_1_value',
        'Attribute 1 visible' => 'attribute_1_visible',
        'Attribute 1 global' => 'attribute_1_global',
        'Attribute 2 name' => 'attribute_2_name',
        'Attribute 2 value(s)' => 'attribute_2_value',
        'Attribute 2 visible' => 'attribute_2_visible',
        'Attribute 2 global' => 'attribute_2_global',
        'Attribute 3 name' => 'attribute_3_name',
        'Attribute 3 value(s)' => 'attribute_3_value',
        'Attribute 3 visible' => 'attribute_3_visible',
        'Attribute 3 global' => 'attribute_3_global',
        'Attribute 4 name' => 'attribute_4_name',
        'Attribute 4 value(s)' => 'attribute_4_value',
        'Attribute 4 visible' => 'attribute_4_visible',
        'Attribute 4 global' => 'attribute_4_global',
        'Attribute 5 name' => 'attribute_5_name',
        'Attribute 5 value(s)' => 'attribute_5_value',
        'Attribute 5 visible' => 'attribute_5_visible',
        'Attribute 5 global' => 'attribute_5_global',
        'Meta: _wp_page_template' => 'meta_wp_page_template',
        'Meta: metal' => 'meta_metal',
        'Meta: _metal' => 'meta_metal_alt',
        'Meta: gemhub_media_link' => 'meta_gemhub_media_link',
        'Meta: gemhub_video_urls' => 'meta_gemhub_video_urls',
        'Meta: gold_type' => 'meta_gold_type',
        'Meta: gold_purity' => 'meta_gold_purity',
        'Meta: gold_weight' => 'meta_gold_weight',
        'Meta: gold_weight_grams' => 'meta_gold_weight_grams',
        'Meta: total_diamond_weight' => 'meta_total_diamond_weight',
        'Meta: centre_diamond_weight' => 'meta_centre_diamond_weight',
        'Meta: diamond_clarity' => 'meta_diamond_clarity',
        'Meta: diamond_color' => 'meta_diamond_color',
        'Meta: _gold_weight' => 'meta_gold_weight_alt',
        'Meta: _gold_purity' => 'meta_gold_purity_alt',
        'Meta: _elementor_edit_mode' => 'meta_elementor_edit_mode',
        'Meta: _elementor_template_type' => 'meta_elementor_template_type',
        'Meta: _elementor_version' => 'meta_elementor_version',
        'Meta: _weight_in_gram' => 'meta_weight_in_gram',
        'Meta: _product_material_type' => 'meta_product_material_type',
        'Meta: woosw_count' => 'meta_woosw_count',
        'Meta: woosw_add' => 'meta_woosw_add',
        'Meta: gold_price_per_gram' => 'meta_gold_price_per_gram',
        'Meta: _gold_price_per_gram' => 'meta_gold_price_per_gram_alt',
        'Meta: product_video_url' => 'meta_product_video_url',
        'Meta: _product_video_url' => 'meta_product_video_url_alt',
        'Meta: wcfv_source' => 'meta_wcfv_source',
        'Meta: wcfv_local_video' => 'meta_wcfv_local_video',
        'Meta: wcfv_poster_image' => 'meta_wcfv_poster_image',
        'Meta: wcfv_youtube_video' => 'meta_wcfv_youtube_video',
        'Meta: wcfv_youtube_image' => 'meta_wcfv_youtube_image',
        'Meta: wcfv_vimeo_video' => 'meta_wcfv_vimeo_video',
        'Meta: wcfv_vimeo_image' => 'meta_wcfv_vimeo_image',
    ];

    /**
     * Import product details from CSV file
     *
     * @param string $filePath Path to the CSV file
     * @return array Result with statistics
     */
    public static function importProductDetails(string $filePath): array
    {
        $results = [
            'total_records' => 0,
            'imported' => 0,
            'skipped_duplicates' => 0,
            'failed' => 0,
            'errors' => [],
            'warnings' => [],
        ];

        try {
            // Validate file exists
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }

            // Open the CSV file
            $file = fopen($filePath, 'r');
            if ($file === false) {
                throw new \Exception('Unable to open the CSV file.');
            }

            // Read and validate header row
            $header = fgetcsv($file);
            if ($header === false || empty($header)) {
                throw new \Exception('CSV file is empty or invalid.');
            }

            // Create header index mapping
            $headerIndex = self::createHeaderIndex($header);

            $rowNumber = 1; // Header is row 1
            $productsToInsert = [];
            $duplicateSkus = [];

            // Read data rows
            while (($row = fgetcsv($file)) !== false) {
                $rowNumber++;
                $results['total_records']++;

                // Skip completely empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    $productData = self::parseRow($row, $headerIndex, $rowNumber);

                    if (!$productData) {
                        continue;
                    }

                    // Check for duplicates by SKU
                    $sku = $productData['sku'] ?? null;
                    if ($sku) {
                        // Check in database
                        if (ProductDetail::bySku($sku)->exists()) {
                            $results['skipped_duplicates']++;
                            $results['warnings'][] = "Row {$rowNumber}: SKU '{$sku}' already exists in database. Skipping.";
                            continue;
                        }

                        // Check in current batch
                        if (in_array($sku, $duplicateSkus)) {
                            $results['skipped_duplicates']++;
                            $results['warnings'][] = "Row {$rowNumber}: Duplicate SKU '{$sku}' in CSV. Skipping.";
                            continue;
                        }

                        $duplicateSkus[] = $sku;
                    }

                    $productsToInsert[] = $productData;
                    $results['imported']++;

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row {$rowNumber}: {$e->getMessage()}";
                    Log::error("CSV Import Error Row {$rowNumber}", [
                        'error' => $e->getMessage(),
                        'row_data' => array_slice($row, 0, 5), // Log first 5 columns
                    ]);
                }

                // Batch insert every 100 products to avoid memory issues
                if (count($productsToInsert) >= 100) {
                    try {
                        // Add timestamps to each record
                        $now = now()->toDateTimeString();
                        $productsToInsert = array_map(function($product) use ($now) {
                            $product['imported_at'] = $now;
                            $product['updated_at'] = $now;
                            return $product;
                        }, $productsToInsert);
                        
                        ProductDetail::insert($productsToInsert);
                        $productsToInsert = [];
                    } catch (\Exception $e) {
                        $results['errors'][] = "Batch insert failed: {$e->getMessage()}";
                        Log::error("CSV Batch Insert Error", ['error' => $e->getMessage()]);
                    }
                }
            }

            // Insert remaining products
            if (!empty($productsToInsert)) {
                try {
                    // Add timestamps to each record
                    $now = now()->toDateTimeString();
                    $productsToInsert = array_map(function($product) use ($now) {
                        $product['imported_at'] = $now;
                        $product['updated_at'] = $now;
                        return $product;
                    }, $productsToInsert);
                    
                    ProductDetail::insert($productsToInsert);
                } catch (\Exception $e) {
                    $results['errors'][] = "Final batch insert failed: {$e->getMessage()}";
                    Log::error("CSV Final Batch Insert Error", ['error' => $e->getMessage()]);
                }
            }

            fclose($file);

            // Log summary
            Log::info('CSV Import Summary', [
                'total_records' => $results['total_records'],
                'imported' => $results['imported'],
                'skipped_duplicates' => $results['skipped_duplicates'],
                'failed' => $results['failed'],
            ]);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('CSV Import Fatal Error', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Create header index mapping for fast lookup
     *
     * @param array $header CSV header row
     * @return array Index mapping column name to column index
     */
    private static function createHeaderIndex(array $header): array
    {
        $index = [];
        foreach ($header as $columnIndex => $columnName) {
            $index[$columnIndex] = $columnName;
        }
        return $index;
    }

    /**
     * Parse a CSV row into product detail data
     *
     * @param array $row CSV row data
     * @param array $headerIndex Header index mapping
     * @param int $rowNumber Current row number (for error messages)
     * @return array|null Product data or null if invalid
     */
    private static function parseRow(array $row, array $headerIndex, int $rowNumber): ?array
    {
        $productData = [];

        // Process each column in order
        foreach ($headerIndex as $columnIndex => $csvColumnName) {
            $value = $row[$columnIndex] ?? '';

            // Get database column name from mapping
            $dbColumnName = self::$columnMapping[$csvColumnName] ?? null;

            if (!$dbColumnName) {
                continue; // Skip unmapped columns
            }

            // Trim and prepare value
            $value = trim($value);

            // Process based on column type
            $processedValue = self::processValue($dbColumnName, $value);

            if ($processedValue !== null || $value !== '') {
                $productData[$dbColumnName] = $processedValue;
            }
        }

        // Validate required fields
        $sku = $productData['sku'] ?? null;
        $name = $productData['name'] ?? null;
        $productId = $productData['product_id'] ?? null;

        if (!$sku && !$productId) {
            throw new \Exception('Either SKU or Product ID is required.');
        }

        return $productData;
    }

    /**
     * Process value based on column type
     *
     * @param string $columnName Database column name
     * @param mixed $value Raw value from CSV
     * @return mixed Processed value
     */
    private static function processValue(string $columnName, $value)
    {
        // Handle empty values
        if ($value === '' || $value === 'NONE') {
            return null;
        }

        // Boolean fields
        $booleanFields = [
            'published', 'is_featured', 'in_stock', 'backorders_allowed',
            'sold_individually', 'allow_customer_reviews',
            'attribute_1_visible', 'attribute_1_global',
            'attribute_2_visible', 'attribute_2_global',
            'attribute_3_visible', 'attribute_3_global',
            'attribute_4_visible', 'attribute_4_global',
            'attribute_5_visible', 'attribute_5_global',
            'meta_elementor_edit_mode',
        ];

        if (in_array($columnName, $booleanFields)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on']) ? 1 : 0;
        }

        // Numeric fields
        $numericFields = [
            'product_id', 'stock', 'low_stock_amount', 'weight_kg',
            'length_cm', 'width_cm', 'height_cm', 'sale_price',
            'regular_price', 'download_limit', 'download_expiry_days',
            'position', 'meta_gold_weight_grams', 'meta_total_diamond_weight',
            'meta_centre_diamond_weight', 'meta_weight_in_gram',
            'meta_woosw_count', 'meta_gold_price_per_gram',
            'meta_gold_price_per_gram_alt',
        ];

        if (in_array($columnName, $numericFields)) {
            if (is_numeric($value)) {
                // Determine if integer or decimal
                if (in_array($columnName, ['product_id', 'download_limit', 'download_expiry_days', 'position', 'meta_woosw_count'])) {
                    return (int) $value;
                }
                return (float) $value;
            }
            return null;
        }

        // DateTime fields
        $dateTimeFields = ['date_sale_price_starts', 'date_sale_price_ends'];
        if (in_array($columnName, $dateTimeFields)) {
            if ($value && strtotime($value)) {
                return date('Y-m-d H:i:s', strtotime($value));
            }
            return null;
        }

        // Default: return as string
        return $value;
    }

    /**
     * Get import summary statistics
     *
     * @return array Statistics
     */
    public static function getImportStats(): array
    {
        return [
            'total_records' => ProductDetail::count(),
            'imported_today' => ProductDetail::whereDate('imported_at', today())->count(),
            'unique_skus' => ProductDetail::distinct('sku')->count('sku'),
            'products_with_diamonds' => ProductDetail::whereNotNull('meta_diamond_clarity')->count(),
            'by_category' => ProductDetail::select('categories', DB::raw('count(*) as count'))
                ->groupBy('categories')
                ->get(),
        ];
    }

    /**
     * Clear all imported product details
     *
     * @return bool
     */
    public static function clearImportedData(): bool
    {
        try {
            ProductDetail::truncate();
            Log::info('ProductDetail table cleared');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear ProductDetail table', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
