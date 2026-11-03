<?php

namespace App\Http\Controllers\UniversityAdmin;

use App\Http\Controllers\Controller;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\Report;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | University Admin Dashboard
    |--------------------------------------------------------------------------
    |
    | This dashboard is intentionally scoped to the authenticated University
    | Administrator's university. It reads the existing NSTP models and does
    | not create duplicate dashboard tables.
    |
    */

    public function index(Request $request): Response
    {
        $administrator =
            auth(
                'university_admin'
            )->user();

        abort_unless(
            $administrator,
            403
        );

        $administrator->loadMissing(
            'university'
        );

        $university =
            $administrator->university;

        abort_if(
            !$university,
            404,
            'University record not found.'
        );

        $universityId =
            (int) $university->getKey();


        /*
        |--------------------------------------------------------------------------
        | Academic Period
        |--------------------------------------------------------------------------
        */

        $academicYears =
            $this->academicYearOptions(
                (string) (
                    $university->academic_year
                    ??
                    ''
                )
            );

        $selectedAcademicYear =
            $this->resolveAcademicYear(
                $request,
                $academicYears,
                (string) (
                    $university->academic_year
                    ??
                    ''
                )
            );

        $semesterOptions =
            $this->semesterOptions(
                (string) (
                    $university->semester
                    ??
                    ''
                )
            );

        $selectedSemester =
            $this->resolveSemester(
                $request,
                $semesterOptions,
                (string) (
                    $university->semester
                    ??
                    ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Enabled NSTP Components
        |--------------------------------------------------------------------------
        */

        $enabledComponents =
            collect(
                $university->components
                ??
                []
            )
                ->map(
                    fn ($component) =>
                        strtoupper(
                            trim(
                                (string) $component
                            )
                        )
                )
                ->filter(
                    fn ($component) =>
                        in_array(
                            $component,
                            [
                                'CWTS',
                                'LTS',
                                'ROTC',
                            ],
                            true
                        )
                )
                ->unique()
                ->values();

        if (
            $enabledComponents->isEmpty()
        ) {
            $enabledComponents =
                collect([
                    'CWTS',
                    'LTS',
                    'ROTC',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        */

        $studentQuery =
            User::query()
                ->where(
                    'university_id',
                    $universityId
                );

        $this->applyExplicitPeriodColumns(
            $studentQuery,
            (new User())->getTable(),
            $selectedAcademicYear,
            $selectedSemester
        );

        $totalStudents =
            (clone $studentQuery)
                ->count();

        $activeStudents =
            $this->countStatus(
                $studentQuery,
                'nstp_status',
                [
                    'ACTIVE',
                ]
            );

        $warningStudents =
            $this->countStatus(
                $studentQuery,
                'nstp_status',
                [
                    'WARNING FOR DROPOUT',
                    'WARNING',
                ]
            );

        $dropoutStudents =
            $this->countStatus(
                $studentQuery,
                'nstp_status',
                [
                    'DROPOUT',
                    'DROPPED',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Student Registration Status
        |--------------------------------------------------------------------------
        |
        | Older NSTP HUB databases may not have a dedicated
        | users.registration_status column yet. The dashboard detects this
        | safely instead of assuming a column that may not exist.
        |
        */

        $registrationStatusColumn =
            $this->firstExistingColumn(
                (new User())->getTable(),
                [
                    'registration_status',
                    'approval_status',
                ]
            );

        $registrationTracked =
            $registrationStatusColumn !==
            null;

        $approvedRegistrations =
            $registrationTracked
                ? $this->countStatus(
                    $studentQuery,
                    $registrationStatusColumn,
                    [
                        'APPROVED',
                        'CONFIRMED',
                        'COMPLETED',
                    ]
                )
                : null;

        $pendingRegistrations =
            $registrationTracked
                ? $this->countStatus(
                    $studentQuery,
                    $registrationStatusColumn,
                    [
                        'PENDING',
                        'SUBMITTED',
                        'FOR APPROVAL',
                    ]
                )
                : null;


        /*
        |--------------------------------------------------------------------------
        | Staff
        |--------------------------------------------------------------------------
        */

        $instructorQuery =
            Instructor::query()
                ->where(
                    'university_id',
                    $universityId
                );

        $coordinatorQuery =
            Coordinator::query()
                ->where(
                    'university_id',
                    $universityId
                );

        $totalInstructors =
            (clone $instructorQuery)
                ->count();

        $totalCoordinators =
            (clone $coordinatorQuery)
                ->count();

        $activeInstructors =
            $this->countStatus(
                $instructorQuery,
                'status',
                [
                    'ACTIVE',
                ]
            );

        $activeCoordinators =
            $this->countStatus(
                $coordinatorQuery,
                'status',
                [
                    'ACTIVE',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Student Reports
        |--------------------------------------------------------------------------
        */

        $reportQuery =
            Report::query()
                ->where(
                    'university_id',
                    $universityId
                );

        $this->applyExplicitPeriodColumns(
            $reportQuery,
            (new Report())->getTable(),
            $selectedAcademicYear,
            $selectedSemester
        );

        $totalReports =
            (clone $reportQuery)
                ->count();

        $pendingReports =
            (clone $reportQuery)
                ->where(
                    'status',
                    Report::STATUS_PENDING
                )
                ->count();

        $inReviewReports =
            (clone $reportQuery)
                ->where(
                    'status',
                    Report::STATUS_IN_REVIEW
                )
                ->count();

        $resolvedReports =
            (clone $reportQuery)
                ->where(
                    'status',
                    Report::STATUS_RESOLVED
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Component Summary
        |--------------------------------------------------------------------------
        */

        $componentSummary =
            collect([
                'CWTS',
                'LTS',
                'ROTC',
            ])
                ->map(
                    function (
                        string $component
                    ) use (
                        $studentQuery,
                        $instructorQuery,
                        $coordinatorQuery,
                        $reportQuery,
                        $enabledComponents
                    ) {
                        return [
                            'name' =>
                                $component,

                            'enabled' =>
                                $enabledComponents
                                    ->contains(
                                        $component
                                    ),

                            'students' =>
                                (clone $studentQuery)
                                    ->where(
                                        'component',
                                        $component
                                    )
                                    ->count(),

                            'instructors' =>
                                (clone $instructorQuery)
                                    ->where(
                                        'component',
                                        $component
                                    )
                                    ->count(),

                            'coordinators' =>
                                (clone $coordinatorQuery)
                                    ->where(
                                        'component',
                                        $component
                                    )
                                    ->count(),

                            'reports' =>
                                (clone $reportQuery)
                                    ->where(
                                        'component',
                                        $component
                                    )
                                    ->count(),
                        ];
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | University Capacity
        |--------------------------------------------------------------------------
        */

        $maximumStudents =
            max(
                0,
                (int) (
                    $university->max_students
                    ??
                    0
                )
            );

        $capacityPercent =
            $maximumStudents >
            0
                ? min(
                    100,
                    (int) round(
                        (
                            $totalStudents
                            /
                            $maximumStudents
                        )
                        *
                        100
                    )
                )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | Recent Students
        |--------------------------------------------------------------------------
        */

        $recentStudentQuery =
            User::query()
                ->where(
                    'university_id',
                    $universityId
                );

        $this->applyExplicitPeriodColumns(
            $recentStudentQuery,
            (new User())->getTable(),
            $selectedAcademicYear,
            $selectedSemester
        );

        $recentStudents =
            $recentStudentQuery
                ->latest(
                    'created_at'
                )
                ->limit(
                    5
                )
                ->get()
                ->map(
                    function (
                        User $student
                    ) {
                        return [
                            'id' =>
                                $student->getKey(),

                            'name' =>
                                $this->studentName(
                                    $student
                                ),

                            'email' =>
                                $student->email,

                            'course' =>
                                $student->course,

                            'component' =>
                                strtoupper(
                                    trim(
                                        (string) (
                                            $student->component
                                            ??
                                            ''
                                        )
                                    )
                                ),

                            'status' =>
                                strtoupper(
                                    trim(
                                        (string) (
                                            $student->nstp_status
                                            ??
                                            ''
                                        )
                                    )
                                ),

                            'created_at' =>
                                optional(
                                    $student->created_at
                                )
                                    ?->toIso8601String(),

                            'created_label' =>
                                optional(
                                    $student->created_at
                                )
                                    ?->format(
                                        'M d, Y'
                                    ),
                        ];
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Recent Reports
        |--------------------------------------------------------------------------
        */

        $recentReportQuery =
            Report::query()
                ->where(
                    'university_id',
                    $universityId
                )
                ->with(
                    'student'
                );

        $this->applyExplicitPeriodColumns(
            $recentReportQuery,
            (new Report())->getTable(),
            $selectedAcademicYear,
            $selectedSemester
        );

        $recentReports =
            $recentReportQuery
                ->latest(
                    'created_at'
                )
                ->limit(
                    5
                )
                ->get()
                ->map(
                    function (
                        Report $report
                    ) {
                        $component =
                            strtoupper(
                                trim(
                                    (string) (
                                        $report->component
                                        ??
                                        ''
                                    )
                                )
                            );

                        return [
                            'id' =>
                                $report->getKey(),

                            'subject' =>
                                $report->subject
                                ??
                                'Student Report',

                            'student' =>
                                $report->student
                                    ? $this->studentName(
                                        $report->student
                                    )
                                    : 'Student',

                            'component' =>
                                $component,

                            'status' =>
                                $report->status,

                            'occurrence_date' =>
                                optional(
                                    $report->occurrence_date
                                )
                                    ?->format(
                                        'M d, Y'
                                    ),

                            'created_at' =>
                                optional(
                                    $report->created_at
                                )
                                    ?->toIso8601String(),

                            'created_label' =>
                                optional(
                                    $report->created_at
                                )
                                    ?->format(
                                        'M d, Y'
                                    ),

                            'route' =>
                                in_array(
                                    $component,
                                    [
                                        'CWTS',
                                        'LTS',
                                        'ROTC',
                                    ],
                                    true
                                )
                                    ? '/university-admin/reports/'
                                        .
                                        strtolower(
                                            $component
                                        )
                                    : '/university-admin/reports/lts',
                        ];
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Recent Activity
        |--------------------------------------------------------------------------
        */

        $recentActivities =
            $this->recentActivities(
                $universityId,
                $recentStudents,
                $recentReports
            );


        /*
        |--------------------------------------------------------------------------
        | Dashboard Payload
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/dashboardUAPage',
            [
                'dashboard' => [
                    'academic_year' =>
                        $selectedAcademicYear,

                    'semester' =>
                        $selectedSemester,

                    'university' => [
                        'id' =>
                            $universityId,

                        'name' =>
                            $university->name,

                        'acronym' =>
                            $university->acronym,

                        'type' =>
                            $university->type,

                        'campus_type' =>
                            $university->campus_type,

                        'logo' =>
                            $university->logo,

                        'logo_url' =>
                            $university->logo_url,

                        'updated_at' =>
                            optional(
                                $university->updated_at
                            )->toISOString(),

                        'city' =>
                            $university->city,

                        'province' =>
                            $university->province,

                        'max_students' =>
                            $maximumStudents,

                        'enabled_components' =>
                            $enabledComponents,
                    ],

                    'administrator' => [
                        'id' =>
                            $administrator->getKey(),

                        'name' =>
                            $administrator->full_name,

                        'email' =>
                            $administrator->email,
                    ],

                    'students' => [
                        'total' =>
                            $totalStudents,

                        'active' =>
                            $activeStudents,

                        'warning' =>
                            $warningStudents,

                        'dropout' =>
                            $dropoutStudents,
                    ],

                    'registrations' => [
                        'total' =>
                            $totalStudents,

                        'tracked' =>
                            $registrationTracked,

                        'pending' =>
                            $pendingRegistrations,

                        'approved' =>
                            $approvedRegistrations,
                    ],

                    'staff' => [
                        'instructors' =>
                            $totalInstructors,

                        'active_instructors' =>
                            $activeInstructors,

                        'coordinators' =>
                            $totalCoordinators,

                        'active_coordinators' =>
                            $activeCoordinators,
                    ],

                    'reports' => [
                        'total' =>
                            $totalReports,

                        'pending' =>
                            $pendingReports,

                        'in_review' =>
                            $inReviewReports,

                        'resolved' =>
                            $resolvedReports,
                    ],

                    'capacity' => [
                        'maximum' =>
                            $maximumStudents,

                        'current' =>
                            $totalStudents,

                        'percent' =>
                            $capacityPercent,
                    ],

                    'components' =>
                        $componentSummary,
                ],

                'periodFilters' => [
                    'academic_year' =>
                        $selectedAcademicYear,

                    'semester' =>
                        $selectedSemester,
                ],

                'academicYears' =>
                    $academicYears,

                'semesterOptions' =>
                    $semesterOptions,

                'recentStudents' =>
                    $recentStudents,

                'recentReports' =>
                    $recentReports,

                'recentActivities' =>
                    $recentActivities,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Academic Year Options
    |--------------------------------------------------------------------------
    */

    private function academicYearOptions(
        string $universityAcademicYear
    ): array {
        $current =
            $this->currentAcademicYear();

        $currentStart =
            (int) explode(
                '-',
                $current
            )[0];

        $years =
            collect([
                $universityAcademicYear,
                $current,
                ($currentStart + 1)
                    .
                    '-'
                    .
                    ($currentStart + 2),
                ($currentStart - 1)
                    .
                    '-'
                    .
                    $currentStart,
                ($currentStart - 2)
                    .
                    '-'
                    .
                    ($currentStart - 1),
                ($currentStart - 3)
                    .
                    '-'
                    .
                    ($currentStart - 2),
            ])
                ->map(
                    fn ($value) =>
                        trim(
                            (string) $value
                        )
                )
                ->filter(
                    fn ($value) =>
                        preg_match(
                            '/^\d{4}-\d{4}$/',
                            $value
                        ) ===
                        1
                )
                ->unique()
                ->sortDesc()
                ->values();

        return $years->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Semester Options
    |--------------------------------------------------------------------------
    */

    private function semesterOptions(
        string $universitySemester
    ): array {
        $normalizedUniversitySemester =
            $this->normalizeSemester(
                $universitySemester
            );

        return collect([
            $normalizedUniversitySemester,
            '1st Semester',
            '2nd Semester',
        ])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Academic Year
    |--------------------------------------------------------------------------
    */

    private function resolveAcademicYear(
        Request $request,
        array $academicYears,
        string $universityAcademicYear
    ): string {
        $requested =
            trim(
                (string) $request->query(
                    'academic_year',
                    ''
                )
            );

        if (
            preg_match(
                '/^\d{4}-\d{4}$/',
                $requested
            ) ===
            1
        ) {
            return $requested;
        }

        if (
            preg_match(
                '/^\d{4}-\d{4}$/',
                trim(
                    $universityAcademicYear
                )
            ) ===
            1
        ) {
            return trim(
                $universityAcademicYear
            );
        }

        return $academicYears[0]
            ??
            $this->currentAcademicYear();
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Semester
    |--------------------------------------------------------------------------
    */

    private function resolveSemester(
        Request $request,
        array $semesterOptions,
        string $universitySemester
    ): string {
        $requested =
            $this->normalizeSemester(
                (string) $request->query(
                    'semester',
                    ''
                )
            );

        if (
            $requested &&
            in_array(
                $requested,
                $semesterOptions,
                true
            )
        ) {
            return $requested;
        }

        $universityValue =
            $this->normalizeSemester(
                $universitySemester
            );

        if (
            $universityValue
        ) {
            return $universityValue;
        }

        return $semesterOptions[0]
            ??
            '1st Semester';
    }


    /*
    |--------------------------------------------------------------------------
    | Current Academic Year
    |--------------------------------------------------------------------------
    */

    private function currentAcademicYear(): string
    {
        $now =
            CarbonImmutable::now(
                'Asia/Manila'
            );

        $startYear =
            $now->month >=
            6
                ? $now->year
                : $now->year - 1;

        return $startYear
            .
            '-'
            .
            ($startYear + 1);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Semester
    |--------------------------------------------------------------------------
    */

    private function normalizeSemester(
        string $semester
    ): string {
        $value =
            strtolower(
                trim(
                    $semester
                )
            );

        return match ($value) {
            '1',
            '1st',
            '1st semester',
            'first',
            'first semester' =>
                '1st Semester',

            '2',
            '2nd',
            '2nd semester',
            'second',
            'second semester' =>
                '2nd Semester',

            default =>
                trim(
                    $semester
                ),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Apply Explicit Period Columns
    |--------------------------------------------------------------------------
    |
    | Some future NSTP HUB tables may contain academic_year / semester.
    | If those columns exist, the dashboard automatically respects them.
    | If not, the dashboard safely shows the university's current records.
    |
    */

    private function applyExplicitPeriodColumns(
        Builder $query,
        string $table,
        string $academicYear,
        string $semester
    ): void {
        if (
            Schema::hasColumn(
                $table,
                'academic_year'
            )
        ) {
            $query->where(
                'academic_year',
                $academicYear
            );
        }

        if (
            Schema::hasColumn(
                $table,
                'semester'
            )
        ) {
            $query->where(
                'semester',
                $semester
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | First Existing Column
    |--------------------------------------------------------------------------
    */

    private function firstExistingColumn(
        string $table,
        array $columns
    ): ?string {
        foreach (
            $columns
            as
            $column
        ) {
            if (
                Schema::hasColumn(
                    $table,
                    $column
                )
            ) {
                return $column;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Count Status Safely
    |--------------------------------------------------------------------------
    */

    private function countStatus(
        Builder $query,
        string $column,
        array $values
    ): int {
        $table =
            $query->getModel()
                ->getTable();

        if (
            !Schema::hasColumn(
                $table,
                $column
            )
        ) {
            return 0;
        }

        $variants =
            collect(
                $values
            )
                ->flatMap(
                    fn ($value) => [
                        strtoupper(
                            trim(
                                (string) $value
                            )
                        ),
                        strtolower(
                            trim(
                                (string) $value
                            )
                        ),
                        ucfirst(
                            strtolower(
                                trim(
                                    (string) $value
                                )
                            )
                        ),
                    ]
                )
                ->unique()
                ->values()
                ->all();

        return (clone $query)
            ->whereIn(
                $column,
                $variants
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Student Name
    |--------------------------------------------------------------------------
    */

    private function studentName(
        User $student
    ): string {
        $parts =
            collect([
                $student->surname,
                $student->first_name,
                $student->middle_name,
            ])
                ->map(
                    fn ($value) =>
                        trim(
                            (string) (
                                $value
                                ??
                                ''
                            )
                        )
                )
                ->filter();

        if (
            $parts->isNotEmpty()
        ) {
            $surname =
                $parts->shift();

            $remaining =
                $parts->implode(
                    ' '
                );

            return trim(
                $surname
                .
                (
                    $remaining
                        ? ', ' . $remaining
                        : ''
                )
            );
        }

        return trim(
            (string) (
                $student->name
                ??
                $student->email
                ??
                'Student'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Recent Activity
    |--------------------------------------------------------------------------
    */

    private function recentActivities(
        int $universityId,
        Collection $recentStudents,
        Collection $recentReports
    ): Collection {
        $studentActivity =
            $recentStudents
                ->map(
                    fn ($student) => [
                        'type' =>
                            'student',

                        'title' =>
                            'Student record added',

                        'description' =>
                            ($student['name']
                                ??
                                'A student')
                            .
                            ' was added to '
                            .
                            (
                                $student['component']
                                ??
                                'NSTP'
                            )
                            .
                            '.',

                        'time' =>
                            $student['created_label']
                            ??
                            'Recently',

                        'timestamp' =>
                            $student['created_at'],

                        'route' =>
                            '/university-admin/users/students/'
                            .
                            $student['id'],
                    ]
                );

        $reportActivity =
            $recentReports
                ->map(
                    fn ($report) => [
                        'type' =>
                            'report',

                        'title' =>
                            'Student report submitted',

                        'description' =>
                            ($report['student']
                                ??
                                'A student')
                            .
                            ' submitted “'
                            .
                            (
                                $report['subject']
                                ??
                                'Student Report'
                            )
                            .
                            '”.',

                        'time' =>
                            $report['created_label']
                            ??
                            'Recently',

                        'timestamp' =>
                            $report['created_at'],

                        'route' =>
                            $report['route'],
                    ]
                );

        $latestInstructor =
            Instructor::query()
                ->where(
                    'university_id',
                    $universityId
                )
                ->latest(
                    'created_at'
                )
                ->first();

        $latestCoordinator =
            Coordinator::query()
                ->where(
                    'university_id',
                    $universityId
                )
                ->latest(
                    'created_at'
                )
                ->first();

        $staffActivity =
            collect();

        if (
            $latestInstructor
        ) {
            $staffActivity->push([
                'type' =>
                    'instructor',

                'title' =>
                    'Instructor account added',

                'description' =>
                    (
                        $latestInstructor->full_name
                        ??
                        'An instructor'
                    )
                    .
                    ' is assigned to '
                    .
                    (
                        $latestInstructor->component
                        ??
                        'NSTP'
                    )
                    .
                    '.',

                'time' =>
                    optional(
                        $latestInstructor->created_at
                    )
                        ?->format(
                            'M d, Y'
                        )
                    ??
                    'Recently',

                'timestamp' =>
                    optional(
                        $latestInstructor->created_at
                    )
                        ?->toIso8601String(),

                'route' =>
                    '/university-admin/instructors',
            ]);
        }

        if (
            $latestCoordinator
        ) {
            $staffActivity->push([
                'type' =>
                    'coordinator',

                'title' =>
                    'Coordinator account added',

                'description' =>
                    (
                        $latestCoordinator->full_name
                        ??
                        'A coordinator'
                    )
                    .
                    ' is assigned to '
                    .
                    (
                        $latestCoordinator->component
                        ??
                        'NSTP'
                    )
                    .
                    '.',

                'time' =>
                    optional(
                        $latestCoordinator->created_at
                    )
                        ?->format(
                            'M d, Y'
                        )
                    ??
                    'Recently',

                'timestamp' =>
                    optional(
                        $latestCoordinator->created_at
                    )
                        ?->toIso8601String(),

                'route' =>
                    '/university-admin/coordinators',
            ]);
        }

        return $studentActivity
            ->concat(
                $reportActivity
            )
            ->concat(
                $staffActivity
            )
            ->sortByDesc(
                function (
                    array $activity
                ) {
                    return $activity['timestamp']
                        ??
                        '';
                }
            )
            ->take(
                8
            )
            ->values();
    }
}
