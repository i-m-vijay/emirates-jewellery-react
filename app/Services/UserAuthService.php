<?php

namespace App\Services;

use App\Models\UserLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserAuthService
{
    /**
     * Register a new user.
     *
     * @param  array{
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   mobile_no: string,
     *   gender: string|null,
     *   date_of_birth: string|null,
     *   password: string
     * } $data
     */
    public function register(array $data): UserLogin
    {
        $user = UserLogin::create([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => strtolower(trim($data['email'])),
            'mobile_no'     => $data['mobile_no'],
            'gender'        => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'password'      => Hash::make($data['password']),
            'is_active'     => true,
        ]);

        // Reload from DB so all column defaults are present on the returned model
        return $user->fresh();
    }

    /**
     * Attempt login. Returns the user + fresh token on success, null on failure.
     *
     * @return array{ user: UserLogin, token: string }|null
     */
    public function login(string $email, string $password): ?array
    {
        $user = UserLogin::where('email', strtolower(trim($email)))
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $token = $this->generateToken($user);

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Invalidate the current token (logout).
     */
    public function logout(UserLogin $user): void
    {
        $user->update([
            'api_token'        => null,
            'token_created_at' => null,
        ]);
    }

    /**
     * Find a user by their Bearer token.
     */
    public function findByToken(string $token): ?UserLogin
    {
        return UserLogin::where('api_token', $token)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Issue a fresh token for an already-authenticated user (used by OTP login).
     */
    public function issueToken(UserLogin $user): string
    {
        return $this->generateToken($user);
    }

    // ── private ───────────────────────────────────────────────────────────

    private function generateToken(UserLogin $user): string
    {
        $token = Str::random(64);

        $user->update([
            'api_token'        => $token,
            'token_created_at' => now(),
        ]);

        return $token;
    }
}
