<?php

namespace App\Http\Controllers\UniversityAdmin\Users;

use App\Http\Controllers\Controller;
use App\Support\StaffCredentialsPdfResponse;
use App\Mail\InstructorAccountMail;
use App\Models\Instructor;
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

class InstructorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Instructor Role
    |--------------------------------------------------------------------------
    */

    private const INSTRUCTOR_ROLE =
        'instructor';


    /*
    |--------------------------------------------------------------------------
    | Instructor Guard
    |--------------------------------------------------------------------------
    */

    private const INSTRUCTOR_GUARD =
        'instructor';


    /*
    |--------------------------------------------------------------------------
    | Instructor List
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
        | Make Sure Instructor Role Exists
        |--------------------------------------------------------------------------
        */

        $this->ensureInstructorRoleExists();


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
        | Get University Instructors
        |--------------------------------------------------------------------------
        */

        $instructors =
            Instructor::query()
                ->with(
                    'managedComponents:id,instructor_id,component'
                )
                ->where(
                    'university_id',
                    $admin->university_id
                )
                ->orderBy(
                    'created_at',
                    'desc'
                )
                ->get([
                    'id',
                    'university_id',
                    'full_name',
                    'username',
                    'email',
                    'phone_number',
                    'profile_photo',
                    'component',
                    'status',
                    'created_at',
                    'updated_at',
                ])
                ->map(
                    function (
                        Instructor $instructor
                    ): Instructor {

                        $componentCodes =
                            $instructor->componentCodes();


                        $instructor->setAttribute(
                            'component_codes',
                            $componentCodes
                        );


                        $instructor->setAttribute(
                            'components_display',
                            implode(
                                ', ',
                                $componentCodes
                            )
                        );


                        return $instructor;
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Render Instructor List
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Users/Instructor/instructor',
            [
                'instructors' =>
                    $instructors,

                'components' =>
                    $components,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Add Instructor Page
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
        | Make Sure Instructor Role Exists
        |--------------------------------------------------------------------------
        */

        $this->ensureInstructorRoleExists();


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
        | Render Add Instructor Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Users/Instructor/AddInstructorPage',
            [
                'mode' =>
                    'create',

                'instructor' =>
                    null,

                'components' =>
                    $components,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Instructor
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
        | Make Sure Spatie Instructor Role Exists
        |--------------------------------------------------------------------------
        |
        | This means creating an Instructor no longer depends on manually
        | running Tinker to create the role.
        |
        */

        $instructorRole =
            $this->ensureInstructorRoleExists();


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
        | Validate Instructor
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
                        'instructors',
                        'username'
                    ),
                ],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',

                    Rule::unique(
                        'instructors',
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
                    'max:255',
                ],


                /*
                |--------------------------------------------------------------------------
                | Component
                |--------------------------------------------------------------------------
                */

                'components' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'components.*' => [
                    'required',
                    'string',
                    'distinct',

                    Rule::in(
                        $components
                    ),
                ],

                'component' => [
                    'nullable',
                    'string',

                    Rule::in(
                        $components
                    ),
                ],


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

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
        | Save Plain Password Temporarily
        |--------------------------------------------------------------------------
        */

        $plainPassword =
            $validated['password'];


        /*
        |--------------------------------------------------------------------------
        | Create Instructor Inside Transaction
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Instructor creation and Spatie role assignment happen together.
        |
        | If role assignment fails, the Instructor insert is rolled back.
        |
        */

        $instructor =
            DB::transaction(
                function () use (
                    $admin,
                    $validated,
                    $instructorRole
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Create Instructor
                    |--------------------------------------------------------------------------
                    */

                    $instructor =
                        new Instructor();


                    /*
                    |--------------------------------------------------------------------------
                    | University
                    |--------------------------------------------------------------------------
                    */

                    $instructor->university_id =
                        $admin->university_id;


                    /*
                    |--------------------------------------------------------------------------
                    | Instructor Information
                    |--------------------------------------------------------------------------
                    */

                    $instructor->full_name =
                        $validated['full_name'];

                    $instructor->username =
                        $validated['username'];

                    $instructor->email =
                        $validated['email'];

                    $instructor->phone_number =
                        $validated['phone_number'];


                    /*
                    |--------------------------------------------------------------------------
                    | Password
                    |--------------------------------------------------------------------------
                    |
                    | Instructor model automatically hashes this because:
                    |
                    | 'password' => 'hashed'
                    |
                    */

                    $instructor->password =
                        $validated['password'];


                    /*
                    |--------------------------------------------------------------------------
                    | Component
                    |--------------------------------------------------------------------------
                    */

                    $instructor->component =
                        $validated['components'][0];


                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    $instructor->status =
                        $validated['status'];


                    /*
                    |--------------------------------------------------------------------------
                    | Save Instructor
                    |--------------------------------------------------------------------------
                    */

                    $instructor->save();


                    foreach (
                        $validated['components']
                        as
                        $component
                    ) {

                        $instructor
                            ->managedComponents()
                            ->create([
                                'component' =>
                                    $component,
                            ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Automatically Assign Instructor Role
                    |--------------------------------------------------------------------------
                    |
                    | Every Instructor created through this controller
                    | automatically gets:
                    |
                    | name       = instructor
                    | guard_name = instructor
                    |
                    */

                    $instructor->assignRole(
                        $instructorRole
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Return Instructor
                    |--------------------------------------------------------------------------
                    */

                    return $instructor;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Store Plain Password Temporarily In Session
        |--------------------------------------------------------------------------
        |
        | The plain password is NOT stored in the database.
        |
        */

        session()->put(
            'new_instructor_passwords.' .
            $instructor->id,
            $plainPassword
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect To New Instructor Page
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.instructors.new',
                [
                    'instructor' =>
                        $instructor->id,
                ]
            )
            ->with(
                'success',
                'Instructor created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Newly Created Instructor
    |--------------------------------------------------------------------------
    */

    public function showNewInstructor(
        Instructor $instructor
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

        $this->ensureInstructorBelongsToUniversity(
            $instructor,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Make Sure Role Exists
        |--------------------------------------------------------------------------
        */

        $role =
            $this->ensureInstructorRoleExists();


        /*
        |--------------------------------------------------------------------------
        | Repair Missing Role Automatically
        |--------------------------------------------------------------------------
        */

        if (
            !$instructor->hasRole(
                $role
            )
        ) {

            $instructor->syncRoles([
                $role,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Temporary Plain Password
        |--------------------------------------------------------------------------
        */

        $plainPassword =
            session()->get(
                'new_instructor_passwords.' .
                $instructor->id,
                ''
            );


        /*
        |--------------------------------------------------------------------------
        | Existing University Access Code
        |--------------------------------------------------------------------------
        */

        $universityAccessCode =
            $admin->university
                ->access_code;


        /*
        |--------------------------------------------------------------------------
        | Render New Instructor Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Users/Instructor/NewInstructorPage',
            [
                /*
                |--------------------------------------------------------------------------
                | Instructor
                |--------------------------------------------------------------------------
                */

                'instructor' => [

                    'id' =>
                        $instructor->id,

                    'university_id' =>
                        $instructor->university_id,

                    'full_name' =>
                        $instructor->full_name,

                    'username' =>
                        $instructor->username,

                    'email' =>
                        $instructor->email,

                    'password' =>
                        $plainPassword,

                    'phone_number' =>
                        $instructor->phone_number,

                    'profile_photo' =>
                        $instructor->profile_photo,

                    'component' =>
                        $instructor->component,

                    'component_name' =>
                        $this->getComponentName(
                            $instructor->component
                        ),

                    'status' =>
                        $instructor->status,

                    'access_code' =>
                        $universityAccessCode,

                    'role' =>
                        self::INSTRUCTOR_ROLE,
                ],


                /*
                |--------------------------------------------------------------------------
                | University Information
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
    | Show Edit Instructor Page
    |--------------------------------------------------------------------------
    */

    /**
     * Download credentials as a short bond paper (8.5 x 11 in) PDF.
     * The clear-text password is read ONLY from this admin's session.
     * No credential PDFs or clear-text passwords are stored in public storage.
     */
    public function downloadCredentialsPdf(Instructor $instructor)
    {
        $admin = $this->getAuthenticatedUniversityAdmin();
        $this->ensureInstructorBelongsToUniversity($instructor, $admin);

        $password = session()->get('new_instructor_passwords.' . $instructor->id);

        if (!is_string($password) || $password === '') {
            \Illuminate\Support\Facades\Log::warning('Staff PDF temporary password missing from session.', [
                'role' => 'instructor',
                'id' => $instructor->id,
                'has_session' => session()->isStarted(),
            ]);
        }

        abort_unless(
            is_string($password) && $password !== '',
            410,
            'The temporary password is no longer available. Create or reset the account password to issue new credentials.'
        );

        $university = $admin->university;
        abort_unless($university && filled($university->access_code), 404);

        return app(StaffCredentialsPdfResponse::class)->download(
            [
                'roleTitle' => 'Instructor',
                'fullName' => $instructor->full_name,
                'universityName' => $university->name,
                'components' => $instructor->componentCodes(),
                'staffRole' => null,
                'accessCode' => $university->access_code,
                'loginUrl' => url('/instructor-coordinator/access-code'),
                'username' => $instructor->username,
                'email' => $instructor->email,
                'phoneNumber' => $instructor->phone_number,
                'temporaryPassword' => $password,
            ],
            'nstphub_instructor_credentials_' . $instructor->id . '.pdf'
        );
    }

    public function edit(
        Instructor $instructor
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

        $this->ensureInstructorBelongsToUniversity(
            $instructor,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Ensure Role Exists
        |--------------------------------------------------------------------------
        */

        $role =
            $this->ensureInstructorRoleExists();


        /*
        |--------------------------------------------------------------------------
        | Automatically Repair Missing Role
        |--------------------------------------------------------------------------
        */

        if (
            !$instructor->hasRole(
                $role
            )
        ) {

            $instructor->syncRoles([
                $role,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University Components
        |--------------------------------------------------------------------------
        */

        $components =
            $this->getUniversityComponents(
                $admin
            );


        $instructor->loadMissing(
            'managedComponents'
        );


        $assignedComponents =
            $instructor->componentCodes();


        /*
        |--------------------------------------------------------------------------
        | Render Edit Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Users/Instructor/EditInstructorPage',
            [
                /*
                |--------------------------------------------------------------------------
                | Instructor Information
                |--------------------------------------------------------------------------
                */

                'instructor' => [

                    'id' =>
                        $instructor->id,

                    'university_id' =>
                        $instructor->university_id,

                    'full_name' =>
                        $instructor->full_name,

                    'username' =>
                        $instructor->username,

                    'email' =>
                        $instructor->email,

                    'phone_number' =>
                        $instructor->phone_number,

                    'profile_photo' =>
                        $instructor->profile_photo,

                    'component' =>
                        $instructor->primaryComponent(),

                    'components' =>
                        $assignedComponents,

                    'component_codes' =>
                        $assignedComponents,

                    'status' =>
                        $instructor->status,

                    'role' =>
                        self::INSTRUCTOR_ROLE,
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
    | Update Instructor
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Instructor $instructor
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

        $this->ensureInstructorBelongsToUniversity(
            $instructor,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Ensure Role Exists
        |--------------------------------------------------------------------------
        */

        $instructorRole =
            $this->ensureInstructorRoleExists();


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
        | Validate Instructor
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
                        'instructors',
                        'username'
                    )->ignore(
                        $instructor->id
                    ),
                ],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',

                    Rule::unique(
                        'instructors',
                        'email'
                    )->ignore(
                        $instructor->id
                    ),
                ],

                'phone_number' => [
                    'required',
                    'string',
                    'max:30',
                ],


                /*
                |--------------------------------------------------------------------------
                | Password Is Optional During Edit
                |--------------------------------------------------------------------------
                */

                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'max:255',
                ],


                /*
                |--------------------------------------------------------------------------
                | Component
                |--------------------------------------------------------------------------
                */

                'components' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'components.*' => [
                    'required',
                    'string',
                    'distinct',

                    Rule::in(
                        $components
                    ),
                ],

                'component' => [
                    'nullable',
                    'string',

                    Rule::in(
                        $components
                    ),
                ],


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

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
        | Update Inside Transaction
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $instructor,
                $validated,
                $instructorRole
            ) {

                /*
                |--------------------------------------------------------------------------
                | Instructor Information
                |--------------------------------------------------------------------------
                */

                $instructor->full_name =
                    $validated['full_name'];

                $instructor->username =
                    $validated['username'];

                $instructor->email =
                    $validated['email'];

                $instructor->phone_number =
                    $validated['phone_number'];

                $instructor->component =
                    $validated['components'][0];

                $instructor->status =
                    $validated['status'];


                /*
                |--------------------------------------------------------------------------
                | Update Password Only When Provided
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $validated['password'] ??
                        null
                    )
                ) {

                    $instructor->password =
                        $validated['password'];
                }


                /*
                |--------------------------------------------------------------------------
                | Save Changes
                |--------------------------------------------------------------------------
                */

                $instructor->save();


                $instructor
                    ->managedComponents()
                    ->delete();


                foreach (
                    $validated['components']
                    as
                    $component
                ) {

                    $instructor
                        ->managedComponents()
                        ->create([
                            'component' =>
                                $component,
                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Ensure Instructor Role
                |--------------------------------------------------------------------------
                |
                | This also fixes older Instructor records that were created
                | before automatic role assignment was implemented.
                |
                */

                $instructor->syncRoles([
                    $instructorRole,
                ]);
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect To Instructor List
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.instructors'
            )
            ->with(
                'success',
                'Instructor updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Instructor
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Instructor $instructor
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

        $this->ensureInstructorBelongsToUniversity(
            $instructor,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Instructor Name
        |--------------------------------------------------------------------------
        */

        $instructorName =
            $instructor->full_name;


        /*
        |--------------------------------------------------------------------------
        | Remove Temporary Password
        |--------------------------------------------------------------------------
        */

        session()->forget(
            'new_instructor_passwords.' .
            $instructor->id
        );


        /*
        |--------------------------------------------------------------------------
        | Delete Instructor In Transaction
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $instructor
            ) {

                /*
                |--------------------------------------------------------------------------
                | Remove Spatie Roles
                |--------------------------------------------------------------------------
                */

                $instructor->syncRoles([]);


                /*
                |--------------------------------------------------------------------------
                | Delete Instructor
                |--------------------------------------------------------------------------
                */

                $instructor->delete();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.instructors'
            )
            ->with(
                'success',
                $instructorName .
                ' was deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Send Instructor Account Email
    |--------------------------------------------------------------------------
    */

    public function sendEmail(
        Instructor $instructor
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

        $this->ensureInstructorBelongsToUniversity(
            $instructor,
            $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Check Instructor Email
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $instructor->email
            )
        ) {

            return back()->withErrors([
                'email' =>
                    'The instructor does not have an email address.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Make Sure Instructor Role Exists
        |--------------------------------------------------------------------------
        */

        $role =
            $this->ensureInstructorRoleExists();


        /*
        |--------------------------------------------------------------------------
        | Automatically Assign Missing Role
        |--------------------------------------------------------------------------
        */

        if (
            !$instructor->hasRole(
                $role
            )
        ) {

            $instructor->syncRoles([
                $role,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Get Temporary Plain Password
        |--------------------------------------------------------------------------
        */

        $plainPassword =
            session()->get(
                'new_instructor_passwords.' .
                $instructor->id
            );


        /*
        |--------------------------------------------------------------------------
        | Password Must Still Be Available
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $plainPassword
            )
        ) {

            return back()->withErrors([
                'email' =>
                    'The temporary instructor password is no longer available. Please reset the instructor password before sending account information.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $university =
            $admin->university;


        if (!$university) {

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
        | Instructor / Coordinator Portal
        |--------------------------------------------------------------------------
        */

        $loginUrl =
            url(
                '/instructor-coordinator/access-code'
            );


        /*
        |--------------------------------------------------------------------------
        | Send Instructor Account Email
        |--------------------------------------------------------------------------
        */

        try {

            Mail::to(
                $instructor->email,
                $instructor->full_name
            )->send(
                new InstructorAccountMail(
                    instructor:
                        $instructor,

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
                'new_instructor_passwords.' .
                $instructor->id
            );


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            return back()->with(
                'success',
                'Instructor account information was successfully sent to ' .
                $instructor->email .
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
            | Error
            |--------------------------------------------------------------------------
            */

            return back()->withErrors([
                'email' =>
                    app()->environment(
                        'local'
                    )
                        ? 'Unable to send email: ' .
                            $exception->getMessage()
                        : 'Unable to send the instructor email. Please try again.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Instructor Role Exists
    |--------------------------------------------------------------------------
    |
    | This prevents:
    |
    | Spatie\Permission\Exceptions\RoleDoesNotExist
    |
    | Every time this controller needs the Instructor role, it guarantees
    | that the correct role exists for the "instructor" guard.
    |
    */

    private function ensureInstructorRoleExists(): Role
    {
        /*
        |--------------------------------------------------------------------------
        | Clear Cached Permissions
        |--------------------------------------------------------------------------
        */

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();


        /*
        |--------------------------------------------------------------------------
        | Find Or Create Instructor Role
        |--------------------------------------------------------------------------
        */

        $role =
            Role::firstOrCreate(
                [
                    'name' =>
                        self::INSTRUCTOR_ROLE,

                    'guard_name' =>
                        self::INSTRUCTOR_GUARD,
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Clear Cache After Role Creation
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
    | Get Logged-In University Administrator
    |--------------------------------------------------------------------------
    */

    private function getAuthenticatedUniversityAdmin():
        UniversityAdministrator
    {
        /*
        |--------------------------------------------------------------------------
        | Explicit University Admin Guard
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

        if (!$admin) {

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
        | Load University
        |--------------------------------------------------------------------------
        */

        $admin->loadMissing(
            'university'
        );


        /*
        |--------------------------------------------------------------------------
        | Must Be Connected To University
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


        /*
        |--------------------------------------------------------------------------
        | Return Administrator
        |--------------------------------------------------------------------------
        */

        return $admin;
    }


    /*
    |--------------------------------------------------------------------------
    | Get University Components
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
        | Must Be Array
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
        | Normalize
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
        | Remove Empty / Duplicates
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
    | Instructor University Security
    |--------------------------------------------------------------------------
    */

    private function ensureInstructorBelongsToUniversity(
        Instructor $instructor,
        UniversityAdministrator $admin
    ): void {

        if (
            (int) $instructor->university_id !==
            (int) $admin->university_id
        ) {

            abort(
                403,
                'You are not authorized to access this instructor.'
            );
        }
    }
}