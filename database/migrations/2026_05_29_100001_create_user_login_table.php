<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_login', function (Blueprint $table) {
            $table->id();

            // Registration form fields
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('mobile_no', 20);
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('password');

            // Auth token — null means logged out
            // Frontend must store this in sessionStorage so it's lost when browser closes
            $table->string('api_token', 80)->nullable()->unique();
            $table->timestamp('token_created_at')->nullable();

            // Account state
            $table->boolean('is_active')->default(1);
            $table->rememberToken();
            $table->timestamps();

            // Fast token lookup
            $table->index('api_token');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login');
    }
};
