<?php

namespace App\Http\Controllers\InstructorCoordinators;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AttendanceSchedule;
use App\Models\Coordinator;
use App\Models\ExcuseLetter;
use App\Models\Instructor;
use App\Models\Report;
use App\Models\RotcStudentProfile;
use App\Models\Schedule;
use App\Models\University;
use App\Models\UniversityAdministrator;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Academic Year Options
    |--------------------------------------------------------------------------
    */

    private const FUTURE_ACADEMIC_YEARS = 5;


    /*
    |--------------------------------------------------------------------------
    | Semester Options
    |--------------------------------------------------------------------------
    |
    | This follows the same wording used by the Super Admin dashboard.
    |
    */

    private const SEMESTERS = [
        '1st Semester',
        '2nd Semester',
    ];


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Resolve Logged-In Instructor / Coordinator
        |--------------------------------------------------------------------------
        */

        $account =
            $this->authenticatedAccount(
                $request
            );


        if (
            $account instanceof RedirectResponse
        ) {
            return $account;
        }


        /** @var Instructor|Coordinator|UniversityAdministrator $actor */

        $actor =
            $account['actor'];


        $role =
            $account['role'];


        $accountType =
            $account['account_type'];


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $actor->load(
            'university'
        );


        /** @var University|null $university */

        $university =
            $actor->university;


        if (
            !$university
        ) {
            return $this->logoutForInvalidUniversity(
                $request,
                $accountType,
                'The university connected to this account could not be found.'
            );
        }


        if (
            !$this->accountIsActive(
                $university->status
            )
        ) {
            return $this->logoutForInvalidUniversity(
                $request,
                $accountType,
                'The university connected to this account is currently inactive.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Component Access
        |--------------------------------------------------------------------------
        |
        | University Admin:
        | - may access LTS, CWTS, and ROTC directly without another login.
        |
        | Instructor:
        | - may manage one or multiple assigned components.
        |
        | Coordinator:
        | - continues to use the component assigned to the account.
        |
        */

        $componentOptions =
            $this->componentOptionsForAccount(
                $actor,
                $accountType
            );


        $component =
            $this->resolveSelectedComponent(
                $request,
                $componentOptions
            );


        $validComponent =
            in_array(
                $component,
                Attendance::components(),
                true
            );


        /*
        |--------------------------------------------------------------------------
        | Academic Year / Semester
        |--------------------------------------------------------------------------
        */

        $academicYears =
            $this->academicYears(
                $university
            );


        $universityAcademicYear =
            $this->normalizeAcademicYear(
                (string) $university->academic_year
            )
            ??
            $this->currentAcademicYear();


        $requestedAcademicYear =
            $this->normalizeAcademicYear(
                (string) $request->query(
                    'academic_year',
                    ''
                )
            );


        $academicYear =
            (
                $requestedAcademicYear !==
                null
                &&
                $academicYears->contains(
                    $requestedAcademicYear
                )
            )
                ? $requestedAcademicYear
                : $universityAcademicYear;


        $universitySemester =
            $this->normalizeSemester(
                (string) $university->semester
            )
            ??
            $this->defaultSemester();


        $requestedSemester =
            $this->normalizeSemester(
                (string) $request->query(
                    'semester',
                    ''
                )
            );


        $semester =
            (
                $requestedSemester !==
                null
            )
                ? $requestedSemester
                : $universitySemester;


        /*
        |--------------------------------------------------------------------------
        | Period Boundaries
        |--------------------------------------------------------------------------
        |
        | Same academic-year convention used by the Super Admin dashboard:
        |
        | 1st Semester:
        | June 1 - December 31
        |
        | 2nd Semester:
        | January 1 - May 31
        |
        */

        [
            $periodStart,
            $periodEnd,
        ] =
            $this->periodRange(
                $academicYear,
                $semester
            );


        $today =
            AttendanceSchedule::today();


        $now =
            AttendanceSchedule::currentDateTime();


        $todayIsInsideSelectedPeriod =
            $now->betweenIncluded(
                $periodStart,
                $periodEnd
            );


        /*
        |--------------------------------------------------------------------------
        | Does The Selected Period Match The University's Current Period?
        |--------------------------------------------------------------------------
        |
        | Users do not currently contain an academic_year column.
        |
        | Because of that, the enrolled-student count is only presented when
        | the selected period matches the university's current database period.
        |
        */

        $selectedPeriodIsCurrentUniversityPeriod =
            (
                $academicYear ===
                $universityAcademicYear
                &&
                $semester ===
                $universitySemester
            );


        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        */

        $studentQuery =
            User::query()
                ->where(
                    'university_id',
                    $university->id
                );


        if (
            $validComponent
        ) {
            $studentQuery->whereRaw(
                'UPPER(TRIM(component)) = ?',
                [
                    $component,
                ]
            );
        } else {
            $studentQuery->whereRaw(
                '1 = 0'
            );
        }


        $students =
            $selectedPeriodIsCurrentUniversityPeriod
                ? (clone $studentQuery)
                    ->count()
                : 0;


        $warningStudents =
            $selectedPeriodIsCurrentUniversityPeriod
                ? (clone $studentQuery)
                    ->where(
                        function (
                            Builder $query
                        ) {

                            $query
                                ->whereRaw(
                                    "UPPER(TRIM(COALESCE(nstp_status, ''))) = ?",
                                    [
                                        'WARNING FOR DROPOUT',
                                    ]
                                )
                                ->orWhereRaw(
                                    "UPPER(TRIM(COALESCE(nstp_status, ''))) = ?",
                                    [
                                        'WARNING',
                                    ]
                                )
                                ->orWhereRaw(
                                    "UPPER(TRIM(COALESCE(nstp_status, ''))) = ?",
                                    [
                                        'AT RISK',
                                    ]
                                )
                                ->orWhereRaw(
                                    "UPPER(TRIM(COALESCE(nstp_status, ''))) = ?",
                                    [
                                        'AT RISK FOR DROPOUT',
                                    ]
                                );

                        }
                    )
                    ->count()
                : 0;


        $dropoutStudents =
            $selectedPeriodIsCurrentUniversityPeriod
                ? (clone $studentQuery)
                    ->where(
                        function (
                            Builder $query
                        ) {

                            $query
                                ->whereRaw(
                                    "UPPER(TRIM(COALESCE(nstp_status, ''))) = ?",
                                    [
                                        'DROPOUT',
                                    ]
                                )
                                ->orWhereRaw(
                                    "UPPER(TRIM(COALESCE(nstp_status, ''))) = ?",
                                    [
                                        'DROPPED',
                                    ]
                                )
                                ->orWhereRaw(
                                    "UPPER(TRIM(COALESCE(nstp_status, ''))) = ?",
                                    [
                                        'DROP OUT',
                                    ]
                                );

                        }
                    )
                    ->count()
                : 0;


        /*
        |--------------------------------------------------------------------------
        | ROTC Completed Profiles
        |--------------------------------------------------------------------------
        */

        $rotcProfilesCompleted =
            0;


        if (
            $selectedPeriodIsCurrentUniversityPeriod
            &&
            $component ===
            Attendance::COMPONENT_ROTC
            &&
            Schema::hasTable(
                'rotc_student_profiles'
            )
        ) {

            $rotcProfilesCompleted =
                RotcStudentProfile::query()
                    ->where(
                        'status',
                        RotcStudentProfile::STATUS_COMPLETED
                    )
                    ->whereHas(
                        'user',
                        function (
                            Builder $query
                        ) use (
                            $university,
                            $component
                        ) {

                            $query
                                ->where(
                                    'university_id',
                                    $university->id
                                )
                                ->whereRaw(
                                    'UPPER(TRIM(component)) = ?',
                                    [
                                        $component,
                                    ]
                                );

                        }
                    )
                    ->count();

        }


        /*
        |--------------------------------------------------------------------------
        | Today's Attendance
        |--------------------------------------------------------------------------
        */

        $attendanceTodayQuery =
            Attendance::query()
                ->whereDate(
                    'attendance_date',
                    $today
                );


        if (
            $validComponent
        ) {
            $attendanceTodayQuery->where(
                'component',
                $component
            );
        } else {
            $attendanceTodayQuery->whereRaw(
                '1 = 0'
            );
        }


        $attendanceTodayQuery->whereHas(
            'student',
            function (
                Builder $query
            ) use (
                $university
            ) {

                $query->where(
                    'university_id',
                    $university->id
                );

            }
        );


        $presentToday =
            $todayIsInsideSelectedPeriod
                ? (clone $attendanceTodayQuery)
                    ->where(
                        'remark',
                        Attendance::REMARK_PRESENT
                    )
                    ->count()
                : 0;


        $lateToday =
            $todayIsInsideSelectedPeriod
                ? (clone $attendanceTodayQuery)
                    ->where(
                        'remark',
                        Attendance::REMARK_LATE
                    )
                    ->count()
                : 0;


        $absentToday =
            $todayIsInsideSelectedPeriod
                ? (clone $attendanceTodayQuery)
                    ->where(
                        'remark',
                        Attendance::REMARK_ABSENT
                    )
                    ->count()
                : 0;


        $excusedToday =
            $todayIsInsideSelectedPeriod
                ? (clone $attendanceTodayQuery)
                    ->where(
                        'remark',
                        Attendance::REMARK_EXCUSED
                    )
                    ->count()
                : 0;


        /*
        |--------------------------------------------------------------------------
        | Excuse Letters
        |--------------------------------------------------------------------------
        */

        $excuseLetterQuery =
            ExcuseLetter::query()
                ->forUniversity(
                    (int) $university->id
                )
                ->whereBetween(
                    'absence_date',
                    [
                        $periodStart->toDateString(),
                        $periodEnd->toDateString(),
                    ]
                );


        if (
            $validComponent
        ) {
            $excuseLetterQuery->forComponent(
                $component
            );
        } else {
            $excuseLetterQuery->whereRaw(
                '1 = 0'
            );
        }


        $totalExcuseLetters =
            (clone $excuseLetterQuery)
                ->count();


        $pendingExcuseLetters =
            (clone $excuseLetterQuery)
                ->pending()
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Announcements
        |--------------------------------------------------------------------------
        */

        $announcementQuery =
            Announcement::query()
                ->where(
                    'university_id',
                    $university->id
                )
                ->whereBetween(
                    'announcement_date',
                    [
                        $periodStart->toDateString(),
                        $periodEnd->toDateString(),
                    ]
                );


        if (
            $validComponent
        ) {
            $announcementQuery->forComponent(
                $component
            );
        } else {
            $announcementQuery->whereRaw(
                '1 = 0'
            );
        }


        $announcementCount =
            (clone $announcementQuery)
                ->count();


        $recentAnnouncements =
            (clone $announcementQuery)
                ->recent()
                ->limit(
                    4
                )
                ->get()
                ->map(
                    fn (
                        Announcement $announcement
                    ): array => [
                        'id' =>
                            $announcement->id,

                        'title' =>
                            $announcement->title,

                        'description' =>
                            $announcement->description,

                        'announcement_date' =>
                            $announcement
                                ->announcement_date
                                ?->format(
                                    'Y-m-d'
                                ),

                        'component' =>
                            $announcement->component,

                        'created_at' =>
                            $announcement
                                ->created_at
                                ?->toISOString(),
                    ]
                )
                ->values()
                ->all();


        /*
        |--------------------------------------------------------------------------
        | Upcoming Schedules
        |--------------------------------------------------------------------------
        */

        $upcomingScheduleQuery =
            Schedule::query()
                ->forUniversity(
                    (int) $university->id
                )
                ->whereBetween(
                    'schedule_date',
                    [
                        max(
                            $today,
                            $periodStart->toDateString()
                        ),
                        $periodEnd->toDateString(),
                    ]
                );


        if (
            $validComponent
        ) {
            $upcomingScheduleQuery->forComponent(
                $component
            );
        } else {
            $upcomingScheduleQuery->whereRaw(
                '1 = 0'
            );
        }


        $upcomingScheduleQuery
            ->orderBy(
                'schedule_date'
            )
            ->orderBy(
                'start_time'
            );


        $upcomingScheduleCount =
            (clone $upcomingScheduleQuery)
                ->count();


        $upcomingSchedules =
            (clone $upcomingScheduleQuery)
                ->limit(
                    4
                )
                ->get()
                ->map(
                    fn (
                        Schedule $schedule
                    ): array => [
                        'id' =>
                            $schedule->id,

                        'title' =>
                            $schedule->title,

                        'schedule_date' =>
                            $schedule
                                ->schedule_date
                                ?->format(
                                    'Y-m-d'
                                ),

                        'start_time' =>
                            $this->timeForFrontend(
                                $schedule->start_time
                            ),

                        'end_time' =>
                            $this->timeForFrontend(
                                $schedule->end_time
                            ),

                        'location' =>
                            $schedule->location,

                        'component' =>
                            $schedule->component,
                    ]
                )
                ->values()
                ->all();


        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        $reportQuery =
            Report::query()
                ->forUniversity(
                    (int) $university->id
                )
                ->whereBetween(
                    'occurrence_date',
                    [
                        $periodStart->toDateString(),
                        $periodEnd->toDateString(),
                    ]
                );


        if (
            $validComponent
        ) {
            $reportQuery->component(
                $component
            );
        } else {
            $reportQuery->whereRaw(
                '1 = 0'
            );
        }


        $totalReports =
            (clone $reportQuery)
                ->count();


        $pendingReports =
            (clone $reportQuery)
                ->pending()
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Attendance Window
        |--------------------------------------------------------------------------
        */

        $attendanceWindow =
            [];


        if (
            $todayIsInsideSelectedPeriod
            &&
            $validComponent
        ) {

            $attendanceSchedule =
                AttendanceSchedule::query()
                    ->where(
                        'university_id',
                        $university->id
                    )
                    ->where(
                        'component',
                        $component
                    )
                    ->whereDate(
                        'attendance_date',
                        $today
                    )
                    ->first();


            if (
                $attendanceSchedule
            ) {
                $attendanceWindow =
                    $attendanceSchedule
                        ->toFrontendArray();
            }

        }


        /*
        |--------------------------------------------------------------------------
        | Recent Activity
        |--------------------------------------------------------------------------
        */

        $recentActivities =
            $this->recentActivities(
                university:
                    $university,

                component:
                    $component,

                validComponent:
                    $validComponent,

                periodStart:
                    $periodStart,

                periodEnd:
                    $periodEnd
            );


        /*
        |--------------------------------------------------------------------------
        | Safe University Data
        |--------------------------------------------------------------------------
        |
        | Do not send access_code to the dashboard frontend.
        |
        */

        $safeUniversity = [
            'id' =>
                (int) $university->id,

            'name' =>
                $university->name,

            'acronym' =>
                $university->acronym,

            /*
            |--------------------------------------------------------------------------
            | Header Identity
            |--------------------------------------------------------------------------
            |
            | UAHeader.vue needs these fields on Instructor / Coordinator pages.
            | Previously they were omitted from this safe payload, so the header
            | fell back to /images/nstphub_logo.png and "UNIVERSITY ADMIN PORTAL".
            |
            */

            'type' =>
                $university->type,

            'campus_type' =>
                $university->campus_type,

            'logo' =>
                $university->logo,

            'logo_url' =>
                $university->logo_url,

            'updated_at' =>
                $university->updated_at?->toISOString(),

            'academic_year' =>
                $universityAcademicYear,

            'semester' =>
                $universitySemester,

            'status' =>
                $university->status,
        ];


        /*
        |--------------------------------------------------------------------------
        | Prevent University Access Code From Being Nested Inside User JSON
        |--------------------------------------------------------------------------
        */

        $actor->unsetRelation(
            'university'
        );


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/Dashboard',
            [
                'user' =>
                    $actor,

                'role' =>
                    $role,

                'accountType' =>
                    $accountType,

                /*
                |--------------------------------------------------------------------------
                | Component Context
                |--------------------------------------------------------------------------
                */

                'selectedComponent' =>
                    $validComponent
                        ? $component
                        : '',

                'componentOptions' =>
                    $componentOptions,

                'isUniversityAdminView' =>
                    $accountType ===
                    'university_admin',

                'auth' => [
                    'user' =>
                        $actor,

                    'university' =>
                        $safeUniversity,

                    'role' =>
                        $role,

                    'account_type' =>
                        $accountType,

                    'selected_component' =>
                        $validComponent
                            ? $component
                            : '',

                    'component_options' =>
                        $componentOptions,
                ],

                'university' =>
                    $safeUniversity,

                'dashboard' => [
                    'component' =>
                        $validComponent
                            ? $component
                            : 'NSTP',

                    'academic_year' =>
                        $academicYear,

                    'semester' =>
                        $semester,

                    'students' =>
                        $students,

                    'present' =>
                        $presentToday,

                    'late' =>
                        $lateToday,

                    'absent' =>
                        $absentToday,

                    'excused' =>
                        $excusedToday,

                    'announcements' =>
                        $announcementCount,

                    'schedules' =>
                        $upcomingScheduleCount,

                    'pendingExcuseLetters' =>
                        $pendingExcuseLetters,

                    'totalExcuseLetters' =>
                        $totalExcuseLetters,

                    'warningStudents' =>
                        $warningStudents,

                    'dropoutStudents' =>
                        $dropoutStudents,

                    'reports' =>
                        $totalReports,

                    'pendingReports' =>
                        $pendingReports,

                    'rotcProfilesCompleted' =>
                        $rotcProfilesCompleted,
                ],

                'periodFilters' => [
                    'academic_year' =>
                        $academicYear,

                    'semester' =>
                        $semester,
                ],

                'academicYears' =>
                    $academicYears
                        ->values()
                        ->all(),

                'semesterOptions' =>
                    self::SEMESTERS,

                'upcomingSchedules' =>
                    $upcomingSchedules,

                'recentAnnouncements' =>
                    $recentAnnouncements,

                'recentActivities' =>
                    $recentActivities,

                'attendanceWindow' =>
                    $attendanceWindow,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticated Account
    |--------------------------------------------------------------------------
    */

    private function authenticatedAccount(
        Request $request
    ): array|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        |
        | A University Admin may open an Instructor/Coordinator component
        | dashboard without logging in as an instructor or coordinator.
        |
        | The admin remains authenticated with the university_admin guard.
        |
        */

        if (
            Auth::guard(
                'university_admin'
            )->check()
        ) {

            /** @var UniversityAdministrator|null $universityAdmin */

            $universityAdmin =
                Auth::guard(
                    'university_admin'
                )->user();


            if (
                !$universityAdmin
            ) {

                Auth::guard(
                    'university_admin'
                )->logout();


                $request
                    ->session()
                    ->forget(
                        'nstp_selected_component'
                    );


                return redirect()
                    ->route(
                        'university-admin.login'
                    )
                    ->withErrors([
                        'login' =>
                            'Your University Administrator account is not available for component dashboard access.',
                    ]);
            }


            return [
                'actor' =>
                    $universityAdmin,

                /*
                |--------------------------------------------------------------------------
                | This is intentionally "university-admin".
                |--------------------------------------------------------------------------
                |
                | The frontend can therefore display:
                |
                | UNIVERSITY ADMIN
                |
                | instead of pretending the admin is an instructor.
                |
                */

                'role' =>
                    'university-admin',

                'account_type' =>
                    'university_admin',
            ];
        }


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
                !$instructor
                ||
                !$instructor->isInstructor()
                ||
                !$this->accountIsActive(
                    $instructor->status
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
                        'instructor-coordinator.login'
                    )
                    ->withErrors([
                        'login' =>
                            'Your Instructor account is not available for dashboard access.',
                    ]);
            }


            return [
                'actor' =>
                    $instructor,

                'role' =>
                    'instructor',

                'account_type' =>
                    'instructor',
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

            /** @var Coordinator|null $coordinator */

            $coordinator =
                Auth::guard(
                    'coordinator'
                )->user();


            $coordinatorRole =
                $coordinator
                    ?->coordinatorRole();


            if (
                !$coordinator
                ||
                !$coordinatorRole
                ||
                !$this->accountIsActive(
                    $coordinator->status
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
                        'instructor-coordinator.login'
                    )
                    ->withErrors([
                        'login' =>
                            'Your Coordinator account is not available for dashboard access.',
                    ]);
            }


            return [
                'actor' =>
                    $coordinator,

                'role' =>
                    $coordinatorRole,

                'account_type' =>
                    'coordinator',
            ];
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
    | Available Components For Current Account
    |--------------------------------------------------------------------------
    */

    private function componentOptionsForAccount(
        Instructor|Coordinator|UniversityAdministrator $actor,
        string $accountType
    ): array {

        /*
        |--------------------------------------------------------------------------
        | University Admin
        |--------------------------------------------------------------------------
        |
        | University administrators may enter every NSTP component dashboard
        | within their own university.
        |
        */

        if (
            $accountType ===
            'university_admin'
        ) {

            $actor->loadMissing(
                'university'
            );


            $universityComponents =
                $actor->university?->components;


            if (
                !is_array(
                    $universityComponents
                )
            ) {

                return [];
            }


            return collect(
                $universityComponents
            )
                ->map(
                    fn (
                        mixed $component
                    ): string =>
                        $this->normalizeComponent(
                            $component
                        )
                )
                ->filter()
                ->unique()
                ->values()
                ->all();
        }


        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        |
        | The updated Instructor model supports multiple component assignments
        | through componentCodes().
        |
        | Backward compatibility:
        | if componentCodes() is unavailable or empty, use the original
        | instructors.component column.
        |
        */

        if (
            $actor instanceof Instructor
        ) {

            $components =
                method_exists(
                    $actor,
                    'componentCodes'
                )
                    ? $actor->componentCodes()
                    : [];


            if (
                empty(
                    $components
                )
            ) {

                $legacy =
                    $this->normalizeComponent(
                        $actor->component
                        ??
                        ''
                    );


                if (
                    $legacy !==
                    ''
                ) {

                    $components = [
                        $legacy,
                    ];
                }
            }


            return collect(
                $components
            )
                ->map(
                    fn (
                        mixed $component
                    ): string =>
                        $this->normalizeComponent(
                            $component
                        )
                )
                ->filter()
                ->unique()
                ->values()
                ->all();
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator
        |--------------------------------------------------------------------------
        */

        $component =
            $this->normalizeComponent(
                $actor->component
                ??
                ''
            );


        return $component !==
            ''
                ? [
                    $component,
                ]
                : [];
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Selected Component
    |--------------------------------------------------------------------------
    |
    | Selection priority:
    |
    | 1. Route parameter:
    |    /university-admin/components/rotc
    |
    | 2. Query parameter:
    |    /instructor-coordinator/dashboard?component=CWTS
    |
    | 3. Session value from a previous selection
    |
    | 4. First component available to the current account
    |
    */

    private function resolveSelectedComponent(
        Request $request,
        array $componentOptions
    ): string {

        $componentOptions =
            collect(
                $componentOptions
            )
                ->map(
                    fn (
                        mixed $component
                    ): string =>
                        $this->normalizeComponent(
                            $component
                        )
                )
                ->filter()
                ->unique()
                ->values()
                ->all();


        if (
            empty(
                $componentOptions
            )
        ) {

            $request
                ->session()
                ->forget(
                    'nstp_selected_component'
                );


            return '';
        }


        /*
        |--------------------------------------------------------------------------
        | Requested Component
        |--------------------------------------------------------------------------
        */

        $requested =
            $this->normalizeComponent(
                $request->route(
                    'component'
                )
                ??
                $request->query(
                    'component'
                )
                ??
                ''
            );


        if (
            $requested !==
            ''
        ) {

            if (
                !in_array(
                    $requested,
                    $componentOptions,
                    true
                )
            ) {

                abort(
                    403,
                    'You do not have access to the selected NSTP component.'
                );
            }


            $request
                ->session()
                ->put(
                    'nstp_selected_component',
                    $requested
                );


            return $requested;
        }


        /*
        |--------------------------------------------------------------------------
        | Session Component
        |--------------------------------------------------------------------------
        */

        $sessionComponent =
            $this->normalizeComponent(
                $request
                    ->session()
                    ->get(
                        'nstp_selected_component'
                    )
            );


        if (
            $sessionComponent !==
            ''
            &&
            in_array(
                $sessionComponent,
                $componentOptions,
                true
            )
        ) {

            return $sessionComponent;
        }


        /*
        |--------------------------------------------------------------------------
        | Default Component
        |--------------------------------------------------------------------------
        */

        $defaultComponent =
            $componentOptions[0];


        $request
            ->session()
            ->put(
                'nstp_selected_component',
                $defaultComponent
            );


        return $defaultComponent;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Component
    |--------------------------------------------------------------------------
    */

    private function normalizeComponent(
        mixed $component
    ): string {

        $component =
            strtoupper(
                trim(
                    (string)
                    (
                        $component
                        ??
                        ''
                    )
                )
            );


        return in_array(
            $component,
            Attendance::components(),
            true
        )
            ? $component
            : '';
    }


    /*
    |--------------------------------------------------------------------------
    | Recent Activity
    |--------------------------------------------------------------------------
    */

    private function recentActivities(
        University $university,
        string $component,
        bool $validComponent,
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd
    ): array {

        $activities =
            collect();


        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        $attendanceQuery =
            Attendance::query()
                ->with(
                    'student'
                )
                ->whereBetween(
                    'attendance_date',
                    [
                        $periodStart->toDateString(),
                        $periodEnd->toDateString(),
                    ]
                )
                ->whereHas(
                    'student',
                    function (
                        Builder $query
                    ) use (
                        $university
                    ) {

                        $query->where(
                            'university_id',
                            $university->id
                        );

                    }
                );


        if (
            $validComponent
        ) {
            $attendanceQuery->where(
                'component',
                $component
            );
        } else {
            $attendanceQuery->whereRaw(
                '1 = 0'
            );
        }


        (clone $attendanceQuery)
            ->latest(
                'updated_at'
            )
            ->limit(
                3
            )
            ->get()
            ->each(
                function (
                    Attendance $attendance
                ) use (
                    $activities
                ) {

                    $studentName =
                        trim(
                            (string) (
                                $attendance
                                    ->student
                                    ?->full_name
                                ??
                                $attendance
                                    ->student
                                    ?->name
                                ??
                                'Student'
                            )
                        );


                    $activities->push([
                        'type' =>
                            'attendance',

                        'title' =>
                            'Attendance recorded',

                        'description' =>
                            $studentName
                            .
                            ' — '
                            .
                            $attendance->remark
                            .
                            '.',

                        'date' =>
                            $this->activityDateLabel(
                                $attendance->updated_at
                                ??
                                $attendance->created_at
                                ??
                                $attendance->attendance_date
                            ),

                        'sort_at' =>
                            $this->sortTimestamp(
                                $attendance->updated_at
                                ??
                                $attendance->created_at
                                ??
                                $attendance->attendance_date
                            ),
                    ]);

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Announcements
        |--------------------------------------------------------------------------
        */

        $announcementQuery =
            Announcement::query()
                ->where(
                    'university_id',
                    $university->id
                )
                ->whereBetween(
                    'announcement_date',
                    [
                        $periodStart->toDateString(),
                        $periodEnd->toDateString(),
                    ]
                );


        if (
            $validComponent
        ) {
            $announcementQuery->forComponent(
                $component
            );
        } else {
            $announcementQuery->whereRaw(
                '1 = 0'
            );
        }


        (clone $announcementQuery)
            ->latest(
                'updated_at'
            )
            ->limit(
                2
            )
            ->get()
            ->each(
                function (
                    Announcement $announcement
                ) use (
                    $activities
                ) {

                    $activities->push([
                        'type' =>
                            'announcement',

                        'title' =>
                            $announcement->title
                            ??
                            'Announcement published',

                        'description' =>
                            'Announcement published for '
                            .
                            strtoupper(
                                (string) $announcement->component
                            )
                            .
                            '.',

                        'date' =>
                            $this->activityDateLabel(
                                $announcement->updated_at
                                ??
                                $announcement->created_at
                                ??
                                $announcement->announcement_date
                            ),

                        'sort_at' =>
                            $this->sortTimestamp(
                                $announcement->updated_at
                                ??
                                $announcement->created_at
                                ??
                                $announcement->announcement_date
                            ),
                    ]);

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Schedules
        |--------------------------------------------------------------------------
        */

        $scheduleQuery =
            Schedule::query()
                ->forUniversity(
                    (int) $university->id
                )
                ->whereBetween(
                    'schedule_date',
                    [
                        $periodStart->toDateString(),
                        $periodEnd->toDateString(),
                    ]
                );


        if (
            $validComponent
        ) {
            $scheduleQuery->forComponent(
                $component
            );
        } else {
            $scheduleQuery->whereRaw(
                '1 = 0'
            );
        }


        (clone $scheduleQuery)
            ->latest(
                'updated_at'
            )
            ->limit(
                2
            )
            ->get()
            ->each(
                function (
                    Schedule $schedule
                ) use (
                    $activities
                ) {

                    $activities->push([
                        'type' =>
                            'schedule',

                        'title' =>
                            $schedule->title
                            ??
                            'NSTP schedule updated',

                        'description' =>
                            'Scheduled for '
                            .
                            (
                                $schedule
                                    ->schedule_date
                                    ?->format(
                                        'M j, Y'
                                    )
                                ??
                                'the selected period'
                            )
                            .
                            '.',

                        'date' =>
                            $this->activityDateLabel(
                                $schedule->updated_at
                                ??
                                $schedule->created_at
                                ??
                                $schedule->schedule_date
                            ),

                        'sort_at' =>
                            $this->sortTimestamp(
                                $schedule->updated_at
                                ??
                                $schedule->created_at
                                ??
                                $schedule->schedule_date
                            ),
                    ]);

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Excuse Letters
        |--------------------------------------------------------------------------
        */

        $excuseQuery =
            ExcuseLetter::query()
                ->with(
                    'student'
                )
                ->forUniversity(
                    (int) $university->id
                )
                ->whereBetween(
                    'absence_date',
                    [
                        $periodStart->toDateString(),
                        $periodEnd->toDateString(),
                    ]
                );


        if (
            $validComponent
        ) {
            $excuseQuery->forComponent(
                $component
            );
        } else {
            $excuseQuery->whereRaw(
                '1 = 0'
            );
        }


        (clone $excuseQuery)
            ->latest(
                'updated_at'
            )
            ->limit(
                2
            )
            ->get()
            ->each(
                function (
                    ExcuseLetter $excuseLetter
                ) use (
                    $activities
                ) {

                    $studentName =
                        trim(
                            (string) (
                                $excuseLetter
                                    ->student
                                    ?->full_name
                                ??
                                $excuseLetter
                                    ->student
                                    ?->name
                                ??
                                'Student'
                            )
                        );


                    $activities->push([
                        'type' =>
                            'excuse-letter',

                        'title' =>
                            'Excuse letter '
                            .
                            strtolower(
                                (string) $excuseLetter->status
                            ),

                        'description' =>
                            $studentName
                            .
                            ' — absence '
                            .
                            (
                                $excuseLetter
                                    ->absence_date
                                    ?->format(
                                        'M j, Y'
                                    )
                                ??
                                'date unavailable'
                            )
                            .
                            '.',

                        'date' =>
                            $this->activityDateLabel(
                                $excuseLetter->updated_at
                                ??
                                $excuseLetter->created_at
                                ??
                                $excuseLetter->absence_date
                            ),

                        'sort_at' =>
                            $this->sortTimestamp(
                                $excuseLetter->updated_at
                                ??
                                $excuseLetter->created_at
                                ??
                                $excuseLetter->absence_date
                            ),
                    ]);

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        $reportQuery =
            Report::query()
                ->forUniversity(
                    (int) $university->id
                )
                ->whereBetween(
                    'occurrence_date',
                    [
                        $periodStart->toDateString(),
                        $periodEnd->toDateString(),
                    ]
                );


        if (
            $validComponent
        ) {
            $reportQuery->component(
                $component
            );
        } else {
            $reportQuery->whereRaw(
                '1 = 0'
            );
        }


        (clone $reportQuery)
            ->latest(
                'updated_at'
            )
            ->limit(
                2
            )
            ->get()
            ->each(
                function (
                    Report $report
                ) use (
                    $activities
                ) {

                    $activities->push([
                        'type' =>
                            'report',

                        'title' =>
                            $report->subject
                            ??
                            'Student report',

                        'description' =>
                            'Report status: '
                            .
                            (
                                $report->status
                                ??
                                Report::STATUS_PENDING
                            )
                            .
                            '.',

                        'date' =>
                            $this->activityDateLabel(
                                $report->updated_at
                                ??
                                $report->created_at
                                ??
                                $report->occurrence_date
                            ),

                        'sort_at' =>
                            $this->sortTimestamp(
                                $report->updated_at
                                ??
                                $report->created_at
                                ??
                                $report->occurrence_date
                            ),
                    ]);

                }
            );


        return $activities
            ->sortByDesc(
                'sort_at'
            )
            ->take(
                6
            )
            ->map(
                function (
                    array $activity
                ): array {

                    unset(
                        $activity['sort_at']
                    );


                    return $activity;

                }
            )
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Academic Years
    |--------------------------------------------------------------------------
    */

    private function academicYears(
        University $university
    ): Collection {

        $currentStartYear =
            AttendanceSchedule::currentDateTime()
                ->month >=
                6
                    ? AttendanceSchedule::currentDateTime()
                        ->year
                    : AttendanceSchedule::currentDateTime()
                        ->year
                        -
                        1;


        $generated =
            collect();


        for (
            $offset = 0;
            $offset <=
            self::FUTURE_ACADEMIC_YEARS;
            $offset++
        ) {

            $startYear =
                $currentStartYear
                +
                $offset;


            $generated->push(
                $startYear
                .
                '-'
                .
                (
                    $startYear
                    +
                    1
                )
            );
        }


        $databaseYear =
            $this->normalizeAcademicYear(
                (string) $university->academic_year
            );


        if (
            $databaseYear
        ) {
            $generated->push(
                $databaseYear
            );
        }


        return $generated
            ->filter()
            ->unique()
            ->sortByDesc(
                function (
                    string $academicYear
                ): int {

                    return (int) (
                        explode(
                            '-',
                            $academicYear
                        )[0]
                        ??
                        0
                    );

                }
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Current Academic Year
    |--------------------------------------------------------------------------
    */

    private function currentAcademicYear(): string
    {
        $now =
            AttendanceSchedule::currentDateTime();


        $startYear =
            $now->month >=
            6
                ? $now->year
                : $now->year - 1;


        return $startYear
            .
            '-'
            .
            (
                $startYear
                +
                1
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Default Semester
    |--------------------------------------------------------------------------
    */

    private function defaultSemester(): string
    {
        return AttendanceSchedule::currentDateTime()
            ->month >=
            6
                ? '1st Semester'
                : '2nd Semester';
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Academic Year
    |--------------------------------------------------------------------------
    */

    private function normalizeAcademicYear(
        string $academicYear
    ): ?string {

        $academicYear =
            trim(
                $academicYear
            );


        if (
            !preg_match(
                '/^(\d{4})-(\d{4})$/',
                $academicYear,
                $matches
            )
        ) {
            return null;
        }


        $startYear =
            (int) $matches[1];


        $endYear =
            (int) $matches[2];


        if (
            $endYear !==
            $startYear + 1
        ) {
            return null;
        }


        return $academicYear;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Semester
    |--------------------------------------------------------------------------
    */

    private function normalizeSemester(
        string $semester
    ): ?string {

        $value =
            strtolower(
                trim(
                    $semester
                )
            );


        return match (
            $value
        ) {

            '1st semester',
            'first semester',
            '1st sem',
            'first sem' =>
                '1st Semester',

            '2nd semester',
            'second semester',
            '2nd sem',
            'second sem' =>
                '2nd Semester',

            default =>
                null,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Selected Period Date Range
    |--------------------------------------------------------------------------
    */

    private function periodRange(
        string $academicYear,
        string $semester
    ): array {

        [
            $startYear,
            $endYear,
        ] =
            array_map(
                'intval',
                explode(
                    '-',
                    $academicYear
                )
            );


        if (
            $semester ===
            '2nd Semester'
        ) {

            return [
                CarbonImmutable::create(
                    $endYear,
                    1,
                    1,
                    0,
                    0,
                    0,
                    AttendanceSchedule::TIMEZONE
                ),

                CarbonImmutable::create(
                    $endYear,
                    5,
                    31,
                    23,
                    59,
                    59,
                    AttendanceSchedule::TIMEZONE
                ),
            ];
        }


        return [
            CarbonImmutable::create(
                $startYear,
                6,
                1,
                0,
                0,
                0,
                AttendanceSchedule::TIMEZONE
            ),

            CarbonImmutable::create(
                $startYear,
                12,
                31,
                23,
                59,
                59,
                AttendanceSchedule::TIMEZONE
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Activity Date Label
    |--------------------------------------------------------------------------
    */

    private function activityDateLabel(
        mixed $value
    ): string {

        if (
            !$value
        ) {
            return 'Recently';
        }


        try {

            return CarbonImmutable::parse(
                (string) $value,
                AttendanceSchedule::TIMEZONE
            )->format(
                'M j, Y · g:i A'
            );

        } catch (
            \Throwable
        ) {

            return 'Recently';

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Sort Timestamp
    |--------------------------------------------------------------------------
    */

    private function sortTimestamp(
        mixed $value
    ): int {

        if (
            !$value
        ) {
            return 0;
        }


        try {

            return CarbonImmutable::parse(
                (string) $value,
                AttendanceSchedule::TIMEZONE
            )->timestamp;

        } catch (
            \Throwable
        ) {

            return 0;

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Time For Frontend
    |--------------------------------------------------------------------------
    */

    private function timeForFrontend(
        mixed $value
    ): ?string {

        $time =
            trim(
                (string) (
                    $value
                    ??
                    ''
                )
            );


        if (
            $time ===
            ''
        ) {
            return null;
        }


        return substr(
            $time,
            0,
            5
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Account Active
    |--------------------------------------------------------------------------
    */

    private function accountIsActive(
        mixed $status
    ): bool {

        return strtoupper(
            trim(
                (string) (
                    $status
                    ??
                    ''
                )
            )
        ) ===
        'ACTIVE';
    }


    /*
    |--------------------------------------------------------------------------
    | Invalid University
    |--------------------------------------------------------------------------
    */

    private function logoutForInvalidUniversity(
        Request $request,
        string $accountType,
        string $message
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | University Admin
        |--------------------------------------------------------------------------
        */

        if (
            $accountType ===
            'university_admin'
        ) {

            Auth::guard(
                'university_admin'
            )->logout();


            $request
                ->session()
                ->forget(
                    'nstp_selected_component'
                );


            return redirect()
                ->route(
                    'university-admin.login'
                )
                ->withErrors([
                    'login' =>
                        $message,
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        */

        if (
            $accountType ===
            'instructor'
        ) {

            Auth::guard(
                'instructor'
            )->logout();
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            $accountType ===
            'coordinator'
        ) {

            Auth::guard(
                'coordinator'
            )->logout();
        }


        $this->clearStaffSession(
            $request
        );


        return redirect()
            ->route(
                'instructor-coordinator.access-code'
            )
            ->withErrors([
                'access_code' =>
                    $message,
            ]);
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
                'staff_logged_out_account',
                'nstp_selected_component',
            ]);
    }
}
