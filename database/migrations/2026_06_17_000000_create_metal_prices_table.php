<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metal_prices', function (Blueprint $table) {
            $table->id();
            $table->string('metal_type');                 // Gold, Diamond
            $table->decimal('per_gram_price', 12, 2);      // today's price per gram
            $table->unsignedInteger('products_updated')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metal_prices');
    }
};