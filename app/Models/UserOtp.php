<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserOtp extends Model
{
    protected $table = 'user_otps';

    protected $fillable = [
        'identifier',
        'identifier_type',
        'purpose',
        'otp_hash',
        'expires_at',
        'is_used',
        'failed_attempts',
    ];

    protected $casts = [
        'expires_at'      => 'datetime',
        'is_used'         => 'boolean',
        'failed_attempts' => 'integer',
    ];

    protected $hidden = ['otp_hash'];

    // ── Scopes ────────────────────────────────────────────────────────────

    /** Active = not used and not expired. */
    public function scopeActive($query)
    {
        return $query->where('is_used', false)
                     ->where('expires_at', '>', Carbon::now());
    }

    /** Filter by identifier + purpose. */
    public function scopeFor($query, string $identifier, string $purpose)
    {
        return $query->where('identifier', $identifier)
                     ->where('purpose', $purpose);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasExceededAttempts(int $max = 3): bool
    {
        return $this->failed_attempts >= $max;
    }
}
