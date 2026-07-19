<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserLogin extends Authenticatable
{
    use Notifiable;

    protected $table = 'user_login';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile_no',
        'gender',
        'date_of_birth',
        'password',
        'api_token',
        'token_created_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected $casts = [
        'date_of_birth'    => 'date',
        'token_created_at' => 'datetime',
        'is_active'        => 'boolean',
    ];

    /** Full name helper */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
