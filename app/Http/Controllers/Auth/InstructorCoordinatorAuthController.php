<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class InstructorCoordinatorAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Access Code Page
    |--------------------------------------------------------------------------
    */

    public function accessCode(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Instructor Already Logged In
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'instructor'
            )->check()
        ) {

            return redirect()->route(
                'instructor-coordinator.dashboard'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator Already Logged In
        |--------------------------------------------------------------------------
        */

        if (
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
        | Render Access Code
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/AccessCode'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify University Access Code
    |--------------------------------------------------------------------------
    */

    public function verifyAccessCode(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'access_code' => [
                        'required',
                        'string',
                        'max:255',
                    ],
                ],
                [
                    'access_code.required' =>
                        'Please enter your University Access Code.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize Access Code
        |--------------------------------------------------------------------------
        */

        $accessCode =
            trim(
                $validated['access_code']
            );


        /*
        |--------------------------------------------------------------------------
        | Find University
        |--------------------------------------------------------------------------
        */

        $university =
            University::query()
                ->where(
                    'access_code',
                    $accessCode
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Invalid Access Code
        |--------------------------------------------------------------------------
        */

        if (
            !$university
        ) {

            return back()
                ->withErrors([
                    'access_code' =>
                        'The University Access Code you entered is invalid.',
                ])
                ->onlyInput(
                    'access_code'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | University Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !$this->accountIsActive(
                $university->status
            )
        ) {

            return back()
                ->withErrors([
                    'access_code' =>
                        'This university is currently inactive.',
                ])
                ->onlyInput(
                    'access_code'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Save University In Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put([
                'staff_access_verified' =>
                    true,

                'staff_university_id' =>
                    $university->id,

                'staff_university_name' =>
                    $university->name,

                'staff_university_acronym' =>
                    $university->acronym,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Redirect To Login
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'instructor-coordinator.login'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Login Page
    |--------------------------------------------------------------------------
    */

    public function loginPage(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Instructor Already Logged In
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'instructor'
            )->check()
        ) {

            return redirect()->route(
                'instructor-coordinator.dashboard'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator Already Logged In
        |--------------------------------------------------------------------------
        */

        if (
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
        | Access Code Must Be Verified
        |--------------------------------------------------------------------------
        */

        if (
            !$request
                ->session()
                ->get(
                    'staff_access_verified',
                    false
                )
        ) {

            return redirect()->route(
                'instructor-coordinator.access-code'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | University ID
        |--------------------------------------------------------------------------
        */

        $universityId =
            $request
                ->session()
                ->get(
                    'staff_university_id'
                );


        /*
        |--------------------------------------------------------------------------
        | Missing University
        |--------------------------------------------------------------------------
        */

        if (
            !$universityId
        ) {

            $this->clearStaffSession(
                $request
            );


            return redirect()->route(
                'instructor-coordinator.access-code'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Find University
        |--------------------------------------------------------------------------
        */

        $university =
            University::find(
                $universityId
            );


        /*
        |--------------------------------------------------------------------------
        | University Not Found
        |--------------------------------------------------------------------------
        */

        if (
            !$university
        ) {

            $this->clearStaffSession(
                $request
            );


            return redirect()
                ->route(
                    'instructor-coordinator.access-code'
                )
                ->withErrors([
                    'access_code' =>
                        'The selected university could not be found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !$this->accountIsActive(
                $university->status
            )
        ) {

            $this->clearStaffSession(
                $request
            );


            return redirect()
                ->route(
                    'instructor-coordinator.access-code'
                )
                ->withErrors([
                    'access_code' =>
                        'This university is currently inactive.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Render Login
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/LoginPage',
            [
                'university' => [

                    'id' =>
                        $university->id,

                    'name' =>
                        $university->name,

                    'acronym' =>
                        $university->acronym,

                    'logo' =>
                        $university->logo,

                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    |
    | Authentication order:
    |
    | 1. Instructor
    | 2. Coordinator
    |
    */

    public function login(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Access Code Must Be Verified
        |--------------------------------------------------------------------------
        */

        if (
            !$request
                ->session()
                ->get(
                    'staff_access_verified',
                    false
                )
        ) {

            return redirect()->route(
                'instructor-coordinator.access-code'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | University ID
        |--------------------------------------------------------------------------
        */

        $universityId =
            $request
                ->session()
                ->get(
                    'staff_university_id'
                );


        /*
        |--------------------------------------------------------------------------
        | Missing University
        |--------------------------------------------------------------------------
        */

        if (
            !$universityId
        ) {

            $this->clearStaffSession(
                $request
            );


            return redirect()->route(
                'instructor-coordinator.access-code'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Find University
        |--------------------------------------------------------------------------
        */

        $university =
            University::find(
                $universityId
            );


        /*
        |--------------------------------------------------------------------------
        | University Not Found
        |--------------------------------------------------------------------------
        */

        if (
            !$university
        ) {

            $this->clearStaffSession(
                $request
            );


            return redirect()
                ->route(
                    'instructor-coordinator.access-code'
                )
                ->withErrors([
                    'access_code' =>
                        'The selected university could not be found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !$this->accountIsActive(
                $university->status
            )
        ) {

            $this->clearStaffSession(
                $request
            );


            return redirect()
                ->route(
                    'instructor-coordinator.access-code'
                )
                ->withErrors([
                    'access_code' =>
                        'This university is currently inactive.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Credentials
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'email' => [
                        'required',
                        'email',
                        'max:255',
                    ],

                    'password' => [
                        'required',
                        'string',
                    ],

                    'remember' => [
                        'nullable',
                        'boolean',
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
                    $validated['email']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        |
        | Never trim or alter a password.
        |
        */

        $password =
            $validated['password'];


        /*
        |--------------------------------------------------------------------------
        | Remember
        |--------------------------------------------------------------------------
        */

        $remember =
            (bool) (
                $validated['remember'] ??
                false
            );


        /*
        |--------------------------------------------------------------------------
        | Search Instructor
        |--------------------------------------------------------------------------
        */

        $instructor =
            Instructor::query()
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
        | Instructor Found
        |--------------------------------------------------------------------------
        */

        if (
            $instructor
        ) {

            /*
            |--------------------------------------------------------------------------
            | Password
            |--------------------------------------------------------------------------
            */

            if (
                !Hash::check(
                    $password,
                    $instructor->password
                )
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
            | Instructor Active
            |--------------------------------------------------------------------------
            */

            if (
                !$this->accountIsActive(
                    $instructor->status
                )
            ) {

                return back()
                    ->withErrors([
                        'login' =>
                            'Your Instructor account is currently inactive.',
                    ])
                    ->onlyInput(
                        'email'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Instructor Role
            |--------------------------------------------------------------------------
            */

            if (
                !$instructor->isInstructor()
            ) {

                return back()
                    ->withErrors([
                        'login' =>
                            'This account does not have the Instructor role.',
                    ])
                    ->onlyInput(
                        'email'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Logout Other Guard
            |--------------------------------------------------------------------------
            */

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
                $remember
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

            $this->storeStaffSession(
                request:
                    $request,

                university:
                    $university,

                accountType:
                    'instructor',

                role:
                    'instructor',

                staffId:
                    $instructor->id
            );


            /*
            |--------------------------------------------------------------------------
            | Remove Logout Transfer
            |--------------------------------------------------------------------------
            */

            $request
                ->session()
                ->forget(
                    'staff_logged_out_account'
                );


            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            return redirect()->route(
                'instructor-coordinator.dashboard'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Search Coordinator
        |--------------------------------------------------------------------------
        */

        $coordinator =
            Coordinator::query()
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
        | Coordinator Found
        |--------------------------------------------------------------------------
        */

        if (
            $coordinator
        ) {

            /*
            |--------------------------------------------------------------------------
            | Password
            |--------------------------------------------------------------------------
            */

            if (
                !Hash::check(
                    $password,
                    $coordinator->password
                )
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
            | Coordinator Active
            |--------------------------------------------------------------------------
            */

            if (
                !$this->accountIsActive(
                    $coordinator->status
                )
            ) {

                return back()
                    ->withErrors([
                        'login' =>
                            'Your Coordinator account is currently inactive.',
                    ])
                    ->onlyInput(
                        'email'
                    );
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

                return back()
                    ->withErrors([
                        'login' =>
                            'This Coordinator account does not have a valid Coordinator role.',
                    ])
                    ->onlyInput(
                        'email'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Logout Other Guard
            |--------------------------------------------------------------------------
            */

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
                $remember
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

            $this->storeStaffSession(
                request:
                    $request,

                university:
                    $university,

                accountType:
                    'coordinator',

                role:
                    $coordinatorRole,

                staffId:
                    $coordinator->id
            );


            /*
            |--------------------------------------------------------------------------
            | Remove Logout Transfer
            |--------------------------------------------------------------------------
            */

            $request
                ->session()
                ->forget(
                    'staff_logged_out_account'
                );


            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            return redirect()->route(
                'instructor-coordinator.dashboard'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Invalid Credentials
        |--------------------------------------------------------------------------
        */

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
    | Login Saved Account
    |--------------------------------------------------------------------------
    |
    | Used by LogoutPage.vue.
    |
    | Existing account:
    |
    | Click account
    |      ↓
    | Enter password
    |      ↓
    | Login directly
    |
    */

    public function loginSavedAccount(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Already Authenticated
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
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'id' => [
                        'required',
                        'integer',
                    ],

                    'account_type' => [
                        'required',
                        'string',
                        'in:instructor,coordinator',
                    ],

                    'email' => [
                        'required',
                        'email',
                        'max:255',
                    ],

                    'password' => [
                        'required',
                        'string',
                    ],

                    'remember' => [
                        'nullable',
                        'boolean',
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

                    'password.required' =>
                        'Please enter your password.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize Data
        |--------------------------------------------------------------------------
        */

        $accountId =
            (int) $validated['id'];


        $accountType =
            strtolower(
                trim(
                    $validated['account_type']
                )
            );


        $email =
            strtolower(
                trim(
                    $validated['email']
                )
            );


        $password =
            $validated['password'];


        $remember =
            (bool) (
                $validated['remember'] ??
                true
            );


        /*
        |--------------------------------------------------------------------------
        | Instructor Saved Account
        |--------------------------------------------------------------------------
        */

        if (
            $accountType ===
            'instructor'
        ) {

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
                        'id',
                        $accountId
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
            | Not Found
            |--------------------------------------------------------------------------
            */

            if (
                !$instructor
            ) {

                return back()->withErrors([
                    'login' =>
                        'The selected Instructor account could not be found.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Password
            |--------------------------------------------------------------------------
            */

            if (
                !Hash::check(
                    $password,
                    $instructor->password
                )
            ) {

                return back()->withErrors([
                    'login' =>
                        'Invalid password.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Active
            |--------------------------------------------------------------------------
            */

            if (
                !$this->accountIsActive(
                    $instructor->status
                )
            ) {

                return back()->withErrors([
                    'login' =>
                        'Your Instructor account is currently inactive.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Role
            |--------------------------------------------------------------------------
            */

            if (
                !$instructor->isInstructor()
            ) {

                return back()->withErrors([
                    'login' =>
                        'This account does not have the Instructor role.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            */

            if (
                !$instructor->university
            ) {

                return back()->withErrors([
                    'login' =>
                        'The university connected to this account could not be found.',
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

                return back()->withErrors([
                    'login' =>
                        'The university connected to this account is currently inactive.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Logout Other Guard
            |--------------------------------------------------------------------------
            */

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
                $remember
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
            | Restore Session
            |--------------------------------------------------------------------------
            */

            $this->storeStaffSession(
                request:
                    $request,

                university:
                    $instructor->university,

                accountType:
                    'instructor',

                role:
                    'instructor',

                staffId:
                    $instructor->id
            );


            /*
            |--------------------------------------------------------------------------
            | Remove Logout Transfer
            |--------------------------------------------------------------------------
            */

            $request
                ->session()
                ->forget(
                    'staff_logged_out_account'
                );


            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            return redirect()->route(
                'instructor-coordinator.dashboard'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator Saved Account
        |--------------------------------------------------------------------------
        */

        $coordinator =
            Coordinator::query()
                ->with(
                    'university'
                )
                ->where(
                    'id',
                    $accountId
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
        | Not Found
        |--------------------------------------------------------------------------
        */

        if (
            !$coordinator
        ) {

            return back()->withErrors([
                'login' =>
                    'The selected Coordinator account could not be found.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        */

        if (
            !Hash::check(
                $password,
                $coordinator->password
            )
        ) {

            return back()->withErrors([
                'login' =>
                    'Invalid password.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Active
        |--------------------------------------------------------------------------
        */

        if (
            !$this->accountIsActive(
                $coordinator->status
            )
        ) {

            return back()->withErrors([
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

            return back()->withErrors([
                'login' =>
                    'This Coordinator account does not have a valid Coordinator role.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        if (
            !$coordinator->university
        ) {

            return back()->withErrors([
                'login' =>
                    'The university connected to this account could not be found.',
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

            return back()->withErrors([
                'login' =>
                    'The university connected to this account is currently inactive.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Logout Other Guard
        |--------------------------------------------------------------------------
        */

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
            $remember
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
        | Restore Session
        |--------------------------------------------------------------------------
        */

        $this->storeStaffSession(
            request:
                $request,

            university:
                $coordinator->university,

            accountType:
                'coordinator',

            role:
                $coordinatorRole,

            staffId:
                $coordinator->id
        );


        /*
        |--------------------------------------------------------------------------
        | Remove Logout Transfer
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget(
                'staff_logged_out_account'
            );


        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'instructor-coordinator.dashboard'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'instructor'
            )->check()
        ) {

            /** @var Instructor|null $instructor */

            $instructor =
                Auth::guard(
                    'instructor'
                )->user();


            /*
            |--------------------------------------------------------------------------
            | Missing Instructor
            |--------------------------------------------------------------------------
            */

            if (
                !$instructor
            ) {

                return redirect()->route(
                    'instructor-coordinator.access-code'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Role
            |--------------------------------------------------------------------------
            */

            if (
                !$instructor->isInstructor()
            ) {

                Auth::guard(
                    'instructor'
                )->logout();


                $request
                    ->session()
                    ->forget([
                        'staff_account_type',
                        'staff_role',
                        'staff_id',
                    ]);


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
            | Active
            |--------------------------------------------------------------------------
            */

            if (
                !$this->accountIsActive(
                    $instructor->status
                )
            ) {

                Auth::guard(
                    'instructor'
                )->logout();


                $request
                    ->session()
                    ->forget([
                        'staff_account_type',
                        'staff_role',
                        'staff_id',
                    ]);


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
            | University
            |--------------------------------------------------------------------------
            */

            $instructor->load(
                'university'
            );


            if (
                !$instructor->university
            ) {

                Auth::guard(
                    'instructor'
                )->logout();


                $this->clearStaffSession(
                    $request
                );


                return redirect()
                    ->route(
                        'instructor-coordinator.access-code'
                    )
                    ->withErrors([
                        'access_code' =>
                            'The university connected to this account could not be found.',
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

                Auth::guard(
                    'instructor'
                )->logout();


                $this->clearStaffSession(
                    $request
                );


                return redirect()
                    ->route(
                        'instructor-coordinator.access-code'
                    )
                    ->withErrors([
                        'access_code' =>
                            'The university connected to this account is currently inactive.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Render Dashboard
            |--------------------------------------------------------------------------
            */

            return Inertia::render(
                'InstructorCoordinators/Dashboard',
                [
                    'user' =>
                        $instructor,

                    'role' =>
                        'instructor',

                    'accountType' =>
                        'instructor',

                    'auth' => [

                        'user' =>
                            $instructor,

                        'university' =>
                            $instructor->university,

                        'role' =>
                            'instructor',

                        'account_type' =>
                            'instructor',

                    ],
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'coordinator'
            )->check()
        ) {

            /** @var Coordinator|null $coordinator */

            $coordinator =
                Auth::guard(
                    'coordinator'
                )->user();


            /*
            |--------------------------------------------------------------------------
            | Missing Coordinator
            |--------------------------------------------------------------------------
            */

            if (
                !$coordinator
            ) {

                return redirect()->route(
                    'instructor-coordinator.access-code'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Active
            |--------------------------------------------------------------------------
            */

            if (
                !$this->accountIsActive(
                    $coordinator->status
                )
            ) {

                Auth::guard(
                    'coordinator'
                )->logout();


                $request
                    ->session()
                    ->forget([
                        'staff_account_type',
                        'staff_role',
                        'staff_id',
                    ]);


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
            | Role
            |--------------------------------------------------------------------------
            */

            $coordinatorRole =
                $this->getCoordinatorRole(
                    $coordinator
                );


            if (
                !$coordinatorRole
            ) {

                Auth::guard(
                    'coordinator'
                )->logout();


                $request
                    ->session()
                    ->forget([
                        'staff_account_type',
                        'staff_role',
                        'staff_id',
                    ]);


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
            | University
            |--------------------------------------------------------------------------
            */

            $coordinator->load(
                'university'
            );


            if (
                !$coordinator->university
            ) {

                Auth::guard(
                    'coordinator'
                )->logout();


                $this->clearStaffSession(
                    $request
                );


                return redirect()
                    ->route(
                        'instructor-coordinator.access-code'
                    )
                    ->withErrors([
                        'access_code' =>
                            'The university connected to this account could not be found.',
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

                Auth::guard(
                    'coordinator'
                )->logout();


                $this->clearStaffSession(
                    $request
                );


                return redirect()
                    ->route(
                        'instructor-coordinator.access-code'
                    )
                    ->withErrors([
                        'access_code' =>
                            'The university connected to this account is currently inactive.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Render Dashboard
            |--------------------------------------------------------------------------
            */

            return Inertia::render(
                'InstructorCoordinators/Dashboard',
                [
                    'user' =>
                        $coordinator,

                    'role' =>
                        $coordinatorRole,

                    'accountType' =>
                        'coordinator',

                    'auth' => [

                        'user' =>
                            $coordinator,

                        'university' =>
                            $coordinator->university,

                        'role' =>
                            $coordinatorRole,

                        'account_type' =>
                            'coordinator',

                    ],
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Not Authenticated
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'instructor-coordinator.access-code'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Logout / Account Chooser Page
    |--------------------------------------------------------------------------
    */

    public function logoutPage(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Still Logged In
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
        | Last Logged-Out Account
        |--------------------------------------------------------------------------
        */

        $loggedOutAccount =
            $request
                ->session()
                ->get(
                    'staff_logged_out_account'
                );


        /*
        |--------------------------------------------------------------------------
        | Accounts
        |--------------------------------------------------------------------------
        */

        $accounts =
            [];


        if (
            is_array(
                $loggedOutAccount
            )
        ) {

            $accounts[] =
                $loggedOutAccount;

        }


        /*
        |--------------------------------------------------------------------------
        | Render LogoutPage.vue
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/LogoutPage',
            [
                'accounts' =>
                    $accounts,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | After logging out we preserve:
    |
    | staff_access_verified
    | staff_university_id
    | staff_university_name
    | staff_university_acronym
    |
    | This allows:
    |
    | Use another account
    |        ↓
    | /instructor-coordinator/login
    |
    | without asking for the university access code again.
    |
    */

    public function logout(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Logged-Out Account
        |--------------------------------------------------------------------------
        */

        $loggedOutAccount =
            null;


        /*
        |--------------------------------------------------------------------------
        | University Context
        |--------------------------------------------------------------------------
        */

        $universityId =
            null;


        $universityName =
            null;


        $universityAcronym =
            null;


        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'instructor'
            )->check()
        ) {

            /** @var Instructor|null $instructor */

            $instructor =
                Auth::guard(
                    'instructor'
                )->user();


            if (
                $instructor
            ) {

                /*
                |--------------------------------------------------------------------------
                | Load University
                |--------------------------------------------------------------------------
                */

                $instructor->loadMissing(
                    'university'
                );


                /*
                |--------------------------------------------------------------------------
                | Preserve University Context
                |--------------------------------------------------------------------------
                */

                $universityId =
                    $instructor->university_id;


                $universityName =
                    $instructor
                        ->university
                        ?->name;


                $universityAcronym =
                    $instructor
                        ->university
                        ?->acronym;


                /*
                |--------------------------------------------------------------------------
                | Saved Account
                |--------------------------------------------------------------------------
                */

                $loggedOutAccount = [

                    'id' =>
                        $instructor->id,

                    'account_type' =>
                        'instructor',

                    'role' =>
                        'instructor',

                    'full_name' =>
                        $instructor->full_name,

                    'name' =>
                        $instructor->full_name,

                    'email' =>
                        $instructor->email,

                    'profile_photo' =>
                        $instructor->profile_photo,

                    'photo' =>
                        $instructor->profile_photo,

                    'component' =>
                        $instructor->component,

                    'university_id' =>
                        $instructor->university_id,

                    'university_name' =>
                        $universityName,

                    'university_acronym' =>
                        $universityAcronym,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Logout Instructor
            |--------------------------------------------------------------------------
            */

            Auth::guard(
                'instructor'
            )->logout();

        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator
        |--------------------------------------------------------------------------
        */

        elseif (
            Auth::guard(
                'coordinator'
            )->check()
        ) {

            /** @var Coordinator|null $coordinator */

            $coordinator =
                Auth::guard(
                    'coordinator'
                )->user();


            if (
                $coordinator
            ) {

                /*
                |--------------------------------------------------------------------------
                | Load University
                |--------------------------------------------------------------------------
                */

                $coordinator->loadMissing(
                    'university'
                );


                /*
                |--------------------------------------------------------------------------
                | Coordinator Role
                |--------------------------------------------------------------------------
                */

                $coordinatorRole =
                    $this->getCoordinatorRole(
                        $coordinator
                    );


                /*
                |--------------------------------------------------------------------------
                | Preserve University Context
                |--------------------------------------------------------------------------
                */

                $universityId =
                    $coordinator->university_id;


                $universityName =
                    $coordinator
                        ->university
                        ?->name;


                $universityAcronym =
                    $coordinator
                        ->university
                        ?->acronym;


                /*
                |--------------------------------------------------------------------------
                | Saved Account
                |--------------------------------------------------------------------------
                */

                $loggedOutAccount = [

                    'id' =>
                        $coordinator->id,

                    'account_type' =>
                        'coordinator',

                    'role' =>
                        $coordinatorRole,

                    'full_name' =>
                        $coordinator->full_name,

                    'name' =>
                        $coordinator->full_name,

                    'email' =>
                        $coordinator->email,

                    'profile_photo' =>
                        $coordinator->profile_photo,

                    'photo' =>
                        $coordinator->profile_photo,

                    'component' =>
                        $coordinator->component,

                    'university_id' =>
                        $coordinator->university_id,

                    'university_name' =>
                        $universityName,

                    'university_acronym' =>
                        $universityAcronym,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Logout Coordinator
            |--------------------------------------------------------------------------
            */

            Auth::guard(
                'coordinator'
            )->logout();

        }


        /*
        |--------------------------------------------------------------------------
        | Clear Authenticated Staff Details
        |--------------------------------------------------------------------------
        */

        $this->clearStaffSession(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Invalidate Previous Authenticated Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->invalidate();


        /*
        |--------------------------------------------------------------------------
        | Generate New CSRF Token
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerateToken();


        /*
        |--------------------------------------------------------------------------
        | Save Logged-Out Account
        |--------------------------------------------------------------------------
        |
        | No password is saved.
        |
        */

        if (
            $loggedOutAccount
        ) {

            $request
                ->session()
                ->put(
                    'staff_logged_out_account',
                    $loggedOutAccount
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Restore University Login Context
        |--------------------------------------------------------------------------
        |
        | This is what allows "Use another account" to go directly
        | to LoginPage.vue.
        |
        */

        if (
            $universityId
        ) {

            $request
                ->session()
                ->put([
                    'staff_access_verified' =>
                        true,

                    'staff_university_id' =>
                        $universityId,

                    'staff_university_name' =>
                        $universityName,

                    'staff_university_acronym' =>
                        $universityAcronym,
                ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Redirect To Logout Page
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'instructor-coordinator.logout-page'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Coordinator Role
    |--------------------------------------------------------------------------
    */

    private function getCoordinatorRole(
        Coordinator $coordinator
    ): ?string {

        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        if (
            $coordinator
                ->isAttendanceCoordinator()
        ) {

            return
                'coordinator-attendance';

        }


        /*
        |--------------------------------------------------------------------------
        | Announcement
        |--------------------------------------------------------------------------
        */

        if (
            $coordinator
                ->isAnnouncementCoordinator()
        ) {

            return
                'coordinator-announcement';

        }


        /*
        |--------------------------------------------------------------------------
        | Schedule
        |--------------------------------------------------------------------------
        */

        if (
            $coordinator
                ->isScheduleCoordinator()
        ) {

            return
                'coordinator-schedule';

        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Store Staff Session
    |--------------------------------------------------------------------------
    */

    private function storeStaffSession(
        Request $request,
        University $university,
        string $accountType,
        string $role,
        int $staffId
    ): void {

        $request
            ->session()
            ->put([
                'staff_access_verified' =>
                    true,

                'staff_university_id' =>
                    $university->id,

                'staff_university_name' =>
                    $university->name,

                'staff_university_acronym' =>
                    $university->acronym,

                'staff_account_type' =>
                    $accountType,

                'staff_role' =>
                    $role,

                'staff_id' =>
                    $staffId,
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Account Status
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


    /*
    |--------------------------------------------------------------------------
    | Clear Staff Session
    |--------------------------------------------------------------------------
    */

    private function clearStaffSession(
        Request $request
    ): void {

        $request
            ->session()
            ->forget([
                'staff_access_verified',

                'staff_university_id',

                'staff_university_name',

                'staff_university_acronym',

                'staff_account_type',

                'staff_role',

                'staff_id',
            ]);
    }
}