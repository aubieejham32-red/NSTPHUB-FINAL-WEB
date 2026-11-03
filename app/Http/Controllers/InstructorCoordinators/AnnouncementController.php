<?php

namespace App\Http\Controllers\InstructorCoordinators;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\University;
use App\Models\User;
use App\Support\NstpComponentAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Announcement Page
    |--------------------------------------------------------------------------
    */

    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Resolve Current Logged-In Account
        |--------------------------------------------------------------------------
        */

        $access = $this->resolveAccess();


        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query = Announcement::query();


        /*
        |--------------------------------------------------------------------------
        | University Isolation
        |--------------------------------------------------------------------------
        |
        | University users can only access announcements belonging to their
        | own university.
        |
        */

        if (
            $access['role'] !==
            Announcement::ROLE_SUPER_ADMIN
        ) {
            $query->where(
                'university_id',
                $access['university_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Component Isolation
        |--------------------------------------------------------------------------
        |
        | University Admin:
        |     ALL
        |     CWTS
        |     LTS
        |     ROTC
        |
        | Instructor / Coordinator / Student:
        |     ALL
        |     +
        |     Own component
        |
        | Example:
        |
        | ROTC Instructor:
        |
        | ALL  ✅
        | ROTC ✅
        | CWTS ❌
        | LTS  ❌
        |
        */

        if (
            $access['role'] !==
                Announcement::ROLE_SUPER_ADMIN
        ) {
            if ($access['component']) {
                $query->whereIn(
                    'component',
                    [
                        Announcement::COMPONENT_ALL,
                        $access['component'],
                    ]
                );
            } else {
                $query->where(
                    'component',
                    Announcement::COMPONENT_ALL
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Student Recipient Access
        |--------------------------------------------------------------------------
        |
        | Students can see:
        |
        | 1. Announcements for all students
        | 2. Announcements sent specifically to their email
        |
        */

        if (
            $access['role'] ===
            Announcement::ROLE_STUDENT
        ) {
            $studentEmail = strtolower(
                trim(
                    (string) (
                        $access['actor']->email
                        ?? ''
                    )
                )
            );

            $query->where(
                function (
                    Builder $query
                ) use ($studentEmail) {

                    $query
                        ->where(
                            'recipient',
                            Announcement::RECIPIENT_ALL_STUDENTS
                        )
                        ->orWhere(
                            function (
                                Builder $query
                            ) use ($studentEmail) {

                                $query
                                    ->where(
                                        'recipient',
                                        Announcement::RECIPIENT_SPECIFIC_STUDENT
                                    )
                                    ->whereRaw(
                                        'LOWER(student_email) = ?',
                                        [$studentEmail]
                                    );
                            }
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Get Announcements
        |--------------------------------------------------------------------------
        */

        $announcements = $query
            ->orderByDesc(
                'announcement_date'
            )
            ->orderByDesc(
                'created_at'
            )
            ->get()
            ->map(
                function (
                    Announcement $announcement
                ) use ($access) {

                    return [
                        'id' =>
                            $announcement->id,

                        'university_id' =>
                            $announcement->university_id,

                        'creator_id' =>
                            $announcement->creator_id,

                        'creator_type' =>
                            $announcement->creator_type,

                        'creator_role' =>
                            $announcement->creator_role,

                        'component' =>
                            $announcement->component,

                        'title' =>
                            $announcement->title,

                        'announcement_date' =>
                            $announcement
                                ->announcement_date
                                ?->format('Y-m-d'),

                        'description' =>
                            $announcement->description,

                        'recipient' =>
                            $announcement->recipient,

                        'student_email' =>
                            $announcement->student_email,

                        'created_at' =>
                            $announcement
                                ->created_at
                                ?->toISOString(),

                        'updated_at' =>
                            $announcement
                                ->updated_at
                                ?->toISOString(),

                        /*
                        |--------------------------------------------------------------------------
                        | CRUD Permissions
                        |--------------------------------------------------------------------------
                        */

                        'can_edit' =>
                            $this->canManageAnnouncement(
                                $announcement,
                                $access
                            ),

                        'can_delete' =>
                            $this->canManageAnnouncement(
                                $announcement,
                                $access
                            ),
                    ];
                }
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Sidebar User Information
        |--------------------------------------------------------------------------
        |
        | This fixes:
        |
        | NSTP USER
        | NSTP Staff
        |
        | appearing when moving from Dashboard to Announcements.
        |
        */

        $sidebarUser =
            $this->buildSidebarUser(
                $access
            );


        /*
        |--------------------------------------------------------------------------
        | Return Inertia Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/Announcement',
            [
                /*
                |--------------------------------------------------------------------------
                | Sidebar / Layout
                |--------------------------------------------------------------------------
                */

                'user' =>
                    $sidebarUser,

                'role' =>
                    $access['role'],


                /*
                |--------------------------------------------------------------------------
                | Announcement Data
                |--------------------------------------------------------------------------
                */

                'announcements' =>
                    $announcements,

                'canManage' =>
                    $access['can_manage'],

                'authRole' =>
                    $access['role'],

                'activeComponent' =>
                    $access['component'],

                'componentOptions' =>
                    $access['component_options'],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Announcement
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {
        $access =
            $this->resolveAccess();

        $this->ensureCanManage(
            $access
        );

        $validated =
            $this->validateAnnouncement(
                $request,
                $access
            );

        $component =
            $this->resolveWriteComponent(
                $validated['component']
                    ?? null,
                $access
            );

        $studentEmail =
            $this->validateSpecificStudent(
                $validated,
                $component,
                $access
            );


        /*
        |--------------------------------------------------------------------------
        | Create Announcement
        |--------------------------------------------------------------------------
        */

        Announcement::create([
            'university_id' =>
                $access['university_id'],

            'creator_id' =>
                $access['actor']->getKey(),

            'creator_type' =>
                get_class(
                    $access['actor']
                ),

            'creator_role' =>
                $access['role'],

            'component' =>
                $component,

            'title' =>
                trim(
                    $validated['title']
                ),

            'announcement_date' =>
                $validated[
                    'announcement_date'
                ],

            'description' =>
                trim(
                    $validated[
                        'description'
                    ]
                ),

            'recipient' =>
                $validated[
                    'recipient'
                ],

            'student_email' =>
                $studentEmail,
        ]);


        return back()->with(
            'success',
            'Announcement posted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Announcement
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Announcement $announcement
    ): RedirectResponse {
        $access =
            $this->resolveAccess();


        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        $this->ensureCanManage(
            $access
        );

        $this->ensureCanManageAnnouncement(
            $announcement,
            $access
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $this->validateAnnouncement(
                $request,
                $access
            );


        /*
        |--------------------------------------------------------------------------
        | Component
        |--------------------------------------------------------------------------
        */

        $component =
            $this->resolveWriteComponent(
                $validated['component']
                    ?? null,
                $access
            );


        /*
        |--------------------------------------------------------------------------
        | Specific Student
        |--------------------------------------------------------------------------
        */

        $studentEmail =
            $this->validateSpecificStudent(
                $validated,
                $component,
                $access
            );


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $announcement->update([
            'component' =>
                $component,

            'title' =>
                trim(
                    $validated['title']
                ),

            'announcement_date' =>
                $validated[
                    'announcement_date'
                ],

            'description' =>
                trim(
                    $validated[
                        'description'
                    ]
                ),

            'recipient' =>
                $validated[
                    'recipient'
                ],

            'student_email' =>
                $studentEmail,
        ]);


        return back()->with(
            'success',
            'Announcement updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Announcement
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Announcement $announcement
    ): RedirectResponse {
        $access =
            $this->resolveAccess();


        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        $this->ensureCanManage(
            $access
        );

        $this->ensureCanManageAnnouncement(
            $announcement,
            $access
        );


        /*
        |--------------------------------------------------------------------------
        | Delete
        |--------------------------------------------------------------------------
        */

        $announcement->delete();


        return back()->with(
            'success',
            'Announcement deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Announcement
    |--------------------------------------------------------------------------
    */

    private function validateAnnouncement(
        Request $request,
        array $access
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Component Validation
        |--------------------------------------------------------------------------
        */

        $componentRules = [
            'nullable',
            'string',
        ];


        /*
        |--------------------------------------------------------------------------
        | University Admin
        |--------------------------------------------------------------------------
        |
        | University Admin may choose:
        |
        | ALL
        | CWTS
        | LTS
        | ROTC
        |
        */

        if (
            $access['role'] ===
            Announcement::ROLE_UNIVERSITY_ADMIN
        ) {
            $componentRules = [
                'required',
                'string',

                Rule::in(
                    $access[
                        'component_options'
                    ]
                ),
            ];
        }


        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'announcement_date' => [
                'required',
                'date',
            ],

            'description' => [
                'required',
                'string',
                'max:5000',
            ],

            'component' =>
                $componentRules,

            'recipient' => [
                'required',

                Rule::in([
                    Announcement::RECIPIENT_ALL_STUDENTS,

                    Announcement::RECIPIENT_SPECIFIC_STUDENT,
                ]),
            ],

            'student_email' => [
                'nullable',
                'email',
                'max:191',

                'required_if:recipient,' .
                Announcement::RECIPIENT_SPECIFIC_STUDENT,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Determine Component Used When Creating / Updating
    |--------------------------------------------------------------------------
    */

    private function resolveWriteComponent(
        ?string $requestedComponent,
        array $access
    ): string {
        /*
        |--------------------------------------------------------------------------
        | University Admin
        |--------------------------------------------------------------------------
        */

        if (
            $access['role'] ===
            Announcement::ROLE_UNIVERSITY_ADMIN
        ) {
            $component =
                $this->normalizeComponent(
                    $requestedComponent
                );


            if (
                !$component ||
                !in_array(
                    $component,
                    $access[
                        'component_options'
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'component' =>
                        'Please select a valid NSTP component.',
                ]);
            }


            return $component;
        }


        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        |
        | Instructor cannot fake another component through the browser.
        |
        | Example:
        |
        | ROTC Instructor always creates ROTC announcement.
        |
        */

        if (
            $access['role'] ===
            Announcement::ROLE_INSTRUCTOR
        ) {
            $this->ensureValidAccountComponent(
                $access
            );


            return $access[
                'component'
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Announcement Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            $access['role'] ===
            Announcement::ROLE_COORDINATOR_ANNOUNCEMENT
        ) {
            $this->ensureValidAccountComponent(
                $access
            );


            return $access[
                'component'
            ];
        }


        abort(
            403,
            'You only have permission to view announcements.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Specific Student
    |--------------------------------------------------------------------------
    */

    private function validateSpecificStudent(
        array $validated,
        string $component,
        array $access
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | All Students
        |--------------------------------------------------------------------------
        */

        if (
            $validated['recipient'] ===
            Announcement::RECIPIENT_ALL_STUDENTS
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim(
                (string) (
                    $validated[
                        'student_email'
                    ]
                    ?? ''
                )
            )
        );


        if (!$email) {
            throw ValidationException::withMessages([
                'student_email' =>
                    'Please enter the student email.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Find Student
        |--------------------------------------------------------------------------
        |
        | Student must belong to the same university.
        |
        */

        $student = User::query()
            ->where(
                'university_id',
                $access[
                    'university_id'
                ]
            )
            ->whereRaw(
                'LOWER(email) = ?',
                [$email]
            )
            ->first();


        if (!$student) {
            throw ValidationException::withMessages([
                'student_email' =>
                    'No student with this email was found in your university.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Student Component
        |--------------------------------------------------------------------------
        */

        $studentComponent =
            $this->normalizeComponent(
                $student->component
                    ?? null
            );


        /*
        |--------------------------------------------------------------------------
        | Component Security
        |--------------------------------------------------------------------------
        |
        | ROTC announcement -> ROTC student
        | CWTS announcement -> CWTS student
        | LTS announcement  -> LTS student
        |
        */

        if (
            $component !==
                Announcement::COMPONENT_ALL
            &&
            $studentComponent !==
                $component
        ) {
            throw ValidationException::withMessages([
                'student_email' =>
                    "This student does not belong to the {$component} component.",
            ]);
        }


        return $email;
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Logged-In Account
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Because this page is primarily inside:
    |
    | /instructor-coordinator/*
    |
    | Instructor and Coordinator are checked FIRST.
    |
    | This prevents an old University Admin session from overriding the
    | currently logged-in Instructor / Coordinator session.
    |
    */

    private function resolveAccess(): array
    {
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
            $actor =
                Auth::guard(
                    'instructor'
                )->user();


            $universityId =
                $this->resolveUniversityId(
                    $actor
                );


            $componentOptions =
                $actor->componentCodes();


            $component =
                NstpComponentAccess::selected(
                    request(),
                    [
                        'components' =>
                            $componentOptions,
                    ]
                );


            return [
                'actor' =>
                    $actor,

                'guard' =>
                    'instructor',

                'role' =>
                    Announcement::ROLE_INSTRUCTOR,

                'university_id' =>
                    $universityId,

                'component' =>
                    $component,

                'component_options' =>
                    $componentOptions,

                'can_manage' =>
                    true,
            ];
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
            $actor =
                Auth::guard(
                    'coordinator'
                )->user();


            $universityId =
                $this->resolveUniversityId(
                    $actor
                );


            $component =
                $this->normalizeComponent(
                    $actor->component
                        ?? null
                );


            /*
            |--------------------------------------------------------------------------
            | Announcement Coordinator
            |--------------------------------------------------------------------------
            */

            if (
                $this->coordinatorHasRole(
                    $actor,
                    Announcement::ROLE_COORDINATOR_ANNOUNCEMENT
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        Announcement::ROLE_COORDINATOR_ANNOUNCEMENT,

                    'university_id' =>
                        $universityId,

                    'component' =>
                        $component,

                    'component_options' =>
                        $component
                            ? [$component]
                            : [],

                    'can_manage' =>
                        true,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Attendance Coordinator
            |--------------------------------------------------------------------------
            |
            | VIEW ONLY
            |
            */

            if (
                $this->coordinatorHasRole(
                    $actor,
                    Announcement::ROLE_COORDINATOR_ATTENDANCE
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        Announcement::ROLE_COORDINATOR_ATTENDANCE,

                    'university_id' =>
                        $universityId,

                    'component' =>
                        $component,

                    'component_options' =>
                        $component
                            ? [$component]
                            : [],

                    'can_manage' =>
                        false,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Schedule Coordinator
            |--------------------------------------------------------------------------
            |
            | VIEW ONLY
            |
            */

            if (
                $this->coordinatorHasRole(
                    $actor,
                    Announcement::ROLE_COORDINATOR_SCHEDULE
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        Announcement::ROLE_COORDINATOR_SCHEDULE,

                    'university_id' =>
                        $universityId,

                    'component' =>
                        $component,

                    'component_options' =>
                        $component
                            ? [$component]
                            : [],

                    'can_manage' =>
                        false,
                ];
            }


            abort(
                403,
                'Your coordinator account does not have a recognized role.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'university_admin'
            )->check()
        ) {
            $actor =
                Auth::guard(
                    'university_admin'
                )->user();


            $universityId =
                $this->resolveUniversityId(
                    $actor
                );


            return [
                'actor' =>
                    $actor,

                'guard' =>
                    'university_admin',

                'role' =>
                    Announcement::ROLE_UNIVERSITY_ADMIN,

                'university_id' =>
                    $universityId,

                'component' =>
                    NstpComponentAccess::selected(
                        request(),
                        [
                            'components' =>
                                $this->universityComponentOptions(
                                    $universityId
                                ),
                        ]
                    ),

                'component_options' =>
                    $this->universityComponentOptions(
                        $universityId
                    ),

                'can_manage' =>
                    true,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'web'
            )->check()
        ) {
            $actor =
                Auth::guard(
                    'web'
                )->user();


            $universityId =
                $this->resolveUniversityId(
                    $actor
                );


            $component =
                $this->normalizeComponent(
                    $actor->component
                        ?? null
                );


            return [
                'actor' =>
                    $actor,

                'guard' =>
                    'web',

                'role' =>
                    Announcement::ROLE_STUDENT,

                'university_id' =>
                    $universityId,

                'component' =>
                    $component,

                'component_options' =>
                    $component
                        ? [$component]
                        : [],

                'can_manage' =>
                    false,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | VIEW ONLY
        |
        */

        if (
            Auth::guard(
                'superadmin'
            )->check()
        ) {
            $actor =
                Auth::guard(
                    'superadmin'
                )->user();


            return [
                'actor' =>
                    $actor,

                'guard' =>
                    'superadmin',

                'role' =>
                    Announcement::ROLE_SUPER_ADMIN,

                'university_id' =>
                    null,

                'component' =>
                    null,

                'component_options' =>
                    Announcement::componentsWithAll(),

                'can_manage' =>
                    false,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Not Authenticated
        |--------------------------------------------------------------------------
        */

        abort(
            401,
            'You must be logged in to view announcements.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Build Sidebar User
    |--------------------------------------------------------------------------
    |
    | Normalizes the different account models into the format expected by
    | AdminSideBar.vue.
    |
    */

    private function buildSidebarUser(
        array $access
    ): array {
        $actor =
            $access['actor'];


        /*
        |--------------------------------------------------------------------------
        | Name
        |--------------------------------------------------------------------------
        */

        $fullName =
            $actor->full_name
            ??
            $actor->name
            ??
            null;


        /*
        |--------------------------------------------------------------------------
        | University Administrator Name
        |--------------------------------------------------------------------------
        */

        if (
            !$fullName &&
            (
                isset(
                    $actor->first_name
                ) ||
                isset(
                    $actor->last_name
                )
            )
        ) {
            $fullName = trim(
                implode(
                    ' ',
                    array_filter([
                        $actor->first_name
                            ?? null,

                        $actor->middle_name
                            ?? null,

                        $actor->last_name
                            ?? null,
                    ])
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Profile Photo
        |--------------------------------------------------------------------------
        |
        | Instructor / Coordinator:
        |     profile_photo
        |
        | University Administrator:
        |     photo
        |
        */

        $profilePhoto =
            $actor->profile_photo
            ??
            $actor->photo
            ??
            null;


        return [
            'id' =>
                $actor->getKey(),

            'university_id' =>
                $access[
                    'university_id'
                ],

            'full_name' =>
                $fullName
                ?: 'NSTP USER',

            'username' =>
                $actor->username
                ?? '',

            'email' =>
                $actor->email
                ?? '',

            'phone_number' =>
                $actor->phone_number
                ??
                $actor->phone
                ??
                '',

            'profile_photo' =>
                $profilePhoto,

            'component' =>
                $access[
                    'component'
                ],

            'role' =>
                $access[
                    'role'
                ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Coordinator Role Check
    |--------------------------------------------------------------------------
    */

    private function coordinatorHasRole(
        object $coordinator,
        string $role
    ): bool {
        if (
            !method_exists(
                $coordinator,
                'hasRole'
            )
        ) {
            return false;
        }


        return $coordinator->hasRole(
            $role
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve University ID
    |--------------------------------------------------------------------------
    */

    private function resolveUniversityId(
        object $actor
    ): int {
        $universityId =
            $actor->university_id
            ?? null;


        if (!$universityId) {
            abort(
                403,
                'Your account is not assigned to a university.'
            );
        }


        return (int) $universityId;
    }


    /*
    |--------------------------------------------------------------------------
    | University Component Options
    |--------------------------------------------------------------------------
    */

    private function universityComponentOptions(
        int $universityId
    ): array {
        $university =
            University::find(
                $universityId
            );


        if (!$university) {
            abort(
                403,
                'University account not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Configured University Components
        |--------------------------------------------------------------------------
        */

        $components =
            $university->components
            ?? [];


        if (
            !is_array(
                $components
            )
        ) {
            $components = [];
        }


        $normalized = [];


        foreach (
            $components as $component
        ) {
            $component =
                $this->normalizeComponent(
                    $component
                );


            if (
                $component &&
                in_array(
                    $component,
                    Announcement::components(),
                    true
                )
            ) {
                $normalized[] =
                    $component;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $normalized
            )
        ) {
            $normalized =
                Announcement::components();
        }


        return [
            Announcement::COMPONENT_ALL,

            ...array_values(
                array_unique(
                    $normalized
                )
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Component
    |--------------------------------------------------------------------------
    */

    private function normalizeComponent(
        mixed $component
    ): ?string {
        if (
            $component === null ||
            $component === ''
        ) {
            return null;
        }


        $component = strtoupper(
            trim(
                (string) $component
            )
        );


        if (
            !in_array(
                $component,
                Announcement::componentsWithAll(),
                true
            )
        ) {
            return null;
        }


        return $component;
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Valid Account Component
    |--------------------------------------------------------------------------
    */

    private function ensureValidAccountComponent(
        array $access
    ): void {
        if (
            !$access['component'] ||
            !in_array(
                $access['component'],
                Announcement::components(),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'component' =>
                    'Your account does not have a valid CWTS, LTS, or ROTC component.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure CRUD Permission
    |--------------------------------------------------------------------------
    */

    private function ensureCanManage(
        array $access
    ): void {
        if (
            !$access[
                'can_manage'
            ]
        ) {
            abort(
                403,
                'You only have permission to view announcements.'
            );
        }


        if (
            !Announcement::roleCanManage(
                $access['role']
            )
        ) {
            abort(
                403,
                'You do not have permission to manage announcements.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Can Manage Specific Announcement
    |--------------------------------------------------------------------------
    */

    private function canManageAnnouncement(
        Announcement $announcement,
        array $access
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | View-Only Accounts
        |--------------------------------------------------------------------------
        */

        if (
            !$access[
                'can_manage'
            ]
        ) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        |
        | Can manage every announcement in their own university.
        |
        */

        if (
            $access['role'] ===
            Announcement::ROLE_UNIVERSITY_ADMIN
        ) {
            return (
                (int)
                $announcement->university_id
                ===
                (int)
                $access['university_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Instructor / Announcement Coordinator
        |--------------------------------------------------------------------------
        |
        | Can manage only their own component.
        |
        */

        if (
            in_array(
                $access['role'],
                [
                    Announcement::ROLE_INSTRUCTOR,

                    Announcement::ROLE_COORDINATOR_ANNOUNCEMENT,
                ],
                true
            )
        ) {
            return (
                (int)
                $announcement->university_id
                ===
                (int)
                $access['university_id']
            )
            &&
            (
                strtoupper(
                    (string)
                    $announcement->component
                )
                ===
                $access['component']
            );
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Enforce Specific Announcement Permission
    |--------------------------------------------------------------------------
    */

    private function ensureCanManageAnnouncement(
        Announcement $announcement,
        array $access
    ): void {
        if (
            !$this->canManageAnnouncement(
                $announcement,
                $access
            )
        ) {
            abort(
                403,
                'You cannot modify this announcement.'
            );
        }
    }
}
