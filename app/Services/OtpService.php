<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\UserLogin;
use App\Models\UserOtp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    const OTP_LENGTH         = 6;
    const OTP_EXPIRY_MINUTES = 10;
    const MAX_ATTEMPTS       = 3;
    // Max OTP requests per identifier per window
    const RATE_LIMIT_COUNT   = 3;
    const RATE_LIMIT_MINUTES = 15;

    // ── Public API ────────────────────────────────────────────────────────

    /**
     * Generate a fresh OTP for the given identifier and purpose.
     *
     * @param  string  $identifier  Email address or mobile number.
     * @param  string  $purpose     registration | login | password_reset
     * @param  string  $identifierType  email | mobile
     *
     * @return array{ otp: string, expires_at: string, message: string }
     *
     * @throws \RuntimeException  When rate-limit is exceeded.
     */
    public function generate(
        string $identifier,
        string $purpose,
        string $identifierType = 'email'
    ): array {
        // ── Rate-limit check ───────────────────────────────────────────
        $recentCount = UserOtp::where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->where('created_at', '>=', Carbon::now()->subMinutes(self::RATE_LIMIT_MINUTES))
            ->count();

        if ($recentCount >= self::RATE_LIMIT_COUNT) {
            throw new \RuntimeException(
                "Too many OTP requests. Please wait " . self::RATE_LIMIT_MINUTES . " minutes before trying again."
            );
        }

        // ── Invalidate any previous unused OTPs ────────────────────────
        UserOtp::for($identifier, $purpose)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // ── Generate plain OTP ─────────────────────────────────────────
        $plainOtp  = $this->generateCode();
        $expiresAt = Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES);

        // ── Persist (store hash, never the plain code) ─────────────────
        UserOtp::create([
            'identifier'      => $identifier,
            'identifier_type' => $identifierType,
            'purpose'         => $purpose,
            'otp_hash'        => Hash::make($plainOtp),
            'expires_at'      => $expiresAt,
        ]);

        // ── Deliver ────────────────────────────────────────────────────
        $this->deliver($identifier, $identifierType, $plainOtp, $purpose);

        return [
            'otp'        => config('app.debug') ? $plainOtp : null, // expose only in dev
            'expires_at' => $expiresAt->toDateTimeString(),
            'message'    => "OTP sent to your {$identifierType}. Valid for " . self::OTP_EXPIRY_MINUTES . " minutes.",
        ];
    }

    /**
     * Verify OTP submitted by the user.
     *
     * @return array{ success: bool, message: string, action?: string }
     */
    public function verify(
        string $identifier,
        string $plainOtp,
        string $purpose
    ): array {
        // Find the latest active OTP for this identifier+purpose
        $record = UserOtp::for($identifier, $purpose)
            ->active()
            ->latest()
            ->first();

        // No active OTP found
        if (!$record) {
            return [
                'success' => false,
                'code'    => 'NO_OTP',
                'message' => 'No active OTP found. Please request a new one.',
            ];
        }

        // Expired
        if ($record->isExpired()) {
            $record->update(['is_used' => true]);
            return [
                'success' => false,
                'code'    => 'EXPIRED',
                'message' => 'OTP has expired. Please request a new one.',
            ];
        }

        // Brute-force guard
        if ($record->hasExceededAttempts(self::MAX_ATTEMPTS)) {
            $record->update(['is_used' => true]);
            return [
                'success' => false,
                'code'    => 'LOCKED',
                'message' => 'Too many incorrect attempts. Please request a new OTP.',
            ];
        }

        // Wrong OTP
        if (!Hash::check($plainOtp, $record->otp_hash)) {
            $remaining = self::MAX_ATTEMPTS - $record->failed_attempts - 1;
            $record->increment('failed_attempts');

            if ($remaining <= 0) {
                $record->update(['is_used' => true]);
                return [
                    'success' => false,
                    'code'    => 'LOCKED',
                    'message' => 'Too many incorrect attempts. Please request a new OTP.',
                ];
            }

            return [
                'success'   => false,
                'code'      => 'INVALID',
                'message'   => "Invalid OTP. {$remaining} attempt(s) remaining.",
                'remaining' => $remaining,
            ];
        }

        // ✔ Correct — mark as used
        $record->update(['is_used' => true]);

        // Post-verification side effects
        $this->applyPurposeEffect($identifier, $purpose);

        return [
            'success' => true,
            'code'    => 'VERIFIED',
            'message' => 'OTP verified successfully.',
        ];
    }

    // ── Private ───────────────────────────────────────────────────────────

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    private function deliver(
        string $identifier,
        string $identifierType,
        string $otp,
        string $purpose
    ): void {
        if ($identifierType === 'email') {
            try {
                Mail::to($identifier)->send(
                    new OtpMail($otp, $purpose, self::OTP_EXPIRY_MINUTES)
                );
            } catch (\Throwable $e) {
                // Mail failure must not break the API response
                Log::error("OtpService: mail delivery failed for {$identifier}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Always log (visible in storage/logs/laravel.log)
        Log::info("OTP [{$purpose}] for {$identifier} → {$otp} (expires " . self::OTP_EXPIRY_MINUTES . " min)");
    }

    /**
     * After a successful OTP verification, run any purpose-specific side effect.
     */
    private function applyPurposeEffect(string $identifier, string $purpose): void
    {
        if ($purpose === 'registration') {
            // Mark email as verified on user_login
            UserLogin::where('email', $identifier)
                ->whereNull('email_verified_at')
                ->update(['email_verified_at' => Carbon::now()]);
        }
    }
}
