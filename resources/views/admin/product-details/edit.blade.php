@extends('layouts.admin')

@section('title', 'Edit Product Detail')
@section('header', 'Edit Product Detail')

@section('content')
<div class="mb-4 flex items-center gap-3">
    <a href="{{ route('admin.product-details.index') }}" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-arrow-left mr-1"></i>Back to list
    </a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 font-medium truncate max-w-md">{{ $productDetail->name ?? 'Product #' . $productDetail->record_id }}</span>
</div>

<form method="POST" action="{{ route('admin.product-details.update', $productDetail->record_id) }}" id="editForm">
    @csrf
    @method('PUT')

    {{-- Tab Navigation --}}
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="flex min-w-max" id="tabNav">
                @php
                    $tabs = [
                        ['id' => 'basic',       'label' => 'Basic Info',       'icon' => 'fas fa-info-circle'],
                        ['id' => 'category',    'label' => 'Category',         'icon' => 'fas fa-tags'],
                        ['id' => 'pricing',     'label' => 'Pricing',          'icon' => 'fas fa-dollar-sign'],
                        ['id' => 'stock',       'label' => 'Stock & Shipping', 'icon' => 'fas fa-boxes'],
                        ['id' => 'description', 'label' => 'Descriptions',     'icon' => 'fas fa-align-left'],
                        ['id' => 'media',       'label' => 'Media & Links',    'icon' => 'fas fa-photo-video'],
                        ['id' => 'attributes',  'label' => 'Attributes',       'icon' => 'fas fa-list'],
                        ['id' => 'meta',        'label' => 'Jewellery Meta',   'icon' => 'fas fa-gem'],
                    ];
                @endphp
                @foreach ($tabs as $i => $tab)
                    <button type="button"
                            onclick="switchTab('{{ $tab['id'] }}')"
                            id="tab-btn-{{ $tab['id'] }}"
                            class="tab-btn flex items-center gap-2 px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition
                                {{ $i === 0 ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        <i class="{{ $tab['icon'] }}"></i>
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Validation errors banner --}}
        @if ($errors->any())
            <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-300 text-red-700 rounded text-sm">
                <strong>Please fix the following errors:</strong>
                <ul class="mt-1 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- TAB 1: Basic Info --}}
        {{-- ============================================================ --}}
        <div id="tab-basic" class="tab-panel p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $productDetail->sku) }}" class="form-input">
                    @error('sku')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Type</label>
                    <input type="text" name="type" value="{{ old('type', $productDetail->type) }}" class="form-input">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="{{ old('name', $productDetail->name) }}" class="form-input">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">GTIN / Barcode</label>
                    <input type="text" name="gtin" value="{{ old('gtin', $productDetail->gtin) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Brands</label>
                    <input type="text" name="brands" value="{{ old('brands', $productDetail->brands) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Visibility in Catalog</label>
                    <select name="visibility_in_catalog" class="form-input">
                        @foreach (['visible','catalog','search','hidden'] as $opt)
                            <option value="{{ $opt }}" {{ old('visibility_in_catalog', $productDetail->visibility_in_catalog) === $opt ? 'selected' : '' }}>
                                {{ ucfirst($opt) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Categories (CSV text)</label>
                    <input type="text" name="categories" value="{{ old('categories', $productDetail->categories) }}" class="form-input" placeholder="e.g. Gold > Rings">
                </div>

                <div>
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" value="{{ old('tags', $productDetail->tags) }}" class="form-input">
                </div>

                <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ([
                        ['published',   'Published'],
                        ['is_featured', 'Featured'],
                    ] as [$field, $label])
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   {{ old($field, $productDetail->$field) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 2: Category --}}
        {{-- ============================================================ --}}
        <div id="tab-category" class="tab-panel p-6 hidden">
            <p class="text-sm text-gray-500 mb-5">Link this product to a category and subcategory. These are used by the React frontend to filter products.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-xl">
                <div>
                    <label class="form-label">Category</label>
                    <select name="category_id" id="categorySelect" class="form-input" onchange="loadSubcategories(this.value)">
                        <option value="">— None —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $productDetail->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Subcategory</label>
                    <select name="subcategory_id" id="subcategorySelect" class="form-input">
                        <option value="">— None —</option>
                        @foreach ($subcategories as $sub)
                            <option value="{{ $sub->id }}" {{ old('subcategory_id', $productDetail->subcategory_id) == $sub->id ? 'selected' : '' }}>
                                {{ $sub->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subcategory_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 3: Pricing --}}
        {{-- ============================================================ --}}
        <div id="tab-pricing" class="tab-panel p-6 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-2xl">

                <div>
                    <label class="form-label">Regular Price (AED)</label>
                    <input type="number" step="0.01" name="regular_price" value="{{ old('regular_price', $productDetail->regular_price) }}" class="form-input">
                    @error('regular_price')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Sale Price (AED)</label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $productDetail->sale_price) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Sale Price Start Date</label>
                    <input type="datetime-local" name="date_sale_price_starts"
                           value="{{ old('date_sale_price_starts', $productDetail->date_sale_price_starts ? \Carbon\Carbon::parse($productDetail->date_sale_price_starts)->format('Y-m-d\TH:i') : '') }}"
                           class="form-input">
                </div>

                <div>
                    <label class="form-label">Sale Price End Date</label>
                    <input type="datetime-local" name="date_sale_price_ends"
                           value="{{ old('date_sale_price_ends', $productDetail->date_sale_price_ends ? \Carbon\Carbon::parse($productDetail->date_sale_price_ends)->format('Y-m-d\TH:i') : '') }}"
                           class="form-input">
                </div>

                <div>
                    <label class="form-label">Tax Status</label>
                    <select name="tax_status" class="form-input">
                        @foreach (['taxable','shipping','none'] as $opt)
                            <option value="{{ $opt }}" {{ old('tax_status', $productDetail->tax_status) === $opt ? 'selected' : '' }}>
                                {{ ucfirst($opt) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Tax Class</label>
                    <input type="text" name="tax_class" value="{{ old('tax_class', $productDetail->tax_class) }}" class="form-input">
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 4: Stock & Shipping --}}
        {{-- ============================================================ --}}
        <div id="tab-stock" class="tab-panel p-6 hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-3xl">

                <div>
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" step="0.01" name="stock" value="{{ old('stock', $productDetail->stock) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Low Stock Threshold</label>
                    <input type="number" step="0.01" name="low_stock_amount" value="{{ old('low_stock_amount', $productDetail->low_stock_amount) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Shipping Class</label>
                    <input type="text" name="shipping_class" value="{{ old('shipping_class', $productDetail->shipping_class) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Weight (kg)</label>
                    <input type="number" step="0.001" name="weight_kg" value="{{ old('weight_kg', $productDetail->weight_kg) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Length (cm)</label>
                    <input type="number" step="0.001" name="length_cm" value="{{ old('length_cm', $productDetail->length_cm) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Width (cm)</label>
                    <input type="number" step="0.001" name="width_cm" value="{{ old('width_cm', $productDetail->width_cm) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Height (cm)</label>
                    <input type="number" step="0.001" name="height_cm" value="{{ old('height_cm', $productDetail->height_cm) }}" class="form-input">
                </div>

                <div class="md:col-span-3 grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ([
                        ['in_stock',           'In Stock'],
                        ['backorders_allowed', 'Allow Backorders'],
                        ['sold_individually',  'Sold Individually'],
                    ] as [$field, $label])
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   {{ old($field, $productDetail->$field) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 5: Descriptions --}}
        {{-- ============================================================ --}}
        <div id="tab-description" class="tab-panel p-6 hidden">
            <div class="space-y-5">

                <div>
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" rows="3" class="form-input">{{ old('short_description', $productDetail->short_description) }}</textarea>
                </div>

                <div>
                    <label class="form-label">Full Description</label>
                    <textarea name="description" rows="8" class="form-input">{{ old('description', $productDetail->description) }}</textarea>
                </div>

                <div>
                    <label class="form-label">Purchase Note</label>
                    <textarea name="purchase_note" rows="3" class="form-input">{{ old('purchase_note', $productDetail->purchase_note) }}</textarea>
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="allow_customer_reviews" value="1"
                           {{ old('allow_customer_reviews', $productDetail->allow_customer_reviews) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Allow Customer Reviews</span>
                </label>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 6: Media & Links --}}
        {{-- ============================================================ --}}
        <div id="tab-media" class="tab-panel p-6 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div class="md:col-span-2">
                    <label class="form-label">Images (comma-separated URLs)</label>
                    <textarea name="images" rows="3" class="form-input font-mono text-xs">{{ old('images', $productDetail->images) }}</textarea>
                </div>

                <div>
                    <label class="form-label">External URL</label>
                    <input type="text" name="external_url" value="{{ old('external_url', $productDetail->external_url) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" value="{{ old('button_text', $productDetail->button_text) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Parent</label>
                    <input type="text" name="parent" value="{{ old('parent', $productDetail->parent) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Position</label>
                    <input type="number" name="position" value="{{ old('position', $productDetail->position) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Grouped Products</label>
                    <input type="text" name="grouped_products" value="{{ old('grouped_products', $productDetail->grouped_products) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Upsells</label>
                    <input type="text" name="upsells" value="{{ old('upsells', $productDetail->upsells) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Cross-sells</label>
                    <input type="text" name="cross_sells" value="{{ old('cross_sells', $productDetail->cross_sells) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Download Limit</label>
                    <input type="number" name="download_limit" value="{{ old('download_limit', $productDetail->download_limit) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Download Expiry (days)</label>
                    <input type="number" name="download_expiry_days" value="{{ old('download_expiry_days', $productDetail->download_expiry_days) }}" class="form-input">
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 7: Attributes --}}
        {{-- ============================================================ --}}
        <div id="tab-attributes" class="tab-panel p-6 hidden">
            <div class="space-y-6">
                @for ($i = 1; $i <= 5; $i++)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Attribute {{ $i }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Name</label>
                                <input type="text" name="attribute_{{ $i }}_name"
                                       value="{{ old('attribute_' . $i . '_name', $productDetail->{'attribute_' . $i . '_name'}) }}"
                                       class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Value(s)</label>
                                <input type="text" name="attribute_{{ $i }}_value"
                                       value="{{ old('attribute_' . $i . '_value', $productDetail->{'attribute_' . $i . '_value'}) }}"
                                       class="form-input">
                            </div>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="attribute_{{ $i }}_visible" value="1"
                                           {{ old('attribute_' . $i . '_visible', $productDetail->{'attribute_' . $i . '_visible'}) ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">Visible</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="attribute_{{ $i }}_global" value="1"
                                           {{ old('attribute_' . $i . '_global', $productDetail->{'attribute_' . $i . '_global'}) ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">Global</span>
                                </label>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 8: Jewellery Meta --}}
        {{-- ============================================================ --}}
        <div id="tab-meta" class="tab-panel p-6 hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <div>
                    <label class="form-label">Metal</label>
                    <input type="text" name="meta_metal" value="{{ old('meta_metal', $productDetail->meta_metal) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Metal (Alt)</label>
                    <input type="text" name="meta_metal_alt" value="{{ old('meta_metal_alt', $productDetail->meta_metal_alt) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gold Type</label>
                    <input type="text" name="meta_gold_type" value="{{ old('meta_gold_type', $productDetail->meta_gold_type) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gold Purity</label>
                    <input type="text" name="meta_gold_purity" value="{{ old('meta_gold_purity', $productDetail->meta_gold_purity) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gold Purity (Alt)</label>
                    <input type="text" name="meta_gold_purity_alt" value="{{ old('meta_gold_purity_alt', $productDetail->meta_gold_purity_alt) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gold Weight</label>
                    <input type="text" name="meta_gold_weight" value="{{ old('meta_gold_weight', $productDetail->meta_gold_weight) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gold Weight (Alt)</label>
                    <input type="text" name="meta_gold_weight_alt" value="{{ old('meta_gold_weight_alt', $productDetail->meta_gold_weight_alt) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gold Weight (grams)</label>
                    <input type="number" step="0.01" name="meta_gold_weight_grams" value="{{ old('meta_gold_weight_grams', $productDetail->meta_gold_weight_grams) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Weight in Gram</label>
                    <input type="number" step="0.01" name="meta_weight_in_gram" value="{{ old('meta_weight_in_gram', $productDetail->meta_weight_in_gram) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Total Diamond Weight (ct)</label>
                    <input type="number" step="0.01" name="meta_total_diamond_weight" value="{{ old('meta_total_diamond_weight', $productDetail->meta_total_diamond_weight) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Centre Diamond Weight (ct)</label>
                    <input type="number" step="0.01" name="meta_centre_diamond_weight" value="{{ old('meta_centre_diamond_weight', $productDetail->meta_centre_diamond_weight) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Diamond Clarity</label>
                    <input type="text" name="meta_diamond_clarity" value="{{ old('meta_diamond_clarity', $productDetail->meta_diamond_clarity) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Diamond Color</label>
                    <input type="text" name="meta_diamond_color" value="{{ old('meta_diamond_color', $productDetail->meta_diamond_color) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gold Price / Gram</label>
                    <input type="number" step="0.01" name="meta_gold_price_per_gram" value="{{ old('meta_gold_price_per_gram', $productDetail->meta_gold_price_per_gram) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gold Price / Gram (Alt)</label>
                    <input type="number" step="0.01" name="meta_gold_price_per_gram_alt" value="{{ old('meta_gold_price_per_gram_alt', $productDetail->meta_gold_price_per_gram_alt) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Material Type</label>
                    <input type="text" name="meta_product_material_type" value="{{ old('meta_product_material_type', $productDetail->meta_product_material_type) }}" class="form-input">
                </div>

                <div class="md:col-span-3 border-t border-gray-200 pt-4 mt-2">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Videos & Media</h3>
                </div>

                <div>
                    <label class="form-label">Product Video URL</label>
                    <input type="text" name="meta_product_video_url" value="{{ old('meta_product_video_url', $productDetail->meta_product_video_url) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Product Video URL (Alt)</label>
                    <input type="text" name="meta_product_video_url_alt" value="{{ old('meta_product_video_url_alt', $productDetail->meta_product_video_url_alt) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">GemHub Media Link</label>
                    <input type="text" name="meta_gemhub_media_link" value="{{ old('meta_gemhub_media_link', $productDetail->meta_gemhub_media_link) }}" class="form-input">
                </div>
                <div class="md:col-span-3">
                    <label class="form-label">GemHub Video URLs</label>
                    <textarea name="meta_gemhub_video_urls" rows="2" class="form-input font-mono text-xs">{{ old('meta_gemhub_video_urls', $productDetail->meta_gemhub_video_urls) }}</textarea>
                </div>
                <div>
                    <label class="form-label">WCFV Source</label>
                    <input type="text" name="meta_wcfv_source" value="{{ old('meta_wcfv_source', $productDetail->meta_wcfv_source) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">WCFV YouTube Video</label>
                    <input type="text" name="meta_wcfv_youtube_video" value="{{ old('meta_wcfv_youtube_video', $productDetail->meta_wcfv_youtube_video) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">WCFV YouTube Image</label>
                    <input type="text" name="meta_wcfv_youtube_image" value="{{ old('meta_wcfv_youtube_image', $productDetail->meta_wcfv_youtube_image) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">WCFV Vimeo Video</label>
                    <input type="text" name="meta_wcfv_vimeo_video" value="{{ old('meta_wcfv_vimeo_video', $productDetail->meta_wcfv_vimeo_video) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">WCFV Vimeo Image</label>
                    <input type="text" name="meta_wcfv_vimeo_image" value="{{ old('meta_wcfv_vimeo_image', $productDetail->meta_wcfv_vimeo_image) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">WCFV Local Video</label>
                    <input type="text" name="meta_wcfv_local_video" value="{{ old('meta_wcfv_local_video', $productDetail->meta_wcfv_local_video) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">WCFV Poster Image</label>
                    <input type="text" name="meta_wcfv_poster_image" value="{{ old('meta_wcfv_poster_image', $productDetail->meta_wcfv_poster_image) }}" class="form-input">
                </div>
            </div>
        </div>

        {{-- Save / Cancel --}}
        <div class="border-t border-gray-200 px-6 py-4 flex items-center gap-4 bg-gray-50 rounded-b-lg">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                <i class="fas fa-save mr-2"></i>Save Changes
            </button>
            <a href="{{ route('admin.product-details.index') }}" class="px-6 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
            <span class="ml-auto text-xs text-gray-400">
                Record #{{ $productDetail->record_id }} &bull; Last updated {{ $productDetail->updated_at?->format('M d, Y H:i') }}
            </span>
        </div>
    </div>
</form>

<style>
.form-label  { @apply block text-sm font-medium text-gray-700 mb-1; }
.form-input  { @apply w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500; }
.form-error  { @apply text-red-500 text-xs mt-1; }
</style>

<script>
function switchTab(id) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('border-blue-600', 'text-blue-600');
        b.classList.add('border-transparent', 'text-gray-500');
    });
    document.getElementById('tab-' + id).classList.remove('hidden');
    const btn = document.getElementById('tab-btn-' + id);
    btn.classList.add('border-blue-600', 'text-blue-600');
    btn.classList.remove('border-transparent', 'text-gray-500');
}

function loadSubcategories(categoryId) {
    const select = document.getElementById('subcategorySelect');
    select.innerHTML = '<option value="">Loading...</option>';
    if (!categoryId) {
        select.innerHTML = '<option value="">— None —</option>';
        return;
    }
    fetch(`/api/subcategories-by-category/${categoryId}`)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">— None —</option>';
            data.data.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                select.appendChild(opt);
            });
        });
}

// Open the tab that has a validation error
@if ($errors->any())
    const fieldTabMap = {
        sku: 'basic', type: 'basic', gtin: 'basic', name: 'basic', brands: 'basic',
        category_id: 'category', subcategory_id: 'category',
        regular_price: 'pricing', sale_price: 'pricing', tax_status: 'pricing',
        in_stock: 'stock', stock: 'stock', weight_kg: 'stock',
        description: 'description', short_description: 'description',
    };
    const errorFields = @json(array_keys($errors->toArray()));
    const firstErrorTab = fieldTabMap[errorFields[0]] ?? 'basic';
    switchTab(firstErrorTab);
@endif
</script>
@endsection
