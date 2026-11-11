<?php

namespace App\Http\Controllers\InstructorCoordinators;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSchedule;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\UniversityAdministrator;
use App\Models\User;
use App\Support\NstpComponentAccess;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Small Clock-Skew Tolerance
    |--------------------------------------------------------------------------
    |
    | The phone and the Laravel host can differ by a few seconds. A short
    | tolerance prevents a valid scan at the displayed opening minute from
    | being rejected solely because the server clock is slightly behind/ahead.
    | Scans accepted inside this tolerance are clamped to the configured
    | boundary before they are stored, so attendance never records an earlier
    | or later time than the configured attendance window.
    |
    */
    private const CLOCK_SKEW_TOLERANCE_SECONDS = 90;

    /*
    |--------------------------------------------------------------------------
    | Attendance Page
    |--------------------------------------------------------------------------
    |
    | Mobile/API requests always use the backend Philippine date.
    | Web requests may still use ?date=YYYY-MM-DD for historical viewing,
    | reporting, and downloads.
    |
    */
    public function index(Request $request): Response|JsonResponse
    {
        [$actor, $role] = $this->authenticatedActor();

        $universityId = $this->universityId($actor);
        $canManage = $this->canManageAttendance($role);
        $componentOptions = $this->componentOptionsForActor($actor, $role);
        $activeComponent = $this->resolveActiveComponent(
            $request,
            $componentOptions
        );

        /*
        |--------------------------------------------------------------------------
        | Attendance Date
        |--------------------------------------------------------------------------
        |
        | MOBILE / API
        |
        | Mobile is never allowed to select, send, or override the attendance
        | date. Laravel automatically gets today's exact Philippine date.
        |
        | WEB
        |
        | The web interface may still use ?date=YYYY-MM-DD for historical
        | viewing, report generation, and downloading attendance information.
        |
        */

        if ($request->expectsJson()) {
            $attendanceDate =
                $this->mobileAttendanceDate(
                    $request
                );
        } else {
            $validatedDate = $request->validate([
                'date' => [
                    'nullable',
                    'date_format:Y-m-d',
                ],
            ]);

            $attendanceDate = (string) (
                $validatedDate['date']
                ??
                AttendanceSchedule::today()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Authoritative Philippine Server Time
        |--------------------------------------------------------------------------
        */

        $serverNow =
            $request->expectsJson()
                ? $this->mobileAttendanceNow(
                    $request
                )
                : AttendanceSchedule::currentDateTime();

        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        */

        $studentsQuery = User::query()
            ->where(
                'university_id',
                $universityId
            )
            ->whereNotNull(
                'component'
            );

        if ($activeComponent !== '') {
            $studentsQuery->whereRaw(
                'UPPER(component) = ?',
                [
                    $activeComponent,
                ]
            );
        }

        $students = $studentsQuery
            ->orderBy(
                'surname'
            )
            ->orderBy(
                'first_name'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Attendance For Active Date
        |--------------------------------------------------------------------------
        */

        $studentIds = $students->pluck(
            'id'
        );

        $attendanceQuery = Attendance::query()
            ->whereDate(
                'attendance_date',
                $attendanceDate
            )
            ->whereIn(
                'user_id',
                $studentIds
            );

        if ($activeComponent !== '') {
            $attendanceQuery->where(
                'component',
                $activeComponent
            );
        }

        $attendanceRecords = $attendanceQuery
            ->get()
            ->keyBy(
                fn (
                    Attendance $attendance
                ): string =>
                    $attendance->user_id
                    .
                    '|'
                    .
                    $this->normalizeComponent(
                        $attendance->component
                    )
            );

        /*
        |--------------------------------------------------------------------------
        | Attendance Rows
        |--------------------------------------------------------------------------
        */

        $attendanceRows = $students
            ->map(
                function (
                    User $student
                ) use (
                    $attendanceRecords,
                    $canManage
                ): array {
                    $component =
                        $this->normalizeComponent(
                            $student->component
                        );

                    $attendanceKey =
                        $student->id
                        .
                        '|'
                        .
                        $component;

                    /** @var Attendance|null $attendance */
                    $attendance =
                        $attendanceRecords->get(
                            $attendanceKey
                        );

                    $studentStatus =
                        $this->normalizeStudentStatus(
                            $student->nstp_status
                            ??
                            'ACTIVE'
                        );

                    return [
                        'id' =>
                            $attendance?->id
                            ??
                            (
                                'student-'
                                .
                                $student->id
                            ),

                        'user_id' =>
                            $student->id,

                        'student_id' =>
                            $student->id,

                        'student_id_number' =>
                            $this->studentIdNumber(
                                $student
                            ),

                        'id_number' =>
                            $this->studentIdNumber(
                                $student
                            ),

                        'full_name' =>
                            $student->full_name,

                        'course' =>
                            $student->course
                            ??
                            '-',

                        'profile_photo' =>
                            $student->profile_photo
                            ??
                            null,

                        'profile_photo_url' =>
                            $this->profilePhotoUrl(
                                $student
                            ),

                        'component' =>
                            $component,

                        'attendance_date' =>
                            $attendance
                                ?->attendance_date
                                ?->format(
                                    'Y-m-d'
                                ),

                        'time_in' =>
                            $attendance?->time_in,

                        'time_out' =>
                            $attendance?->time_out,

                        'remark' =>
                            $attendance?->remark,

                        'nstp_status' =>
                            $studentStatus,

                        'status' =>
                            $studentStatus,

                        'can_edit' =>
                            $canManage,

                        'student' => [
                            'id' =>
                                $student->id,

                            'student_id_number' =>
                                $this->studentIdNumber(
                                    $student
                                ),

                            'id_number' =>
                                $this->studentIdNumber(
                                    $student
                                ),

                            'full_name' =>
                                $student->full_name,

                            'name' =>
                                $student->full_name,

                            'surname' =>
                                $student->surname,

                            'first_name' =>
                                $student->first_name,

                            'middle_name' =>
                                $student->middle_name,

                            'course' =>
                                $student->course,

                            'profile_photo' =>
                                $student->profile_photo
                                ??
                                null,

                            'profile_photo_url' =>
                                $this->profilePhotoUrl(
                                    $student
                                ),

                            'year_level' =>
                                $student->year_level,

                            'section' =>
                                $student->section,

                            'component' =>
                                $component,

                            'nstp_status' =>
                                $studentStatus,

                            'status' =>
                                $studentStatus,
                        ],
                    ];
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'active' =>
                $attendanceRows
                    ->where(
                        'status',
                        'ACTIVE'
                    )
                    ->count(),

            'warning' =>
                $attendanceRows
                    ->where(
                        'status',
                        'WARNING FOR DROPOUT'
                    )
                    ->count(),

            'dropout' =>
                $attendanceRows
                    ->where(
                        'status',
                        'DROPOUT'
                    )
                    ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Attendance Schedules
        |--------------------------------------------------------------------------
        */

        $attendanceSchedules =
            AttendanceSchedule::query()
                ->where(
                    'university_id',
                    $universityId
                )
                ->whereDate(
                    'attendance_date',
                    $attendanceDate
                )
                ->whereIn(
                    'component',
                    Attendance::components()
                )
                ->get()
                ->mapWithKeys(
                    fn (
                        AttendanceSchedule $schedule
                    ): array => [
                        $this->normalizeComponent(
                            $schedule->component
                        ) =>
                            $schedule->toFrontendArray(),
                    ]
                );

        /*
        |--------------------------------------------------------------------------
        | Payload
        |--------------------------------------------------------------------------
        */

        $payload = [
            'user' =>
                $this->layoutUser(
                    $actor
                ),

            'role' =>
                $role,

            'authRole' =>
                $role,

            'canManage' =>
                $canManage,

            'activeComponent' =>
                $activeComponent,

            'componentOptions' =>
                $componentOptions,

            /*
            |--------------------------------------------------------------------------
            | Backend Date
            |--------------------------------------------------------------------------
            |
            | Mobile receives this date for display only.
            |
            */

            'attendanceDate' =>
                $attendanceDate,

            'serverNow' =>
                $serverNow->toIso8601String(),

            'serverTime' =>
                $serverNow->format(
                    'g:i:s A'
                ),

            'timezone' =>
                AttendanceSchedule::TIMEZONE,

            'clockSkewToleranceSeconds' =>
                self::CLOCK_SKEW_TOLERANCE_SECONDS,

            'summary' =>
                $summary,

            'attendances' =>
                $attendanceRows,

            'attendanceSchedules' =>
                $attendanceSchedules,

            'scheduleEndpoint' =>
                route(
                    'instructor-coordinator.attendance.schedule.update',
                    [],
                    false
                ),

            'scanFeedback' =>
                $request->hasSession()
                    ? $request
                        ->session()
                        ->get(
                            'attendance_scan'
                        )
                    : null,

            'scanEndpoint' =>
                route(
                    'instructor-coordinator.attendance.scan',
                    [],
                    false
                ),

            'viewRouteBase' =>
                '/instructor-coordinator/attendance/students',

            'editRouteBase' =>
                '/instructor-coordinator/attendance/students',
        ];

        /*
        |--------------------------------------------------------------------------
        | Mobile/API Response
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                ...$payload,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Web Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/Attendance/StudentAttendance',
            $payload
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Student Attendance Standing
    |--------------------------------------------------------------------------
    */

    public function edit(
        User $student
    ): Response {
        [$actor, $role] =
            $this->authenticatedActor();

        if (
            !$this->canManageAttendance(
                $role
            )
        ) {
            abort(
                403,
                'You are not allowed to edit student attendance standing.'
            );
        }

        $this->ensureStudentIsVisible(
            $actor,
            $role,
            $student
        );

        $studentComponent =
            $this->normalizeComponent(
                $student->component
            );

        $studentStatus =
            $this->normalizeStudentStatus(
                $student->nstp_status
                ??
                'ACTIVE'
            );

        return Inertia::render(
            'InstructorCoordinators/Attendance/EditStudentAttendance',
            [
                'user' =>
                    $this->layoutUser(
                        $actor
                    ),

                'role' =>
                    $role,

                'authRole' =>
                    $role,

                'student' => [
                    'id' =>
                        $student->id,

                    'student_id_number' =>
                        $this->studentIdNumber(
                            $student
                        ),

                    'id_number' =>
                        $this->studentIdNumber(
                            $student
                        ),

                    'full_name' =>
                        $student->full_name,

                    'name' =>
                        $student->full_name,

                    'surname' =>
                        $student->surname,

                    'first_name' =>
                        $student->first_name,

                    'middle_name' =>
                        $student->middle_name,

                    'course' =>
                        $student->course
                        ??
                        '-',

                    'year_level' =>
                        $student->year_level,

                    'section' =>
                        $student->section,

                    'component' =>
                        $studentComponent,

                    'nstp_status' =>
                        $studentStatus,

                    'status' =>
                        $studentStatus,

                    'profile_photo' =>
                        $student->profile_photo
                        ??
                        null,

                    'profile_photo_url' =>
                        $this->profilePhotoUrl(
                            $student
                        ),
                ],

                'updateEndpoint' =>
                    route(
                        'instructor-coordinator.attendance.students.status.update',
                        [
                            'student' =>
                                $student->id,
                        ],
                        false
                    ),

                'viewEndpoint' =>
                    route(
                        'instructor-coordinator.attendance.students.show',
                        [
                            'student' =>
                                $student->id,
                        ],
                        false
                    ),

                'backEndpoint' =>
                    route(
                        'instructor-coordinator.attendance.index',
                        [],
                        false
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Student NSTP Standing
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        User $student
    ): RedirectResponse|JsonResponse {
        [$actor, $role] =
            $this->authenticatedActor();

        if (
            !$this->canManageAttendance(
                $role
            )
        ) {
            abort(
                403,
                'You are not allowed to update student attendance standing.'
            );
        }

        $this->ensureStudentIsVisible(
            $actor,
            $role,
            $student
        );

        $validated =
            $request->validate(
                [
                    'nstp_status' => [
                        'required',
                        'string',

                        Rule::in(
                            [
                                'ACTIVE',
                                'WARNING FOR DROPOUT',
                                'DROPOUT',
                            ]
                        ),
                    ],
                ]
            );

        DB::transaction(
            function () use (
                $student,
                $validated
            ): void {
                $lockedStudent =
                    User::query()
                        ->whereKey(
                            $student->getKey()
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $lockedStudent->nstp_status =
                    $validated[
                        'nstp_status'
                    ];

                $lockedStudent->save();
            }
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,

                'message' =>
                    'Student NSTP standing updated successfully.',

                'student' => [
                    'id' =>
                        (int) $student->id,

                    'nstp_status' =>
                        $validated[
                            'nstp_status'
                        ],

                    'status' =>
                        $validated[
                            'nstp_status'
                        ],
                ],
            ]);
        }

        return redirect()
            ->route(
                'instructor-coordinator.attendance.students.show',
                [
                    'student' =>
                        $student->id,
                ]
            )
            ->with(
                'success',
                'Student NSTP standing updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | View Student Attendance
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        User $student
    ): Response|JsonResponse {
        [$actor, $role] =
            $this->authenticatedActor();

        $this->ensureStudentIsVisible(
            $actor,
            $role,
            $student
        );

        $studentComponent =
            $this->normalizeComponent(
                $student->component
            );

        $attendanceQuery =
            Attendance::query()
                ->where(
                    'user_id',
                    $student->id
                );

        if ($studentComponent !== '') {
            $attendanceQuery->where(
                'component',
                $studentComponent
            );
        }

        $attendanceRecords =
            $attendanceQuery
                ->orderByDesc(
                    'attendance_date'
                )
                ->orderByDesc(
                    'id'
                )
                ->get();

        $summary = [
            'present' =>
                $attendanceRecords
                    ->where(
                        'remark',
                        Attendance::REMARK_PRESENT
                    )
                    ->count(),

            'late' =>
                $attendanceRecords
                    ->where(
                        'remark',
                        Attendance::REMARK_LATE
                    )
                    ->count(),

            'excused' =>
                $attendanceRecords
                    ->where(
                        'remark',
                        Attendance::REMARK_EXCUSED
                    )
                    ->count(),

            'absent' =>
                $attendanceRecords
                    ->where(
                        'remark',
                        Attendance::REMARK_ABSENT
                    )
                    ->count(),
        ];

        $studentStatus =
            $this->normalizeStudentStatus(
                $student->nstp_status
                ??
                'ACTIVE'
            );

        $payload = [
            'user' =>
                $this->layoutUser(
                    $actor
                ),

            'role' =>
                $role,

            'authRole' =>
                $role,

            'canManage' =>
                $this->canManageAttendance(
                    $role
                ),

            'student' => [
                'id' =>
                    $student->id,

                'student_id_number' =>
                    $this->studentIdNumber(
                        $student
                    ),

                'id_number' =>
                    $this->studentIdNumber(
                        $student
                    ),

                'full_name' =>
                    $student->full_name,

                'name' =>
                    $student->full_name,

                'surname' =>
                    $student->surname,

                'first_name' =>
                    $student->first_name,

                'middle_name' =>
                    $student->middle_name,

                'course' =>
                    $student->course
                    ??
                    '-',

                'year_level' =>
                    $student->year_level,

                'section' =>
                    $student->section,

                'component' =>
                    $studentComponent,

                'nstp_status' =>
                    $studentStatus,

                'status' =>
                    $studentStatus,

                'profile_photo' =>
                    $student->profile_photo
                    ??
                    null,

                'profile_photo_url' =>
                    $this->profilePhotoUrl(
                        $student
                    ),
            ],

            'attendanceRecords' =>
                $attendanceRecords
                    ->map(
                        fn (
                            Attendance $attendance
                        ): array => [
                            'id' =>
                                $attendance->id,

                            'attendance_date' =>
                                $attendance
                                    ->attendance_date
                                    ?->format(
                                        'Y-m-d'
                                    ),

                            'time_in' =>
                                $attendance->time_in,

                            'time_out' =>
                                $attendance->time_out,

                            'remark' =>
                                $attendance->remark,

                            'notes' =>
                                $attendance->notes,

                            'component' =>
                                $attendance->component,
                        ]
                    )
                    ->values(),

            'summary' =>
                $summary,

            'editEndpoint' =>
                route(
                    'instructor-coordinator.attendance.students.edit',
                    [
                        'student' =>
                            $student->id,
                    ],
                    false
                ),

            'backEndpoint' =>
                route(
                    'instructor-coordinator.attendance.index',
                    [],
                    false
                ),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                ...$payload,
            ]);
        }

        return Inertia::render(
            'InstructorCoordinators/Attendance/ViewStudentAttendance',
            $payload
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Save / Update Today's Attendance Time Windows
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | attendance_date is NOT accepted from the client.
    |
    | Laravel is the source of truth and always uses the current Philippine
    | date through AttendanceSchedule::today().
    |
    */

    public function updateSchedule(
        Request $request
    ): RedirectResponse|JsonResponse {
        [$actor, $role] =
            $this->authenticatedActor();

        if (
            !$this->canManageAttendance(
                $role
            )
        ) {
            abort(
                403,
                'You are not allowed to set attendance time.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'component' => [
                        'required',
                        'string',

                        Rule::in(
                            Attendance::components()
                        ),
                    ],

                    'start_time_in' => [
                        'required',
                        'date_format:H:i',
                    ],

                    'end_time_in' => [
                        'required',
                        'date_format:H:i',
                    ],

                    'time_in_extension_minutes' => [
                        'required',
                        'integer',
                        'min:0',
                        'max:180',
                    ],

                    'start_time_out' => [
                        'required',
                        'date_format:H:i',
                    ],

                    'end_time_out' => [
                        'required',
                        'date_format:H:i',
                    ],

                    'time_out_extension_minutes' => [
                        'required',
                        'integer',
                        'min:0',
                        'max:180',
                    ],
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Component
        |--------------------------------------------------------------------------
        */

        $component =
            $this->normalizeComponent(
                $validated[
                    'component'
                ]
            );

        $this->ensureActorCanUseComponent(
            $actor,
            $role,
            $component
        );

        /*
        |--------------------------------------------------------------------------
        | Backend-Controlled Philippine Date
        |--------------------------------------------------------------------------
        */

        $date =
            $request->expectsJson()
                ? $this->mobileAttendanceDate(
                    $request
                )
                : AttendanceSchedule::today();

        /*
        |--------------------------------------------------------------------------
        | Time In
        |--------------------------------------------------------------------------
        */

        $startTimeIn =
            CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                $date
                .
                ' '
                .
                $validated[
                    'start_time_in'
                ],
                AttendanceSchedule::TIMEZONE
            );

        $endTimeIn =
            CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                $date
                .
                ' '
                .
                $validated[
                    'end_time_in'
                ],
                AttendanceSchedule::TIMEZONE
            );

        $finalTimeInCutoff =
            $endTimeIn->addMinutes(
                (int) $validated[
                    'time_in_extension_minutes'
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Time Out
        |--------------------------------------------------------------------------
        */

        $startTimeOut =
            CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                $date
                .
                ' '
                .
                $validated[
                    'start_time_out'
                ],
                AttendanceSchedule::TIMEZONE
            );

        $endTimeOut =
            CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                $date
                .
                ' '
                .
                $validated[
                    'end_time_out'
                ],
                AttendanceSchedule::TIMEZONE
            );

        $finalTimeOutCutoff =
            $endTimeOut->addMinutes(
                (int) $validated[
                    'time_out_extension_minutes'
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Validate Time Order
        |--------------------------------------------------------------------------
        */

        if (
            !$endTimeIn->greaterThan(
                $startTimeIn
            )
        ) {
            throw ValidationException::withMessages(
                [
                    'end_time_in' =>
                        'On-time until must be later than Check-in opens.',
                ]
            );
        }

        if (
            !$startTimeOut->greaterThan(
                $finalTimeInCutoff
            )
        ) {
            throw ValidationException::withMessages(
                [
                    'start_time_out' =>
                        'Check-out opens must be later than the Late until time.',
                ]
            );
        }

        if (
            !$endTimeOut->greaterThan(
                $startTimeOut
            )
        ) {
            throw ValidationException::withMessages(
                [
                    'end_time_out' =>
                        'Check-out closes must be later than Check-out opens.',
                ]
            );
        }

        if (
            $finalTimeOutCutoff->toDateString() !==
            $date
        ) {
            throw ValidationException::withMessages(
                [
                    'time_out_extension_minutes' =>
                        'The final Check-out time must remain within the same attendance day.',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $universityId =
            $this->universityId(
                $actor
            );

        /*
        |--------------------------------------------------------------------------
        | Save Schedule
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $universityId,
                $component,
                $validated,
                $actor,
                $startTimeIn,
                $endTimeIn,
                $date
            ): void {
                $schedule =
                    AttendanceSchedule::query()
                        ->where(
                            'university_id',
                            $universityId
                        )
                        ->where(
                            'component',
                            $component
                        )
                        ->whereDate(
                            'attendance_date',
                            $date
                        )
                        ->lockForUpdate()
                        ->first();

                if (!$schedule) {
                    $schedule =
                        new AttendanceSchedule();

                    $schedule->university_id =
                        $universityId;

                    $schedule->component =
                        $component;

                    $schedule->attendance_date =
                        $date;

                    $schedule->created_by_type =
                        get_class(
                            $actor
                        );

                    $schedule->created_by_id =
                        $actor->getKey();
                }

                /*
                |--------------------------------------------------------------------------
                | Time Window Columns
                |--------------------------------------------------------------------------
                */

                $schedule->start_time_in =
                    $validated[
                        'start_time_in'
                    ]
                    .
                    ':00';

                $schedule->end_time_in =
                    $validated[
                        'end_time_in'
                    ]
                    .
                    ':00';

                $schedule->time_in_extension_minutes =
                    (int) $validated[
                        'time_in_extension_minutes'
                    ];

                $schedule->start_time_out =
                    $validated[
                        'start_time_out'
                    ]
                    .
                    ':00';

                $schedule->end_time_out =
                    $validated[
                        'end_time_out'
                    ]
                    .
                    ':00';

                $schedule->time_out_extension_minutes =
                    (int) $validated[
                        'time_out_extension_minutes'
                    ];

                /*
                |--------------------------------------------------------------------------
                | Legacy Columns
                |--------------------------------------------------------------------------
                |
                | Keep these synchronized because the existing web/backend
                | implementation already uses them.
                |
                */

                $schedule->time_in =
                    $validated[
                        'start_time_in'
                    ]
                    .
                    ':00';

                $schedule->late_grace_minutes =
                    max(
                        0,
                        (int) floor(
                            (
                                $endTimeIn->getTimestamp()
                                -
                                $startTimeIn->getTimestamp()
                            )
                            /
                            60
                        )
                    );

                $schedule->time_out =
                    $validated[
                        'start_time_out'
                    ]
                    .
                    ':00';

                $schedule->updated_by_type =
                    get_class(
                        $actor
                    );

                $schedule->updated_by_id =
                    $actor->getKey();

                $schedule->save();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Saved Schedule
        |--------------------------------------------------------------------------
        */

        $savedSchedule =
            AttendanceSchedule::query()
                ->where(
                    'university_id',
                    $universityId
                )
                ->where(
                    'component',
                    $component
                )
                ->whereDate(
                    'attendance_date',
                    $date
                )
                ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Recalculate Existing PRESENT / LATE
        |--------------------------------------------------------------------------
        |
        | PRESENT:
        | Check-in opens through "On-time until".
        |
        | LATE:
        | After "On-time until" through "Late until".
        |
        | Existing ABSENT and EXCUSED rows are not changed.
        |
        */

        Attendance::query()
            ->where(
                'component',
                $component
            )
            ->whereDate(
                'attendance_date',
                $date
            )
            ->whereNotNull(
                'time_in'
            )
            ->whereIn(
                'remark',
                [
                    Attendance::REMARK_PRESENT,
                    Attendance::REMARK_LATE,
                ]
            )
            ->whereHas(
                'student',
                fn (
                    $query
                ) =>
                    $query->where(
                        'university_id',
                        $universityId
                    )
            )
            ->get()
            ->each(
                function (
                    Attendance $attendance
                ) use (
                    $savedSchedule
                ): void {
                    $scanAt =
                        CarbonImmutable::createFromFormat(
                            'Y-m-d H:i:s',
                            $attendance
                                ->attendance_date
                                ->format(
                                    'Y-m-d'
                                )
                            .
                            ' '
                            .
                            $attendance->time_in,
                            AttendanceSchedule::TIMEZONE
                        );

                    $newRemark =
                        Attendance::remarkForSchedule(
                            $savedSchedule,
                            $scanAt
                        );

                    if (
                        $attendance->remark !==
                        $newRemark
                    ) {
                        $attendance->remark =
                            $newRemark;

                        $attendance->save();
                    }
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,

                'message' =>
                    'Today\'s attendance schedule was saved successfully.',

                'attendanceDate' =>
                    $date,

                'schedule' =>
                    $savedSchedule
                        ->toFrontendArray(),
            ]);
        }

        return back()
            ->with(
                'success',
                'Attendance Time In and Time Out windows saved successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Today's Attendance Time
    |--------------------------------------------------------------------------
    |
    | Only today's attendance_schedules record is deleted.
    | Existing student attendance records remain untouched.
    |
    */

    public function destroySchedule(
        Request $request,
        string $component
    ): RedirectResponse|JsonResponse {
        [$actor, $role] =
            $this->authenticatedActor();

        /*
        |--------------------------------------------------------------------------
        | Permission
        |--------------------------------------------------------------------------
        */

        if (
            !$this->canManageAttendance(
                $role
            )
        ) {
            abort(
                403,
                'You are not allowed to remove attendance time.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Component
        |--------------------------------------------------------------------------
        */

        $component =
            $this->normalizeComponent(
                $component
            );

        if (
            !in_array(
                $component,
                Attendance::components(),
                true
            )
        ) {
            abort(
                404
            );
        }

        $this->ensureActorCanUseComponent(
            $actor,
            $role,
            $component
        );

        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $universityId =
            $this->universityId(
                $actor
            );

        /*
        |--------------------------------------------------------------------------
        | Delete Today's Schedule
        |--------------------------------------------------------------------------
        */

        $attendanceDate =
            $request->expectsJson()
                ? $this->mobileAttendanceDate(
                    $request
                )
                : AttendanceSchedule::today();

        $deleted =
            DB::transaction(
                function () use (
                    $universityId,
                    $component,
                    $attendanceDate
                ): bool {
                    $schedule =
                        AttendanceSchedule::query()
                            ->where(
                                'university_id',
                                $universityId
                            )
                            ->where(
                                'component',
                                $component
                            )
                            ->whereDate(
                                'attendance_date',
                                $attendanceDate
                            )
                            ->lockForUpdate()
                            ->first();

                    if (!$schedule) {
                        return false;
                    }

                    $schedule->delete();

                    return true;
                }
            );

        if (!$deleted) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,

                    'message' =>
                        'There is no attendance time setting to remove for today.',
                ]);
            }

            return back()
                ->with(
                    'success',
                    'There is no attendance time setting to remove for today.'
                );
        }

        $message =
            $component
            .
            ' attendance time settings have been removed.';

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()
            ->with(
                'success',
                $message
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Scan Student QR
    |--------------------------------------------------------------------------
    |
    | FIRST SCAN  = TIME IN
    | SECOND SCAN = TIME OUT
    |
    | Mobile does NOT provide attendance_date.
    |
    | Laravel uses:
    |
    | AttendanceSchedule::today()
    | AttendanceSchedule::currentDateTime()
    |
    | This prevents the phone/admin from recording attendance under an
    | incorrect manually selected date.
    |
    */

    public function scan(
        Request $request
    ): RedirectResponse|JsonResponse {
        [$actor, $role] =
            $this->authenticatedActor();

        if (
            !$this->canManageAttendance(
                $role
            )
        ) {
            abort(
                403,
                'You are not allowed to record attendance.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        |
        | attendance_date is deliberately absent.
        |
        */

        $validated =
            $request->validate(
                [
                    'qr_token' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'qr_value' => [
                        'nullable',
                        'string',
                        'max:2000',
                    ],

                    'component' => [
                        'required',
                        'string',

                        Rule::in(
                            Attendance::components()
                        ),
                    ],
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | QR Token
        |--------------------------------------------------------------------------
        */

        $qrToken =
            trim(
                (string) $validated[
                    'qr_token'
                ]
            );

        $qrPrefix =
            'NSTPHUB:ATTENDANCE:';

        /*
        |--------------------------------------------------------------------------
        | NSTPHUB:ATTENDANCE:<token>
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                strtoupper(
                    $qrToken
                ),
                $qrPrefix
            )
        ) {
            $qrToken =
                trim(
                    substr(
                        $qrToken,
                        strlen(
                            $qrPrefix
                        )
                    )
                );
        }

        /*
        |--------------------------------------------------------------------------
        | JSON QR Value
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $qrToken,
                '{'
            )
        ) {
            $decoded =
                json_decode(
                    $qrToken,
                    true
                );

            if (
                is_array(
                    $decoded
                )
            ) {
                $qrToken =
                    trim(
                        (string) (
                            $decoded[
                                'qr_token'
                            ]
                            ??
                            $decoded[
                                'token'
                            ]
                            ??
                            $decoded[
                                'student_token'
                            ]
                            ??
                            ''
                        )
                    );

                if (
                    str_starts_with(
                        strtoupper(
                            $qrToken
                        ),
                        $qrPrefix
                    )
                ) {
                    $qrToken =
                        trim(
                            substr(
                                $qrToken,
                                strlen(
                                    $qrPrefix
                                )
                            )
                        );
                }
            }
        }

        if (
            $qrToken ===
            ''
        ) {
            throw ValidationException::withMessages(
                [
                    'qr_token' =>
                        'The scanned QR code does not contain a valid attendance token.',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Backend-Controlled Attendance Date
        |--------------------------------------------------------------------------
        */

        $scanNow =
            $request->expectsJson()
                ? $this->mobileAttendanceNow(
                    $request
                )
                : AttendanceSchedule::currentDateTime();

        $attendanceDate =
            $scanNow->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Component
        |--------------------------------------------------------------------------
        */

        $component =
            $this->normalizeComponent(
                $validated[
                    'component'
                ]
            );

        $this->ensureActorCanUseComponent(
            $actor,
            $role,
            $component
        );

        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $universityId =
            $this->universityId(
                $actor
            );

        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        $student =
            User::query()
                ->where(
                    'university_id',
                    $universityId
                )
                ->where(
                    'qr_token',
                    $qrToken
                )
                ->first();

        if (!$student) {
            throw ValidationException::withMessages(
                [
                    'qr_token' =>
                        'The scanned QR code does not belong to a valid student in your university.',
                ]
            );
        }

        if (
            $this->normalizeComponent(
                $student->component
            ) !==
            $component
        ) {
            throw ValidationException::withMessages(
                [
                    'component' =>
                        'This student is not enrolled in the selected NSTP component.',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Record Attendance
        |--------------------------------------------------------------------------
        */

        $scanResult =
            DB::transaction(
                function () use (
                    $student,
                    $component,
                    $actor,
                    $universityId,
                    $attendanceDate,
                    $scanNow
                ): array {
                    /*
                    |--------------------------------------------------------------------------
                    | Today's Schedule
                    |--------------------------------------------------------------------------
                    */

                    $schedule =
                        AttendanceSchedule::query()
                            ->where(
                                'university_id',
                                $universityId
                            )
                            ->where(
                                'component',
                                $component
                            )
                            ->whereDate(
                                'attendance_date',
                                $attendanceDate
                            )
                            ->lockForUpdate()
                            ->first();

                    if (
                        !$schedule
                        ||
                        !$schedule->isConfigured()
                    ) {
                        throw ValidationException::withMessages(
                            [
                                'qr_token' =>
                                    'Set and save today\'s complete Time In and Time Out schedule before scanning students.',
                            ]
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Existing Student Attendance
                    |--------------------------------------------------------------------------
                    */

                    $attendance =
                        Attendance::query()
                            ->where(
                                'user_id',
                                $student->id
                            )
                            ->where(
                                'component',
                                $component
                            )
                            ->whereDate(
                                'attendance_date',
                                $attendanceDate
                            )
                            ->lockForUpdate()
                            ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | Exact Philippine Time
                    |--------------------------------------------------------------------------
                    */

                    $now =
                        $scanNow;

                    $startTimeInAt =
                        $schedule->startTimeInAt();

                    $endTimeInAt =
                        $schedule->endTimeInAt();

                    $finalTimeInCutoffAt =
                        $schedule->finalTimeInCutoffAt();

                    $startTimeOutAt =
                        $schedule->startTimeOutAt();

                    $endTimeOutAt =
                        $schedule->endTimeOutAt();

                    $finalTimeOutCutoffAt =
                        $schedule->finalTimeOutCutoffAt();

                    /*
                    |--------------------------------------------------------------------------
                    | FIRST SCAN = TIME IN
                    |--------------------------------------------------------------------------
                    |
                    | Check-in opens -> On-time until
                    | = PRESENT
                    |
                    | After On-time until -> Late until
                    | = LATE
                    |
                    */

                    if (
                        !$attendance
                        ||
                        empty(
                            $attendance->time_in
                        )
                    ) {
                        /*
                        |--------------------------------------------------------------------------
                        | Too Early
                        |--------------------------------------------------------------------------
                        */

                        $checkInOpensWithTolerance =
                            $startTimeInAt->subSeconds(
                                self::CLOCK_SKEW_TOLERANCE_SECONDS
                            );

                        if (
                            $now->lessThan(
                                $checkInOpensWithTolerance
                            )
                        ) {
                            throw ValidationException::withMessages(
                                [
                                    'qr_token' =>
                                        'Check-in opens at '
                                        .
                                        $startTimeInAt->format(
                                            'g:i A'
                                        )
                                        .
                                        '.',
                                ]
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Too Late
                        |--------------------------------------------------------------------------
                        */

                        $checkInClosesWithTolerance =
                            $finalTimeInCutoffAt->addSeconds(
                                self::CLOCK_SKEW_TOLERANCE_SECONDS
                            );

                        if (
                            $now->greaterThan(
                                $checkInClosesWithTolerance
                            )
                        ) {
                            throw ValidationException::withMessages(
                                [
                                    'qr_token' =>
                                        'Check-in is already closed. Late check-in ended at '
                                        .
                                        $finalTimeInCutoffAt->format(
                                            'g:i A'
                                        )
                                        .
                                        '.',
                                ]
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Effective Scan Time
                        |--------------------------------------------------------------------------
                        |
                        | If the phone/server clocks differ slightly, keep the stored time inside
                        | the configured window instead of storing an impossible early/late time.
                        |
                        */

                        $effectiveScanAt =
                            $now;

                        if (
                            $effectiveScanAt->lessThan(
                                $startTimeInAt
                            )
                        ) {
                            $effectiveScanAt =
                                $startTimeInAt;
                        }

                        if (
                            $effectiveScanAt->greaterThan(
                                $finalTimeInCutoffAt
                            )
                        ) {
                            $effectiveScanAt =
                                $finalTimeInCutoffAt;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | PRESENT / LATE
                        |--------------------------------------------------------------------------
                        */

                        $remark =
                            Attendance::remarkForSchedule(
                                $schedule,
                                $effectiveScanAt
                            );

                        if (!$attendance) {
                            $attendance =
                                new Attendance();

                            $attendance->user_id =
                                $student->id;

                            $attendance->component =
                                $component;

                            $attendance->attendance_date =
                                $attendanceDate;
                        }

                        $attendance->time_in =
                            $effectiveScanAt->format(
                                'H:i:s'
                            );

                        $attendance->remark =
                            $remark;

                        $attendance->recorded_by_type =
                            get_class(
                                $actor
                            );

                        $attendance->recorded_by_id =
                            $actor->getKey();

                        $attendance->save();

                        return [
                            'type' =>
                                'time_in',

                            'student_id' =>
                                $student->id,

                            'student_name' =>
                                $student->full_name,

                            'attendance_date' =>
                                $attendanceDate,

                            'remark' =>
                                $remark,

                            'time' =>
                                $effectiveScanAt->format(
                                    'g:i A'
                                ),

                            'server_time' =>
                                $now->format(
                                    'g:i:s A'
                                ),

                            'message' =>
                                'Time In recorded for '
                                .
                                $student->full_name
                                .
                                ' at '
                                .
                                $effectiveScanAt->format(
                                    'g:i A'
                                )
                                .
                                ' — '
                                .
                                $remark
                                .
                                '.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SECOND SCAN = TIME OUT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        empty(
                            $attendance->time_out
                        )
                    ) {
                        /*
                        |--------------------------------------------------------------------------
                        | Too Early
                        |--------------------------------------------------------------------------
                        */

                        $checkOutOpensWithTolerance =
                            $startTimeOutAt->subSeconds(
                                self::CLOCK_SKEW_TOLERANCE_SECONDS
                            );

                        if (
                            $now->lessThan(
                                $checkOutOpensWithTolerance
                            )
                        ) {
                            throw ValidationException::withMessages(
                                [
                                    'qr_token' =>
                                        'Check-out opens at '
                                        .
                                        $startTimeOutAt->format(
                                            'g:i A'
                                        )
                                        .
                                        '.',
                                ]
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Too Late
                        |--------------------------------------------------------------------------
                        */

                        $checkOutClosesWithTolerance =
                            $finalTimeOutCutoffAt->addSeconds(
                                self::CLOCK_SKEW_TOLERANCE_SECONDS
                            );

                        if (
                            $now->greaterThan(
                                $checkOutClosesWithTolerance
                            )
                        ) {
                            throw ValidationException::withMessages(
                                [
                                    'qr_token' =>
                                        'Check-out is already closed. Check-out ended at '
                                        .
                                        $finalTimeOutCutoffAt->format(
                                            'g:i A'
                                        )
                                        .
                                        '.',
                                ]
                            );
                        }

                        $effectiveScanAt =
                            $now;

                        if (
                            $effectiveScanAt->lessThan(
                                $startTimeOutAt
                            )
                        ) {
                            $effectiveScanAt =
                                $startTimeOutAt;
                        }

                        if (
                            $effectiveScanAt->greaterThan(
                                $finalTimeOutCutoffAt
                            )
                        ) {
                            $effectiveScanAt =
                                $finalTimeOutCutoffAt;
                        }

                        $attendance->time_out =
                            $effectiveScanAt->format(
                                'H:i:s'
                            );

                        $attendance->recorded_by_type =
                            get_class(
                                $actor
                            );

                        $attendance->recorded_by_id =
                            $actor->getKey();

                        $attendance->save();

                        $extensionMessage =
                            $effectiveScanAt->greaterThan(
                                $endTimeOutAt
                            )
                                ? ' Accepted during the additional Check-out period.'
                                : '';

                        return [
                            'type' =>
                                'time_out',

                            'student_id' =>
                                $student->id,

                            'student_name' =>
                                $student->full_name,

                            'attendance_date' =>
                                $attendanceDate,

                            'time' =>
                                $effectiveScanAt->format(
                                    'g:i A'
                                ),

                            'server_time' =>
                                $now->format(
                                    'g:i:s A'
                                ),

                            'message' =>
                                'Time Out recorded for '
                                .
                                $student->full_name
                                .
                                ' at '
                                .
                                $effectiveScanAt->format(
                                    'g:i A'
                                )
                                .
                                '.'
                                .
                                $extensionMessage,
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Already Complete
                    |--------------------------------------------------------------------------
                    */

                    throw ValidationException::withMessages(
                        [
                            'qr_token' =>
                                $student->full_name
                                .
                                ' already has both Time In and Time Out recorded for today.',
                        ]
                    );
                }
            );

        /*
        |--------------------------------------------------------------------------
        | API Response
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,

                'attendanceDate' =>
                    $attendanceDate,

                'scan' =>
                    $scanResult,

                'message' =>
                    $scanResult[
                        'message'
                    ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Web Response
        |--------------------------------------------------------------------------
        */

        return back()
            ->with(
                'attendance_scan',
                $scanResult
            )
            ->with(
                'success',
                $scanResult[
                    'message'
                ]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticated Actor
    |--------------------------------------------------------------------------
    */

    private function authenticatedActor(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Sanctum / API User
        |--------------------------------------------------------------------------
        */

        $apiActor =
            request()->user();

        if (
            $apiActor instanceof
            UniversityAdministrator
        ) {
            return [
                $apiActor,
                'university-admin',
            ];
        }

        if (
            $apiActor instanceof
            Instructor
            &&
            $apiActor->isInstructor()
        ) {
            return [
                $apiActor,
                'instructor',
            ];
        }

        if (
            $apiActor instanceof
            Coordinator
        ) {
            return [
                $apiActor,
                $this->coordinatorRole(
                    $apiActor
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Web University Administrator
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'university_admin'
            )->check()
        ) {
            return [
                Auth::guard(
                    'university_admin'
                )->user(),

                'university-admin',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Web Instructor
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'instructor'
            )->check()
        ) {
            return [
                Auth::guard(
                    'instructor'
                )->user(),

                'instructor',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Web Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'coordinator'
            )->check()
        ) {
            $coordinator =
                Auth::guard(
                    'coordinator'
                )->user();

            return [
                $coordinator,

                $this->coordinatorRole(
                    $coordinator
                ),
            ];
        }

        abort(
            401,
            'You must be logged in to access attendance.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Coordinator Role
    |--------------------------------------------------------------------------
    */

    private function coordinatorRole(
        object $coordinator
    ): string {
        // Use only the Spatie role actually assigned to this account.
        // Unverified role-like columns must not grant attendance access.
        if ($coordinator instanceof Coordinator) {
            $role = $coordinator->coordinatorRole();

            if ($role !== null) {
                return $role;
            }
        }

        abort(403, 'Your coordinator account has no recognized assigned role.');
    }


    /*
    |--------------------------------------------------------------------------
    | Permission
    |--------------------------------------------------------------------------
    */

    private function canManageAttendance(
        string $role
    ): bool {
        return in_array(
            $role,
            [
                'university-admin',
                'instructor',
                'coordinator-attendance',
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | University
    |--------------------------------------------------------------------------
    */

    private function universityId(
        object $actor
    ): int {
        $universityId =
            (int) (
                $actor->university_id
                ??
                0
            );

        if (
            $universityId <=
            0
        ) {
            abort(
                403,
                'Your account is not assigned to a university.'
            );
        }

        return $universityId;
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Component
    |--------------------------------------------------------------------------
    */

    private function resolveActiveComponent(
        Request $request,
        array $componentOptions
    ): string {
        $component =
            NstpComponentAccess::selected(
                $request,
                [
                    'components' =>
                        $componentOptions,
                ]
            );

        if (
            $component ===
            ''
        ) {
            abort(
                403,
                'Your account is not assigned to an NSTP component.'
            );
        }

        return $component;
    }


    /*
    |--------------------------------------------------------------------------
    | Component Options For Actor
    |--------------------------------------------------------------------------
    |
    | University Administrators:
    | - All university-enabled NSTP components.
    |
    | Instructors:
    | - Every component assigned through instructor_components.
    |
    | Attendance Coordinators:
    | - Their assigned component.
    |
    */

    private function componentOptionsForActor(
        object $actor,
        string $role
    ): array {
        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        */

        if (
            $role ===
            'university-admin'
            &&
            $actor instanceof
            UniversityAdministrator
        ) {
            $actor->loadMissing(
                'university'
            );

            $configured =
                $actor
                    ->university
                    ?->components;

            $components =
                is_array(
                    $configured
                )
                    ? collect(
                        $configured
                    )
                        ->map(
                            fn (
                                mixed $component
                            ): string =>
                                $this->normalizeComponent(
                                    $component
                                )
                        )
                        ->filter(
                            fn (
                                string $component
                            ): bool =>
                                in_array(
                                    $component,
                                    Attendance::components(),
                                    true
                                )
                        )
                        ->unique()
                        ->values()
                        ->all()
                    : [];

            return empty(
                $components
            )
                ? Attendance::components()
                : $components;
        }

        /*
        |--------------------------------------------------------------------------
        | Instructor - Multiple Components
        |--------------------------------------------------------------------------
        */

        if (
            $actor instanceof
            Instructor
            &&
            method_exists(
                $actor,
                'componentCodes'
            )
        ) {
            return collect(
                $actor->componentCodes()
            )
                ->map(
                    fn (
                        mixed $component
                    ): string =>
                        $this->normalizeComponent(
                            $component
                        )
                )
                ->filter(
                    fn (
                        string $component
                    ): bool =>
                        in_array(
                            $component,
                            Attendance::components(),
                            true
                        )
                )
                ->unique()
                ->values()
                ->all();
        }

        /*
        |--------------------------------------------------------------------------
        | Coordinator / Legacy Single Component
        |--------------------------------------------------------------------------
        */

        $component =
            $this->accountComponent(
                $actor
            );

        return $component !== ''
            ? [
                $component,
            ]
            : [];
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Actor Can Use Component
    |--------------------------------------------------------------------------
    */

    private function ensureActorCanUseComponent(
        object $actor,
        string $role,
        string $component
    ): void {
        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        */

        if (
            $role ===
            'university-admin'
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Instructor Multi-Component
        |--------------------------------------------------------------------------
        */

        if (
            method_exists(
                $actor,
                'managesComponent'
            )
        ) {
            if (
                !$actor->managesComponent(
                    $component
                )
            ) {
                throw ValidationException::withMessages(
                    [
                        'component' =>
                            'You can only manage attendance for an NSTP component assigned to your account.',
                    ]
                );
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Coordinator / Legacy Account
        |--------------------------------------------------------------------------
        */

        $assignedComponent =
            $this->accountComponent(
                $actor
            );

        if (
            $assignedComponent ===
            ''
            ||
            $assignedComponent !==
            $component
        ) {
            throw ValidationException::withMessages(
                [
                    'component' =>
                        'You can only manage attendance for your assigned NSTP component.',
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Account Component
    |--------------------------------------------------------------------------
    */

    private function accountComponent(
        object $actor
    ): string {
        $component =
            $this->normalizeComponent(
                $actor->component
                ??
                $actor->nstp_component
                ??
                ''
            );

        if (
            !in_array(
                $component,
                Attendance::components(),
                true
            )
        ) {
            return '';
        }

        return $component;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Component
    |--------------------------------------------------------------------------
    */

    private function normalizeComponent(
        mixed $value
    ): string {
        return strtoupper(
            trim(
                (string) (
                    $value
                    ??
                    ''
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Student Visibility
    |--------------------------------------------------------------------------
    */

    private function ensureStudentIsVisible(
        object $actor,
        string $role,
        User $student
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Same University
        |--------------------------------------------------------------------------
        */

        if (
            (int) $student->university_id !==
            $this->universityId(
                $actor
            )
        ) {
            abort(
                404
            );
        }

        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        */

        if (
            $role ===
            'university-admin'
        ) {
            return;
        }

        $studentComponent =
            $this->normalizeComponent(
                $student->component
            );

        /*
        |--------------------------------------------------------------------------
        | Instructor Multi-Component
        |--------------------------------------------------------------------------
        */

        if (
            $actor instanceof
            Instructor
            &&
            method_exists(
                $actor,
                'managesComponent'
            )
        ) {
            if (
                !$actor->managesComponent(
                    $studentComponent
                )
            ) {
                abort(
                    404
                );
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Coordinator / Legacy
        |--------------------------------------------------------------------------
        */

        $accountComponent =
            $this->accountComponent(
                $actor
            );

        if (
            $accountComponent ===
            ''
            ||
            $studentComponent !==
            $accountComponent
        ) {
            abort(
                404
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Student ID
    |--------------------------------------------------------------------------
    */

    private function studentIdNumber(
        User $student
    ): string {
        return trim(
            (string) (
                $student->student_id_number
                ??
                $student->id_number
                ??
                $student->school_id
                ??
                $student->username
                ??
                '-'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Student Status
    |--------------------------------------------------------------------------
    */

    private function normalizeStudentStatus(
        mixed $value
    ): string {
        $status =
            strtoupper(
                trim(
                    str_replace(
                        [
                            '_',
                            '-',
                        ],
                        ' ',
                        (string) (
                            $value
                            ??
                            ''
                        )
                    )
                )
            );

        if (
            in_array(
                $status,
                [
                    'WARNING',
                    'AT RISK',
                    'AT RISK FOR DROPOUT',
                    'WARNING FOR DROPOUT',
                ],
                true
            )
        ) {
            return 'WARNING FOR DROPOUT';
        }

        if (
            in_array(
                $status,
                [
                    'DROPOUT',
                    'DROP OUT',
                    'DROPPED',
                ],
                true
            )
        ) {
            return 'DROPOUT';
        }

        return 'ACTIVE';
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Photo
    |--------------------------------------------------------------------------
    */

    private function profilePhotoUrl(
        User $student
    ): ?string {
        $path =
            trim(
                (string) (
                    $student->profile_photo
                    ??
                    ''
                )
            );

        if (
            $path ===
            ''
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Complete External URL
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $path,
                'http://'
            )
            ||
            str_starts_with(
                $path,
                'https://'
            )
        ) {
            return $path;
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Public Storage Path
        |--------------------------------------------------------------------------
        |
        | A relative /storage URL is returned so Laravel APP_URL=localhost does
        | not send an unusable localhost URL to the physical mobile device.
        |
        */

        $path =
            ltrim(
                $path,
                '/'
            );

        if (
            str_starts_with(
                $path,
                'public/'
            )
        ) {
            $path =
                substr(
                    $path,
                    strlen(
                        'public/'
                    )
                );
        }

        if (
            str_starts_with(
                $path,
                'app/public/'
            )
        ) {
            $path =
                substr(
                    $path,
                    strlen(
                        'app/public/'
                    )
                );
        }

        if (
            str_starts_with(
                $path,
                'storage/'
            )
        ) {
            return '/'
                .
                $path;
        }

        return '/storage/'
            .
            $path;
    }


    /*
    |--------------------------------------------------------------------------
    | Layout User
    |--------------------------------------------------------------------------
    */

    private function layoutUser(
        object $actor
    ): array {
        return [
            'id' =>
                $actor->getKey(),

            'name' =>
                $actor->name
                ??
                $actor->full_name
                ??
                '',

            'email' =>
                $actor->email
                ??
                '',

            'profile_photo' =>
                $actor->profile_photo
                ??
                null,

            'component' =>
                $actor->component
                ??
                $actor->nstp_component
                ??
                null,

            'university_id' =>
                $actor->university_id
                ??
                null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Admin-Mobile Date / Time Synchronization
    |--------------------------------------------------------------------------
    |
    | The mobile app supplies its local Philippine date/time in request headers.
    | This is used only for JSON/mobile requests and only when the device reports
    | UTC+08:00 and is at most one calendar day away from the server date.
    |
    | Web attendance pages continue to use Laravel/server time normally.
    |
    */
    private function mobileAttendanceNow(
        Request $request
    ): CarbonImmutable {
        $serverNow =
            AttendanceSchedule::currentDateTime();

        $offset =
            trim(
                (string) $request->header(
                    'X-NSTPHUB-Client-Timezone-Offset',
                    ''
                )
            );

        $clientDate =
            trim(
                (string) $request->header(
                    'X-NSTPHUB-Client-Date',
                    ''
                )
            );

        $clientDateTime =
            trim(
                (string) $request->header(
                    'X-NSTPHUB-Client-DateTime',
                    ''
                )
            );

        if (
            $offset !== '480'
            ||
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $clientDate
            )
            ||
            $clientDateTime === ''
        ) {
            return $serverNow;
        }

        try {
            $clientNow =
                CarbonImmutable::parse(
                    $clientDateTime
                )
                    ->setTimezone(
                        AttendanceSchedule::TIMEZONE
                    );

            $clientToday =
                CarbonImmutable::createFromFormat(
                    'Y-m-d',
                    $clientDate,
                    AttendanceSchedule::TIMEZONE
                )
                    ->startOfDay();

            if (
                $clientNow->toDateString()
                !==
                $clientToday->toDateString()
            ) {
                return $serverNow;
            }

            $serverToday =
                $serverNow->startOfDay();

            if (
                abs(
                    $serverToday->diffInDays(
                        $clientToday
                    )
                ) > 1
            ) {
                return $serverNow;
            }

            return $clientNow;
        } catch (\Throwable) {
            return $serverNow;
        }
    }


    private function mobileAttendanceDate(
        Request $request
    ): string {
        return $this
            ->mobileAttendanceNow(
                $request
            )
            ->toDateString();
    }

}
