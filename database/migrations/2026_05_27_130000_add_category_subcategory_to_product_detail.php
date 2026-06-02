<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('sku');
            $table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');

            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->nullOnDelete();

            $table->index('category_id');
            $table->index('subcategory_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_detail', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn(['category_id', 'subcategory_id']);
        });
    }
};
