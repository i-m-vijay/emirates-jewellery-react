<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetalPrice extends Model
{
    protected $fillable = [
        'metal_type',
        'per_gram_price',
        'products_updated',
    ];
}