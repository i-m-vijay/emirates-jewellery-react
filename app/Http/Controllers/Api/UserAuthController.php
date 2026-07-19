<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserLogin;
use App\Services\OtpService;
use App\Services\UserAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserAuthController extends Controller
{
    public function __construct(
        private readonly UserAuthService $authService,
        private readonly OtpService $otpService,
    ) {}

    /**
     * POST /api/user/register
     *
     * Register a new user account.
     *
     * Body (JSON or form-data):
     * {
     *   "first_name":    "Raj",
     *   "last_name":     "Shah",
     *   "email":         "raj@example.com",
     *   "mobile_no":     "9876543210",
     *   "gender":        "male",           // male | female | other
     *   "date_of_birth": "1995-08-15",     // YYYY-MM-DD
     *   "password":      "Secret@123",
     *   "password_confirmation": "Secret@123"
     * }
     *
     * Success 201:
     * {
     *   "success": true,
     *   "message": "Registration successful.",
     *   "data": { "id": 1, "first_name": "Raj", "last_name": "Shah", ... }
     * }
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:255', 'unique:user_login,email'],
            'mobile_no'     => ['required', 'string', 'max:20'],
            'gender'        => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'password'      => ['required', 'confirmed', Password::min(6)],
        ], [
            'email.unique'       => 'This email is already registered.',
            'password.confirmed' => 'Password and confirm password do not match.',
            'date_of_birth.before' => 'Date of birth must be a past date.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $this->authService->register($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data'    => $this->userPayload($user),
        ], 201);
    }

    /**
     * POST /api/user/login
     *
     * Authenticate and receive a Bearer token.
     * Store the token in sessionStorage on the frontend so it is
     * automatically cleared when the browser tab/window is closed.
     *
     * Body:
     * { "email": "raj@example.com", "password": "Secret@123" }
     *
     * Success 200:
     * {
     *   "success": true,
     *   "message": "Login successful.",
     *   "token":   "abc123…",        ← send as Authorization: Bearer <token>
     *   "token_type": "Bearer",
     *   "data": { "id": 1, "first_name": "Raj", ... }
     * }
     *
     * Failure 401:
     * { "success": false, "message": "Invalid credentials." }
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Login successful.',
            'token'      => $result['token'],
            'token_type' => 'Bearer',
            'data'       => $this->userPayload($result['user']),
        ]);
    }

    /**
     * POST /api/user/logout          [requires: Authorization: Bearer <token>]
     *
     * Invalidates the current token.
     *
     * Success 200:
     * { "success": true, "message": "Logged out successfully." }
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/user/profile          [requires: Authorization: Bearer <token>]
     *
     * Returns the authenticated user's profile.
     *
     * Success 200:
     * { "success": true, "data": { "id": 1, "first_name": "Raj", ... } }
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->userPayload($request->user()),
        ]);
    }

    /**
     * POST /api/user/request-otp
     *
     * Generate and send an OTP to the given email address.
     *
     * Body:
     * { "email": "raj@example.com", "purpose": "registration|login|password_reset" }
     *
     * Success 200:
     * {
     *   "success":    true,
     *   "message":    "OTP sent to your email. Valid for 10 minutes.",
     *   "expires_at": "2026-05-30 12:10:00",
     *   "otp":        "123456"   ← only present when APP_DEBUG=true
     * }
     *
     * Rate-limited 429 when ≥ 3 requests within 15 minutes.
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'   => ['required', 'email'],
            'purpose' => ['required', 'in:registration,login,password_reset'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->otpService->generate(
                strtolower(trim($request->input('email'))),
                $request->input('purpose'),
                'email'
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);
        }

        $response = [
            'success'    => true,
            'message'    => $result['message'],
            'expires_at' => $result['expires_at'],
        ];

        if ($result['otp'] !== null) {
            $response['otp'] = $result['otp']; // only in debug mode
        }

        return response()->json($response);
    }

    /**
     * POST /api/user/verify-otp
     *
     * Verify a previously sent OTP.
     * When purpose=login and OTP is correct, a Bearer token is issued.
     *
     * Body:
     * { "email": "raj@example.com", "otp": "123456", "purpose": "registration|login|password_reset" }
     *
     * Success 200 (registration / password_reset):
     * { "success": true, "message": "OTP verified successfully." }
     *
     * Success 200 (login):
     * {
     *   "success":    true,
     *   "message":    "OTP verified successfully.",
     *   "token":      "abc123…",
     *   "token_type": "Bearer",
     *   "data":       { ... user fields ... }
     * }
     *
     * Failure codes: INVALID (422) | EXPIRED (403) | LOCKED (403) | NO_OTP (422)
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'   => ['required', 'email'],
            'otp'     => ['required', 'string', 'size:6'],
            'purpose' => ['required', 'in:registration,login,password_reset'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email  = strtolower(trim($request->input('email')));
        $result = $this->otpService->verify($email, $request->input('otp'), $request->input('purpose'));

        if (!$result['success']) {
            $status = in_array($result['code'], ['LOCKED', 'EXPIRED']) ? 403 : 422;

            $payload = [
                'success' => false,
                'code'    => $result['code'],
                'message' => $result['message'],
            ];

            if (isset($result['remaining'])) {
                $payload['remaining'] = $result['remaining'];
            }

            return response()->json($payload, $status);
        }

        $response = [
            'success' => true,
            'message' => $result['message'],
        ];

        if ($request->input('purpose') === 'login') {
            $user = UserLogin::where('email', $email)->where('is_active', true)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account not found or inactive.',
                ], 404);
            }

            $response['token']      = $this->authService->issueToken($user);
            $response['token_type'] = 'Bearer';
            $response['data']       = $this->userPayload($user);
        }

        return response()->json($response);
    }

    // ── private ───────────────────────────────────────────────────────────

    /** Safe user fields returned to the client (no password / token). */
    private function userPayload(\App\Models\UserLogin $user): array
    {
        return [
            'id'            => $user->id,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'full_name'     => $user->full_name,
            'email'         => $user->email,
            'mobile_no'     => $user->mobile_no,
            'gender'        => $user->gender,
            'date_of_birth' => $user->date_of_birth?->format('d-m-Y'),
            'is_active'     => $user->is_active,
            'created_at'    => $user->created_at?->toDateTimeString(),
        ];
    }
}
