<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Holds the material/theme groupings inside "Collections"
    // e.g. Gold, Diamond, Precious Stone, Platinum, Pearl
    public function up(): void
    {
        Schema::create('collection_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Gold, Diamond, Precious Stone, Platinum, Pearl
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_categories');
    }
};
