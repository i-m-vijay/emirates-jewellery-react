<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_otps', function (Blueprint $table) {
            $table->id();

            // Who is this OTP for
            $table->string('identifier');                              // email or mobile_no value
            $table->enum('identifier_type', ['email', 'mobile'])
                  ->default('email');

            // Why was this OTP generated
            $table->enum('purpose', ['registration', 'login', 'password_reset'])
                  ->default('registration');

            // The code itself (6 digits, stored hashed)
            $table->string('otp_hash');                               // bcrypt of 6-digit code
            $table->timestamp('expires_at');                          // 10 min from creation

            // State
            $table->boolean('is_used')->default(false);
            $table->unsignedTinyInteger('failed_attempts')->default(0); // brute-force guard

            $table->timestamps();

            // Lookup index
            $table->index(['identifier', 'purpose', 'is_used']);
        });

        // Add email_verified_at to user_login so OTP verification can mark it
        Schema::table('user_login', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('user_login', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });

        Schema::dropIfExists('user_otps');
    }
};
