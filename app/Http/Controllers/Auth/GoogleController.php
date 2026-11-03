<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\SuperAdmin;
use App\Models\UniversityAdministrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | University Admin - Redirect To Google
    |--------------------------------------------------------------------------
    |
    | GET /auth/google
    |
    */

    public function redirect(
        Request $request
    ): SymfonyRedirectResponse {

        Log::info(
            'Google Login: University Admin redirect started.',
            [
                'session_id' =>
                    $request->session()->getId(),
            ]
        );


        return Socialite::driver(
            'google'
        )
            ->redirectUrl(
                route(
                    'google.callback'
                )
            )
            ->redirect();
    }


    /*
    |--------------------------------------------------------------------------
    | Instructor / Coordinator - Redirect To Google
    |--------------------------------------------------------------------------
    |
    | GET /auth/google/instructor-coordinator
    |
    */

    public function redirectInstructorCoordinator(
        Request $request
    ): SymfonyRedirectResponse|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | University Access Must Already Be Verified
        |--------------------------------------------------------------------------
        */

        $accessVerified =
            $request
                ->session()
                ->get(
                    'staff_access_verified',
                    false
                );


        $universityId =
            (int) $request
                ->session()
                ->get(
                    'staff_university_id',
                    0
                );


        if (
            !$accessVerified ||
            !$universityId
        ) {

            Log::warning(
                'Google Login: Staff Google login attempted without university context.',
                [
                    'staff_access_verified' =>
                        $accessVerified,

                    'staff_university_id' =>
                        $universityId,

                    'session_id' =>
                        $request
                            ->session()
                            ->getId(),
                ]
            );


            return redirect()
                ->route(
                    'instructor-coordinator.access-code'
                )
                ->withErrors([
                    'access_code' =>
                        'Please verify your University Access Code before using Google login.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Preserve University For Callback
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put(
                'google_staff_university_id',
                $universityId
            );


        Log::info(
            'Google Login: Instructor / Coordinator redirect started.',
            [
                'university_id' =>
                    $universityId,

                'session_id' =>
                    $request
                        ->session()
                        ->getId(),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Instructor / Coordinator has its OWN Google callback URL.
        |
        */

        return Socialite::driver(
            'google'
        )
            ->redirectUrl(
                route(
                    'google.instructor-coordinator.callback'
                )
            )
            ->redirect();
    }


    /*
    |--------------------------------------------------------------------------
    | Super Admin - Redirect To Google
    |--------------------------------------------------------------------------
    |
    | GET /auth/google/superadmin
    |
    | Super Admin uses a dedicated redirect/callback pair so it can never be
    | confused with the University Admin or Instructor/Coordinator flow.
    |
    */

    public function redirectSuperAdmin(
        Request $request
    ): SymfonyRedirectResponse|RedirectResponse {

        if (
            Auth::guard(
                'superadmin'
            )->check()
        ) {

            return redirect()->route(
                'superadmin.dashboard'
            );
        }


        Log::info(
            'Google Login: Super Admin redirect started.',
            [
                'session_id' =>
                    $request
                        ->session()
                        ->getId(),
            ]
        );


        return Socialite::driver(
            'google'
        )
            ->redirectUrl(
                route(
                    'google.superadmin.callback'
                )
            )
            ->scopes([
                'openid',
                'profile',
                'email',
            ])
            ->redirect();
    }


    /*
    |--------------------------------------------------------------------------
    | Super Admin Google Callback
    |--------------------------------------------------------------------------
    |
    | GET /auth/google/superadmin/callback
    |
    | SECURITY:
    | - Google login does not auto-create Super Admin accounts.
    | - The Google email must already exist in super_admins.
    | - The Super Admin stays authenticated with the superadmin guard.
    |
    */

    public function callbackSuperAdmin(
        Request $request
    ): RedirectResponse {

        try {

            $googleUser =
                Socialite::driver(
                    'google'
                )
                    ->redirectUrl(
                        route(
                            'google.superadmin.callback'
                        )
                    )
                    ->user();


            $email =
                strtolower(
                    trim(
                        (string) $googleUser
                            ->getEmail()
                    )
                );


            if (
                $email ===
                ''
            ) {

                return redirect()
                    ->route(
                        'superadmin.login'
                    )
                    ->withErrors([
                        'login' =>
                            'Google did not provide an email address.',
                    ]);
            }


            /*
            |------------------------------------------------------------------
            | Require A Pre-existing Super Admin Account
            |------------------------------------------------------------------
            */

            $superAdmin =
                SuperAdmin::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [
                            $email,
                        ]
                    )
                    ->first();


            if (
                !$superAdmin
            ) {

                Log::warning(
                    'Google Login: No Super Admin matched Google email.',
                    [
                        'email' =>
                            $email,
                    ]
                );


                return redirect()
                    ->route(
                        'superadmin.login'
                    )
                    ->withErrors([
                        'login' =>
                            'No Super Admin account is connected to this Google email.',
                    ]);
            }


            /*
            |------------------------------------------------------------------
            | Logout Other Web Guards
            |------------------------------------------------------------------
            |
            | Only one web-role session should be active after the callback.
            |
            */

            Auth::guard(
                'web'
            )->logout();

            Auth::guard(
                'university_admin'
            )->logout();

            Auth::guard(
                'instructor'
            )->logout();

            Auth::guard(
                'coordinator'
            )->logout();

            Auth::guard(
                'superadmin'
            )->logout();


            /*
            |------------------------------------------------------------------
            | Authenticate As Super Admin
            |------------------------------------------------------------------
            */

            Auth::guard(
                'superadmin'
            )->login(
                $superAdmin,
                false
            );


            $request
                ->session()
                ->regenerate();


            if (
                !Auth::guard(
                    'superadmin'
                )->check()
            ) {

                return redirect()
                    ->route(
                        'superadmin.login'
                    )
                    ->withErrors([
                        'login' =>
                            'Unable to create your Super Admin login session.',
                    ]);
            }


            Log::info(
                'Google Login: Super Admin authenticated.',
                [
                    'super_admin_id' =>
                        $superAdmin->id,

                    'email' =>
                        $superAdmin->email,
                ]
            );


            return redirect()->route(
                'superadmin.dashboard'
            );

        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Google Login: Super Admin callback failed.',
                [
                    'exception' =>
                        get_class(
                            $exception
                        ),

                    'message' =>
                        $exception
                            ->getMessage(),

                    'file' =>
                        $exception
                            ->getFile(),

                    'line' =>
                        $exception
                            ->getLine(),
                ]
            );


            return redirect()
                ->route(
                    'superadmin.login'
                )
                ->withErrors([
                    'login' =>
                        'Google authentication failed. Please try again.',
                ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | University Admin Google Callback
    |--------------------------------------------------------------------------
    |
    | GET /auth/google/callback
    |
    */

    public function callback(
        Request $request
    ): RedirectResponse {

        try {

            /*
            |--------------------------------------------------------------------------
            | Get Google User
            |--------------------------------------------------------------------------
            */

            $googleUser =
                Socialite::driver(
                    'google'
                )
                    ->redirectUrl(
                        route(
                            'google.callback'
                        )
                    )
                    ->user();


            /*
            |--------------------------------------------------------------------------
            | Google Email
            |--------------------------------------------------------------------------
            */

            $email =
                strtolower(
                    trim(
                        (string) $googleUser
                            ->getEmail()
                    )
                );


            if (
                $email ===
                ''
            ) {

                return redirect()
                    ->route(
                        'university-admin.login'
                    )
                    ->withErrors([
                        'login' =>
                            'Google did not provide an email address.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | University Administrator
            |--------------------------------------------------------------------------
            */

            $admin =
                UniversityAdministrator::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [
                            $email,
                        ]
                    )
                    ->first();


            if (
                !$admin
            ) {

                return redirect()
                    ->route(
                        'university-admin.login'
                    )
                    ->withErrors([
                        'login' =>
                            'No University Administrator account is connected to this Google email.',
                    ]);
            }


            if (
                empty(
                    $admin->university_id
                )
            ) {

                return redirect()
                    ->route(
                        'university-admin.login'
                    )
                    ->withErrors([
                        'login' =>
                            'This University Administrator account is not connected to a university.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Logout Other Guards
            |--------------------------------------------------------------------------
            */

            Auth::guard(
                'web'
            )->logout();


            Auth::guard(
                'instructor'
            )->logout();


            Auth::guard(
                'coordinator'
            )->logout();


            /*
            |--------------------------------------------------------------------------
            | Login University Administrator
            |--------------------------------------------------------------------------
            */

            Auth::guard(
                'university_admin'
            )->login(
                $admin,
                false
            );


            /*
            |--------------------------------------------------------------------------
            | Regenerate Session
            |--------------------------------------------------------------------------
            */

            $request
                ->session()
                ->regenerate();


            /*
            |--------------------------------------------------------------------------
            | Verify Login
            |--------------------------------------------------------------------------
            */

            if (
                !Auth::guard(
                    'university_admin'
                )->check()
            ) {

                return redirect()
                    ->route(
                        'university-admin.login'
                    )
                    ->withErrors([
                        'login' =>
                            'Unable to create your University Administrator login session.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Success Log
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Google Login: University Administrator authenticated.',
                [
                    'admin_id' =>
                        $admin->id,

                    'email' =>
                        $admin->email,

                    'university_id' =>
                        $admin->university_id,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | University Admin Dashboard
            |--------------------------------------------------------------------------
            */

            return redirect()->route(
                'university-admin.dashboard'
            );

        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Google Login: University Admin callback failed.',
                [
                    'exception' =>
                        get_class(
                            $exception
                        ),

                    'message' =>
                        $exception
                            ->getMessage(),

                    'file' =>
                        $exception
                            ->getFile(),

                    'line' =>
                        $exception
                            ->getLine(),
                ]
            );


            return redirect()
                ->route(
                    'university-admin.login'
                )
                ->withErrors([
                    'login' =>
                        'Google authentication failed. Please try again.',
                ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Instructor / Coordinator Google Callback
    |--------------------------------------------------------------------------
    |
    | GET /auth/google/instructor-coordinator/callback
    |
    | IMPORTANT:
    |
    | This method can NEVER fall through to University Admin login.
    |
    */

    public function callbackInstructorCoordinator(
        Request $request
    ): RedirectResponse {

        try {

            /*
            |--------------------------------------------------------------------------
            | Get Google User
            |--------------------------------------------------------------------------
            |
            | Use the SAME redirect URL that was used when sending
            | the user to Google.
            |
            */

            $googleUser =
                Socialite::driver(
                    'google'
                )
                    ->redirectUrl(
                        route(
                            'google.instructor-coordinator.callback'
                        )
                    )
                    ->user();


            /*
            |--------------------------------------------------------------------------
            | Google Email
            |--------------------------------------------------------------------------
            */

            $email =
                strtolower(
                    trim(
                        (string) $googleUser
                            ->getEmail()
                    )
                );


            if (
                $email ===
                ''
            ) {

                return redirect()
                    ->route(
                        'instructor-coordinator.login'
                    )
                    ->withErrors([
                        'login' =>
                            'Google did not provide an email address.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | University ID
            |--------------------------------------------------------------------------
            */

            $universityId =
                (int) (
                    $request
                        ->session()
                        ->get(
                            'google_staff_university_id'
                        )
                    ??
                    $request
                        ->session()
                        ->get(
                            'staff_university_id'
                        )
                    ??
                    0
                );


            /*
            |--------------------------------------------------------------------------
            | University Context Missing
            |--------------------------------------------------------------------------
            */

            if (
                !$universityId
            ) {

                Log::warning(
                    'Google Login: Staff callback lost university context.',
                    [
                        'google_email' =>
                            $email,

                        'session_id' =>
                            $request
                                ->session()
                                ->getId(),
                    ]
                );


                return redirect()
                    ->route(
                        'instructor-coordinator.access-code'
                    )
                    ->withErrors([
                        'access_code' =>
                            'Your university session expired. Please verify your University Access Code again.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Log Callback
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Google Login: Instructor / Coordinator callback.',
                [
                    'email' =>
                        $email,

                    'university_id' =>
                        $universityId,

                    'session_id' =>
                        $request
                            ->session()
                            ->getId(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Find Instructor
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
            | Find Coordinator
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
            | Same Email Exists In Both Tables
            |--------------------------------------------------------------------------
            */

            if (
                $instructor &&
                $coordinator
            ) {

                return redirect()
                    ->route(
                        'instructor-coordinator.login'
                    )
                    ->withErrors([
                        'login' =>
                            'This Google email is connected to both an Instructor and Coordinator account. Please contact your University Administrator.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Instructor
            |--------------------------------------------------------------------------
            */

            if (
                $instructor
            ) {

                return $this->loginInstructor(
                    request:
                        $request,

                    instructor:
                        $instructor
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Coordinator
            |--------------------------------------------------------------------------
            */

            if (
                $coordinator
            ) {

                return $this->loginCoordinator(
                    request:
                        $request,

                    coordinator:
                        $coordinator
                );
            }


            /*
            |--------------------------------------------------------------------------
            | No Matching Account
            |--------------------------------------------------------------------------
            */

            Log::warning(
                'Google Login: No Instructor / Coordinator matched Google account.',
                [
                    'email' =>
                        $email,

                    'university_id' =>
                        $universityId,
                ]
            );


            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'No Instructor or Coordinator account is connected to this Google email for the selected university.',
                ]);

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Log Exact Error
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Google Login: Instructor / Coordinator callback failed.',
                [
                    'exception' =>
                        get_class(
                            $exception
                        ),

                    'message' =>
                        $exception
                            ->getMessage(),

                    'file' =>
                        $exception
                            ->getFile(),

                    'line' =>
                        $exception
                            ->getLine(),

                    'staff_university_id' =>
                        $request
                            ->session()
                            ->get(
                                'staff_university_id'
                            ),

                    'google_staff_university_id' =>
                        $request
                            ->session()
                            ->get(
                                'google_staff_university_id'
                            ),

                    'session_id' =>
                        $request
                            ->session()
                            ->getId(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Staff callback ALWAYS returns to staff login on failure.
            |
            */

            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'Google authentication failed. Please try again.',
                ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Login Instructor
    |--------------------------------------------------------------------------
    */

    private function loginInstructor(
        Request $request,
        Instructor $instructor
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Instructor Active
        |--------------------------------------------------------------------------
        */

        if (
            !$this->accountIsActive(
                $instructor->status
            )
        ) {

            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'Your Instructor account is currently inactive.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Instructor Role
        |--------------------------------------------------------------------------
        */

        if (
            !$instructor
                ->isInstructor()
        ) {

            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'This account does not have the Instructor role.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Required
        |--------------------------------------------------------------------------
        */

        if (
            !$instructor->university
        ) {

            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'The university connected to this Instructor account could not be found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Active
        |--------------------------------------------------------------------------
        */

        if (
            !$this->accountIsActive(
                $instructor
                    ->university
                    ->status
            )
        ) {

            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'The university connected to this account is currently inactive.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Logout Other Guards
        |--------------------------------------------------------------------------
        */

        Auth::guard(
            'web'
        )->logout();


        Auth::guard(
            'university_admin'
        )->logout();


        Auth::guard(
            'coordinator'
        )->logout();


        /*
        |--------------------------------------------------------------------------
        | Login Instructor
        |--------------------------------------------------------------------------
        */

        Auth::guard(
            'instructor'
        )->login(
            $instructor,
            false
        );


        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Store Staff Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put([
                'staff_access_verified' =>
                    true,

                'staff_university_id' =>
                    $instructor
                        ->university
                        ->id,

                'staff_university_name' =>
                    $instructor
                        ->university
                        ->name,

                'staff_university_acronym' =>
                    $instructor
                        ->university
                        ->acronym,

                'staff_account_type' =>
                    'instructor',

                'staff_role' =>
                    'instructor',

                'staff_id' =>
                    $instructor->id,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Remove Temporary Google Context
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget([
                'google_staff_university_id',
                'google_login_portal',
                'google_login_university_id',
                'staff_logged_out_account',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Verify Guard
        |--------------------------------------------------------------------------
        */

        if (
            !Auth::guard(
                'instructor'
            )->check()
        ) {

            Log::error(
                'Google Login: Instructor guard failed.',
                [
                    'instructor_id' =>
                        $instructor->id,

                    'email' =>
                        $instructor->email,
                ]
            );


            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'Unable to create your Instructor login session.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Google Login: Instructor authenticated successfully.',
            [
                'instructor_id' =>
                    $instructor->id,

                'email' =>
                    $instructor->email,

                'university_id' =>
                    $instructor->university_id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Instructor / Coordinator Dashboard
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'instructor-coordinator.dashboard'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Login Coordinator
    |--------------------------------------------------------------------------
    */

    private function loginCoordinator(
        Request $request,
        Coordinator $coordinator
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Coordinator Active
        |--------------------------------------------------------------------------
        */

        if (
            !$this->accountIsActive(
                $coordinator->status
            )
        ) {

            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'Your Coordinator account is currently inactive.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator Role
        |--------------------------------------------------------------------------
        */

        $coordinatorRole =
            $this->getCoordinatorRole(
                $coordinator
            );


        if (
            !$coordinatorRole
        ) {

            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'This Coordinator account does not have a valid Coordinator role.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Required
        |--------------------------------------------------------------------------
        */

        if (
            !$coordinator->university
        ) {

            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'The university connected to this Coordinator account could not be found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Active
        |--------------------------------------------------------------------------
        */

        if (
            !$this->accountIsActive(
                $coordinator
                    ->university
                    ->status
            )
        ) {

            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'The university connected to this account is currently inactive.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Logout Other Guards
        |--------------------------------------------------------------------------
        */

        Auth::guard(
            'web'
        )->logout();


        Auth::guard(
            'university_admin'
        )->logout();


        Auth::guard(
            'instructor'
        )->logout();


        /*
        |--------------------------------------------------------------------------
        | Login Coordinator
        |--------------------------------------------------------------------------
        */

        Auth::guard(
            'coordinator'
        )->login(
            $coordinator,
            false
        );


        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Store Staff Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put([
                'staff_access_verified' =>
                    true,

                'staff_university_id' =>
                    $coordinator
                        ->university
                        ->id,

                'staff_university_name' =>
                    $coordinator
                        ->university
                        ->name,

                'staff_university_acronym' =>
                    $coordinator
                        ->university
                        ->acronym,

                'staff_account_type' =>
                    'coordinator',

                'staff_role' =>
                    $coordinatorRole,

                'staff_id' =>
                    $coordinator->id,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Remove Temporary Google Context
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget([
                'google_staff_university_id',
                'google_login_portal',
                'google_login_university_id',
                'staff_logged_out_account',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Verify Guard
        |--------------------------------------------------------------------------
        */

        if (
            !Auth::guard(
                'coordinator'
            )->check()
        ) {

            Log::error(
                'Google Login: Coordinator guard failed.',
                [
                    'coordinator_id' =>
                        $coordinator->id,

                    'email' =>
                        $coordinator->email,
                ]
            );


            return redirect()
                ->route(
                    'instructor-coordinator.login'
                )
                ->withErrors([
                    'login' =>
                        'Unable to create your Coordinator login session.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Google Login: Coordinator authenticated successfully.',
            [
                'coordinator_id' =>
                    $coordinator->id,

                'email' =>
                    $coordinator->email,

                'role' =>
                    $coordinatorRole,

                'university_id' =>
                    $coordinator->university_id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Instructor / Coordinator Dashboard
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'instructor-coordinator.dashboard'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Coordinator Role
    |--------------------------------------------------------------------------
    */

    private function getCoordinatorRole(
        Coordinator $coordinator
    ): ?string {

        if (
            $coordinator
                ->isAttendanceCoordinator()
        ) {

            return 'coordinator-attendance';
        }


        if (
            $coordinator
                ->isAnnouncementCoordinator()
        ) {

            return 'coordinator-announcement';
        }


        if (
            $coordinator
                ->isScheduleCoordinator()
        ) {

            return 'coordinator-schedule';
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Account Active
    |--------------------------------------------------------------------------
    */

    private function accountIsActive(
        mixed $status
    ): bool {

        return (
            strtolower(
                trim(
                    (string) $status
                )
            ) ===
            'active'
        );
    }
}