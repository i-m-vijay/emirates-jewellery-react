<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionCategory extends Model
{
    protected $fillable = ['name', 'slug', 'image', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function collectionSubcategories(): HasMany
    {
        return $this->hasMany(CollectionSubcategory::class)->orderBy('sort_order');
    }

    public function productDetails(): HasMany
    {
        return $this->hasMany(ProductDetail::class);
    }
}
