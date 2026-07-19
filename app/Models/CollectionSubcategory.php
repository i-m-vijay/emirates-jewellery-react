<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionSubcategory extends Model
{
    protected $fillable = [
        'collection_category_id', 'name', 'slug', 'image', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function collectionCategory(): BelongsTo
    {
        return $this->belongsTo(CollectionCategory::class);
    }

    public function productDetails(): HasMany
    {
        return $this->hasMany(ProductDetail::class);
    }
}
