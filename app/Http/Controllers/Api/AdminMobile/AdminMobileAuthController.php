<?php

namespace App\Http\Controllers\Api\AdminMobile;

use App\Http\Controllers\Controller;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\UniversityAdministrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AdminMobileAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Login
    |--------------------------------------------------------------------------
    |
    | Allowed mobile roles:
    | - University Administrator
    | - Instructor
    | - Attendance Coordinator
    |
    */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $access = $this->resolveUniversityAccess($request);

        if ($access instanceof JsonResponse) {
            return $access;
        }

        [$accessToken, $universityId] = $access;

        $email = strtolower(trim((string) $validated['email']));
        $password = (string) $validated['password'];

        $matches = [];

        $administrator = UniversityAdministrator::query()
            ->with('university')
            ->where('university_id', $universityId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (
            $administrator &&
            $this->administratorPasswordMatches($administrator, $password) &&
            $this->universityIsActive($administrator)
        ) {
            $matches[] = [
                'account' => $administrator,
                'role' => 'university-administrator',
                'account_type' => 'university_administrator',
            ];
        }

        $instructor = Instructor::query()
            ->with('university')
            ->where('university_id', $universityId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (
            $instructor &&
            Hash::check($password, $instructor->getAuthPassword()) &&
            $this->staffAccountIsActive($instructor->status) &&
            $instructor->isInstructor() &&
            $this->universityIsActive($instructor)
        ) {
            $matches[] = [
                'account' => $instructor,
                'role' => 'instructor',
                'account_type' => 'instructor',
            ];
        }

        $coordinator = Coordinator::query()
            ->with('university')
            ->where('university_id', $universityId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (
            $coordinator &&
            Hash::check($password, $coordinator->getAuthPassword()) &&
            $this->staffAccountIsActive($coordinator->status) &&
            $coordinator->isAttendanceCoordinator() &&
            $this->universityIsActive($coordinator)
        ) {
            $matches[] = [
                'account' => $coordinator,
                'role' => 'coordinator-attendance',
                'account_type' => 'coordinator',
            ];
        }

        if (count($matches) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password, or this account is not allowed to use the admin mobile app.',
            ], 422);
        }

        if (count($matches) > 1) {
            return response()->json([
                'success' => false,
                'message' => 'This email matches more than one NSTP HUB staff account. Please contact your University Administrator.',
            ], 409);
        }

        $match = $matches[0];

        return $this->createLoginResponse(
            account: $match['account'],
            role: $match['role'],
            accountType: $match['account_type'],
            universityAccessToken: $accessToken,
            loginMethod: 'password'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Google Login
    |--------------------------------------------------------------------------
    |
    | The Android app signs in with Google and sends the Google access token.
    | Laravel Socialite verifies that token directly with Google.
    | No account is auto-created: the Google email must already belong to one
    | of the three allowed roles in the selected university.
    |
    */
    public function googleLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string', 'min:20', 'max:4096'],
        ]);

        $access = $this->resolveUniversityAccess($request);

        if ($access instanceof JsonResponse) {
            return $access;
        }

        [$accessToken, $universityId] = $access;

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($validated['access_token']);

            $email = strtolower(trim((string) $googleUser->getEmail()));

            if ($email === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Google did not provide an email address.',
                ], 422);
            }

            $rawGoogleUser = is_array($googleUser->user)
                ? $googleUser->user
                : [];

            $verifiedEmail = $rawGoogleUser['verified_email']
                ?? $rawGoogleUser['email_verified']
                ?? null;

            if ($verifiedEmail === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your Google email address is not verified.',
                ], 403);
            }

            $matches = [];

            $administrator = UniversityAdministrator::query()
                ->with('university')
                ->where('university_id', $universityId)
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            if ($administrator && $this->universityIsActive($administrator)) {
                $matches[] = [
                    'account' => $administrator,
                    'role' => 'university-administrator',
                    'account_type' => 'university_administrator',
                ];
            }

            $instructor = Instructor::query()
                ->with('university')
                ->where('university_id', $universityId)
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            if (
                $instructor &&
                $this->staffAccountIsActive($instructor->status) &&
                $instructor->isInstructor() &&
                $this->universityIsActive($instructor)
            ) {
                $matches[] = [
                    'account' => $instructor,
                    'role' => 'instructor',
                    'account_type' => 'instructor',
                ];
            }

            $coordinator = Coordinator::query()
                ->with('university')
                ->where('university_id', $universityId)
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            if (
                $coordinator &&
                $this->staffAccountIsActive($coordinator->status) &&
                $coordinator->isAttendanceCoordinator() &&
                $this->universityIsActive($coordinator)
            ) {
                $matches[] = [
                    'account' => $coordinator,
                    'role' => 'coordinator-attendance',
                    'account_type' => 'coordinator',
                ];
            }

            if (count($matches) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No eligible University Administrator, Attendance Coordinator, or Instructor account is connected to this Google email for the selected university.',
                ], 403);
            }

            if (count($matches) > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Google email is connected to more than one eligible NSTP HUB staff account. Please contact your University Administrator.',
                ], 409);
            }

            $match = $matches[0];

            return $this->createLoginResponse(
                account: $match['account'],
                role: $match['role'],
                accountType: $match['account_type'],
                universityAccessToken: $accessToken,
                loginMethod: 'google'
            );
        } catch (Throwable $exception) {
            Log::warning('Admin mobile Google authentication failed.', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed. Please sign in with Google again.',
            ], 401);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $account = $request->user();

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'You are not logged in.',
            ], 401);
        }

        $account->loadMissing('university');

        [$role, $accountType] = $this->roleAndType($account);

        if (!$role || !$accountType) {
            return response()->json([
                'success' => false,
                'message' => 'This account is not allowed to use the admin mobile app.',
            ], 403);
        }

        $payload = $this->accountPayload($account, $role, $accountType);

        return response()->json([
            'success' => true,
            'role' => $role,
            'account_type' => $accountType,
            'account' => $payload,
            // Backward-compatible key used by the existing mobile UI.
            'administrator' => $payload,
            'university' => $this->universityPayload($account),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Signed out successfully.',
        ]);
    }

    private function resolveUniversityAccess(Request $request): array|JsonResponse
    {
        $accessToken = trim(
            (string) $request->header('X-University-Access-Token', '')
        );

        if ($accessToken === '') {
            return response()->json([
                'success' => false,
                'message' => 'Institutional authorization is required before login.',
            ], 403);
        }

        $accessSession = Cache::store('file')->get(
            $this->universityAccessCacheKey($accessToken)
        );

        $universityId = (int) ($accessSession['university_id'] ?? 0);

        if ($universityId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Your institutional authorization session has expired. Please enter the authorization code again.',
            ], 403);
        }

        return [$accessToken, $universityId];
    }

    private function administratorPasswordMatches(
        UniversityAdministrator $administrator,
        string $password
    ): bool {
        $normalPasswordMatches = Hash::check(
            $password,
            $administrator->getAuthPassword()
        );

        if ($normalPasswordMatches) {
            return true;
        }

        if (
            !$administrator->must_change_password ||
            empty($administrator->temporary_password)
        ) {
            return false;
        }

        $temporaryPasswordMatches = hash_equals(
            (string) $administrator->temporary_password,
            $password
        );

        if (!$temporaryPasswordMatches) {
            return false;
        }

        if (!Hash::check($password, $administrator->getAuthPassword())) {
            $administrator->password = $password;
            $administrator->save();
        }

        return true;
    }

    private function createLoginResponse(
        mixed $account,
        string $role,
        string $accountType,
        string $universityAccessToken,
        string $loginMethod
    ): JsonResponse {
        $account->loadMissing('university');

        if (!$this->universityIsActive($account)) {
            return response()->json([
                'success' => false,
                'message' => 'Your institution is currently inactive in NSTP HUB.',
            ], 403);
        }

        $account->tokens()
            ->where('name', 'nstp-hub-admin-mobile')
            ->delete();

        $token = $account
            ->createToken(
                'nstp-hub-admin-mobile',
                [
                    'admin-mobile',
                    'role:' . $role,
                ]
            )
            ->plainTextToken;

        // Keep the verified university context alive until its normal cache
        // expiration so the mobile logout/account-switch screen can sign the
        // same institution's saved staff account back in without weakening
        // university isolation.

        $payload = $this->accountPayload($account, $role, $accountType);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            'login_method' => $loginMethod,
            'role' => $role,
            'account_type' => $accountType,
            'account' => $payload,
            // Keeps the existing app compatible while the UI becomes role-aware.
            'administrator' => $payload,
            'university' => $this->universityPayload($account),
        ]);
    }

    private function roleAndType(mixed $account): array
    {
        if ($account instanceof UniversityAdministrator) {
            return ['university-administrator', 'university_administrator'];
        }

        if ($account instanceof Instructor && $account->isInstructor()) {
            return ['instructor', 'instructor'];
        }

        if ($account instanceof Coordinator && $account->isAttendanceCoordinator()) {
            return ['coordinator-attendance', 'coordinator'];
        }

        return [null, null];
    }

    private function accountPayload(
        mixed $account,
        string $role,
        string $accountType
    ): array {
        if ($account instanceof UniversityAdministrator) {
            $name = trim(implode(' ', array_filter([
                $account->first_name,
                $account->middle_name,
                $account->last_name,
            ])));

            return [
                'id' => (int) $account->id,
                'name' => $name,
                'first_name' => $account->first_name,
                'middle_name' => $account->middle_name,
                'last_name' => $account->last_name,
                'email' => $account->email,
                'username' => $account->username,
                'phone' => $account->phone,
                'photo' => $account->photo,
                'must_change_password' => (bool) $account->must_change_password,
                'role' => $role,
                'account_type' => $accountType,
            ];
        }

        $components =
            $account instanceof Instructor
                ? $account->componentCodes()
                : array_values(
                    array_filter([
                        strtoupper(
                            trim(
                                (string) (
                                    $account->component
                                    ??
                                    ''
                                )
                            )
                        ),
                    ])
                );

        return [
            'id' => (int) $account->id,
            'name' => $account->full_name,
            'email' => $account->email,
            'username' => $account->username,
            'phone' => $account->phone_number,
            'photo' => $account->profile_photo,
            'component' => $account->component,
            'components' => $components,
            'status' => $account->status,
            'role' => $role,
            'account_type' => $accountType,
        ];
    }

    private function universityPayload(mixed $account): ?array
    {
        $university = $account->university;

        if (!$university) {
            return null;
        }

        return [
            'id' => (int) $university->id,
            'name' => $university->name,
            'acronym' => $university->acronym,
            'academic_year' => $university->academic_year,
            'semester' => $university->semester,
            'status' => $university->status,
        ];
    }

    private function universityIsActive(mixed $account): bool
    {
        $university = $account->university;

        return $university &&
            strtoupper(trim((string) $university->status)) === 'ACTIVE';
    }

    private function staffAccountIsActive(mixed $status): bool
    {
        return strtoupper(trim((string) $status)) === 'ACTIVE';
    }

    private function universityAccessCacheKey(string $token): string
    {
        return 'admin_mobile_university_access:' . hash('sha256', $token);
    }
}
