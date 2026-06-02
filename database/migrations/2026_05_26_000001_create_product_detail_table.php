<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_detail', function (Blueprint $table) {
            // Primary identifier
            $table->id('record_id')->autoIncrement();
            $table->unsignedBigInteger('product_id')->nullable()->unique(); // CSV ID field
            $table->string('sku')->nullable()->unique(); // Unique SKU for duplicate prevention
            
            // Basic Product Information (Columns 1-25)
            $table->string('type')->nullable();
            $table->string('gtin')->nullable();
            $table->string('name')->nullable();
            $table->boolean('published')->default(0);
            $table->boolean('is_featured')->default(0);
            $table->string('visibility_in_catalog')->nullable();
            $table->longText('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->dateTime('date_sale_price_starts')->nullable();
            $table->dateTime('date_sale_price_ends')->nullable();
            $table->string('tax_status')->nullable();
            $table->string('tax_class')->nullable();
            $table->boolean('in_stock')->default(0);
            $table->decimal('stock', 10, 2)->nullable();
            $table->decimal('low_stock_amount', 10, 2)->nullable();
            $table->boolean('backorders_allowed')->default(0);
            $table->boolean('sold_individually')->default(0);
            
            // Dimensions (Columns 19-22)
            $table->decimal('weight_kg', 10, 3)->nullable();
            $table->decimal('length_cm', 10, 3)->nullable();
            $table->decimal('width_cm', 10, 3)->nullable();
            $table->decimal('height_cm', 10, 3)->nullable();
            
            // Review & Purchase (Columns 23-26)
            $table->boolean('allow_customer_reviews')->default(1);
            $table->longText('purchase_note')->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->decimal('regular_price', 15, 2)->nullable();
            
            // Categories & Tags (Columns 27-29)
            $table->longText('categories')->nullable();
            $table->longText('tags')->nullable();
            $table->string('shipping_class')->nullable();
            
            // Media & Links (Columns 30-39)
            $table->longText('images')->nullable();
            $table->integer('download_limit')->nullable();
            $table->integer('download_expiry_days')->nullable();
            $table->string('parent')->nullable();
            $table->longText('grouped_products')->nullable();
            $table->longText('upsells')->nullable();
            $table->longText('cross_sells')->nullable();
            $table->longText('external_url')->nullable();
            $table->string('button_text')->nullable();
            
            // Position & Brand (Columns 40-41)
            $table->integer('position')->nullable();
            $table->string('brands')->nullable();
            
            // Attributes (Columns 42-61) - 5 attributes with 4 properties each
            $table->string('attribute_1_name')->nullable();
            $table->longText('attribute_1_value')->nullable();
            $table->boolean('attribute_1_visible')->default(0);
            $table->boolean('attribute_1_global')->default(0);
            
            $table->string('attribute_2_name')->nullable();
            $table->longText('attribute_2_value')->nullable();
            $table->boolean('attribute_2_visible')->default(0);
            $table->boolean('attribute_2_global')->default(0);
            
            $table->string('attribute_3_name')->nullable();
            $table->longText('attribute_3_value')->nullable();
            $table->boolean('attribute_3_visible')->default(0);
            $table->boolean('attribute_3_global')->default(0);
            
            $table->string('attribute_4_name')->nullable();
            $table->longText('attribute_4_value')->nullable();
            $table->boolean('attribute_4_visible')->default(0);
            $table->boolean('attribute_4_global')->default(0);
            
            $table->string('attribute_5_name')->nullable();
            $table->longText('attribute_5_value')->nullable();
            $table->boolean('attribute_5_visible')->default(0);
            $table->boolean('attribute_5_global')->default(0);
            
            // Meta Fields (Columns 62-94)
            $table->string('meta_wp_page_template')->nullable();
            $table->string('meta_metal')->nullable();
            $table->string('meta_metal_alt')->nullable();
            $table->string('meta_gemhub_media_link')->nullable();
            $table->longText('meta_gemhub_video_urls')->nullable();
            $table->string('meta_gold_type')->nullable();
            $table->string('meta_gold_purity')->nullable();
            $table->string('meta_gold_weight')->nullable();
            $table->decimal('meta_gold_weight_grams', 10, 2)->nullable();
            $table->decimal('meta_total_diamond_weight', 10, 2)->nullable();
            $table->decimal('meta_centre_diamond_weight', 10, 2)->nullable();
            $table->string('meta_diamond_clarity')->nullable();
            $table->string('meta_diamond_color')->nullable();
            $table->string('meta_gold_weight_alt')->nullable();
            $table->string('meta_gold_purity_alt')->nullable();
            $table->boolean('meta_elementor_edit_mode')->default(0);
            $table->string('meta_elementor_template_type')->nullable();
            $table->string('meta_elementor_version')->nullable();
            $table->decimal('meta_weight_in_gram', 10, 2)->nullable();
            $table->string('meta_product_material_type')->nullable();
            $table->integer('meta_woosw_count')->nullable();
            $table->string('meta_woosw_add')->nullable();
            $table->decimal('meta_gold_price_per_gram', 10, 2)->nullable();
            $table->decimal('meta_gold_price_per_gram_alt', 10, 2)->nullable();
            $table->string('meta_product_video_url')->nullable();
            $table->string('meta_product_video_url_alt')->nullable();
            $table->string('meta_wcfv_source')->nullable();
            $table->string('meta_wcfv_local_video')->nullable();
            $table->string('meta_wcfv_poster_image')->nullable();
            $table->string('meta_wcfv_youtube_video')->nullable();
            $table->string('meta_wcfv_youtube_image')->nullable();
            $table->string('meta_wcfv_vimeo_video')->nullable();
            $table->string('meta_wcfv_vimeo_image')->nullable();
            
            // Timestamps for tracking
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Indexes for performance
            $table->index('product_id');
            $table->index('sku');
            $table->index('name');
            $table->index('imported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_detail');
    }
};
