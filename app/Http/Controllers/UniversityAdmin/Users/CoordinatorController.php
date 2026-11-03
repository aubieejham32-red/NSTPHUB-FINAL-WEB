<?php

namespace App\Http\Controllers\UniversityAdmin\Users;

use App\Http\Controllers\Controller;
use App\Mail\CoordinatorAccountMail;
use App\Models\Coordinator;
use App\Models\UniversityAdministrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CoordinatorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Spatie Coordinator Guard
    |--------------------------------------------------------------------------
    */

    private const COORDINATOR_GUARD =
        'coordinator';


    /*
    |--------------------------------------------------------------------------
    | Spatie Coordinator Roles
    |--------------------------------------------------------------------------
    |
    | These are the REAL roles used by Spatie.
    |
    */

    private const COORDINATOR_ROLES = [
        'coordinator-attendance',
        'coordinator-announcement',
        'coordinator-schedule',
    ];


    /*
    |--------------------------------------------------------------------------
    | Coordinator Role Labels
    |--------------------------------------------------------------------------
    |
    | These preserve compatibility with the existing Vue forms
    | and the existing coordinators.role database column.
    |
    | Authentication and authorization still use the lowercase
    | Spatie role names above.
    |
    */

    private const COORDINATOR_ROLE_LABELS = [

        'coordinator-attendance' =>
            'Coordinator-Attendance',

        'coordinator-announcement' =>
            'Coordinator-Announcement',

        'coordinator-schedule' =>
            'Coordinator-Schedule',

    ];


    /*
    |--------------------------------------------------------------------------
    | Coordinator List
    |--------------------------------------------------------------------------
    */

    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Get Logged-In University Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            $this->getAuthenticatedUniversityAdmin();


        /*
        |--------------------------------------------------------------------------
        | Make Sure Coordinator Roles Exist
        |--------------------------------------------------------------------------
        */

        $this->ensureAllCoordinatorRolesExist();


        /*
        |--------------------------------------------------------------------------
        | Get University Components
        |--------------------------------------------------------------------------
        */

        $components =
            $this->getUniversityComponents(
                $admin
            );


        /*
        |--------------------------------------------------------------------------
        | Get University Coordinators
        |--------------------------------------------------------------------------
        */

        $coordinators =
            Coordinator::query()
                ->with(
                    'roles'
                )
                ->where(
                    'university_id',
                    $admin->university_id
                )
                ->orderBy(
                    'created_at',
                    'desc'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Prepare Coordinator Data
        |--------------------------------------------------------------------------
        |
        | Existing coordinators created before Spatie role assignment
        | will automatically be repaired using their old role column.
        |
        */

        $coordinators =
            $coordinators->map(
                function (
                    Coordinator $coordinator
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Resolve / Repair Spatie Role
                    |--------------------------------------------------------------------------
                    */

                    $roleName =
                        $this->resolveCoordinatorRole(
                            $coordinator
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Frontend Role Value
                    |--------------------------------------------------------------------------
                    */

                    $coordinator->setAttribute(
                        'role',
                        $this->formatCoordinatorRoleForFrontend(
                            $roleName
                        )
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Hide Raw Spatie Relationship
                    |--------------------------------------------------------------------------
                    */

                    $coordinator->unsetRelation(
                        'roles'
                    );


                    return $coordinator;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Render Coordinator List
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Users/Coordinators/coordinator',
            [
                'coordinators' =>
                    $coordinators,

                'components' =>
                    $components,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Add Coordinator Page
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Get University Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            $this->getAuthenticatedUniversityAdmin();


        /*
        |--------------------------------------------------------------------------
        | Make Sure Coordinator Roles Exist
        |--------------------------------------------------------------------------
        */

        $this->ensureAllCoordinatorRolesExist();


        /*
        |--------------------------------------------------------------------------
        | Get University Components
        |--------------------------------------------------------------------------
        */

        $components =
            $this->getUniversityComponents(
                $admin
            );


        /*
        |--------------------------------------------------------------------------
        | Render Add Coordinator Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Users/Coordinators/AddCoordinatorPage',
            [
                'mode' =>
                    'create',

                'coordinator' =>
                    null,

                'components' =>
                    $components,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Coordinator
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Get Logged-In University Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            $this->getAuthenticatedUniversityAdmin();


        /*
        |--------------------------------------------------------------------------
        | Normalize Coordinator Role
        |--------------------------------------------------------------------------
        |
        | This allows the frontend to send either:
        |
        | Coordinator-Attendance
        |
        | or:
        |
        | coordinator-attendance
        |
        | Both are converted to the Spatie format.
        |
        */

        $request->merge([
            'role' =>
                $this->normalizeCoordinatorRole(
                    $request->input(
                        'role'
                    )
                ),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Get University's Existing Components
        |--------------------------------------------------------------------------
        */

        $components =
            $this->getUniversityComponents(
                $admin
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Coordinator
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'full_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'username' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'coordinators',
                        'username'
                    ),
                ],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:191',

                    Rule::unique(
                        'coordinators',
                        'email'
                    ),
                ],

                'phone_number' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'max:191',
                ],

                'component' => [
                    'required',
                    'string',

                    Rule::in(
                        $components
                    ),
                ],

                'role' => [
                    'required',
                    'string',

                    Rule::in(
                        self::COORDINATOR_ROLES
                    ),
                ],

                'status' => [
                    'required',

                    Rule::in([
                        'active',
                        'inactive',
                    ]),
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Make Sure Selected Spatie Role Exists
        |--------------------------------------------------------------------------
        */

        $coordinatorRole =
            $this->ensureCoordinatorRoleExists(
                $validated['role']
            );


        /*
        |--------------------------------------------------------------------------
        | Save Plain Password Temporarily
        |--------------------------------------------------------------------------
        */

        $plainPassword =
            $validated['password'];


        /*
        |--------------------------------------------------------------------------
        | Create Coordinator Inside Transaction
        |--------------------------------------------------------------------------
        |
        | Coordinator creation and Spatie role assignment happen
        | in one transaction.
        |
        | If role assignment fails, the coordinator record will
        | automatically be rolled back.
        |
        */

        $coordinator =
            DB::transaction(
                function () use (
                    $admin,
                    $validated,
                    $coordinatorRole
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Create Coordinator
                    |--------------------------------------------------------------------------
                    */

                    $coordinator =
                        new Coordinator();


                    /*
                    |--------------------------------------------------------------------------
                    | University
                    |--------------------------------------------------------------------------
                    */

                    $coordinator->university_id =
                        $admin->university_id;


                    /*
                    |--------------------------------------------------------------------------
                    | Coordinator Information
                    |--------------------------------------------------------------------------
                    */

                    $coordinator->full_name =
                        $validated['full_name'];

                    $coordinator->username =
                        $validated['username'];

                    $coordinator->email =
                        $validated['email'];

                    $coordinator->phone_number =
                        $validated['phone_number'];


                    /*
                    |--------------------------------------------------------------------------
                    | Password
                    |--------------------------------------------------------------------------
                    |
                    | Coordinator model automatically hashes this.
                    |
                    */

                    $coordinator->password =
                        $validated['password'];


                    /*
                    |--------------------------------------------------------------------------
                    | Component
                    |--------------------------------------------------------------------------
                    */

                    $coordinator->component =
                        $validated['component'];


                    /*
                    |--------------------------------------------------------------------------
                    | Legacy Role Column
                    |--------------------------------------------------------------------------
                    |
                    | We keep this synchronized because your current
                    | database / Vue pages still use the role column.
                    |
                    | Spatie remains the real authorization source.
                    |
                    */

                    $coordinator->role =
                        $this->formatCoordinatorRoleForFrontend(
                            $validated['role']
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    $coordinator->status =
                        $validated['status'];


                    /*
                    |--------------------------------------------------------------------------
                    | Save Coordinator
                    |--------------------------------------------------------------------------
                    */

                    $coordinator->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Assign Spatie Coordinator Role Automatically
                    |--------------------------------------------------------------------------
                    */

                    $coordinator->syncRoles([
                        $coordinatorRole,
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Return Coordinator
                    |--------------------------------------------------------------------------
                    */

                    return $coordinator;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Store Plain Password Temporarily In Session
        |--------------------------------------------------------------------------
        */

        session()->put(
            'new_coordinator_passwords.' .
            $coordinator->id,
            $plainPassword
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect To New Coordinator Page
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.coordinators.new',
                [
                    'coordinator' =>
                        $coordinator->id,
                ]
            )
            ->with(
                'success',
                'Coordinator created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Newly Created Coordinator
    |--------------------------------------------------------------------------
    */

    public function showNewCoordinator(
        Coordinator $coordinator
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Get University Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            $this->getAuthenticatedUniversityAdmin();


        /*
        |--------------------------------------------------------------------------
        | Security Check
        |--------------------------------------------------------------------------
        */

        $this->ensureCoordinatorBelongsToUniversity(
            $coordinator,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Resolve / Repair Coordinator Role
        |--------------------------------------------------------------------------
        */

        $roleName =
            $this->resolveCoordinatorRole(
                $coordinator
            );


        /*
        |--------------------------------------------------------------------------
        | Temporary Plain Password
        |--------------------------------------------------------------------------
        */

        $plainPassword =
            session()->get(
                'new_coordinator_passwords.' .
                $coordinator->id,
                ''
            );


        /*
        |--------------------------------------------------------------------------
        | University Access Code
        |--------------------------------------------------------------------------
        */

        $universityAccessCode =
            $admin->university
                ->access_code;


        /*
        |--------------------------------------------------------------------------
        | Render New Coordinator Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Users/Coordinators/NewCoordinatorPage',
            [
                /*
                |--------------------------------------------------------------------------
                | Coordinator
                |--------------------------------------------------------------------------
                */

                'coordinator' => [

                    'id' =>
                        $coordinator->id,

                    'university_id' =>
                        $coordinator->university_id,

                    'full_name' =>
                        $coordinator->full_name,

                    'username' =>
                        $coordinator->username,

                    'email' =>
                        $coordinator->email,

                    'temporary_password' =>
                        $plainPassword,

                    'password' =>
                        $plainPassword,

                    'phone_number' =>
                        $coordinator->phone_number,

                    'profile_photo' =>
                        $coordinator->profile_photo,

                    'component' =>
                        $coordinator->component,

                    'component_name' =>
                        $this->getComponentName(
                            $coordinator->component
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | Role For Existing Vue UI
                    |--------------------------------------------------------------------------
                    */

                    'role' =>
                        $this->formatCoordinatorRoleForFrontend(
                            $roleName
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | Actual Spatie Role
                    |--------------------------------------------------------------------------
                    */

                    'spatie_role' =>
                        $roleName,

                    'status' =>
                        $coordinator->status,

                    'access_code' =>
                        $universityAccessCode,
                ],


                /*
                |--------------------------------------------------------------------------
                | University
                |--------------------------------------------------------------------------
                */

                'university' => [

                    'id' =>
                        $admin->university->id,

                    'name' =>
                        $admin->university->name,

                    'acronym' =>
                        $admin->university->acronym,

                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Edit Coordinator Page
    |--------------------------------------------------------------------------
    */

    public function edit(
        Coordinator $coordinator
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Get University Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            $this->getAuthenticatedUniversityAdmin();


        /*
        |--------------------------------------------------------------------------
        | Security Check
        |--------------------------------------------------------------------------
        */

        $this->ensureCoordinatorBelongsToUniversity(
            $coordinator,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Resolve / Repair Role
        |--------------------------------------------------------------------------
        */

        $roleName =
            $this->resolveCoordinatorRole(
                $coordinator
            );


        /*
        |--------------------------------------------------------------------------
        | Get University Components
        |--------------------------------------------------------------------------
        */

        $components =
            $this->getUniversityComponents(
                $admin
            );


        /*
        |--------------------------------------------------------------------------
        | Render Edit Coordinator Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Users/Coordinators/EditCoordinatorPage',
            [
                /*
                |--------------------------------------------------------------------------
                | Coordinator Information
                |--------------------------------------------------------------------------
                */

                'coordinator' => [

                    'id' =>
                        $coordinator->id,

                    'university_id' =>
                        $coordinator->university_id,

                    'full_name' =>
                        $coordinator->full_name,

                    'username' =>
                        $coordinator->username,

                    'email' =>
                        $coordinator->email,

                    'phone_number' =>
                        $coordinator->phone_number,

                    'profile_photo' =>
                        $coordinator->profile_photo,

                    'component' =>
                        $coordinator->component,

                    /*
                    |--------------------------------------------------------------------------
                    | Keep Existing Vue Role Value Compatible
                    |--------------------------------------------------------------------------
                    */

                    'role' =>
                        $this->formatCoordinatorRoleForFrontend(
                            $roleName
                        ),

                    'spatie_role' =>
                        $roleName,

                    'status' =>
                        $coordinator->status,

                ],


                /*
                |--------------------------------------------------------------------------
                | University Components
                |--------------------------------------------------------------------------
                */

                'components' =>
                    $components,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Coordinator
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Coordinator $coordinator
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Get University Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            $this->getAuthenticatedUniversityAdmin();


        /*
        |--------------------------------------------------------------------------
        | Security Check
        |--------------------------------------------------------------------------
        */

        $this->ensureCoordinatorBelongsToUniversity(
            $coordinator,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Normalize Role From Frontend
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'role' =>
                $this->normalizeCoordinatorRole(
                    $request->input(
                        'role'
                    )
                ),
        ]);


        /*
        |--------------------------------------------------------------------------
        | University Components
        |--------------------------------------------------------------------------
        */

        $components =
            $this->getUniversityComponents(
                $admin
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Coordinator
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'full_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'username' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'coordinators',
                        'username'
                    )->ignore(
                        $coordinator->id
                    ),
                ],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:191',

                    Rule::unique(
                        'coordinators',
                        'email'
                    )->ignore(
                        $coordinator->id
                    ),
                ],

                'phone_number' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'max:191',
                ],

                'component' => [
                    'required',
                    'string',

                    Rule::in(
                        $components
                    ),
                ],

                'role' => [
                    'required',
                    'string',

                    Rule::in(
                        self::COORDINATOR_ROLES
                    ),
                ],

                'status' => [
                    'required',

                    Rule::in([
                        'active',
                        'inactive',
                    ]),
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Ensure Selected Spatie Role Exists
        |--------------------------------------------------------------------------
        */

        $coordinatorRole =
            $this->ensureCoordinatorRoleExists(
                $validated['role']
            );


        /*
        |--------------------------------------------------------------------------
        | Update Coordinator Inside Transaction
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $coordinator,
                $validated,
                $coordinatorRole
            ) {

                /*
                |--------------------------------------------------------------------------
                | Coordinator Information
                |--------------------------------------------------------------------------
                */

                $coordinator->full_name =
                    $validated['full_name'];

                $coordinator->username =
                    $validated['username'];

                $coordinator->email =
                    $validated['email'];

                $coordinator->phone_number =
                    $validated['phone_number'];

                $coordinator->component =
                    $validated['component'];

                $coordinator->status =
                    $validated['status'];


                /*
                |--------------------------------------------------------------------------
                | Keep Legacy Role Column Synchronized
                |--------------------------------------------------------------------------
                */

                $coordinator->role =
                    $this->formatCoordinatorRoleForFrontend(
                        $validated['role']
                    );


                /*
                |--------------------------------------------------------------------------
                | Update Password Only If Provided
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $validated['password'] ??
                        null
                    )
                ) {

                    $coordinator->password =
                        $validated['password'];
                }


                /*
                |--------------------------------------------------------------------------
                | Save Changes
                |--------------------------------------------------------------------------
                */

                $coordinator->save();


                /*
                |--------------------------------------------------------------------------
                | Synchronize Spatie Role
                |--------------------------------------------------------------------------
                |
                | syncRoles() removes the previous Coordinator role and
                | replaces it with the newly selected role.
                |
                */

                $coordinator->syncRoles([
                    $coordinatorRole,
                ]);
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.coordinators'
            )
            ->with(
                'success',
                'Coordinator updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Coordinator
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Coordinator $coordinator
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Get University Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            $this->getAuthenticatedUniversityAdmin();


        /*
        |--------------------------------------------------------------------------
        | Security Check
        |--------------------------------------------------------------------------
        */

        $this->ensureCoordinatorBelongsToUniversity(
            $coordinator,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Coordinator Name
        |--------------------------------------------------------------------------
        */

        $coordinatorName =
            $coordinator->full_name;


        /*
        |--------------------------------------------------------------------------
        | Remove Temporary Password
        |--------------------------------------------------------------------------
        */

        session()->forget(
            'new_coordinator_passwords.' .
            $coordinator->id
        );


        /*
        |--------------------------------------------------------------------------
        | Delete Coordinator Inside Transaction
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $coordinator
            ) {

                /*
                |--------------------------------------------------------------------------
                | Remove Spatie Roles
                |--------------------------------------------------------------------------
                */

                $coordinator->syncRoles([]);


                /*
                |--------------------------------------------------------------------------
                | Delete Coordinator
                |--------------------------------------------------------------------------
                */

                $coordinator->delete();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.coordinators'
            )
            ->with(
                'success',
                $coordinatorName .
                ' was deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Send Coordinator Account Email
    |--------------------------------------------------------------------------
    */

    public function sendEmail(
        Coordinator $coordinator
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Get University Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            $this->getAuthenticatedUniversityAdmin();


        /*
        |--------------------------------------------------------------------------
        | Security Check
        |--------------------------------------------------------------------------
        */

        $this->ensureCoordinatorBelongsToUniversity(
            $coordinator,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Check Coordinator Email
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $coordinator->email
            )
        ) {

            return back()->withErrors([
                'email' =>
                    'The coordinator does not have an email address.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve / Repair Spatie Coordinator Role
        |--------------------------------------------------------------------------
        */

        $roleName =
            $this->resolveCoordinatorRole(
                $coordinator
            );


        /*
        |--------------------------------------------------------------------------
        | Coordinator Must Have A Valid Role
        |--------------------------------------------------------------------------
        */

        if (
            !$roleName
        ) {

            return back()->withErrors([
                'role' =>
                    'The coordinator does not have a valid coordinator role. Please edit the coordinator and select a coordinator type.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Get Temporary Plain Password
        |--------------------------------------------------------------------------
        */

        $plainPassword =
            session()->get(
                'new_coordinator_passwords.' .
                $coordinator->id
            );


        /*
        |--------------------------------------------------------------------------
        | Temporary Password Required
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $plainPassword
            )
        ) {

            return back()->withErrors([
                'email' =>
                    'The temporary coordinator password is no longer available. Please create or reset the coordinator password before sending account information.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $university =
            $admin->university;


        if (
            !$university
        ) {

            return back()->withErrors([
                'email' =>
                    'The university information could not be found.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Access Code
        |--------------------------------------------------------------------------
        */

        $accessCode =
            $university->access_code;


        if (
            empty(
                $accessCode
            )
        ) {

            return back()->withErrors([
                'email' =>
                    'The university access code could not be found.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Shared Instructor / Coordinator Portal
        |--------------------------------------------------------------------------
        */

        $loginUrl =
            url(
                '/instructor-coordinator/access-code'
            );


        /*
        |--------------------------------------------------------------------------
        | Send Coordinator Account Email
        |--------------------------------------------------------------------------
        */

        try {

            Mail::to(
                $coordinator->email,
                $coordinator->full_name
            )->send(
                new CoordinatorAccountMail(
                    coordinator:
                        $coordinator,

                    university:
                        $university,

                    temporaryPassword:
                        $plainPassword,

                    accessCode:
                        $accessCode,

                    loginUrl:
                        $loginUrl,
                )
            );


            /*
            |--------------------------------------------------------------------------
            | Remove Temporary Password After Successful Email
            |--------------------------------------------------------------------------
            */

            session()->forget(
                'new_coordinator_passwords.' .
                $coordinator->id
            );


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            return back()->with(
                'success',
                'Coordinator account information was successfully sent to ' .
                $coordinator->email .
                '.'
            );

        } catch (
            \Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Report Error
            |--------------------------------------------------------------------------
            */

            report(
                $exception
            );


            /*
            |--------------------------------------------------------------------------
            | Error Response
            |--------------------------------------------------------------------------
            */

            return back()->withErrors([
                'email' =>
                    app()->environment(
                        'local'
                    )
                        ? 'Unable to send email: ' .
                            $exception->getMessage()
                        : 'Unable to send the coordinator email. Please try again.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure All Coordinator Roles Exist
    |--------------------------------------------------------------------------
    */

    private function ensureAllCoordinatorRolesExist(): void
    {
        foreach (
            self::COORDINATOR_ROLES
            as $roleName
        ) {

            $this->ensureCoordinatorRoleExists(
                $roleName
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Coordinator Role Exists
    |--------------------------------------------------------------------------
    |
    | Prevents:
    |
    | Spatie\Permission\Exceptions\RoleDoesNotExist
    |
    */

    private function ensureCoordinatorRoleExists(
        string $roleName
    ): Role {

        /*
        |--------------------------------------------------------------------------
        | Normalize Role
        |--------------------------------------------------------------------------
        */

        $roleName =
            $this->normalizeCoordinatorRole(
                $roleName
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Internal Role
        |--------------------------------------------------------------------------
        */

        if (
            !$roleName ||
            !in_array(
                $roleName,
                self::COORDINATOR_ROLES,
                true
            )
        ) {

            throw new \InvalidArgumentException(
                'Invalid coordinator role.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Find Or Create Role
        |--------------------------------------------------------------------------
        */

        $role =
            Role::firstOrCreate(
                [
                    'name' =>
                        $roleName,

                    'guard_name' =>
                        self::COORDINATOR_GUARD,
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Clear Spatie Cache
        |--------------------------------------------------------------------------
        */

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();


        /*
        |--------------------------------------------------------------------------
        | Return Role
        |--------------------------------------------------------------------------
        */

        return $role;
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Coordinator Role
    |--------------------------------------------------------------------------
    |
    | First:
    |
    | Check Spatie.
    |
    | If there is no Spatie role, check the old database role column
    | and automatically migrate it into Spatie.
    |
    */

    private function resolveCoordinatorRole(
        Coordinator $coordinator
    ): ?string {

        /*
        |--------------------------------------------------------------------------
        | Check Existing Spatie Roles
        |--------------------------------------------------------------------------
        */

        foreach (
            self::COORDINATOR_ROLES
            as $roleName
        ) {

            if (
                $coordinator->hasRole(
                    $roleName
                )
            ) {

                return $roleName;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Try Legacy Database Role Column
        |--------------------------------------------------------------------------
        */

        $legacyRole =
            $this->normalizeCoordinatorRole(
                $coordinator->getAttribute(
                    'role'
                )
            );


        /*
        |--------------------------------------------------------------------------
        | No Valid Legacy Role
        |--------------------------------------------------------------------------
        */

        if (
            !$legacyRole ||
            !in_array(
                $legacyRole,
                self::COORDINATOR_ROLES,
                true
            )
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure Role Exists
        |--------------------------------------------------------------------------
        */

        $role =
            $this->ensureCoordinatorRoleExists(
                $legacyRole
            );


        /*
        |--------------------------------------------------------------------------
        | Automatically Repair Existing Coordinator
        |--------------------------------------------------------------------------
        */

        $coordinator->syncRoles([
            $role,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Return Repaired Role
        |--------------------------------------------------------------------------
        */

        return $legacyRole;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Coordinator Role
    |--------------------------------------------------------------------------
    |
    | Accepts:
    |
    | Coordinator-Attendance
    | coordinator-attendance
    | COORDINATOR-ATTENDANCE
    |
    | Returns:
    |
    | coordinator-attendance
    |
    */

    private function normalizeCoordinatorRole(
        mixed $role
    ): ?string {

        if (
            !is_string(
                $role
            )
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $role =
            strtolower(
                trim(
                    $role
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize Separators
        |--------------------------------------------------------------------------
        */

        $role =
            str_replace(
                [
                    '_',
                    ' ',
                ],
                '-',
                $role
            );


        /*
        |--------------------------------------------------------------------------
        | Collapse Duplicate Hyphens
        |--------------------------------------------------------------------------
        */

        $role =
            preg_replace(
                '/-+/',
                '-',
                $role
            );


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return $role ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Format Coordinator Role For Existing Frontend
    |--------------------------------------------------------------------------
    */

    private function formatCoordinatorRoleForFrontend(
        ?string $roleName
    ): ?string {

        if (
            !$roleName
        ) {

            return null;
        }


        return
            self::COORDINATOR_ROLE_LABELS[
                $roleName
            ] ??
            $roleName;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Logged-In University Administrator
    |--------------------------------------------------------------------------
    */

    private function getAuthenticatedUniversityAdmin():
        UniversityAdministrator
    {
        /*
        |--------------------------------------------------------------------------
        | University Admin Guard
        |--------------------------------------------------------------------------
        */

        $admin =
            Auth::guard(
                'university_admin'
            )->user();


        /*
        |--------------------------------------------------------------------------
        | Must Be Authenticated
        |--------------------------------------------------------------------------
        */

        if (
            !$admin
        ) {

            abort(
                401,
                'You must be logged in as a University Administrator.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Correct Account Type
        |--------------------------------------------------------------------------
        */

        if (
            !(
                $admin instanceof
                UniversityAdministrator
            )
        ) {

            abort(
                403,
                'Invalid University Administrator account.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Load Existing University
        |--------------------------------------------------------------------------
        */

        $admin->loadMissing(
            'university'
        );


        /*
        |--------------------------------------------------------------------------
        | Must Have University
        |--------------------------------------------------------------------------
        */

        if (
            !$admin->university_id ||
            !$admin->university
        ) {

            abort(
                403,
                'University Administrator is not connected to a university.'
            );
        }


        return $admin;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Existing University Components
    |--------------------------------------------------------------------------
    */

    private function getUniversityComponents(
        UniversityAdministrator $admin
    ): array {

        $components =
            $admin->university
                ->components;


        /*
        |--------------------------------------------------------------------------
        | Components Must Be An Array
        |--------------------------------------------------------------------------
        */

        if (
            !is_array(
                $components
            )
        ) {

            return [];
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Components
        |--------------------------------------------------------------------------
        */

        $components =
            array_map(
                function (
                    $component
                ) {

                    return strtoupper(
                        trim(
                            (string) $component
                        )
                    );

                },
                $components
            );


        /*
        |--------------------------------------------------------------------------
        | Remove Empty / Duplicate Values
        |--------------------------------------------------------------------------
        */

        return array_values(
            array_unique(
                array_filter(
                    $components
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Component Full Name
    |--------------------------------------------------------------------------
    */

    private function getComponentName(
        ?string $component
    ): string {

        return match (
            strtoupper(
                $component ??
                ''
            )
        ) {

            'CWTS' =>
                'CWTS - Civic Welfare Training Service',

            'LTS' =>
                'LTS - Literacy Training Service',

            'ROTC' =>
                "ROTC - Reserve Officers' Training Corps",

            default =>
                $component ??
                '',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Coordinator University Security
    |--------------------------------------------------------------------------
    */

    private function ensureCoordinatorBelongsToUniversity(
        Coordinator $coordinator,
        UniversityAdministrator $admin
    ): void {

        if (
            (int) $coordinator->university_id !==
            (int) $admin->university_id
        ) {

            abort(
                403,
                'You are not authorized to access this coordinator.'
            );
        }
    }
}