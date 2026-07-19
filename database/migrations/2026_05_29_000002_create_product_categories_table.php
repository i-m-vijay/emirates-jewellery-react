<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')->constrained('product_types')->cascadeOnDelete();
            $table->string('name');           // Earrings, Rings, Pendants …
            $table->string('slug');
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->unique(['product_type_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
