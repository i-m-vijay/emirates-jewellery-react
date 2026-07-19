<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Named collections inside each collection_category
    // e.g. Apurva, Veda, Yuva … (under Gold)
    public function up(): void
    {
        Schema::create('collection_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_category_id')
                  ->constrained('collection_categories')
                  ->cascadeOnDelete();
            $table->string('name');           // Apurva, Veda, Eleganza …
            $table->string('slug');
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->unique(['collection_category_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_subcategories');
    }
};
