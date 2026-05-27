<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('product_detail', function (Blueprint $table) {
        if (!Schema::hasColumn('product_detail', 'created_at')) {
            $table->timestamps();
        }
        if (!Schema::hasColumn('product_detail', 'imported_at')) {
            $table->timestamp('imported_at')->nullable();
        }
    });
}

public function down(): void
{
    Schema::table('product_detail', function (Blueprint $table) {
        $table->dropTimestamps();
        $table->dropColumn('imported_at');
    });
}
};
