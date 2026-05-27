<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProductDetail;

// Test single insert
try {
    $testData = [
        'product_id' => 1,
        'sku' => 'test-sku-001',
        'name' => 'Test Product',
        'type' => 'simple',
        'published' => 1,
        'in_stock' => 1,
        'stock' => 100,
        'regular_price' => 50.00,
        'imported_at' => now()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ];

    // Test insert() method
    $result = ProductDetail::insert([$testData]);
    echo "Insert successful!\n";
    echo "Total records: " . ProductDetail::count() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
