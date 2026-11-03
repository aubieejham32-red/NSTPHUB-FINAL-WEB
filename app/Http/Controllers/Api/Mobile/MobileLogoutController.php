<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MobileLogoutController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | University Access Lifetime
    |--------------------------------------------------------------------------
    |
    | After logout, the selected university is preserved for one hour.
    |
    | This allows:
    |
    | Logout
    |     ↓
    | Choose An Account
    |     ↓
    | Use another account
    |     ↓
    | LoginScreen
    |
    | without immediately asking for the University Access Code again.
    |
    */

    private const UNIVERSITY_ACCESS_TTL_MINUTES = 60;


    /*
    |--------------------------------------------------------------------------
    | Logout Current Student
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /api/mobile/logout-page/logout
    |
    | Protected by:
    |
    | auth:sanctum
    | EnsureMobileStudent
    |
    | Flow:
    |
    | 1. Get authenticated student.
    | 2. Prepare safe saved-account information.
    | 3. Preserve university login context.
    | 4. Delete current Sanctum token.
    | 5. Return account information to Logout.js.
    |
    | IMPORTANT:
    |
    | Passwords are NEVER returned.
    | Sanctum tokens are NEVER placed in the saved account.
    |
    */

    public function logout(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Current Student
        |--------------------------------------------------------------------------
        */

        $student =
            $request->user();


        if (
            !(
                $student instanceof User
            )
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'This account is not authorized to use the NSTP HUB mobile application.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $student->loadMissing(
            'university'
        );


        /*
        |--------------------------------------------------------------------------
        | Saved Account Payload
        |--------------------------------------------------------------------------
        |
        | This is the only student information that Logout.js saves locally.
        |
        */

        $savedAccount =
            $this->savedAccountPayload(
                request:
                    $request,

                student:
                    $student
            );


        /*
        |--------------------------------------------------------------------------
        | Preserve University Access
        |--------------------------------------------------------------------------
        */

        $universityAccessToken =
            null;


        if (
            $student->university
                instanceof University
            &&
            $this->universityIsActive(
                $student->university
            )
        ) {
            $universityAccessToken =
                $this->createUniversityAccessToken(
                    $student->university
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Revoke Current Sanctum Token
        |--------------------------------------------------------------------------
        |
        | Only the current mobile login session is logged out.
        |
        */

        $currentToken =
            $student
                ->currentAccessToken();


        if (
            $currentToken
        ) {
            $currentToken
                ->delete();
        }


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Logout successful.',

            /*
            |--------------------------------------------------------------------------
            | Account For Device Chooser
            |--------------------------------------------------------------------------
            */

            'saved_account' =>
                $savedAccount,

            /*
            |--------------------------------------------------------------------------
            | Preserved University Context
            |--------------------------------------------------------------------------
            */

            'university_access_token' =>
                $universityAccessToken,

            'university_access_expires_in' =>
                $universityAccessToken
                    ? (
                        self::UNIVERSITY_ACCESS_TTL_MINUTES
                        *
                        60
                    )
                    : 0,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Login Saved Student Account
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /api/mobile/logout-page/login
    |
    | Public route.
    |
    | Flow:
    |
    | Saved Account
    |      ↓
    | Enter Password
    |      ↓
    | Verify ID + Email + University + Password
    |      ↓
    | MobileAuthController
    |      ↓
    | Create Sanctum Token
    |      ↓
    | Continue To Correct Student Screen
    |
    */

    public function loginSavedAccount(
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
                    'id' => [
                        'required',
                        'integer',
                        'min:1',
                    ],

                    'account_type' => [
                        'required',
                        'string',
                        'in:student',
                    ],

                    'email' => [
                        'required',
                        'string',
                        'email',
                        'max:255',
                    ],

                    'university_id' => [
                        'required',
                        'integer',
                        'min:1',
                    ],

                    'password' => [
                        'required',
                        'string',
                        'min:8',
                        'max:255',
                    ],
                ],
                [
                    'id.required' =>
                        'Unable to identify the selected account.',

                    'account_type.required' =>
                        'Unable to identify the selected account type.',

                    'email.required' =>
                        'Unable to identify the selected account email.',

                    'email.email' =>
                        'The selected account email is invalid.',

                    'university_id.required' =>
                        'Unable to identify the university for this saved account.',

                    'password.required' =>
                        'Please enter your password.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $accountId =
            (int)
            $validated[
                'id'
            ];


        $universityId =
            (int)
            $validated[
                'university_id'
            ];


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
        | Password
        |--------------------------------------------------------------------------
        |
        | Never trim or alter the password.
        |
        */

        $password =
            $validated[
                'password'
            ];


        /*
        |--------------------------------------------------------------------------
        | Find Exact Student
        |--------------------------------------------------------------------------
        |
        | We verify:
        |
        | - User ID
        | - University ID
        | - Email
        |
        | before checking the password.
        |
        */

        $student =
            User::query()
                ->with(
                    'university'
                )
                ->whereKey(
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


        /*
        |--------------------------------------------------------------------------
        | Student Not Found
        |--------------------------------------------------------------------------
        */

        if (
            !$student
        ) {
            throw ValidationException::withMessages([
                'login' => [
                    'The selected saved account could not be found.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        */

        if (
            !Hash::check(
                $password,
                $student->password
            )
        ) {
            throw ValidationException::withMessages([
                'login' => [
                    'Invalid password.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Required
        |--------------------------------------------------------------------------
        */

        if (
            !$student->university
        ) {
            throw ValidationException::withMessages([
                'login' => [
                    'The university connected to this account could not be found.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !$this->universityIsActive(
                $student->university
            )
        ) {
            throw ValidationException::withMessages([
                'login' => [
                    'The university connected to this account is currently inactive.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Create Temporary University Access Session
        |--------------------------------------------------------------------------
        |
        | MobileAuthController already requires university_access_token.
        |
        | Instead of duplicating the entire login process here, this controller
        | creates the same university session format and delegates authentication
        | back to MobileAuthController.
        |
        */

        $universityAccessToken =
            $this->createUniversityAccessToken(
                $student->university
            );


        /*
        |--------------------------------------------------------------------------
        | Prepare Existing Mobile Login Request
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'email' =>
                $email,

            'password' =>
                $password,

            'university_access_token' =>
                $universityAccessToken,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Existing Mobile Authentication
        |--------------------------------------------------------------------------
        |
        | MobileAuthController remains responsible for:
        |
        | - Student role
        | - University connection
        | - Revoking old mobile tokens
        | - Creating new Sanctum token
        | - Registration workflow
        | - next_screen
        |
        */

        return app(
            MobileAuthController::class
        )
            ->login(
                $request
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Saved Account Payload
    |--------------------------------------------------------------------------
    |
    | Safe information allowed to be stored on the student's phone.
    |
    | NO PASSWORD.
    | NO SANCTUM TOKEN.
    |
    */

    private function savedAccountPayload(
        Request $request,
        User $student
    ): array {
        $university =
            $student->university;


        return [
            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            'id' =>
                $student->id,

            'account_type' =>
                'student',

            'role' =>
                'student',


            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            */

            'full_name' =>
                $student->full_name,

            'name' =>
                $student->full_name
                ?:
                $student->name,

            'email' =>
                $student->email,

            'student_id_number' =>
                $student->student_id_number,

            'component' =>
                $student->component,


            /*
            |--------------------------------------------------------------------------
            | Profile Photo
            |--------------------------------------------------------------------------
            */

            'profile_photo' =>
                $student->profile_photo,

            'profile_photo_url' =>
                $this->publicFileUrl(
                    request:
                        $request,

                    path:
                        $student->profile_photo
                ),


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            */

            'university_id' =>
                $student->university_id,

            'university_name' =>
                $university
                    ?->name,

            'university_acronym' =>
                $university
                    ?->acronym,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Create University Access Token
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | This cache key is intentionally identical to the key read by:
    |
    | MobileAuthController::resolveUniversityAccessToken()
    |
    */

    private function createUniversityAccessToken(
        University $university
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Plain One-Time Context Token
        |--------------------------------------------------------------------------
        */

        $plainToken =
            Str::random(
                96
            );


        /*
        |--------------------------------------------------------------------------
        | Store Hashed Cache Key
        |--------------------------------------------------------------------------
        */

        Cache::store(
            'file'
        )
            ->put(
                $this->universityAccessCacheKey(
                    $plainToken
                ),

                [
                    'university_id' =>
                        $university->id,
                ],

                now()->addMinutes(
                    self::UNIVERSITY_ACCESS_TTL_MINUTES
                )
            );


        return $plainToken;
    }


    /*
    |--------------------------------------------------------------------------
    | University Access Cache Key
    |--------------------------------------------------------------------------
    */

    private function universityAccessCacheKey(
        string $token
    ): string {
        return
            'nstphub:mobile:university-access:'
            .
            hash(
                'sha256',
                $token
            );
    }


    /*
    |--------------------------------------------------------------------------
    | University Is Active
    |--------------------------------------------------------------------------
    */

    private function universityIsActive(
        University $university
    ): bool {
        return (
            strtoupper(
                trim(
                    (string)
                    $university->status
                )
            )
            ===
            'ACTIVE'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Public Profile Photo URL
    |--------------------------------------------------------------------------
    */

    private function publicFileUrl(
        Request $request,
        ?string $path
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $path =
            trim(
                (string)
                $path
            );


        if (
            $path ===
            ''
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Already Public
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                $path,
                [
                    'http://',
                    'https://',
                    'data:',
                ]
            )
        ) {
            return $path;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Stored Path
        |--------------------------------------------------------------------------
        */

        $normalized =
            preg_replace(
                '#^/?storage/#',
                '',
                str_replace(
                    '\\',
                    '/',
                    $path
                )
            );


        if (
            !$normalized
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Laravel Public Disk URL
        |--------------------------------------------------------------------------
        */

        $storageUrl =
            Storage::disk(
                'public'
            )
                ->url(
                    $normalized
                );


        /*
        |--------------------------------------------------------------------------
        | Absolute Disk URL
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                $storageUrl,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return $storageUrl;
        }


        /*
        |--------------------------------------------------------------------------
        | Use Actual Request Host
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | http://192.168.254.117:8000/storage/...
        |
        */

        return (
            rtrim(
                $request
                    ->getSchemeAndHttpHost(),
                '/'
            )
            .
            '/'
            .
            ltrim(
                $storageUrl,
                '/'
            )
        );
    }
}