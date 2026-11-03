<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\InstructorCoordinatorVerificationCodeMail;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\UniversityAdministrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class InstructorCoordinatorForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Verification Code Lifetime
    |--------------------------------------------------------------------------
    |
    | Verification code expires after 10 minutes.
    |
    */

    private const CODE_EXPIRATION_MINUTES = 10;


    /*
    |--------------------------------------------------------------------------
    | Reset Authorization Lifetime
    |--------------------------------------------------------------------------
    |
    | After successfully verifying the OTP, the user has 15 minutes
    | to create a new password.
    |
    */

    private const RESET_EXPIRATION_MINUTES = 15;


    /*
    |--------------------------------------------------------------------------
    | Maximum Verification Attempts
    |--------------------------------------------------------------------------
    */

    private const MAX_VERIFICATION_ATTEMPTS = 5;


    /*
    |--------------------------------------------------------------------------
    | Admin Mobile Password Recovery
    |--------------------------------------------------------------------------
    |
    | The web flow below continues to use Laravel sessions. These mobile
    | methods use short-lived cache tokens so React Native can use the same
    | controller without requiring a browser session cookie.
    |
    */

    public function mobileSendVerificationCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $universityId = $this->mobileUniversityId($request);

        if (!$universityId) {
            return response()->json([
                'success' => false,
                'message' => 'Your institutional authorization session has expired. Please verify the University Access Code again.',
            ], 403);
        }

        $email = strtolower(trim((string) $validated['email']));
        $matches = [];

        $administrator = UniversityAdministrator::query()
            ->with('university')
            ->where('university_id', $universityId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($administrator) {
            $matches[] = [$administrator, 'university_administrator'];
        }

        $instructor = Instructor::query()
            ->with('university')
            ->where('university_id', $universityId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($instructor && $instructor->isInstructor()) {
            $matches[] = [$instructor, 'instructor'];
        }

        $coordinator = Coordinator::query()
            ->with('university')
            ->where('university_id', $universityId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($coordinator && $coordinator->isAttendanceCoordinator()) {
            $matches[] = [$coordinator, 'coordinator'];
        }

        if (count($matches) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No supported NSTP HUB administrator, instructor, or attendance coordinator account was found with this email address.',
            ], 404);
        }

        if (count($matches) > 1) {
            return response()->json([
                'success' => false,
                'message' => 'This email address matches more than one NSTP HUB staff account. Please contact your University Administrator.',
            ], 409);
        }

        [$account, $accountType] = $matches[0];

        return $this->createMobileChallenge($account, $accountType);
    }

    public function mobileVerifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
            'challenge_token' => ['required', 'string', 'min:40', 'max:255'],
        ]);

        $challengeKey = $this->mobileChallengeKey($validated['challenge_token']);
        $challenge = Cache::store('file')->get($challengeKey);

        if (!$challenge) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code not found or expired. Please request a new code.',
            ], 404);
        }

        if (strtolower(trim($validated['email'])) !== ($challenge['email'] ?? '')) {
            return response()->json([
                'success' => false,
                'message' => 'The verification request does not match this email address.',
            ], 422);
        }

        $attempts = (int) ($challenge['attempts'] ?? 0);

        if (!Hash::check((string) $validated['code'], (string) ($challenge['code_hash'] ?? ''))) {
            $attempts++;
            $challenge['attempts'] = $attempts;

            Cache::store('file')->put(
                $challengeKey,
                $challenge,
                now()->addMinutes(self::CODE_EXPIRATION_MINUTES)
            );

            if ($attempts >= self::MAX_VERIFICATION_ATTEMPTS) {
                Cache::store('file')->forget($challengeKey);

                return response()->json([
                    'success' => false,
                    'message' => 'Too many incorrect verification attempts. Please request a new code.',
                ], 429);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
            ], 422);
        }

        $resetToken = Str::random(96);

        Cache::store('file')->put(
            $this->mobileResetKey($resetToken),
            [
                'email' => $challenge['email'],
                'account_type' => $challenge['account_type'],
                'account_id' => (int) $challenge['account_id'],
                'university_id' => (int) $challenge['university_id'],
            ],
            now()->addMinutes(self::RESET_EXPIRATION_MINUTES)
        );

        Cache::store('file')->forget($challengeKey);

        return response()->json([
            'success' => true,
            'message' => 'Verification successful.',
            'account_type' => $challenge['account_type'],
            'reset_token' => $resetToken,
        ]);
    }

    public function mobileResendCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'challenge_token' => ['required', 'string', 'min:40', 'max:255'],
        ]);

        $oldKey = $this->mobileChallengeKey($validated['challenge_token']);
        $challenge = Cache::store('file')->get($oldKey);

        if (!$challenge) {
            return response()->json([
                'success' => false,
                'message' => 'Your verification request has expired. Please start again.',
            ], 404);
        }

        if (strtolower(trim($validated['email'])) !== ($challenge['email'] ?? '')) {
            return response()->json([
                'success' => false,
                'message' => 'The email address does not match the current password reset request.',
            ], 422);
        }

        $account = $this->findMobileAccount(
            (string) $challenge['account_type'],
            (int) $challenge['account_id'],
            (string) $challenge['email'],
            (int) $challenge['university_id']
        );

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'The NSTP HUB staff account could not be found.',
            ], 404);
        }

        Cache::store('file')->forget($oldKey);

        return $this->createMobileChallenge($account, (string) $challenge['account_type']);
    }

    public function mobileResetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'reset_token' => ['required', 'string', 'min:40', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $resetKey = $this->mobileResetKey($validated['reset_token']);
        $context = Cache::store('file')->get($resetKey);

        if (!$context) {
            return response()->json([
                'success' => false,
                'message' => 'Your password reset authorization has expired. Please request a new verification code.',
            ], 403);
        }

        if (strtolower(trim($validated['email'])) !== ($context['email'] ?? '')) {
            return response()->json([
                'success' => false,
                'message' => 'The email address does not match the verified password reset request.',
            ], 422);
        }

        $account = $this->findMobileAccount(
            (string) $context['account_type'],
            (int) $context['account_id'],
            (string) $context['email'],
            (int) $context['university_id']
        );

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'The NSTP HUB staff account could not be found.',
            ], 404);
        }

        $account->password = $validated['password'];

        if ($account instanceof UniversityAdministrator) {
            $account->must_change_password = false;
            $account->temporary_password = null;
        }

        $account->save();
        Cache::store('file')->forget($resetKey);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
            'account_type' => $context['account_type'],
        ]);
    }

    private function createMobileChallenge(
        Instructor|Coordinator|UniversityAdministrator $account,
        string $accountType
    ): JsonResponse {
        $code = (string) random_int(100000, 999999);
        $account->loadMissing('university');

        $fullName = $account instanceof UniversityAdministrator
            ? trim(implode(' ', array_filter([
                $account->first_name,
                $account->middle_name,
                $account->last_name,
            ])))
            : $account->full_name;

        try {
            Mail::to($account->email)->send(
                new InstructorCoordinatorVerificationCodeMail(
                    code: $code,
                    accountType: str_replace('_', ' ', $accountType),
                    fullName: $fullName ?: 'NSTP HUB Staff',
                    component: $account->component ?? null,
                    universityName: $account->university?->name
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send the verification email. Please try again.',
            ], 500);
        }

        $challengeToken = Str::random(96);

        Cache::store('file')->put(
            $this->mobileChallengeKey($challengeToken),
            [
                'email' => strtolower(trim((string) $account->email)),
                'account_type' => $accountType,
                'account_id' => (int) $account->getKey(),
                'university_id' => (int) $account->university_id,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
            ],
            now()->addMinutes(self::CODE_EXPIRATION_MINUTES)
        );

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent successfully.',
            'account_type' => $accountType,
            'challenge_token' => $challengeToken,
            'expires_in_seconds' => self::CODE_EXPIRATION_MINUTES * 60,
        ]);
    }

    private function mobileUniversityId(Request $request): ?int
    {
        $token = trim((string) $request->header('X-University-Access-Token', ''));

        if ($token === '') {
            return null;
        }

        $context = Cache::store('file')->get(
            'admin_mobile_university_access:' . hash('sha256', $token)
        );

        $id = (int) ($context['university_id'] ?? 0);

        return $id > 0 ? $id : null;
    }

    private function findMobileAccount(
        string $accountType,
        int $accountId,
        string $email,
        int $universityId
    ): Instructor|Coordinator|UniversityAdministrator|null {
        $model = match ($accountType) {
            'instructor' => Instructor::class,
            'coordinator' => Coordinator::class,
            'university_administrator' => UniversityAdministrator::class,
            default => null,
        };

        if (!$model) {
            return null;
        }

        return $model::query()
            ->with('university')
            ->whereKey($accountId)
            ->where('university_id', $universityId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();
    }

    private function mobileChallengeKey(string $token): string
    {
        return 'admin_mobile_password_reset_challenge:' . hash('sha256', $token);
    }

    private function mobileResetKey(string $token): string
    {
        return 'admin_mobile_password_reset_authorization:' . hash('sha256', $token);
    }


    /*
    |--------------------------------------------------------------------------
    | Show Forgot Password Page
    |--------------------------------------------------------------------------
    |
    | GET:
    |
    | /instructor-coordinator/forgot-password
    |
    */

    public function showForgotPassword(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Already Logged In
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'instructor'
            )->check() ||
            Auth::guard(
                'coordinator'
            )->check()
        ) {

            return redirect()->route(
                'instructor-coordinator.dashboard'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | University Access Must Already Be Verified
        |--------------------------------------------------------------------------
        |
        | Instructor / Coordinator login is scoped to a university.
        |
        */

        if (
            !$this->hasUniversityContext(
                $request
            )
        ) {

            return redirect()->route(
                'instructor-coordinator.access-code'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Clear Previous Password Reset Process
        |--------------------------------------------------------------------------
        */

        $this->clearPasswordResetSession(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Render Forgot Password
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/ForgotPasswordPages/ForgotPassword'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Send Verification Code
    |--------------------------------------------------------------------------
    |
    | POST:
    |
    | /instructor-coordinator/forgot-password
    |
    */

    public function sendVerificationCode(
        Request $request
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | University Context Required
        |--------------------------------------------------------------------------
        */

        if (
            !$this->hasUniversityContext(
                $request
            )
        ) {

            return response()->json(
                [
                    'message' =>
                        'Your university session has expired. Please return to the login page and verify your University Access Code again.',
                ],
                403
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Email
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email =
            strtolower(
                trim(
                    $validated['email']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | University ID
        |--------------------------------------------------------------------------
        */

        $universityId =
            (int) $request
                ->session()
                ->get(
                    'staff_university_id'
                );


        /*
        |--------------------------------------------------------------------------
        | Search Instructor
        |--------------------------------------------------------------------------
        */

        $instructor =
            Instructor::query()
                ->with(
                    'university'
                )
                ->where(
                    'university_id',
                    $universityId
                )
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Search Coordinator
        |--------------------------------------------------------------------------
        */

        $coordinator =
            Coordinator::query()
                ->with(
                    'university'
                )
                ->where(
                    'university_id',
                    $universityId
                )
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Account Not Found
        |--------------------------------------------------------------------------
        */

        if (
            !$instructor &&
            !$coordinator
        ) {

            return response()->json(
                [
                    'message' =>
                        'No Instructor or Coordinator account was found with this email address.',
                ],
                404
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Ambiguous Account
        |--------------------------------------------------------------------------
        |
        | This protects against the unusual situation where exactly the
        | same email exists in both tables for the same university.
        |
        */

        if (
            $instructor &&
            $coordinator
        ) {

            return response()->json(
                [
                    'message' =>
                        'This email address is connected to more than one NSTP staff account. Please contact your University Administrator.',
                ],
                422
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        */

        if (
            $instructor
        ) {

            return $this->createAndSendCode(
                request:
                    $request,

                account:
                    $instructor,

                accountType:
                    'instructor'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator
        |--------------------------------------------------------------------------
        */

        return $this->createAndSendCode(
            request:
                $request,

            account:
                $coordinator,

            accountType:
                'coordinator'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Show Verify Code Page
    |--------------------------------------------------------------------------
    |
    | GET:
    |
    | /instructor-coordinator/forgot-password/verify-code
    |
    */

    public function showVerifyCode(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Reset Email
        |--------------------------------------------------------------------------
        */

        $email =
            $request
                ->session()
                ->get(
                    'staff_password_reset_email'
                );


        /*
        |--------------------------------------------------------------------------
        | Account Type
        |--------------------------------------------------------------------------
        */

        $accountType =
            $request
                ->session()
                ->get(
                    'staff_password_reset_account_type'
                );


        /*
        |--------------------------------------------------------------------------
        | Missing Password Reset Session
        |--------------------------------------------------------------------------
        */

        if (
            !$email ||
            !$this->validAccountType(
                $accountType
            )
        ) {

            return redirect()->route(
                'instructor-coordinator.forgot-password'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Render Verify Code
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/ForgotPasswordPages/VerifyCode',
            [
                'email' =>
                    $email,

                'accountType' =>
                    $accountType,
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Verify Code
    |--------------------------------------------------------------------------
    |
    | POST:
    |
    | /instructor-coordinator/verify-code
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
            $request->validate([
                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],

                'code' => [
                    'required',
                    'digits:6',
                ],

                'account_type' => [
                    'nullable',
                    'string',
                    'in:instructor,coordinator',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email =
            strtolower(
                trim(
                    $validated['email']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Session Values
        |--------------------------------------------------------------------------
        */

        $sessionEmail =
            strtolower(
                trim(
                    (string) $request
                        ->session()
                        ->get(
                            'staff_password_reset_email',
                            ''
                        )
                )
            );


        $accountType =
            $request
                ->session()
                ->get(
                    'staff_password_reset_account_type'
                );


        $codeHash =
            $request
                ->session()
                ->get(
                    'staff_password_reset_code_hash'
                );


        $expiresAt =
            (int) $request
                ->session()
                ->get(
                    'staff_password_reset_code_expires_at',
                    0
                );


        /*
        |--------------------------------------------------------------------------
        | Missing Challenge
        |--------------------------------------------------------------------------
        */

        if (
            !$sessionEmail ||
            !$accountType ||
            !$codeHash ||
            !$expiresAt
        ) {

            return response()->json(
                [
                    'message' =>
                        'Verification code not found. Please request a new code.',
                ],
                404
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Email Must Match
        |--------------------------------------------------------------------------
        */

        if (
            $email !==
            $sessionEmail
        ) {

            return response()->json(
                [
                    'message' =>
                        'The verification request does not match this email address.',
                ],
                422
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Expiration
        |--------------------------------------------------------------------------
        */

        if (
            now()->timestamp >
            $expiresAt
        ) {

            $request
                ->session()
                ->forget([
                    'staff_password_reset_code_hash',
                    'staff_password_reset_code_expires_at',
                    'staff_password_reset_attempts',
                ]);


            return response()->json(
                [
                    'message' =>
                        'Your verification code has expired. Please request a new code.',
                ],
                422
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Attempts
        |--------------------------------------------------------------------------
        */

        $attempts =
            (int) $request
                ->session()
                ->get(
                    'staff_password_reset_attempts',
                    0
                );


        /*
        |--------------------------------------------------------------------------
        | Too Many Attempts
        |--------------------------------------------------------------------------
        */

        if (
            $attempts >=
            self::MAX_VERIFICATION_ATTEMPTS
        ) {

            return response()->json(
                [
                    'message' =>
                        'Too many verification attempts. Please request a new verification code.',
                ],
                429
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Verify Code
        |--------------------------------------------------------------------------
        */

        if (
            !Hash::check(
                (string) $validated['code'],
                $codeHash
            )
        ) {

            $attempts++;


            $request
                ->session()
                ->put(
                    'staff_password_reset_attempts',
                    $attempts
                );


            /*
            |--------------------------------------------------------------------------
            | Attempt Limit Reached
            |--------------------------------------------------------------------------
            */

            if (
                $attempts >=
                self::MAX_VERIFICATION_ATTEMPTS
            ) {

                return response()->json(
                    [
                        'message' =>
                            'Too many incorrect verification attempts. Please request a new code.',
                    ],
                    429
                );

            }


            return response()->json(
                [
                    'message' =>
                        'Invalid verification code.',
                ],
                422
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Mark Reset Request As Verified
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put([
                'staff_password_reset_verified' =>
                    true,

                'staff_password_reset_verified_at' =>
                    now()->timestamp,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Verification Code Can No Longer Be Reused
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget([
                'staff_password_reset_code_hash',
                'staff_password_reset_code_expires_at',
                'staff_password_reset_attempts',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'Verification successful.',

            'account_type' =>
                $accountType,
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Resend Verification Code
    |--------------------------------------------------------------------------
    |
    | POST:
    |
    | /instructor-coordinator/resend-code
    |
    */

    public function resendCode(
        Request $request
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],

                'account_type' => [
                    'nullable',
                    'string',
                    'in:instructor,coordinator',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email =
            strtolower(
                trim(
                    $validated['email']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Session Account Information
        |--------------------------------------------------------------------------
        */

        $sessionEmail =
            strtolower(
                trim(
                    (string) $request
                        ->session()
                        ->get(
                            'staff_password_reset_email',
                            ''
                        )
                )
            );


        $accountType =
            $request
                ->session()
                ->get(
                    'staff_password_reset_account_type'
                );


        $accountId =
            (int) $request
                ->session()
                ->get(
                    'staff_password_reset_account_id',
                    0
                );


        $universityId =
            (int) $request
                ->session()
                ->get(
                    'staff_password_reset_university_id',
                    0
                );


        /*
        |--------------------------------------------------------------------------
        | Missing Reset Context
        |--------------------------------------------------------------------------
        */

        if (
            !$sessionEmail ||
            !$accountId ||
            !$universityId ||
            !$this->validAccountType(
                $accountType
            )
        ) {

            return response()->json(
                [
                    'message' =>
                        'Your password reset session has expired. Please request a new verification code.',
                ],
                422
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Email Must Match
        |--------------------------------------------------------------------------
        */

        if (
            $email !==
            $sessionEmail
        ) {

            return response()->json(
                [
                    'message' =>
                        'The email address does not match the current password reset request.',
                ],
                422
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Find Account
        |--------------------------------------------------------------------------
        */

        $account =
            $this->findAccount(
                accountType:
                    $accountType,

                accountId:
                    $accountId,

                email:
                    $sessionEmail,

                universityId:
                    $universityId
            );


        /*
        |--------------------------------------------------------------------------
        | Account Missing
        |--------------------------------------------------------------------------
        */

        if (
            !$account
        ) {

            return response()->json(
                [
                    'message' =>
                        'The Instructor or Coordinator account could not be found.',
                ],
                404
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Send New Code
        |--------------------------------------------------------------------------
        */

        return $this->createAndSendCode(
            request:
                $request,

            account:
                $account,

            accountType:
                $accountType
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Show Reset Password Page
    |--------------------------------------------------------------------------
    |
    | GET:
    |
    | /instructor-coordinator/reset-password
    |
    */

    public function showResetPassword(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Must Be Verified
        |--------------------------------------------------------------------------
        */

        if (
            !$this->hasValidResetAuthorization(
                $request
            )
        ) {

            $this->clearPasswordResetSession(
                $request
            );


            return redirect()->route(
                'instructor-coordinator.forgot-password'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Session Values
        |--------------------------------------------------------------------------
        */

        $email =
            $request
                ->session()
                ->get(
                    'staff_password_reset_email'
                );


        $accountType =
            $request
                ->session()
                ->get(
                    'staff_password_reset_account_type'
                );


        /*
        |--------------------------------------------------------------------------
        | Render Reset Password
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/ForgotPasswordPages/ResetPassword',
            [
                'email' =>
                    $email,

                'accountType' =>
                    $accountType,
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    |
    | POST:
    |
    | /instructor-coordinator/reset-password
    |
    */

    public function resetPassword(
        Request $request
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Reset Authorization Required
        |--------------------------------------------------------------------------
        */

        if (
            !$this->hasValidResetAuthorization(
                $request
            )
        ) {

            $this->clearPasswordResetSession(
                $request
            );


            return response()->json(
                [
                    'message' =>
                        'Your password reset session has expired. Please request a new verification code.',
                ],
                403
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],

                'account_type' => [
                    'nullable',
                    'string',
                    'in:instructor,coordinator',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Session Source Of Truth
        |--------------------------------------------------------------------------
        |
        | Do not trust account_type from the browser.
        |
        */

        $sessionEmail =
            strtolower(
                trim(
                    (string) $request
                        ->session()
                        ->get(
                            'staff_password_reset_email'
                        )
                )
            );


        $accountType =
            $request
                ->session()
                ->get(
                    'staff_password_reset_account_type'
                );


        $accountId =
            (int) $request
                ->session()
                ->get(
                    'staff_password_reset_account_id',
                    0
                );


        $universityId =
            (int) $request
                ->session()
                ->get(
                    'staff_password_reset_university_id',
                    0
                );


        /*
        |--------------------------------------------------------------------------
        | Submitted Email
        |--------------------------------------------------------------------------
        */

        $submittedEmail =
            strtolower(
                trim(
                    $validated['email']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Email Must Match Verified Email
        |--------------------------------------------------------------------------
        */

        if (
            $submittedEmail !==
            $sessionEmail
        ) {

            return response()->json(
                [
                    'message' =>
                        'The email address does not match the verified password reset request.',
                ],
                422
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Find Verified Account
        |--------------------------------------------------------------------------
        */

        $account =
            $this->findAccount(
                accountType:
                    $accountType,

                accountId:
                    $accountId,

                email:
                    $sessionEmail,

                universityId:
                    $universityId
            );


        /*
        |--------------------------------------------------------------------------
        | Account Not Found
        |--------------------------------------------------------------------------
        */

        if (
            !$account
        ) {

            return response()->json(
                [
                    'message' =>
                        'Instructor or Coordinator account not found.',
                ],
                404
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Set New Password
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Instructor and Coordinator models both use:
        |
        | 'password' => 'hashed'
        |
        | Therefore we assign the plain password and Laravel hashes it.
        |
        */

        $account->password =
            $validated['password'];


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $account->save();


        /*
        |--------------------------------------------------------------------------
        | Store Success Page Account Type
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put(
                'staff_password_reset_success_account_type',
                $accountType
            );


        /*
        |--------------------------------------------------------------------------
        | Clear Sensitive Password Reset Information
        |--------------------------------------------------------------------------
        */

        $this->clearPasswordResetSession(
            $request,
            preserveSuccess:
                true
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'Password reset successfully.',

            'account_type' =>
                $accountType,
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Show Password Reset Success
    |--------------------------------------------------------------------------
    |
    | GET:
    |
    | /instructor-coordinator/password-reset-success
    |
    */

    public function showPasswordResetSuccess(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Account Type
        |--------------------------------------------------------------------------
        */

        $accountType =
            $request
                ->session()
                ->get(
                    'staff_password_reset_success_account_type'
                );


        /*
        |--------------------------------------------------------------------------
        | Invalid Success Session
        |--------------------------------------------------------------------------
        */

        if (
            !$this->validAccountType(
                $accountType
            )
        ) {

            return redirect()->route(
                'instructor-coordinator.login'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Render Success Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/ForgotPasswordPages/PasswordResetSuccess',
            [
                'accountType' =>
                    $accountType,
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Create And Send Verification Code
    |--------------------------------------------------------------------------
    */

    private function createAndSendCode(
        Request $request,
        Instructor|Coordinator $account,
        string $accountType
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Generate 6-Digit Code
        |--------------------------------------------------------------------------
        */

        $code =
            (string) random_int(
                100000,
                999999
            );


        /*
        |--------------------------------------------------------------------------
        | Load University
        |--------------------------------------------------------------------------
        */

        $account->loadMissing(
            'university'
        );


        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */

        try {

            Mail::to(
                $account->email
            )->send(
                new InstructorCoordinatorVerificationCodeMail(
                    code:
                        $code,

                    accountType:
                        $accountType,

                    fullName:
                        $account->full_name,

                    component:
                        $account->component,

                    universityName:
                        $account
                            ->university
                            ?->name
                )
            );

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Log Exception
            |--------------------------------------------------------------------------
            */

            report(
                $exception
            );


            /*
            |--------------------------------------------------------------------------
            | Mail Error
            |--------------------------------------------------------------------------
            */

            return response()->json(
                [
                    'message' =>
                        'Unable to send the verification email. Please try again.',
                ],
                500
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Store Verification Challenge
        |--------------------------------------------------------------------------
        |
        | Store only a HASH of the verification code.
        |
        */

        $request
            ->session()
            ->put([
                'staff_password_reset_email' =>
                    strtolower(
                        trim(
                            $account->email
                        )
                    ),

                'staff_password_reset_account_type' =>
                    $accountType,

                'staff_password_reset_account_id' =>
                    $account->id,

                'staff_password_reset_university_id' =>
                    $account->university_id,

                'staff_password_reset_code_hash' =>
                    Hash::make(
                        $code
                    ),

                'staff_password_reset_code_expires_at' =>
                    now()
                        ->addMinutes(
                            self::CODE_EXPIRATION_MINUTES
                        )
                        ->timestamp,

                'staff_password_reset_attempts' =>
                    0,

                'staff_password_reset_verified' =>
                    false,

                'staff_password_reset_verified_at' =>
                    null,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Remove Old Success Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget(
                'staff_password_reset_success_account_type'
            );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'Verification code sent successfully.',

            'account_type' =>
                $accountType,
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Find Account
    |--------------------------------------------------------------------------
    */

    private function findAccount(
        string $accountType,
        int $accountId,
        string $email,
        int $universityId
    ): Instructor|Coordinator|null {

        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        */

        if (
            $accountType ===
            'instructor'
        ) {

            return Instructor::query()
                ->with(
                    'university'
                )
                ->where(
                    'id',
                    $accountId
                )
                ->where(
                    'university_id',
                    $universityId
                )
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->first();

        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            $accountType ===
            'coordinator'
        ) {

            return Coordinator::query()
                ->with(
                    'university'
                )
                ->where(
                    'id',
                    $accountId
                )
                ->where(
                    'university_id',
                    $universityId
                )
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->first();

        }


        return null;

    }


    /*
    |--------------------------------------------------------------------------
    | University Context
    |--------------------------------------------------------------------------
    */

    private function hasUniversityContext(
        Request $request
    ): bool {

        return (
            $request
                ->session()
                ->get(
                    'staff_access_verified',
                    false
                ) ===
                true &&
            !empty(
                $request
                    ->session()
                    ->get(
                        'staff_university_id'
                    )
            )
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Valid Account Type
    |--------------------------------------------------------------------------
    */

    private function validAccountType(
        mixed $accountType
    ): bool {

        return in_array(
            $accountType,
            [
                'instructor',
                'coordinator',
            ],
            true
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Valid Reset Authorization
    |--------------------------------------------------------------------------
    */

    private function hasValidResetAuthorization(
        Request $request
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | Verified
        |--------------------------------------------------------------------------
        */

        if (
            $request
                ->session()
                ->get(
                    'staff_password_reset_verified',
                    false
                ) !==
                true
        ) {

            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | Verified Time
        |--------------------------------------------------------------------------
        */

        $verifiedAt =
            (int) $request
                ->session()
                ->get(
                    'staff_password_reset_verified_at',
                    0
                );


        if (
            !$verifiedAt
        ) {

            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | Authorization Expired
        |--------------------------------------------------------------------------
        */

        if (
            now()->timestamp >
            (
                $verifiedAt +
                (
                    self::RESET_EXPIRATION_MINUTES *
                    60
                )
            )
        ) {

            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | Required Account Information
        |--------------------------------------------------------------------------
        */

        if (
            !$request
                ->session()
                ->get(
                    'staff_password_reset_email'
                ) ||
            !$request
                ->session()
                ->get(
                    'staff_password_reset_account_id'
                ) ||
            !$request
                ->session()
                ->get(
                    'staff_password_reset_university_id'
                ) ||
            !$this->validAccountType(
                $request
                    ->session()
                    ->get(
                        'staff_password_reset_account_type'
                    )
            )
        ) {

            return false;

        }


        return true;

    }


    /*
    |--------------------------------------------------------------------------
    | Clear Password Reset Session
    |--------------------------------------------------------------------------
    */

    private function clearPasswordResetSession(
        Request $request,
        bool $preserveSuccess = false
    ): void {

        $keys = [
            'staff_password_reset_email',
            'staff_password_reset_account_type',
            'staff_password_reset_account_id',
            'staff_password_reset_university_id',
            'staff_password_reset_code_hash',
            'staff_password_reset_code_expires_at',
            'staff_password_reset_attempts',
            'staff_password_reset_verified',
            'staff_password_reset_verified_at',
        ];


        /*
        |--------------------------------------------------------------------------
        | Also Remove Success State
        |--------------------------------------------------------------------------
        */

        if (
            !$preserveSuccess
        ) {

            $keys[] =
                'staff_password_reset_success_account_type';

        }


        /*
        |--------------------------------------------------------------------------
        | Forget
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget(
                $keys
            );

    }
}