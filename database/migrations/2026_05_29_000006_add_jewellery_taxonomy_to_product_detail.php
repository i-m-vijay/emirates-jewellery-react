<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Attach the new taxonomy FKs to the existing product_detail table
    public function up(): void
    {
        Schema::table('product_detail', function (Blueprint $table) {
            // Which product type does this product belong to? (Gold / Diamond / …)
            $table->foreignId('product_type_id')
                  ->nullable()
                  ->after('subcategory_id')
                  ->constrained('product_types')
                  ->nullOnDelete();

            // Which category within that type? (Earrings / Rings / …)
            $table->foreignId('product_category_id')
                  ->nullable()
                  ->after('product_type_id')
                  ->constrained('product_categories')
                  ->nullOnDelete();

            // Top-level jewellery bucket (Gold Jewellery / Diamond Jewellery / All Jewellery)
            $table->foreignId('jewellery_id')
                  ->nullable()
                  ->after('product_category_id')
                  ->constrained('jewellery')
                  ->nullOnDelete();

            // Collections taxonomy (only populated when product_type = Collections)
            $table->foreignId('collection_category_id')
                  ->nullable()
                  ->after('jewellery_id')
                  ->constrained('collection_categories')
                  ->nullOnDelete();

            $table->foreignId('collection_subcategory_id')
                  ->nullable()
                  ->after('collection_category_id')
                  ->constrained('collection_subcategories')
                  ->nullOnDelete();

            // Indexes for front-end filtering
            $table->index('product_type_id');
            $table->index('product_category_id');
            $table->index('jewellery_id');
            $table->index('collection_category_id');
            $table->index('collection_subcategory_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_detail', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
            $table->dropForeign(['product_category_id']);
            $table->dropForeign(['jewellery_id']);
            $table->dropForeign(['collection_category_id']);
            $table->dropForeign(['collection_subcategory_id']);

            $table->dropIndex(['product_type_id']);
            $table->dropIndex(['product_category_id']);
            $table->dropIndex(['jewellery_id']);
            $table->dropIndex(['collection_category_id']);
            $table->dropIndex(['collection_subcategory_id']);

            $table->dropColumn([
                'product_type_id',
                'product_category_id',
                'jewellery_id',
                'collection_category_id',
                'collection_subcategory_id',
            ]);
        });
    }
};
