<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductDetail;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductDetail::with(['category', 'subcategory']);

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
                  ->orWhere('brands', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->input('subcategory_id'));
        }

        if ($request->filled('in_stock')) {
            $query->where('in_stock', $request->input('in_stock'));
        }

        $productDetails = $query->latest('record_id')->paginate(20);
        $categories     = Category::orderBy('category_name')->get();
        $subcategories  = $request->filled('category_id')
            ? Subcategory::where('category_id', $request->input('category_id'))->orderBy('name')->get()
            : Subcategory::orderBy('name')->get();

        return view('admin.product-details.index', compact('productDetails', 'categories', 'subcategories'));
    }

    public function edit(ProductDetail $productDetail)
    {
        $categories    = Category::orderBy('category_name')->get();
        $subcategories = $productDetail->category_id
            ? Subcategory::where('category_id', $productDetail->category_id)->orderBy('name')->get()
            : collect();

        return view('admin.product-details.edit', compact('productDetail', 'categories', 'subcategories'));
    }

    public function update(Request $request, ProductDetail $productDetail)
    {
        $validated = $request->validate([
            // Identifiers
            'sku'                       => 'nullable|string|max:255|unique:product_detail,sku,' . $productDetail->record_id . ',record_id',
            'type'                      => 'nullable|string|max:100',
            'gtin'                      => 'nullable|string|max:100',
            'name'                      => 'nullable|string|max:500',
            // Category
            'category_id'               => 'nullable|exists:categories,id',
            'subcategory_id'            => 'nullable|exists:subcategories,id',
            // Flags
            'published'                 => 'boolean',
            'is_featured'               => 'boolean',
            'visibility_in_catalog'     => 'nullable|string|max:100',
            // Descriptions
            'short_description'         => 'nullable|string',
            'description'               => 'nullable|string',
            'purchase_note'             => 'nullable|string',
            'allow_customer_reviews'    => 'boolean',
            // Pricing
            'regular_price'             => 'nullable|numeric|min:0',
            'sale_price'                => 'nullable|numeric|min:0',
            'date_sale_price_starts'    => 'nullable|date',
            'date_sale_price_ends'      => 'nullable|date',
            'tax_status'                => 'nullable|string|max:100',
            'tax_class'                 => 'nullable|string|max:100',
            // Stock
            'in_stock'                  => 'boolean',
            'stock'                     => 'nullable|numeric',
            'low_stock_amount'          => 'nullable|numeric',
            'backorders_allowed'        => 'boolean',
            'sold_individually'         => 'boolean',
            // Dimensions
            'weight_kg'                 => 'nullable|numeric',
            'length_cm'                 => 'nullable|numeric',
            'width_cm'                  => 'nullable|numeric',
            'height_cm'                 => 'nullable|numeric',
            'shipping_class'            => 'nullable|string|max:100',
            // Taxonomy
            'categories'                => 'nullable|string',
            'tags'                      => 'nullable|string',
            'brands'                    => 'nullable|string|max:255',
            // Media & Links
            'images'                    => 'nullable|string',
            'external_url'              => 'nullable|string',
            'button_text'               => 'nullable|string|max:255',
            'parent'                    => 'nullable|string|max:255',
            'grouped_products'          => 'nullable|string',
            'upsells'                   => 'nullable|string',
            'cross_sells'               => 'nullable|string',
            'download_limit'            => 'nullable|integer',
            'download_expiry_days'      => 'nullable|integer',
            'position'                  => 'nullable|integer',
            // Attributes
            'attribute_1_name'          => 'nullable|string|max:255',
            'attribute_1_value'         => 'nullable|string',
            'attribute_1_visible'       => 'boolean',
            'attribute_1_global'        => 'boolean',
            'attribute_2_name'          => 'nullable|string|max:255',
            'attribute_2_value'         => 'nullable|string',
            'attribute_2_visible'       => 'boolean',
            'attribute_2_global'        => 'boolean',
            'attribute_3_name'          => 'nullable|string|max:255',
            'attribute_3_value'         => 'nullable|string',
            'attribute_3_visible'       => 'boolean',
            'attribute_3_global'        => 'boolean',
            'attribute_4_name'          => 'nullable|string|max:255',
            'attribute_4_value'         => 'nullable|string',
            'attribute_4_visible'       => 'boolean',
            'attribute_4_global'        => 'boolean',
            'attribute_5_name'          => 'nullable|string|max:255',
            'attribute_5_value'         => 'nullable|string',
            'attribute_5_visible'       => 'boolean',
            'attribute_5_global'        => 'boolean',
            // Meta – Jewellery
            'meta_metal'                => 'nullable|string|max:255',
            'meta_metal_alt'            => 'nullable|string|max:255',
            'meta_gold_type'            => 'nullable|string|max:255',
            'meta_gold_purity'          => 'nullable|string|max:255',
            'meta_gold_weight'          => 'nullable|string|max:255',
            'meta_gold_weight_grams'    => 'nullable|numeric',
            'meta_total_diamond_weight' => 'nullable|numeric',
            'meta_centre_diamond_weight'=> 'nullable|numeric',
            'meta_diamond_clarity'      => 'nullable|string|max:255',
            'meta_diamond_color'        => 'nullable|string|max:255',
            'meta_gold_weight_alt'      => 'nullable|string|max:255',
            'meta_gold_purity_alt'      => 'nullable|string|max:255',
            'meta_weight_in_gram'       => 'nullable|numeric',
            'meta_product_material_type'=> 'nullable|string|max:255',
            'meta_gold_price_per_gram'  => 'nullable|numeric',
            'meta_gold_price_per_gram_alt' => 'nullable|numeric',
            'meta_product_video_url'    => 'nullable|string',
            'meta_product_video_url_alt'=> 'nullable|string',
            'meta_gemhub_media_link'    => 'nullable|string',
            'meta_gemhub_video_urls'    => 'nullable|string',
            'meta_wcfv_source'          => 'nullable|string|max:255',
            'meta_wcfv_local_video'     => 'nullable|string',
            'meta_wcfv_poster_image'    => 'nullable|string',
            'meta_wcfv_youtube_video'   => 'nullable|string',
            'meta_wcfv_youtube_image'   => 'nullable|string',
            'meta_wcfv_vimeo_video'     => 'nullable|string',
            'meta_wcfv_vimeo_image'     => 'nullable|string',
        ]);

        // Convert unchecked checkboxes (missing from POST) to false
        $booleans = [
            'published', 'is_featured', 'in_stock', 'backorders_allowed',
            'sold_individually', 'allow_customer_reviews',
            'attribute_1_visible', 'attribute_1_global',
            'attribute_2_visible', 'attribute_2_global',
            'attribute_3_visible', 'attribute_3_global',
            'attribute_4_visible', 'attribute_4_global',
            'attribute_5_visible', 'attribute_5_global',
        ];
        foreach ($booleans as $bool) {
            $validated[$bool] = $request->boolean($bool);
        }

        $productDetail->update($validated);

        return redirect()->route('admin.product-details.index')
                         ->with('success', "Product detail \"{$productDetail->name}\" updated successfully!");
    }
}
