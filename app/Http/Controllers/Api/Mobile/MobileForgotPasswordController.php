<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class MobileForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    private const OTP_EXPIRATION_MINUTES = 10;

    private const RESET_TOKEN_EXPIRATION_MINUTES = 10;


    /*
    |--------------------------------------------------------------------------
    | Send Verification Code
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /api/mobile/forgot-password/send-code
    |
    */

    public function sendCode(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'email' => [
                        'required',
                        'string',
                        'email',
                        'max:255',
                    ],
                ],
                [
                    'email.required' =>
                        'Please enter your email address.',

                    'email.email' =>
                        'Please enter a valid email address.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email =
            strtolower(
                trim(
                    $validated[
                        'email'
                    ]
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Find Student Account
        |--------------------------------------------------------------------------
        |
        | NSTP HUB mobile students are stored in the users table.
        |
        */

        $student =
            User::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->first();


        if (
            !$student
        ) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'No NSTP student account was found with this email address.',
                ],
                404
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Generate Six-Digit OTP
        |--------------------------------------------------------------------------
        */

        $code =
            (string)
            random_int(
                100000,
                999999
            );


        /*
        |--------------------------------------------------------------------------
        | Save / Replace OTP
        |--------------------------------------------------------------------------
        */

        PasswordResetCode::updateOrCreate(
            [
                'email' =>
                    $email,
            ],
            [
                'code' =>
                    $code,

                'expires_at' =>
                    Carbon::now()
                        ->addMinutes(
                            self::OTP_EXPIRATION_MINUTES
                        ),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */

        try {
            Mail::to(
                $email
            )
                ->send(
                    new ForgotPasswordMail(
                        $code
                    )
                );

        } catch (
            Throwable $exception
        ) {
            /*
            |--------------------------------------------------------------------------
            | Remove Unsent OTP
            |--------------------------------------------------------------------------
            */

            PasswordResetCode::query()
                ->where(
                    'email',
                    $email
                )
                ->delete();


            report(
                $exception
            );


            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Unable to send the verification code right now. Please try again.',
                ],
                500
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Verification code sent successfully.',

            'email' =>
                $email,

            'expires_in_minutes' =>
                self::OTP_EXPIRATION_MINUTES,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Verification Code
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /api/mobile/forgot-password/verify-code
    |
    */

    public function verifyCode(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'email' => [
                        'required',
                        'string',
                        'email',
                        'max:255',
                    ],

                    'code' => [
                        'required',
                        'string',
                        'digits:6',
                    ],
                ],
                [
                    'email.required' =>
                        'Email address is required.',

                    'email.email' =>
                        'Please enter a valid email address.',

                    'code.required' =>
                        'Please enter the verification code.',

                    'code.digits' =>
                        'The verification code must contain exactly 6 digits.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $email =
            strtolower(
                trim(
                    $validated[
                        'email'
                    ]
                )
            );


        $code =
            trim(
                $validated[
                    'code'
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Find OTP
        |--------------------------------------------------------------------------
        */

        $record =
            PasswordResetCode::query()
                ->where(
                    'email',
                    $email
                )
                ->first();


        if (
            !$record
        ) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Verification code not found. Please request a new code.',
                ],
                404
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Expired
        |--------------------------------------------------------------------------
        */

        if (
            Carbon::now()
                ->greaterThan(
                    Carbon::parse(
                        $record->expires_at
                    )
                )
        ) {
            $record->delete();


            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'The verification code has expired. Please request a new code.',
                ],
                422
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Invalid OTP
        |--------------------------------------------------------------------------
        */

        if (
            !hash_equals(
                (string)
                $record->code,

                $code
            )
        ) {
            throw ValidationException::withMessages([
                'code' => [
                    'Invalid verification code.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Confirm Student Still Exists
        |--------------------------------------------------------------------------
        */

        $studentExists =
            User::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->exists();


        if (
            !$studentExists
        ) {
            $record->delete();


            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'The NSTP student account could not be found.',
                ],
                404
            );
        }


        /*
        |--------------------------------------------------------------------------
        | OTP Can No Longer Be Reused
        |--------------------------------------------------------------------------
        */

        $record->delete();


        /*
        |--------------------------------------------------------------------------
        | Create Temporary Reset Token
        |--------------------------------------------------------------------------
        |
        | The password-reset endpoint requires this token.
        |
        | This prevents someone from skipping OTP verification and calling
        | reset-password directly.
        |
        */

        $resetToken =
            Str::random(
                80
            );


        Cache::store(
            'file'
        )
            ->put(
                $this->resetTokenCacheKey(
                    $resetToken
                ),
                [
                    'email' =>
                        $email,

                    'verified_at' =>
                        now()
                            ->toIso8601String(),
                ],
                now()
                    ->addMinutes(
                        self::RESET_TOKEN_EXPIRATION_MINUTES
                    )
            );


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Verification successful.',

            'reset_token' =>
                $resetToken,

            'reset_token_expires_in_minutes' =>
                self::RESET_TOKEN_EXPIRATION_MINUTES,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /api/mobile/forgot-password/reset-password
    |
    */

    public function resetPassword(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'email' => [
                        'required',
                        'string',
                        'email',
                        'max:255',
                    ],

                    'reset_token' => [
                        'required',
                        'string',
                        'min:40',
                        'max:255',
                    ],

                    'password' => [
                        'required',
                        'string',
                        'min:8',
                        'max:255',
                        'confirmed',
                    ],
                ],
                [
                    'email.required' =>
                        'Email address is required.',

                    'email.email' =>
                        'Please enter a valid email address.',

                    'reset_token.required' =>
                        'Your password reset session has expired. Please request another verification code.',

                    'password.required' =>
                        'Please enter your new password.',

                    'password.min' =>
                        'Password must contain at least 8 characters.',

                    'password.confirmed' =>
                        'The passwords do not match.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email =
            strtolower(
                trim(
                    $validated[
                        'email'
                    ]
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Get Verified Reset Session
        |--------------------------------------------------------------------------
        */

        $cacheKey =
            $this->resetTokenCacheKey(
                $validated[
                    'reset_token'
                ]
            );


        $resetSession =
            Cache::store(
                'file'
            )
                ->get(
                    $cacheKey
                );


        if (
            !is_array(
                $resetSession
            )
            ||
            empty(
                $resetSession[
                    'email'
                ]
            )
        ) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Your password reset session has expired. Please request a new verification code.',
                ],
                403
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Email Must Match Verified Session
        |--------------------------------------------------------------------------
        */

        if (
            !hash_equals(
                strtolower(
                    trim(
                        (string)
                        $resetSession[
                            'email'
                        ]
                    )
                ),
                $email
            )
        ) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'The password reset session does not belong to this account.',
                ],
                403
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Find Student
        |--------------------------------------------------------------------------
        */

        $student =
            User::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->first();


        if (
            !$student
        ) {
            Cache::store(
                'file'
            )
                ->forget(
                    $cacheKey
                );


            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'NSTP student account not found.',
                ],
                404
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Same Password
        |--------------------------------------------------------------------------
        */

        if (
            Hash::check(
                $validated[
                    'password'
                ],
                $student->password
            )
        ) {
            throw ValidationException::withMessages([
                'password' => [
                    'Your new password must be different from your current password.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        */

        $student
            ->forceFill([
                'password' =>
                    Hash::make(
                        $validated[
                            'password'
                        ]
                    ),
            ])
            ->save();


        /*
        |--------------------------------------------------------------------------
        | Revoke Existing Mobile Sessions
        |--------------------------------------------------------------------------
        |
        | Because this is a forgot-password flow, every existing Sanctum login
        | is revoked. The student must sign in again using the new password.
        |
        */

        $student
            ->tokens()
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | Delete Reset Data
        |--------------------------------------------------------------------------
        */

        PasswordResetCode::query()
            ->where(
                'email',
                $email
            )
            ->delete();


        Cache::store(
            'file'
        )
            ->forget(
                $cacheKey
            );


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Password updated successfully.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Resend Verification Code
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /api/mobile/forgot-password/resend-code
    |
    */

    public function resendCode(
        Request $request
    ): JsonResponse {
        return $this->sendCode(
            $request
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Token Cache Key
    |--------------------------------------------------------------------------
    */

    private function resetTokenCacheKey(
        string $token
    ): string {
        return (
            'nstphub:mobile:password-reset:'
            .
            hash(
                'sha256',
                $token
            )
        );
    }
}