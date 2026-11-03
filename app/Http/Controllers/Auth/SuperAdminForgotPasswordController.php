<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\PasswordResetCode;
use App\Models\SuperAdmin;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminForgotPasswordController extends Controller
{
    private const ACCOUNT_TYPE = 'superadmin';
    private const CODE_EXPIRATION_MINUTES = 10;
    private const RESET_AUTHORIZATION_MINUTES = 15;
    private const SESSION_EMAIL = 'superadmin_password_reset_email';
    private const SESSION_VERIFIED_AT = 'superadmin_password_reset_verified_at';

    public function showForgotPassword(Request $request): Response
    {
        $this->clearResetSession($request);

        return Inertia::render(
            'SuperAdmin/ForgotPasswordPages/ForgotPassword'
        );
    }

    public function showVerifyCode(Request $request): Response
    {
        $email = strtolower(trim((string) $request->query('email', '')));

        return Inertia::render(
            'SuperAdmin/ForgotPasswordPages/VerifyCode',
            [
                'email' => $email,
            ]
        );
    }

    public function showResetPassword(Request $request): Response
    {
        $email = strtolower(trim((string) $request->query('email', '')));

        return Inertia::render(
            'SuperAdmin/ForgotPasswordPages/ResetPassword',
            [
                'email' => $email,
            ]
        );
    }

    public function showSuccess(): Response
    {
        return Inertia::render(
            'SuperAdmin/ForgotPasswordPages/PasswordResetSuccess'
        );
    }

    public function sendCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        $superAdmin = SuperAdmin::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$superAdmin) {
            return response()->json([
                'message' => 'No Super Administrator account was found with this email address.',
            ], 404);
        }

        $code = (string) random_int(100000, 999999);

        PasswordResetCode::query()->updateOrCreate(
            [
                'email' => $email,
                'account_type' => self::ACCOUNT_TYPE,
            ],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(self::CODE_EXPIRATION_MINUTES),
            ]
        );

        $request->session()->put(self::SESSION_EMAIL, $email);
        $request->session()->forget(self::SESSION_VERIFIED_AT);

        Mail::to($superAdmin->email)
            ->send(new ForgotPasswordMail($code));

        return response()->json([
            'message' => 'Verification code sent successfully.',
        ]);
    }

    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim($validated['email']));

        $record = PasswordResetCode::query()
            ->where('email', $email)
            ->where('account_type', self::ACCOUNT_TYPE)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Verification code not found. Please request a new code.',
            ], 404);
        }

        if (Carbon::now()->greaterThan($record->expires_at)) {
            $record->delete();

            return response()->json([
                'message' => 'Verification code has expired. Please request a new code.',
            ], 422);
        }

        if (!hash_equals((string) $record->code, (string) $validated['code'])) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        $request->session()->put(self::SESSION_EMAIL, $email);
        $request->session()->put(self::SESSION_VERIFIED_AT, now()->timestamp);

        return response()->json([
            'message' => 'Verification successful.',
        ]);
    }

    public function resendCode(Request $request): JsonResponse
    {
        return $this->sendCode($request);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $email = strtolower(trim($validated['email']));
        $sessionEmail = strtolower(trim((string) $request->session()->get(self::SESSION_EMAIL, '')));
        $verifiedAt = (int) $request->session()->get(self::SESSION_VERIFIED_AT, 0);

        if (
            $sessionEmail === '' ||
            $sessionEmail !== $email ||
            $verifiedAt <= 0 ||
            now()->timestamp - $verifiedAt > (self::RESET_AUTHORIZATION_MINUTES * 60)
        ) {
            $this->clearResetSession($request);

            return response()->json([
                'message' => 'Your password reset verification has expired. Please request a new verification code.',
            ], 403);
        }

        $record = PasswordResetCode::query()
            ->where('email', $email)
            ->where('account_type', self::ACCOUNT_TYPE)
            ->first();

        if (!$record || Carbon::now()->greaterThan($record->expires_at)) {
            $this->clearResetSession($request);

            return response()->json([
                'message' => 'Your verification code has expired. Please request a new code.',
            ], 403);
        }

        $superAdmin = SuperAdmin::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$superAdmin) {
            return response()->json([
                'message' => 'Super Administrator account not found.',
            ], 404);
        }

        $superAdmin->password = Hash::make($validated['password']);
        $superAdmin->save();

        PasswordResetCode::query()
            ->where('email', $email)
            ->where('account_type', self::ACCOUNT_TYPE)
            ->delete();

        $this->clearResetSession($request);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    private function clearResetSession(Request $request): void
    {
        $request->session()->forget([
            self::SESSION_EMAIL,
            self::SESSION_VERIFIED_AT,
        ]);
    }
}
