<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Throwable;

class MobileAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email + Password Login
    |--------------------------------------------------------------------------
    */

    public function login(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'max:255',
                ],

                /*
                |--------------------------------------------------------------------------
                | Verified University Session
                |--------------------------------------------------------------------------
                */

                'university_access_token' => [
                    'required',
                    'string',
                    'min:40',
                    'max:255',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Resolve University
        |--------------------------------------------------------------------------
        */

        $university =
            $this->resolveUniversityAccessToken(
                $validated[
                    'university_access_token'
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
                    $validated['email']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Find Student Account
        |--------------------------------------------------------------------------
        */

        $user =
            User::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Verify Credentials
        |--------------------------------------------------------------------------
        */

        if (
            !$user
            ||
            !Hash::check(
                $validated['password'],
                $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'The email or password is incorrect.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure Student Role
        |--------------------------------------------------------------------------
        */

        $this->ensureStudentRole(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | Connect To Selected University
        |--------------------------------------------------------------------------
        */

        $this->connectStudentToUniversity(
            user: $user,
            university: $university
        );


        /*
        |--------------------------------------------------------------------------
        | Create Mobile Session
        |--------------------------------------------------------------------------
        */

        return $this->createMobileLoginResponse(
            request: $request,
            user: $user,
            loginMethod: 'password'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Google Login
    |--------------------------------------------------------------------------
    */

    public function googleLogin(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'access_token' => [
                    'required',
                    'string',
                    'min:20',
                    'max:4096',
                ],

                'university_access_token' => [
                    'required',
                    'string',
                    'min:40',
                    'max:255',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Resolve University
        |--------------------------------------------------------------------------
        */

        $university =
            $this->resolveUniversityAccessToken(
                $validated[
                    'university_access_token'
                ]
            );


        try {
            /*
            |--------------------------------------------------------------------------
            | Verify Google Token
            |--------------------------------------------------------------------------
            */

            $googleUser =
                Socialite::driver(
                    'google'
                )
                    ->stateless()
                    ->userFromToken(
                        $validated[
                            'access_token'
                        ]
                    );


            $googleEmail =
                strtolower(
                    trim(
                        (string)
                        $googleUser->getEmail()
                    )
                );


            $googleId =
                trim(
                    (string)
                    $googleUser->getId()
                );


            $googleName =
                trim(
                    (string)
                    $googleUser->getName()
                );


            if (
                $googleEmail === ''
                ||
                $googleId === ''
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Google did not provide the required account information.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Verify Google Email
            |--------------------------------------------------------------------------
            */

            $rawGoogleUser =
                is_array(
                    $googleUser->user
                )
                    ? $googleUser->user
                    : [];


            $verifiedEmail =
                $rawGoogleUser[
                    'verified_email'
                ]
                ??
                $rawGoogleUser[
                    'email_verified'
                ]
                ??
                null;


            if (
                $verifiedEmail === false
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Your Google email address is not verified.',
                ], 403);
            }


            /*
            |--------------------------------------------------------------------------
            | Existing Student
            |--------------------------------------------------------------------------
            */

            $existingUser =
                User::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [
                            $googleEmail,
                        ]
                    )
                    ->first();


            if (
                $existingUser
            ) {
                /*
                |--------------------------------------------------------------------------
                | Student Role
                |--------------------------------------------------------------------------
                */

                $this->ensureStudentRole(
                    $existingUser
                );


                /*
                |--------------------------------------------------------------------------
                | Connect University
                |--------------------------------------------------------------------------
                */

                $this->connectStudentToUniversity(
                    user: $existingUser,
                    university: $university
                );


                /*
                |--------------------------------------------------------------------------
                | Google Email Is Verified
                |--------------------------------------------------------------------------
                */

                if (
                    !$existingUser
                        ->email_verified_at
                ) {
                    $existingUser
                        ->forceFill([
                            'email_verified_at' =>
                                now(),
                        ])
                        ->save();
                }


                return $this->createMobileLoginResponse(
                    request: $request,
                    user: $existingUser,
                    loginMethod: 'google'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | New Google Student
            |--------------------------------------------------------------------------
            */

            $setupToken =
                Str::random(
                    96
                );


            /*
            |--------------------------------------------------------------------------
            | Temporary Setup Session
            |--------------------------------------------------------------------------
            */

            Cache::store(
                'file'
            )
                ->put(
                    $this->googleSetupCacheKey(
                        $setupToken
                    ),

                    [
                        'google_id' =>
                            $googleId,

                        'email' =>
                            $googleEmail,

                        'name' =>
                            $googleName !== ''
                                ? $googleName
                                : Str::before(
                                    $googleEmail,
                                    '@'
                                ),

                        'university_id' =>
                            $university->id,
                    ],

                    now()->addMinutes(
                        10
                    )
                );


            Log::info(
                'Mobile Google onboarding started.',
                [
                    'email' =>
                        $googleEmail,

                    'university_id' =>
                        $university->id,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Password Setup Required
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    true,

                'requires_password_setup' =>
                    true,

                'registration_complete' =>
                    false,

                'has_general_registration' =>
                    false,

                'requires_nstp_registration' =>
                    true,

                'registration_status' =>
                    'not_started',

                'next_screen' =>
                    'create_password',

                'message' =>
                    'Google account verified. Please create your NSTP HUB password.',

                'setup_token' =>
                    $setupToken,

                'email' =>
                    $googleEmail,

                'name' =>
                    $googleName,

                'expires_in' =>
                    600,
            ]);

        } catch (
            ValidationException $exception
        ) {
            throw $exception;

        } catch (
            Throwable $exception
        ) {
            Log::warning(
                'Mobile Google authentication failed.',
                [
                    'exception' =>
                        get_class(
                            $exception
                        ),

                    'message' =>
                        $exception->getMessage(),
                ]
            );


            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Google authentication failed. Please sign in with Google again.',
            ], 401);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Google Password Setup
    |--------------------------------------------------------------------------
    */

    public function setGooglePassword(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'setup_token' => [
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
            ]);


        /*
        |--------------------------------------------------------------------------
        | Retrieve One-Time Google Setup
        |--------------------------------------------------------------------------
        */

        $googleSetup =
            Cache::store(
                'file'
            )
                ->pull(
                    $this->googleSetupCacheKey(
                        $validated[
                            'setup_token'
                        ]
                    )
                );


        if (
            !is_array(
                $googleSetup
            )
            ||
            empty(
                $googleSetup[
                    'email'
                ]
            )
            ||
            empty(
                $googleSetup[
                    'university_id'
                ]
            )
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your Google registration session has expired. Please sign in with Google again.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $university =
            University::query()
                ->whereKey(
                    $googleSetup[
                        'university_id'
                    ]
                )
                ->first();


        if (
            !$university
            ||
            strtoupper(
                trim(
                    (string)
                    $university->status
                )
            )
            !==
            'ACTIVE'
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'The selected university is no longer available.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email =
            strtolower(
                trim(
                    $googleSetup[
                        'email'
                    ]
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Duplicate Protection
        |--------------------------------------------------------------------------
        */

        $existingUser =
            User::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        $email,
                    ]
                )
                ->first();


        if (
            $existingUser
        ) {
            $this->ensureStudentRole(
                $existingUser
            );


            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'An NSTP HUB account already exists for this Google email. Please return to the login screen.',
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | Create Student
        |--------------------------------------------------------------------------
        */

        $user =
            DB::transaction(
                function () use (
                    $googleSetup,
                    $email,
                    $validated,
                    $university
                ) {
                    $userData = [
                        'name' =>
                            trim(
                                $googleSetup[
                                    'name'
                                ]
                                ??
                                Str::before(
                                    $email,
                                    '@'
                                )
                            ),

                        'email' =>
                            $email,

                        'password' =>
                            $validated[
                                'password'
                            ],

                        'university_id' =>
                            $university->id,
                    ];


                    /*
                    |--------------------------------------------------------------------------
                    | Username
                    |--------------------------------------------------------------------------
                    */

                    if (
                        Schema::hasColumn(
                            'users',
                            'username'
                        )
                    ) {
                        $userData[
                            'username'
                        ] =
                            $this->generateUniqueUsername(
                                $email
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Create User
                    |--------------------------------------------------------------------------
                    */

                    $user =
                        User::create(
                            $userData
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Google Email Verified
                    |--------------------------------------------------------------------------
                    */

                    $user
                        ->forceFill([
                            'email_verified_at' =>
                                now(),
                        ])
                        ->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Student Role
                    |--------------------------------------------------------------------------
                    */

                    $this->ensureStudentRole(
                        $user
                    );


                    return $user;
                }
            );


        Log::info(
            'Google student account created.',
            [
                'user_id' =>
                    $user->id,

                'email' =>
                    $user->email,

                'university_id' =>
                    $university->id,
            ]
        );


        return $this->createMobileLoginResponse(
            request: $request,
            user: $user,
            loginMethod:
                'google_password_setup'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Current Student
    |--------------------------------------------------------------------------
    */

    public function me(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Authenticated User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        if (
            !(
                $user instanceof User
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
        | Ensure Student Role
        |--------------------------------------------------------------------------
        */

        $this->ensureStudentRole(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $user->load(
            'university'
        );


        /*
        |--------------------------------------------------------------------------
        | Registration Workflow
        |--------------------------------------------------------------------------
        */

        $registrationStatus =
            $this->registrationStatus(
                $user
            );


        $hasGeneralRegistration =
            $this->hasGeneralRegistration(
                $user
            );


        $registrationComplete =
            $this->hasCompletedNstpRegistration(
                $user
            );


        $nextScreen =
            $this->resolveMobileNextScreen(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'registration_status' =>
                $registrationStatus,

            'has_general_registration' =>
                $hasGeneralRegistration,

            'registration_complete' =>
                $registrationComplete,

            'requires_nstp_registration' =>
                !$hasGeneralRegistration,

            'next_screen' =>
                $nextScreen,

            'student' =>
                $this->studentData(
                    request: $request,
                    user: $user
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(
        Request $request
    ): JsonResponse {
        $user =
            $request->user();


        if (
            $user
        ) {
            $token =
                $user
                    ->currentAccessToken();


            if (
                $token
            ) {
                $token->delete();
            }
        }


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Logout successful.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Mobile Login Response
    |--------------------------------------------------------------------------
    */

    private function createMobileLoginResponse(
        Request $request,
        User $user,
        string $loginMethod
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Student Role
        |--------------------------------------------------------------------------
        */

        $this->ensureStudentRole(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | Remove Old Mobile Tokens
        |--------------------------------------------------------------------------
        */

        $user
            ->tokens()
            ->where(
                'name',
                'nstp-hub-mobile'
            )
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | Create Sanctum Token
        |--------------------------------------------------------------------------
        */

        $token =
            $user
                ->createToken(
                    'nstp-hub-mobile',
                    [
                        'mobile',
                    ]
                )
                ->plainTextToken;


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $user->load(
            'university'
        );


        /*
        |--------------------------------------------------------------------------
        | Registration State
        |--------------------------------------------------------------------------
        */

        $registrationStatus =
            $this->registrationStatus(
                $user
            );


        $hasGeneralRegistration =
            $this->hasGeneralRegistration(
                $user
            );


        $registrationComplete =
            $this->hasCompletedNstpRegistration(
                $user
            );


        $nextScreen =
            $this->resolveMobileNextScreen(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Message
        |--------------------------------------------------------------------------
        */

        $message =
            match (
                $registrationStatus
            ) {
                'completed' =>
                    'Login successful.',

                'confirmed' =>
                    'Login successful. Your NSTP registration has been approved.',

                'submitted',
                'under_review' =>
                    'Login successful. Your NSTP registration is waiting for confirmation.',

                'draft' =>
                    'Login successful. Please continue your NSTP registration.',

                default =>
                    'Login successful. Please complete your NSTP registration.',
            };


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'requires_password_setup' =>
                false,

            /*
            |--------------------------------------------------------------------------
            | Registration
            |--------------------------------------------------------------------------
            */

            'registration_status' =>
                $registrationStatus,

            'has_general_registration' =>
                $hasGeneralRegistration,

            'registration_complete' =>
                $registrationComplete,

            'requires_nstp_registration' =>
                !$hasGeneralRegistration,

            /*
            |--------------------------------------------------------------------------
            | Laravel Controls Navigation
            |--------------------------------------------------------------------------
            */

            'next_screen' =>
                $nextScreen,

            /*
            |--------------------------------------------------------------------------
            | Authentication
            |--------------------------------------------------------------------------
            */

            'message' =>
                $message,

            'login_method' =>
                $loginMethod,

            'token_type' =>
                'Bearer',

            'token' =>
                $token,

            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            */

            'student' =>
                $this->studentData(
                    request: $request,
                    user: $user
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve University Access Token
    |--------------------------------------------------------------------------
    */

    private function resolveUniversityAccessToken(
        string $token
    ): University {
        /*
        |--------------------------------------------------------------------------
        | Access Session
        |--------------------------------------------------------------------------
        */

        $accessSession =
            Cache::store(
                'file'
            )
                ->get(
                    $this->universityAccessCacheKey(
                        $token
                    )
                );


        /*
        |--------------------------------------------------------------------------
        | Missing / Expired
        |--------------------------------------------------------------------------
        */

        if (
            !is_array(
                $accessSession
            )
            ||
            empty(
                $accessSession[
                    'university_id'
                ]
            )
        ) {
            throw ValidationException::withMessages([
                'university_access_token' => [
                    'Your university access session has expired. Please enter the university access code again.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $university =
            University::query()
                ->find(
                    $accessSession[
                        'university_id'
                    ]
                );


        /*
        |--------------------------------------------------------------------------
        | Active University Required
        |--------------------------------------------------------------------------
        */

        if (
            !$university
            ||
            strtoupper(
                trim(
                    (string)
                    $university->status
                )
            )
            !==
            'ACTIVE'
        ) {
            throw ValidationException::withMessages([
                'university_access_token' => [
                    'The selected university is no longer available.',
                ],
            ]);
        }


        return $university;
    }


    /*
    |--------------------------------------------------------------------------
    | Connect Student To University
    |--------------------------------------------------------------------------
    */

    private function connectStudentToUniversity(
        User $user,
        University $university
    ): void {
        /*
        |--------------------------------------------------------------------------
        | No University Yet
        |--------------------------------------------------------------------------
        */

        if (
            !$user->university_id
        ) {
            $user
                ->forceFill([
                    'university_id' =>
                        $university->id,
                ])
                ->save();


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Already Correct University
        |--------------------------------------------------------------------------
        */

        if (
            (int)
            $user->university_id
            ===
            (int)
            $university->id
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Submitted Student Cannot Switch University
        |--------------------------------------------------------------------------
        |
        | Do not check only "completed".
        |
        | Once a student has submitted registration for review, changing the
        | university would break the registration approval workflow.
        |
        */

        if (
            $this->hasSubmittedNstpRegistration(
                $user
            )
        ) {
            throw ValidationException::withMessages([
                'university_access_token' => [
                    'This student account already has an NSTP registration with another university.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Draft / Unregistered Student May Correct University
        |--------------------------------------------------------------------------
        */

        $user
            ->forceFill([
                'university_id' =>
                    $university->id,
            ])
            ->save();
    }


    /*
    |--------------------------------------------------------------------------
    | Has General Registration
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Having a component selected does NOT mean registration is completed.
    |
    | This method only means the student has filled the required general
    | registration fields.
    |
    */

    private function hasGeneralRegistration(
        User $user
    ): bool {
        return
            filled(
                $user->subject
            )
            &&
            filled(
                $user->component
            )
            &&
            filled(
                $user->term
            )
            &&
            filled(
                $user->surname
            )
            &&
            filled(
                $user->first_name
            )
            &&
            filled(
                $user->course
            )
            &&
            filled(
                $user->year_level
            )
            &&
            filled(
                $user->section
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Registration Status
    |--------------------------------------------------------------------------
    |
    | Workflow:
    |
    | not_started
    |     ↓
    | draft
    |     ↓
    | submitted / under_review
    |     ↓
    | confirmed
    |     ↓
    | component-specific continuation
    |     ↓
    | completed
    |
    */

    private function registrationStatus(
        User $user
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Stored Status
        |--------------------------------------------------------------------------
        */

        $storedStatus =
            strtolower(
                trim(
                    (string) (
                        $user->getAttribute(
                            'registration_status'
                        )
                        ??
                        ''
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Completed Has Highest Priority
        |--------------------------------------------------------------------------
        */

        if (
            $storedStatus ===
            'completed'
            ||
            $user->getAttribute(
                'registration_completed_at'
            )
            !==
            null
        ) {
            return 'completed';
        }


        /*
        |--------------------------------------------------------------------------
        | University Admin Confirmed
        |--------------------------------------------------------------------------
        */

        if (
            $storedStatus ===
            'confirmed'
            ||
            $user->getAttribute(
                'confirmed_at'
            )
            !==
            null
        ) {
            return 'confirmed';
        }


        /*
        |--------------------------------------------------------------------------
        | Submitted / Under Review
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $storedStatus,
                [
                    'submitted',
                    'under_review',
                ],
                true
            )
        ) {
            return $storedStatus;
        }


        /*
        |--------------------------------------------------------------------------
        | Signature Means Registration Was Submitted
        |--------------------------------------------------------------------------
        */

        if (
            filled(
                $user->signature_path
            )
            ||
            $user->getAttribute(
                'registration_submitted_at'
            )
            !==
            null
        ) {
            return 'under_review';
        }


        /*
        |--------------------------------------------------------------------------
        | Draft
        |--------------------------------------------------------------------------
        */

        if (
            $storedStatus ===
            'draft'
            ||
            $this->hasGeneralRegistration(
                $user
            )
        ) {
            return 'draft';
        }


        return 'not_started';
    }


    /*
    |--------------------------------------------------------------------------
    | Has Registration Been Confirmed?
    |--------------------------------------------------------------------------
    */

    private function isRegistrationConfirmed(
        User $user
    ): bool {
        return in_array(
            $this->registrationStatus(
                $user
            ),

            [
                'confirmed',
                'completed',
            ],

            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Fully Completed NSTP Registration
    |--------------------------------------------------------------------------
    |
    | VERY IMPORTANT:
    |
    | A selected component is NOT completion.
    |
    | This replaces the old incorrect logic:
    |
    | component = ROTC/CWTS/LTS → completed
    |
    | Now only workflow status "completed" means the student's full onboarding
    | process has finished.
    |
    */

    private function hasCompletedNstpRegistration(
        User $user
    ): bool {
        return (
            $this->registrationStatus(
                $user
            )
            ===
            'completed'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Has Registration Been Submitted?
    |--------------------------------------------------------------------------
    */

    private function hasSubmittedNstpRegistration(
        User $user
    ): bool {
        $status =
            $this->registrationStatus(
                $user
            );


        return in_array(
            $status,

            [
                'submitted',
                'under_review',
                'confirmed',
                'completed',
            ],

            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Mobile Next Screen
    |--------------------------------------------------------------------------
    |
    | THIS IS THE IMPORTANT ROUTING FIX.
    |
    | Admin approval must NOT automatically mean Homepage.
    |
    | Once approved:
    |
    | confirmed
    |     ↓
    | RegistrationConfirmation
    |     ↓
    | component is checked
    |     │
    |     ├── CWTS → Homepage
    |     ├── LTS  → Homepage
    |     └── ROTC → ROTC Enrollment
    |
    */

    private function resolveMobileNextScreen(
        User $user
    ): string {
        /*
        |--------------------------------------------------------------------------
        | No Registration
        |--------------------------------------------------------------------------
        */

        if (
            !$this->hasGeneralRegistration(
                $user
            )
        ) {
            return
                '/Registration/PreRegistrationScreen';
        }


        /*
        |--------------------------------------------------------------------------
        | Current Status
        |--------------------------------------------------------------------------
        */

        $status =
            $this->registrationStatus(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Draft Registration
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $status,
                [
                    'not_started',
                    'draft',
                ],
                true
            )
        ) {
            return
                '/Registration/GeneralRegistrationScreen';
        }


        /*
        |--------------------------------------------------------------------------
        | Waiting For Admin Approval
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $status,
                [
                    'submitted',
                    'under_review',
                ],
                true
            )
        ) {
            return
                '/Registration/RegistrationWaitEdit';
        }


        /*
        |--------------------------------------------------------------------------
        | Approved
        |--------------------------------------------------------------------------
        |
        | Do NOT route straight to Homepage here.
        |
        | RegistrationConfirmationController determines the component.
        |
        */

        if (
            $status ===
            'confirmed'
        ) {
            return
                '/Registration/RegistrationConfirmation';
        }


        /*
        |--------------------------------------------------------------------------
        | Fully Finished
        |--------------------------------------------------------------------------
        */

        if (
            $status ===
            'completed'
        ) {
            return
                '/(tabs)/HomepageScreen';
        }


        /*
        |--------------------------------------------------------------------------
        | Safe Fallback
        |--------------------------------------------------------------------------
        */

        return
            '/Registration/PreRegistrationScreen';
    }


    /*
    |--------------------------------------------------------------------------
    | Student Role
    |--------------------------------------------------------------------------
    */

    private function ensureStudentRole(
        User $user
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Student Role
        |--------------------------------------------------------------------------
        */

        $studentRole =
            Role::firstOrCreate([
                'name' =>
                    'student',

                'guard_name' =>
                    'web',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Assign If Missing
        |--------------------------------------------------------------------------
        */

        if (
            !$user->hasRole(
                'student'
            )
        ) {
            $user->assignRole(
                $studentRole
            );
        }
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
    | Google Setup Cache Key
    |--------------------------------------------------------------------------
    */

    private function googleSetupCacheKey(
        string $setupToken
    ): string {
        return
            'nstphub:mobile:google-password-setup:'
            .
            hash(
                'sha256',
                $setupToken
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Unique Username
    |--------------------------------------------------------------------------
    */

    private function generateUniqueUsername(
        string $email
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Base Username
        |--------------------------------------------------------------------------
        */

        $base =
            Str::of(
                Str::before(
                    $email,
                    '@'
                )
            )
                ->lower()
                ->replaceMatches(
                    '/[^a-z0-9_]/',
                    ''
                )
                ->value();


        if (
            $base === ''
        ) {
            $base =
                'student';
        }


        /*
        |--------------------------------------------------------------------------
        | Candidate
        |--------------------------------------------------------------------------
        */

        $candidate =
            $base;


        $counter =
            1;


        /*
        |--------------------------------------------------------------------------
        | Guarantee Unique Username
        |--------------------------------------------------------------------------
        */

        while (
            User::query()
                ->where(
                    'username',
                    $candidate
                )
                ->exists()
        ) {
            $candidate =
                $base
                .
                $counter;


            $counter++;
        }


        return $candidate;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Data
    |--------------------------------------------------------------------------
    */

    private function studentData(
        Request $request,
        User $user
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Workflow State
        |--------------------------------------------------------------------------
        */

        $registrationStatus =
            $this->registrationStatus(
                $user
            );


        $hasGeneralRegistration =
            $this->hasGeneralRegistration(
                $user
            );


        $registrationComplete =
            $this->hasCompletedNstpRegistration(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [
            /*
            |--------------------------------------------------------------------------
            | Account
            |--------------------------------------------------------------------------
            */

            'id' =>
                $user->id,

            'name' =>
                $user->name,

            'full_name' =>
                $user->full_name,

            'username' =>
                $user->username,

            'email' =>
                $user->email,


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            */

            'university_id' =>
                $user->university_id,


            /*
            |--------------------------------------------------------------------------
            | Registration Workflow
            |--------------------------------------------------------------------------
            */

            'registration_status' =>
                $registrationStatus,

            'has_general_registration' =>
                $hasGeneralRegistration,

            'registration_complete' =>
                $registrationComplete,

            'requires_nstp_registration' =>
                !$hasGeneralRegistration,

            'next_screen' =>
                $this->resolveMobileNextScreen(
                    $user
                ),


            /*
            |--------------------------------------------------------------------------
            | NSTP ID
            |--------------------------------------------------------------------------
            */

            'student_id_number' =>
                $user->student_id_number,


            /*
            |--------------------------------------------------------------------------
            | NSTP Registration
            |--------------------------------------------------------------------------
            */

            'subject' =>
                $user->subject,

            'component' =>
                $user->component,

            'term' =>
                $user->term,


            /*
            |--------------------------------------------------------------------------
            | Student Name
            |--------------------------------------------------------------------------
            */

            'surname' =>
                $user->surname,

            'first_name' =>
                $user->first_name,

            'middle_name' =>
                $user->middle_name,


            /*
            |--------------------------------------------------------------------------
            | Academic Information
            |--------------------------------------------------------------------------
            */

            'course' =>
                $user->course,

            'year_level' =>
                $user->year_level,

            'section' =>
                $user->section,


            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            'gender' =>
                $user->gender,

            'birth_date' =>
                $user->birth_date
                    ? $user
                        ->birth_date
                        ->format(
                            'Y-m-d'
                        )
                    : null,

            'contact_number' =>
                $user->contact_number,


            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'city_address' =>
                $user->city_address,

            'municipality' =>
                $user->municipality,

            'province' =>
                $user->province,

            'full_address' =>
                $user->full_address,


            /*
            |--------------------------------------------------------------------------
            | Guardian
            |--------------------------------------------------------------------------
            */

            'guardian_name' =>
                $user->guardian_name,

            'guardian_address' =>
                $user->guardian_address,

            'guardian_contact_number' =>
                $user->guardian_contact_number,


            /*
            |--------------------------------------------------------------------------
            | Profile Photo
            |--------------------------------------------------------------------------
            */

            'profile_photo' =>
                $user->profile_photo,

            'profile_photo_url' =>
                $this->fileUrl(
                    request: $request,
                    path: $user->profile_photo
                ),


            /*
            |--------------------------------------------------------------------------
            | Signature
            |--------------------------------------------------------------------------
            */

            'signature_path' =>
                $user->signature_path,

            'signature_url' =>
                $this->fileUrl(
                    request: $request,
                    path: $user->signature_path
                ),


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            */

            'university' =>
                $user->university
                    ? [
                        'id' =>
                            $user
                                ->university
                                ->id,

                        'name' =>
                            $user
                                ->university
                                ->name,

                        'acronym' =>
                            $user
                                ->university
                                ->acronym,
                    ]
                    : null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | File URL
    |--------------------------------------------------------------------------
    */

    private function fileUrl(
        Request $request,
        ?string $path
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | Missing File
        |--------------------------------------------------------------------------
        */

        if (
            !$path
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Already Absolute URL
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                $path,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return $path;
        }


        /*
        |--------------------------------------------------------------------------
        | Public Storage URL
        |--------------------------------------------------------------------------
        */

        $relativeUrl =
            Storage::disk(
                'public'
            )
                ->url(
                    $path
                );


        return
            $request
                ->getSchemeAndHttpHost()
            .
            $relativeUrl;
    }
}