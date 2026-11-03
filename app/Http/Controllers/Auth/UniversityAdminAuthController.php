<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UniversityAdministrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UniversityAdminAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show University Administrator Login Page
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        return Inertia::render(
            'UniversityAdmin/LoginPage'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Login University Administrator
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Validate Login Information
        |--------------------------------------------------------------------------
        */

        $credentials =
            $request->validate(
                [
                    'email' => [
                        'required',
                        'email',
                    ],

                    'password' => [
                        'required',
                        'string',
                    ],
                ],
                [
                    'email.required' =>
                        'Please enter your email address.',

                    'email.email' =>
                        'Please enter a valid email address.',

                    'password.required' =>
                        'Please enter your password.',
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
                    $credentials['email']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | University Administrator Guard
        |--------------------------------------------------------------------------
        */

        $guard =
            Auth::guard(
                'university_admin'
            );


        /*
        |--------------------------------------------------------------------------
        | First Attempt: Normal Password
        |--------------------------------------------------------------------------
        |
        | Laravel checks the entered password against:
        |
        | university_administrators.password
        |
        | This is the normal and preferred authentication path.
        |
        */

        $normalLogin =
            $guard->attempt(
                [
                    'email' =>
                        $email,

                    'password' =>
                        $credentials['password'],
                ],

                $request->boolean(
                    'remember'
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Temporary Password Login
        |--------------------------------------------------------------------------
        |
        | If normal login failed, check whether:
        |
        | 1. University Administrator exists
        | 2. must_change_password = true
        | 3. temporary_password exists
        | 4. Entered password matches temporary_password
        |
        */

        if (
            !$normalLogin
        ) {

            /*
            |--------------------------------------------------------------------------
            | Find Administrator
            |--------------------------------------------------------------------------
            */

            $administrator =
                UniversityAdministrator::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [$email]
                    )
                    ->first();


            /*
            |--------------------------------------------------------------------------
            | Temporary Password Match
            |--------------------------------------------------------------------------
            */

            $temporaryPasswordMatches =
                false;


            if (
                $administrator
                &&
                $administrator
                    ->must_change_password
                &&
                !empty(
                    $administrator
                        ->temporary_password
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | temporary_password Is Automatically Decrypted
                |--------------------------------------------------------------------------
                |
                | Because UniversityAdministrator has:
                |
                | 'temporary_password' => 'encrypted'
                |
                */

                $storedTemporaryPassword =
                    (string)
                    $administrator
                        ->temporary_password;


                $enteredPassword =
                    (string)
                    $credentials[
                        'password'
                    ];


                $temporaryPasswordMatches =
                    hash_equals(
                        $storedTemporaryPassword,
                        $enteredPassword
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Invalid Normal + Temporary Password
            |--------------------------------------------------------------------------
            */

            if (
                !$administrator
                ||
                !$temporaryPasswordMatches
            ) {

                return back()
                    ->withErrors([
                        'login' =>
                            'Invalid email or password.',
                    ])
                    ->onlyInput(
                        'email'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Repair Main Password Hash If Necessary
            |--------------------------------------------------------------------------
            |
            | The temporary password should also be the current login password.
            |
            | If an older account has temporary_password saved but its main
            | password hash does not match, synchronize it now.
            |
            */

            if (
                !Hash::check(
                    $credentials[
                        'password'
                    ],
                    $administrator
                        ->getAuthPassword()
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | Model Automatically Hashes Password
                |--------------------------------------------------------------------------
                |
                | UniversityAdministrator uses:
                |
                | 'password' => 'hashed'
                |
                */

                $administrator->password =
                    $credentials[
                        'password'
                    ];


                $administrator->save();
            }


            /*
            |--------------------------------------------------------------------------
            | Login Using Temporary Password
            |--------------------------------------------------------------------------
            */

            $guard->login(
                $administrator,

                $request->boolean(
                    'remember'
                )
            );
        }


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
        | Remove Temporary Logout Account Session Data
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget(
                'university_admin_logout_account'
            );


        /*
        |--------------------------------------------------------------------------
        | Redirect To Dashboard
        |--------------------------------------------------------------------------
        |
        | We do NOT force a password change.
        |
        | University Administrator may continue using the temporary password
        | until they intentionally change their password.
        |
        */

        return redirect()
            ->route(
                'university-admin.dashboard'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Display Change Password Page
    |--------------------------------------------------------------------------
    */

    public function showChangePassword(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Get University Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            Auth::guard(
                'university_admin'
            )->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication Required
        |--------------------------------------------------------------------------
        */

        if (
            !$admin
        ) {

            return redirect()
                ->route(
                    'university-admin.login'
                )
                ->with(
                    'error',
                    'Please log in first.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Render Change Password Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Profile/ChangePassUA'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update University Administrator Password
    |--------------------------------------------------------------------------
    */

    public function updatePassword(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Get Authenticated Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            Auth::guard(
                'university_admin'
            )->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication Required
        |--------------------------------------------------------------------------
        */

        if (
            !$admin
        ) {

            return redirect()
                ->route(
                    'university-admin.login'
                )
                ->with(
                    'error',
                    'Please log in first.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Password
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    /*
                    |--------------------------------------------------------------------------
                    | Current Password
                    |--------------------------------------------------------------------------
                    |
                    | This also works after temporary-password login because the
                    | main password hash is synchronized during authentication.
                    |
                    */

                    'current_password' => [
                        'required',
                        'string',
                        'current_password:university_admin',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | New Password
                    |--------------------------------------------------------------------------
                    */

                    'password' => [
                        'required',
                        'string',

                        Password::min(
                            8
                        ),

                        'confirmed',
                    ],
                ],
                [
                    'current_password.required' =>
                        'Please enter your current password.',

                    'current_password.current_password' =>
                        'The current password you entered is incorrect.',

                    'password.required' =>
                        'Please enter your new password.',

                    'password.min' =>
                        'The new password must contain at least 8 characters.',

                    'password.confirmed' =>
                        'The password confirmation does not match.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Prevent Reusing Current Password
        |--------------------------------------------------------------------------
        */

        if (
            Hash::check(
                $validated[
                    'password'
                ],
                $admin
                    ->getAuthPassword()
            )
        ) {

            return back()
                ->withErrors([
                    'password' =>
                        'Your new password must be different from your current password.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Save New Password
        |--------------------------------------------------------------------------
        |
        | UniversityAdministrator has:
        |
        | 'password' => 'hashed'
        |
        | Therefore we assign the plain new password and Laravel hashes it.
        |
        */

        $admin->password =
            $validated[
                'password'
            ];


        /*
        |--------------------------------------------------------------------------
        | Remove Temporary Password
        |--------------------------------------------------------------------------
        |
        | Once the Administrator chooses a new password:
        |
        | temporary_password = NULL
        |
        | Therefore the original temporary credential can never be used again.
        |
        */

        $admin->temporary_password =
            null;


        /*
        |--------------------------------------------------------------------------
        | Password Is No Longer Temporary
        |--------------------------------------------------------------------------
        */

        $admin->must_change_password =
            false;


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $admin->save();


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
        | Redirect To Profile
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.profile'
            )
            ->with(
                'success',
                'Your password has been changed successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | University Administrator Logout Page
    |--------------------------------------------------------------------------
    */

    public function logoutPage(
        Request $request
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Get Last Logged-Out Account
        |--------------------------------------------------------------------------
        */

        $logoutAccount =
            $request
                ->session()
                ->get(
                    'university_admin_logout_account'
                );


        /*
        |--------------------------------------------------------------------------
        | Prepare Accounts
        |--------------------------------------------------------------------------
        */

        $accounts = [];


        if (
            $logoutAccount
        ) {

            $accounts[] =
                $logoutAccount;
        }


        /*
        |--------------------------------------------------------------------------
        | Render Logout Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/LogoutPage',
            [
                'accounts' =>
                    $accounts,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Logout University Administrator
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Get Administrator Before Logout
        |--------------------------------------------------------------------------
        */

        $admin =
            Auth::guard(
                'university_admin'
            )->user();


        /*
        |--------------------------------------------------------------------------
        | Safe Logout Account Information
        |--------------------------------------------------------------------------
        */

        $logoutAccount =
            null;


        if (
            $admin
        ) {

            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            */

            $admin->loadMissing(
                'university'
            );


            /*
            |--------------------------------------------------------------------------
            | Administrator Name
            |--------------------------------------------------------------------------
            */

            $name =
                trim(
                    collect([
                        $admin->first_name,

                        $admin->middle_name,

                        $admin->last_name,
                    ])
                        ->filter()
                        ->implode(
                            ' '
                        )
                );


            /*
            |--------------------------------------------------------------------------
            | Fallback Name
            |--------------------------------------------------------------------------
            */

            if (
                $name === ''
            ) {

                $name =
                    $admin->username
                    ?:
                    $admin->email;
            }


            /*
            |--------------------------------------------------------------------------
            | Safe Account Data
            |--------------------------------------------------------------------------
            |
            | Never include:
            |
            | password
            | temporary_password
            | remember_token
            |
            */

            $logoutAccount = [
                'id' =>
                    $admin->id,

                'name' =>
                    $name,

                'email' =>
                    $admin->email,

                'photo' =>
                    $admin->photo,

                'university_name' =>
                    $admin->university
                        ? $admin
                            ->university
                            ->name
                        : null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Auth::guard(
            'university_admin'
        )->logout();


        /*
        |--------------------------------------------------------------------------
        | Invalidate Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->invalidate();


        /*
        |--------------------------------------------------------------------------
        | Regenerate CSRF Token
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerateToken();


        /*
        |--------------------------------------------------------------------------
        | Store Safe Account After Logout
        |--------------------------------------------------------------------------
        */

        if (
            $logoutAccount
        ) {

            $request
                ->session()
                ->put(
                    'university_admin_logout_account',
                    $logoutAccount
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Redirect To Logout Page
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.logout.page'
            );
    }
}