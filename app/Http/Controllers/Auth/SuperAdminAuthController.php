<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class SuperAdminAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Super Administrator Login Page
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render('SuperAdmin/SuperAdminLogin');
    }


    /*
    |--------------------------------------------------------------------------
    | Super Administrator Registration Page
    |--------------------------------------------------------------------------
    |
    | Registration is available only in the local environment.
    |
    | Public registration must not be available in production because
    | Super Administrators have access to sensitive university data.
    |
    */

    public function createAccount()
    {
        abort_unless(
            app()->environment('local'),
            404
        );

        return Inertia::render(
            'SuperAdmin/SuperAdminCreateAcc'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticate Super Administrator
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
            ],
        ]);

        $superAdmin = SuperAdmin::where(
            'email',
            $request->email
        )->first();


        /*
        |--------------------------------------------------------------------------
        | Email Does Not Exist
        |--------------------------------------------------------------------------
        */

        if (!$superAdmin) {

            if ($request->expectsJson()) {

                return response()->json([
                    'errors' => [
                        'email' => [
                            'The email does not exist.',
                        ],
                    ],
                ], 422);
            }

            return back()->withErrors([
                'email' => 'The email does not exist.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Incorrect Password
        |--------------------------------------------------------------------------
        */

        if (
            !Hash::check(
                $request->password,
                $superAdmin->password
            )
        ) {

            if ($request->expectsJson()) {

                return response()->json([
                    'errors' => [
                        'password' => [
                            'Incorrect password.',
                        ],
                    ],
                ], 422);
            }

            return back()->withErrors([
                'password' => 'Incorrect password.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Login Super Administrator
        |--------------------------------------------------------------------------
        */

        Auth::guard('superadmin')->login(
            $superAdmin,
            $request->boolean('remember')
        );

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | JSON Response
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {

            return response()->json([
                'success' => true,

                'redirect' => route(
                    'superadmin.dashboard'
                ),

                'user' => [
                    'id' => $superAdmin->id,
                    'name' => $superAdmin->name,
                    'email' => $superAdmin->email,
                    'photo' => $superAdmin->photo,
                ],
            ]);
        }

        return redirect()->route(
            'superadmin.dashboard'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Register Super Administrator
    |--------------------------------------------------------------------------
    |
    | Registration is restricted to the local environment.
    |
    | Production Super Administrator accounts must be provisioned
    | through an authorized administrative process.
    |
    */

    public function register(Request $request)
    {
        abort_unless(
            app()->environment('local'),
            404
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'required',
                'string',
                'max:255',
                'unique:super_admins,username',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:super_admins,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Create Super Administrator Account
        |--------------------------------------------------------------------------
        */

        SuperAdmin::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],

            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        return redirect()
            ->route('superadmin.login')
            ->with(
                'success',
                'Super Admin account created successfully. Please log in.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Super Administrator Password
    |--------------------------------------------------------------------------
    */

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => [
                'required',
            ],

            'password' => [
                'required',
                'min:8',
                'confirmed',
            ],
        ]);

        $superAdmin = Auth::guard(
            'superadmin'
        )->user();


        /*
        |--------------------------------------------------------------------------
        | Verify Authentication
        |--------------------------------------------------------------------------
        */

        if (!$superAdmin) {

            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Verify Current Password
        |--------------------------------------------------------------------------
        */

        if (
            !Hash::check(
                $request->current_password,
                $superAdmin->password
            )
        ) {

            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        */

        $superAdmin->password = Hash::make(
            $request->password
        );

        $superAdmin->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Logout Super Administrator
    |--------------------------------------------------------------------------
    |
    | Returns a snapshot of the signed-out Super Administrator account.
    |
    | This allows the frontend to maintain its account chooser even
    | when localStorage is empty or Google authentication was used.
    |
    */

    public function destroy(Request $request)
    {
        $superAdmin = Auth::guard(
            'superadmin'
        )->user();

        $account = null;


        /*
        |--------------------------------------------------------------------------
        | Prepare Account Snapshot
        |--------------------------------------------------------------------------
        */

        if ($superAdmin) {

            $account = [
                'id' => $superAdmin->id,

                'name' => $superAdmin->name,

                'username' => $superAdmin->username,

                'email' => $superAdmin->email,

                'photo' => $superAdmin->photo ?? null,

                'profile_photo' =>
                    $superAdmin->profile_photo ?? null,

                'account_type' => 'superadmin',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Logout and Invalidate Session
        |--------------------------------------------------------------------------
        */

        Auth::guard('superadmin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();


        /*
        |--------------------------------------------------------------------------
        | JSON Logout Response
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {

            return response()->json([
                'success' => true,

                'account' => $account,

                'redirect' => route(
                    'superadmin.logout'
                ),
            ]);
        }

        return redirect()->route(
            'superadmin.logout'
        );
    }
}
