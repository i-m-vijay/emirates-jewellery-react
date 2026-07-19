<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class)->orderBy('sort_order');
    }

    public function productDetails(): HasMany
    {
        return $this->hasMany(ProductDetail::class);
    }
}
