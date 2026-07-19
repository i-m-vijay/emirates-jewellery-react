<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jewellery extends Model
{
    protected $table = 'jewellery';

    protected $fillable = ['name', 'slug', 'image', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function productDetails(): HasMany
    {
        return $this->hasMany(ProductDetail::class);
    }
}
