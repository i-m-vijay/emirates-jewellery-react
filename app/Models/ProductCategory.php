<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $fillable = [
        'product_type_id', 'name', 'slug', 'image', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function productDetails(): HasMany
    {
        return $this->hasMany(ProductDetail::class);
    }
}
