<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Top-level jewellery buckets: Gold Jewellery, Diamond Jewellery, All Jewellery
    public function up(): void
    {
        Schema::create('jewellery', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Gold Jewellery | Diamond Jewellery | All Jewellery
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jewellery');
    }
};
