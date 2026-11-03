<?php

namespace App\Http\Controllers\InstructorCoordinators;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\University;
use App\Support\NstpComponentAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Schedule Page
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

        $query = Schedule::query();


        /*
        |--------------------------------------------------------------------------
        | University Isolation
        |--------------------------------------------------------------------------
        |
        | Every university account can only see schedules belonging to
        | their own university.
        |
        | Super Admin is view-only and may see all.
        |
        */

        if (
            $access['role'] !==
            Schedule::ROLE_SUPER_ADMIN
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
        |
        | ALL
        | CWTS
        | LTS
        | ROTC
        |
        | Instructor / Coordinator / Student:
        |
        | ALL
        | +
        | Own component
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
                Schedule::ROLE_SUPER_ADMIN
        ) {
            if ($access['component']) {
                $query->whereIn(
                    'component',
                    [
                        Schedule::COMPONENT_ALL,
                        $access['component'],
                    ]
                );
            } else {
                $query->where(
                    'component',
                    Schedule::COMPONENT_ALL
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Get Schedules
        |--------------------------------------------------------------------------
        */

        $schedules = $query
            ->orderByDesc(
                'schedule_date'
            )
            ->orderByDesc(
                'start_time'
            )
            ->orderByDesc(
                'created_at'
            )
            ->get()
            ->map(
                function (
                    Schedule $schedule
                ) use ($access) {

                    return [
                        /*
                        |--------------------------------------------------------------------------
                        | Primary Information
                        |--------------------------------------------------------------------------
                        */

                        'id' =>
                            $schedule->id,

                        'university_id' =>
                            $schedule->university_id,


                        /*
                        |--------------------------------------------------------------------------
                        | Creator
                        |--------------------------------------------------------------------------
                        */

                        'creator_id' =>
                            $schedule->creator_id,

                        'creator_type' =>
                            $schedule->creator_type,

                        'creator_role' =>
                            $schedule->creator_role,


                        /*
                        |--------------------------------------------------------------------------
                        | Component
                        |--------------------------------------------------------------------------
                        */

                        'component' =>
                            $schedule->component,


                        /*
                        |--------------------------------------------------------------------------
                        | Schedule Information
                        |--------------------------------------------------------------------------
                        */

                        'title' =>
                            $schedule->title,

                        'schedule_date' =>
                            $schedule
                                ->schedule_date
                                ?->format('Y-m-d'),

                        'start_time' =>
                            $this->normalizeTimeOutput(
                                $schedule->start_time
                            ),

                        'end_time' =>
                            $this->normalizeTimeOutput(
                                $schedule->end_time
                            ),

                        'location' =>
                            $schedule->location,


                        /*
                        |--------------------------------------------------------------------------
                        | Timestamps
                        |--------------------------------------------------------------------------
                        */

                        'created_at' =>
                            $schedule
                                ->created_at
                                ?->toISOString(),

                        'updated_at' =>
                            $schedule
                                ->updated_at
                                ?->toISOString(),


                        /*
                        |--------------------------------------------------------------------------
                        | CRUD Permissions
                        |--------------------------------------------------------------------------
                        */

                        'can_edit' =>
                            $this->canManageSchedule(
                                $schedule,
                                $access
                            ),

                        'can_delete' =>
                            $this->canManageSchedule(
                                $schedule,
                                $access
                            ),
                    ];
                }
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Sidebar User
        |--------------------------------------------------------------------------
        |
        | Keeps the correct:
        |
        | Name
        | Profile Photo
        | Role
        | Component
        |
        | when moving from Dashboard to Schedule.
        |
        */

        $sidebarUser =
            $this->buildSidebarUser(
                $access
            );


        /*
        |--------------------------------------------------------------------------
        | Return Schedule Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/Schedule',
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
                | Schedule Data
                |--------------------------------------------------------------------------
                */

                'schedules' =>
                    $schedules,

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
    | Store Schedule
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Resolve Access
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $this->validateSchedule(
                $request,
                $access
            );


        /*
        |--------------------------------------------------------------------------
        | Resolve Component
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
        | Create Schedule
        |--------------------------------------------------------------------------
        */

        Schedule::create([
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

            'schedule_date' =>
                $validated[
                    'schedule_date'
                ],

            'start_time' =>
                $validated[
                    'start_time'
                ],

            'end_time' =>
                $validated[
                    'end_time'
                ],

            'location' =>
                trim(
                    $validated[
                        'location'
                    ]
                ),
        ]);


        return back()->with(
            'success',
            'Schedule posted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Schedule
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Schedule $schedule
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Resolve Access
        |--------------------------------------------------------------------------
        */

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

        $this->ensureCanManageSchedule(
            $schedule,
            $access
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $this->validateSchedule(
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
        | Update Schedule
        |--------------------------------------------------------------------------
        */

        $schedule->update([
            'component' =>
                $component,

            'title' =>
                trim(
                    $validated['title']
                ),

            'schedule_date' =>
                $validated[
                    'schedule_date'
                ],

            'start_time' =>
                $validated[
                    'start_time'
                ],

            'end_time' =>
                $validated[
                    'end_time'
                ],

            'location' =>
                trim(
                    $validated[
                        'location'
                    ]
                ),
        ]);


        return back()->with(
            'success',
            'Schedule updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Schedule
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Schedule $schedule
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Resolve Access
        |--------------------------------------------------------------------------
        */

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

        $this->ensureCanManageSchedule(
            $schedule,
            $access
        );


        /*
        |--------------------------------------------------------------------------
        | Delete
        |--------------------------------------------------------------------------
        */

        $schedule->delete();


        return back()->with(
            'success',
            'Schedule deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Schedule
    |--------------------------------------------------------------------------
    */

    private function validateSchedule(
        Request $request,
        array $access
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Component Rules
        |--------------------------------------------------------------------------
        |
        | Instructor / Coordinator component comes from their account.
        |
        | University Admin selects from the allowed university components.
        |
        */

        $componentRules = [
            'nullable',
            'string',
        ];


        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        */

        if (
            $access['role'] ===
            Schedule::ROLE_UNIVERSITY_ADMIN
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


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'schedule_date' => [
                'required',
                'date',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],

            'location' => [
                'required',
                'string',
                'max:255',
            ],

            'component' =>
                $componentRules,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Write Component
    |--------------------------------------------------------------------------
    |
    | University Admin:
    |
    | Can select ALL / CWTS / LTS / ROTC.
    |
    | Instructor:
    |
    | Automatically uses their assigned component.
    |
    | Schedule Coordinator:
    |
    | Automatically uses their assigned component.
    |
    */

    private function resolveWriteComponent(
        ?string $requestedComponent,
        array $access
    ): string {
        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        */

        if (
            $access['role'] ===
            Schedule::ROLE_UNIVERSITY_ADMIN
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
        | Example:
        |
        | ROTC Instructor
        |
        | Browser submits CWTS ❌
        |
        | Controller ignores it.
        |
        | Saved component remains ROTC ✅
        |
        */

        if (
            $access['role'] ===
            Schedule::ROLE_INSTRUCTOR
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
        | Schedule Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            $access['role'] ===
            Schedule::ROLE_COORDINATOR_SCHEDULE
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
        | View-Only User
        |--------------------------------------------------------------------------
        */

        abort(
            403,
            'You only have permission to view schedules.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Logged-In Account
    |--------------------------------------------------------------------------
    |
    | Because this controller is used primarily under:
    |
    | /instructor-coordinator/*
    |
    | Instructor and Coordinator are checked before other guards.
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
                    Schedule::ROLE_INSTRUCTOR,

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
            | Schedule Coordinator
            |--------------------------------------------------------------------------
            |
            | FULL CRUD ACCESS
            |
            */

            if (
                $this->coordinatorHasRole(
                    $actor,
                    Schedule::ROLE_COORDINATOR_SCHEDULE
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        Schedule::ROLE_COORDINATOR_SCHEDULE,

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
                    Schedule::ROLE_COORDINATOR_ATTENDANCE
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        Schedule::ROLE_COORDINATOR_ATTENDANCE,

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
            | Announcement Coordinator
            |--------------------------------------------------------------------------
            |
            | VIEW ONLY
            |
            */

            if (
                $this->coordinatorHasRole(
                    $actor,
                    Schedule::ROLE_COORDINATOR_ANNOUNCEMENT
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        Schedule::ROLE_COORDINATOR_ANNOUNCEMENT,

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
            | Invalid Coordinator Role
            |--------------------------------------------------------------------------
            */

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
                    Schedule::ROLE_UNIVERSITY_ADMIN,

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
                    Schedule::ROLE_STUDENT,

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
                    Schedule::ROLE_SUPER_ADMIN,

                'university_id' =>
                    null,

                'component' =>
                    null,

                'component_options' =>
                    Schedule::componentsWithAll(),

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
            'You must be logged in to view schedules.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Build Sidebar User
    |--------------------------------------------------------------------------
    |
    | Keeps Admin_IC_Layout and AdminSideBar information consistent with
    | Dashboard and Announcement pages.
    |
    */

    private function buildSidebarUser(
        array $access
    ): array {
        $actor =
            $access['actor'];


        /*
        |--------------------------------------------------------------------------
        | Full Name
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
                )
                ||
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
        */

        $profilePhoto =
            $actor->profile_photo
            ??
            $actor->photo
            ??
            null;


        /*
        |--------------------------------------------------------------------------
        | Return Sidebar User
        |--------------------------------------------------------------------------
        */

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


        if (
            !$universityId
        ) {
            abort(
                403,
                'Your account is not assigned to a university.'
            );
        }


        return (int)
            $universityId;
    }


    /*
    |--------------------------------------------------------------------------
    | University Component Options
    |--------------------------------------------------------------------------
    |
    | Uses the components configured for that university.
    |
    */

    private function universityComponentOptions(
        int $universityId
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Find University
        |--------------------------------------------------------------------------
        */

        $university =
            University::find(
                $universityId
            );


        if (
            !$university
        ) {
            abort(
                403,
                'University account not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Components
        |--------------------------------------------------------------------------
        */

        $components =
            $university->components
            ?? [];


        /*
        |--------------------------------------------------------------------------
        | Ensure Array
        |--------------------------------------------------------------------------
        */

        if (
            !is_array(
                $components
            )
        ) {
            $components = [];
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Components
        |--------------------------------------------------------------------------
        */

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
                    Schedule::components(),
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
        |
        | If university components are not configured yet,
        | allow the standard NSTP components.
        |
        */

        if (
            empty(
                $normalized
            )
        ) {
            $normalized =
                Schedule::components();
        }


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [
            Schedule::COMPONENT_ALL,

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
        /*
        |--------------------------------------------------------------------------
        | Empty
        |--------------------------------------------------------------------------
        */

        if (
            $component === null
            ||
            $component === ''
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $component = strtoupper(
            trim(
                (string)
                $component
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $component,
                Schedule::componentsWithAll(),
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
            !$access['component']
            ||
            !in_array(
                $access['component'],
                Schedule::components(),
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
        /*
        |--------------------------------------------------------------------------
        | View-Only
        |--------------------------------------------------------------------------
        */

        if (
            !$access[
                'can_manage'
            ]
        ) {
            abort(
                403,
                'You only have permission to view schedules.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Role
        |--------------------------------------------------------------------------
        */

        if (
            !Schedule::roleCanManage(
                $access['role']
            )
        ) {
            abort(
                403,
                'You do not have permission to manage schedules.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Can Manage Specific Schedule?
    |--------------------------------------------------------------------------
    */

    private function canManageSchedule(
        Schedule $schedule,
        array $access
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | View-Only Account
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
        | Can manage any schedule belonging to their university.
        |
        */

        if (
            $access['role'] ===
            Schedule::ROLE_UNIVERSITY_ADMIN
        ) {
            return (
                (int)
                $schedule->university_id
                ===
                (int)
                $access['university_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Instructor / Schedule Coordinator
        |--------------------------------------------------------------------------
        |
        | Can only manage schedules belonging to their own component.
        |
        | Example:
        |
        | ROTC Instructor
        |
        | ROTC schedule ✅
        | CWTS schedule ❌
        | LTS schedule  ❌
        | ALL schedule  ❌
        |
        */

        if (
            in_array(
                $access['role'],
                [
                    Schedule::ROLE_INSTRUCTOR,

                    Schedule::ROLE_COORDINATOR_SCHEDULE,
                ],
                true
            )
        ) {
            return (
                (int)
                $schedule->university_id
                ===
                (int)
                $access['university_id']
            )
            &&
            (
                strtoupper(
                    (string)
                    $schedule->component
                )
                ===
                $access['component']
            );
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Enforce Schedule Permission
    |--------------------------------------------------------------------------
    */

    private function ensureCanManageSchedule(
        Schedule $schedule,
        array $access
    ): void {
        if (
            !$this->canManageSchedule(
                $schedule,
                $access
            )
        ) {
            abort(
                403,
                'You cannot modify this schedule.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Time Output
    |--------------------------------------------------------------------------
    |
    | MySQL normally returns:
    |
    | 08:00:00
    |
    | Vue time input expects:
    |
    | 08:00
    |
    */

    private function normalizeTimeOutput(
        mixed $time
    ): ?string {
        if (
            !$time
        ) {
            return null;
        }


        $time =
            (string)
            $time;


        if (
            strlen(
                $time
            ) >= 5
        ) {
            return substr(
                $time,
                0,
                5
            );
        }


        return $time;
    }
}
