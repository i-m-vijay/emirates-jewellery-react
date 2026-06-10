<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDetail extends Model
{
    use HasFactory;

    protected $table = 'product_detail';
    protected $primaryKey = 'record_id';

    // ── Legacy category taxonomy ──────────────────────────────────────────
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    // ── New jewellery taxonomy ────────────────────────────────────────────
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function jewellery(): BelongsTo
    {
        return $this->belongsTo(Jewellery::class);
    }

    public function collectionCategory(): BelongsTo
    {
        return $this->belongsTo(CollectionCategory::class);
    }

    public function collectionSubcategory(): BelongsTo
    {
        return $this->belongsTo(CollectionSubcategory::class);
    }
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'product_id',
        'sku',
        'category_id',
        'subcategory_id',
        'product_type_id',
        'product_category_id',
        'jewellery_id',
        'collection_category_id',
        'collection_subcategory_id',
        'type',
        'gtin',
        'name',
        'published',
        'is_featured',
        'visibility_in_catalog',
        'short_description',
        'description',
        'date_sale_price_starts',
        'date_sale_price_ends',
        'tax_status',
        'tax_class',
        'in_stock',
        'stock',
        'low_stock_amount',
        'backorders_allowed',
        'sold_individually',
        'weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'allow_customer_reviews',
        'purchase_note',
        'sale_price',
        'regular_price',
        'categories',
        'tags',
        'shipping_class',
        'images',
        'download_limit',
        'download_expiry_days',
        'parent',
        'grouped_products',
        'upsells',
        'cross_sells',
        'external_url',
        'button_text',
        'position',
        'brands',
        'attribute_1_name',
        'attribute_1_value',
        'attribute_1_visible',
        'attribute_1_global',
        'attribute_2_name',
        'attribute_2_value',
        'attribute_2_visible',
        'attribute_2_global',
        'attribute_3_name',
        'attribute_3_value',
        'attribute_3_visible',
        'attribute_3_global',
        'attribute_4_name',
        'attribute_4_value',
        'attribute_4_visible',
        'attribute_4_global',
        'attribute_5_name',
        'attribute_5_value',
        'attribute_5_visible',
        'attribute_5_global',
        'meta_wp_page_template',
        'meta_metal',
        'meta_metal_alt',
        'meta_gemhub_media_link',
        'meta_gemhub_video_urls',
        'meta_gold_type',
        'meta_gold_purity',
        'meta_gold_weight',
        'meta_gold_weight_grams',
        'meta_total_diamond_weight',
        'meta_centre_diamond_weight',
        'meta_diamond_clarity',
        'meta_diamond_color',
        'meta_gold_weight_alt',
        'meta_gold_purity_alt',
        'meta_elementor_edit_mode',
        'meta_elementor_template_type',
        'meta_elementor_version',
        'meta_weight_in_gram',
        'meta_product_material_type',
        'meta_woosw_count',
        'meta_woosw_add',
        'meta_gold_price_per_gram',
        'meta_gold_price_per_gram_alt',
        'meta_product_video_url',
        'meta_product_video_url_alt',
        'meta_wcfv_source',
        'meta_wcfv_local_video',
        'meta_wcfv_poster_image',
        'meta_wcfv_youtube_video',
        'meta_wcfv_youtube_image',
        'meta_wcfv_vimeo_video',
        'meta_wcfv_vimeo_image',
    ];

    protected $casts = [
        'published' => 'boolean',
        'is_featured' => 'boolean',
        'in_stock' => 'boolean',
        'backorders_allowed' => 'boolean',
        'sold_individually' => 'boolean',
        'allow_customer_reviews' => 'boolean',
        'attribute_1_visible' => 'boolean',
        'attribute_1_global' => 'boolean',
        'attribute_2_visible' => 'boolean',
        'attribute_2_global' => 'boolean',
        'attribute_3_visible' => 'boolean',
        'attribute_3_global' => 'boolean',
        'attribute_4_visible' => 'boolean',
        'attribute_4_global' => 'boolean',
        'attribute_5_visible' => 'boolean',
        'attribute_5_global' => 'boolean',
        'meta_elementor_edit_mode' => 'boolean',
        'imported_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to find product by SKU (for duplicate prevention)
     */
    public function scopeBySku($query, $sku)
    {
        return $query->where('sku', $sku);
    }

    /**
     * Scope to find product by product_id
     */
    public function scopeByProductId($query, $id)
    {
        return $query->where('product_id', $id);
    }

    /**
     * Get the import date formatted
     */
    public function getImportDateAttribute()
    {
        return $this->imported_at ? $this->imported_at->format('Y-m-d H:i:s') : null;
    }
}
