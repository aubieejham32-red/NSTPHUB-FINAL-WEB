<?php

namespace App\Http\Controllers\Api\AdminMobile;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Coordinator;
use App\Models\ExcuseLetter;
use App\Models\Instructor;
use App\Models\UniversityAdministrator;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminMobileDashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        $account = $request->user();

        $account->loadMissing(
            'university'
        );

        $university =
            $account->university;


        /*
        |--------------------------------------------------------------------------
        | University Validation
        |--------------------------------------------------------------------------
        */

        if (!$university) {
            return response()->json([
                'success' => false,
                'message' =>
                    'University record not found for this account.',
            ], 404);
        }


        if (
            strtoupper(
                trim(
                    (string) $university->status
                )
            ) !== 'ACTIVE'
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This institution is currently inactive in NSTP HUB.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Account Role
        |--------------------------------------------------------------------------
        */

        [
            $role,
            $accountType,
        ] = $this->roleAndType(
            $account
        );


        if (
            !$role ||
            !$accountType
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This account is not allowed to use the admin mobile dashboard.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Current Philippine Date
        |--------------------------------------------------------------------------
        |
        | The dashboard uses the Philippine date as its source of truth.
        |
        | Example:
        |
        | September 9, 2026
        |
        | Academic Year:
        | 2026-2027
        |
        | Semester:
        | 1st Semester
        |
        */

        $today =
            $this->resolveMobileToday(
                $request
            );

        $todayString =
            $today->toDateString();


        /*
        |--------------------------------------------------------------------------
        | Academic Period From Current Date
        |--------------------------------------------------------------------------
        */

        [
            $academicYear,
            $semester,
        ] = $this->academicPeriod(
            $today
        );


        /*
        |--------------------------------------------------------------------------
        | Component Permissions
        |--------------------------------------------------------------------------
        */

        $componentOptions =
            $this->componentOptions(
                $account,
                $university
            );


        $selectedComponent =
            $this->defaultComponent(
                $account,
                $componentOptions
            );


        /*
        |--------------------------------------------------------------------------
        | Build Statistics For Every Allowed Component
        |--------------------------------------------------------------------------
        |
        | This is important for instructors managing more than one component.
        |
        | Example:
        |
        | Instructor:
        |
        | LTS
        | CWTS
        |
        | Both dashboards are returned to mobile.
        |
        | The mobile screen can switch between them instantly.
        |
        */

        $componentDashboards = [];


        foreach (
            $componentOptions as
            $component
        ) {
            $componentDashboards[
                $component
            ] =
                $this->buildStatistics(
                    (int) $university->id,
                    $todayString,
                    $component
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Default Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        if (
            $selectedComponent &&
            isset(
                $componentDashboards[
                    $selectedComponent
                ]
            )
        ) {
            $stats =
                $componentDashboards[
                    $selectedComponent
                ];
        } else {
            /*
            |--------------------------------------------------------------------------
            | Fallback
            |--------------------------------------------------------------------------
            |
            | If no NSTP component is configured, return university-wide statistics.
            |
            */

            $stats =
                $this->buildStatistics(
                    (int) $university->id,
                    $todayString,
                    null
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Account Payload
        |--------------------------------------------------------------------------
        */

        $accountPayload = [
            'id' =>
                (int) $account->id,

            'name' =>
                $this->accountName(
                    $account
                ),

            'email' =>
                $account->email,

            'role' =>
                $role,

            'account_type' =>
                $accountType,

            'components' =>
                $componentOptions,

            'selected_component' =>
                $selectedComponent,
        ];


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'role' =>
                $role,

            'account_type' =>
                $accountType,

            'account' =>
                $accountPayload,


            /*
            |--------------------------------------------------------------------------
            | Backward Compatibility
            |--------------------------------------------------------------------------
            */

            'administrator' =>
                $accountPayload,


            /*
            |--------------------------------------------------------------------------
            | Server-Date Context
            |--------------------------------------------------------------------------
            */

            'period' => [
                'date' =>
                    $todayString,

                'date_label' =>
                    $today->format(
                        'l, F j, Y'
                    ),

                'academic_year' =>
                    $academicYear,

                'semester' =>
                    $semester,

                'timezone' =>
                    'Asia/Manila',

                'is_today' =>
                    true,
            ],


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            */

            'university' => [
                'id' =>
                    (int) $university->id,

                'name' =>
                    $university->name,

                'acronym' =>
                    $university->acronym,

                /*
                |--------------------------------------------------------------------------
                | Original Stored Logo Path
                |--------------------------------------------------------------------------
                */

                'logo' =>
                    $university->logo,

                /*
                |--------------------------------------------------------------------------
                | Mobile-Ready Absolute Logo URL
                |--------------------------------------------------------------------------
                */

                'logo_url' =>
                    $this->universityLogoUrl(
                        $request,
                        $university->logo
                    ),

                /*
                |--------------------------------------------------------------------------
                | Date-Based Academic Context
                |--------------------------------------------------------------------------
                */

                'academic_year' =>
                    $academicYear,

                'semester' =>
                    $semester,

                /*
                |--------------------------------------------------------------------------
                | Values Currently Stored In Universities Table
                |--------------------------------------------------------------------------
                |
                | These are retained for reference.
                |
                */

                'configured_academic_year' =>
                    $university->academic_year,

                'configured_semester' =>
                    $university->semester,

                'components' =>
                    $componentOptions,

                'status' =>
                    $university->status,
            ],


            /*
            |--------------------------------------------------------------------------
            | Component Context
            |--------------------------------------------------------------------------
            */

            'component_context' => [
                'selected' =>
                    $selectedComponent,

                'options' =>
                    $componentOptions,

                'can_switch' =>
                    count(
                        $componentOptions
                    ) > 1,

                'is_multi_component' =>
                    count(
                        $componentOptions
                    ) > 1,
            ],


            /*
            |--------------------------------------------------------------------------
            | Current Selected Component Stats
            |--------------------------------------------------------------------------
            */

            'stats' =>
                $stats,


            /*
            |--------------------------------------------------------------------------
            | Every Allowed Component
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | component_dashboards: {
            |     "LTS": {...},
            |     "CWTS": {...}
            | }
            |
            */

            'component_dashboards' =>
                $componentDashboards,


            /*
            |--------------------------------------------------------------------------
            | Refresh Timestamp
            |--------------------------------------------------------------------------
            */

            'refreshed_at' =>
                $this->resolveMobileNow(
                    $request
                )
                    ->toIso8601String(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Build Dashboard Statistics
    |--------------------------------------------------------------------------
    */

    private function buildStatistics(
        int $universityId,
        string $date,
        ?string $component
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Tables
        |--------------------------------------------------------------------------
        */

        $userTable =
            (new User())->getTable();

        $attendanceTable =
            (new Attendance())->getTable();

        $excuseLetterTable =
            (new ExcuseLetter())->getTable();


        /*
        |--------------------------------------------------------------------------
        | Find Component Columns
        |--------------------------------------------------------------------------
        |
        | This supports common project column names without crashing if
        | one of them does not exist.
        |
        */

        $userComponentColumn =
            $this->firstExistingColumn(
                $userTable,
                [
                    'component',
                    'nstp_component',
                    'assigned_component',
                ]
            );


        $excuseComponentColumn =
            $this->firstExistingColumn(
                $excuseLetterTable,
                [
                    'component',
                    'nstp_component',
                    'assigned_component',
                ]
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
                    $universityId
                );


        if (
            $component &&
            $userComponentColumn
        ) {
            $this->applyComponentFilter(
                $studentQuery,
                $userComponentColumn,
                $component
            );
        }


        $students =
            $studentQuery->count();


        /*
        |--------------------------------------------------------------------------
        | Attendance For Current Date
        |--------------------------------------------------------------------------
        */

        $attendanceQuery =
            Attendance::query()
                ->join(
                    'users',
                    'users.id',
                    '=',
                    "{$attendanceTable}.user_id"
                )
                ->where(
                    'users.university_id',
                    $universityId
                )
                ->whereDate(
                    "{$attendanceTable}.attendance_date",
                    $date
                );


        /*
        |--------------------------------------------------------------------------
        | Component-Specific Attendance
        |--------------------------------------------------------------------------
        */

        if (
            $component &&
            $userComponentColumn
        ) {
            $this->applyComponentFilter(
                $attendanceQuery,
                "users.{$userComponentColumn}",
                $component
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Present
        |--------------------------------------------------------------------------
        */

        $present =
            (clone $attendanceQuery)
                ->where(
                    "{$attendanceTable}.remark",
                    Attendance::REMARK_PRESENT
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Late
        |--------------------------------------------------------------------------
        */

        $late =
            (clone $attendanceQuery)
                ->where(
                    "{$attendanceTable}.remark",
                    Attendance::REMARK_LATE
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Absent
        |--------------------------------------------------------------------------
        */

        $absent =
            (clone $attendanceQuery)
                ->where(
                    "{$attendanceTable}.remark",
                    Attendance::REMARK_ABSENT
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Excused
        |--------------------------------------------------------------------------
        */

        $excused =
            (clone $attendanceQuery)
                ->where(
                    "{$attendanceTable}.remark",
                    Attendance::REMARK_EXCUSED
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Attendance Totals
        |--------------------------------------------------------------------------
        */

        $attendanceRecorded =
            $present +
            $late +
            $absent +
            $excused;


        $attendanceRate =
            $attendanceRecorded > 0
                ? (int) round(
                    (
                        (
                            $present +
                            $late
                        )
                        /
                        $attendanceRecorded
                    )
                    *
                    100
                )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | Excuse Letters For Current Date
        |--------------------------------------------------------------------------
        */

        $excuseLetters =
            ExcuseLetter::query()
                ->where(
                    'university_id',
                    $universityId
                );


        /*
        |--------------------------------------------------------------------------
        | Filter Excuse Letters By Submission Date
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                $excuseLetterTable,
                'created_at'
            )
        ) {
            $excuseLetters
                ->whereDate(
                    "{$excuseLetterTable}.created_at",
                    $date
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Filter Excuse Letters By Component
        |--------------------------------------------------------------------------
        */

        if ($component) {
            /*
            |--------------------------------------------------------------------------
            | Best Case:
            | excuse_letters has its own component column.
            |--------------------------------------------------------------------------
            */

            if (
                $excuseComponentColumn
            ) {
                $this->applyComponentFilter(
                    $excuseLetters,
                    $excuseComponentColumn,
                    $component
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Fallback:
            | resolve the component through excuse_letters.user_id -> users.
            |--------------------------------------------------------------------------
            */

            elseif (
                $userComponentColumn &&
                Schema::hasColumn(
                    $excuseLetterTable,
                    'user_id'
                )
            ) {
                $studentIds =
                    User::query()
                        ->select('id')
                        ->where(
                            'university_id',
                            $universityId
                        );

                $this->applyComponentFilter(
                    $studentIds,
                    $userComponentColumn,
                    $component
                );

                $excuseLetters
                    ->whereIn(
                        'user_id',
                        $studentIds
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Excuse Letter Counts
        |--------------------------------------------------------------------------
        */

        $totalExcuseLetters =
            (clone $excuseLetters)
                ->count();


        $pendingExcuseLetters =
            (clone $excuseLetters)
                ->where(
                    'status',
                    ExcuseLetter::STATUS_PENDING
                )
                ->count();


        $approvedExcuseLetters =
            (clone $excuseLetters)
                ->where(
                    'status',
                    ExcuseLetter::STATUS_APPROVED
                )
                ->count();


        $rejectedExcuseLetters =
            (clone $excuseLetters)
                ->where(
                    'status',
                    ExcuseLetter::STATUS_REJECTED
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

        return [
            'component' =>
                $component,

            'date' =>
                $date,

            'students' =>
                $students,

            'present' =>
                $present,

            'late' =>
                $late,

            'absent' =>
                $absent,

            'excused' =>
                $excused,

            'attendance_recorded' =>
                $attendanceRecorded,

            'attendance_rate' =>
                $attendanceRate,

            'total_excuse_letters' =>
                $totalExcuseLetters,

            'pending_excuse_letters' =>
                $pendingExcuseLetters,

            'approved_excuse_letters' =>
                $approvedExcuseLetters,

            'rejected_excuse_letters' =>
                $rejectedExcuseLetters,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Allowed NSTP Components
    |--------------------------------------------------------------------------
    */

    private function componentOptions(
        mixed $account,
        mixed $university
    ): array {
        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        |
        | University administrators can view the components enabled for
        | their institution.
        |
        */

        if (
            $account instanceof
            UniversityAdministrator
        ) {
            $components =
                $this->normalizeComponentList(
                    $university->components
                    ?? []
                );

            /*
            |--------------------------------------------------------------------------
            | Backward Compatible Fallback
            |--------------------------------------------------------------------------
            */

            if (empty($components)) {
                return [
                    'LTS',
                    'CWTS',
                    'ROTC',
                ];
            }

            return $components;
        }


        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        |
        | This uses Instructor::componentCodes().
        |
        | Therefore an instructor can manage:
        |
        | LTS only
        |
        | or
        |
        | LTS + CWTS
        |
        | or any other valid assigned combination.
        |
        */

        if (
            $account instanceof
            Instructor
        ) {
            return
                $account
                    ->componentCodes();
        }


        /*
        |--------------------------------------------------------------------------
        | Attendance Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            $account instanceof
            Coordinator
        ) {
            $component =
                $this->normalizeComponent(
                    $account->component
                    ?? ''
                );

            return $component
                ? [$component]
                : [];
        }


        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Default Component
    |--------------------------------------------------------------------------
    */

    private function defaultComponent(
        mixed $account,
        array $componentOptions
    ): string {
        if (
            empty(
                $componentOptions
            )
        ) {
            return '';
        }


        /*
        |--------------------------------------------------------------------------
        | Instructor Primary Component
        |--------------------------------------------------------------------------
        */

        if (
            $account instanceof
            Instructor
        ) {
            $primary =
                $this->normalizeComponent(
                    $account
                        ->primaryComponent()
                );

            if (
                $primary &&
                in_array(
                    $primary,
                    $componentOptions,
                    true
                )
            ) {
                return $primary;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator Component
        |--------------------------------------------------------------------------
        */

        if (
            $account instanceof
            Coordinator
        ) {
            $component =
                $this->normalizeComponent(
                    $account->component
                    ?? ''
                );

            if (
                $component &&
                in_array(
                    $component,
                    $componentOptions,
                    true
                )
            ) {
                return $component;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | University Admin / General Fallback
        |--------------------------------------------------------------------------
        */

        return
            $componentOptions[0];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Component List
    |--------------------------------------------------------------------------
    */

    private function normalizeComponentList(
        mixed $values
    ): array {
        if (
            is_string($values)
        ) {
            $values = [
                $values,
            ];
        }


        if (
            !is_array($values)
        ) {
            return [];
        }


        $components = [];


        foreach (
            $values as
            $key => $value
        ) {
            $component = '';


            /*
            |--------------------------------------------------------------------------
            | Array Object
            |--------------------------------------------------------------------------
            |
            | Supports:
            |
            | [
            |   ['component' => 'CWTS'],
            |   ['component' => 'LTS']
            | ]
            |
            */

            if (
                is_array($value)
            ) {
                $component =
                    $this->normalizeComponent(
                        $value['component']
                        ??
                        $value['code']
                        ??
                        $value['name']
                        ??
                        ''
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Normal Array
            |--------------------------------------------------------------------------
            |
            | Supports:
            |
            | ['CWTS', 'LTS']
            |
            */

            else {
                $component =
                    $this->normalizeComponent(
                        $value
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Associative Array
            |--------------------------------------------------------------------------
            |
            | Supports:
            |
            | [
            |     'CWTS' => true,
            |     'LTS' => true
            | ]
            |
            */

            if (
                !$component &&
                is_string($key)
            ) {
                $enabled =
                    $value === true ||
                    $value === 1 ||
                    $value === '1';

                if ($enabled) {
                    $component =
                        $this->normalizeComponent(
                            $key
                        );
                }
            }


            if ($component) {
                $components[] =
                    $component;
            }
        }


        return array_values(
            array_unique(
                $components
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize One Component
    |--------------------------------------------------------------------------
    */

    private function normalizeComponent(
        mixed $value
    ): string {
        if (
            !is_string($value) &&
            !is_numeric($value)
        ) {
            return '';
        }


        $component =
            strtoupper(
                trim(
                    (string) $value
                )
            );


        return in_array(
            $component,
            [
                'LTS',
                'CWTS',
                'ROTC',
            ],
            true
        )
            ? $component
            : '';
    }


    /*
    |--------------------------------------------------------------------------
    | Apply Component Filter
    |--------------------------------------------------------------------------
    */

    private function applyComponentFilter(
        mixed $query,
        string $column,
        string $component
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Column is internally resolved from a fixed safe list.
        |--------------------------------------------------------------------------
        */

        $query->whereRaw(
            "UPPER(TRIM({$column})) = ?",
            [
                strtoupper(
                    trim(
                        $component
                    )
                ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Find First Existing Column
    |--------------------------------------------------------------------------
    */

    private function firstExistingColumn(
        string $table,
        array $columns
    ): ?string {
        foreach (
            $columns as
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
    | Academic Year / Semester Based On Date
    |--------------------------------------------------------------------------
    |
    | This matches the fallback behavior used by your web dashboard:
    |
    | June - December
    |     1st Semester
    |
    | January - May
    |     2nd Semester
    |
    | Example:
    |
    | September 9, 2026
    |
    | startYear = 2026
    | academicYear = 2026-2027
    | semester = 1st Semester
    |
    */

    private function academicPeriod(
        Carbon $date
    ): array {
        $startYear =
            $date->month >= 6
                ? $date->year
                : $date->year - 1;


        $academicYear =
            $startYear
            .
            '-'
            .
            (
                $startYear + 1
            );


        $semester =
            $date->month >= 6
                ? '1st Semester'
                : '2nd Semester';


        return [
            $academicYear,
            $semester,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University Logo URL
    |--------------------------------------------------------------------------
    |
    | Database examples supported:
    |
    | universities/logos/snsu.png
    |
    | storage/universities/logos/snsu.png
    |
    | public/universities/logos/snsu.png
    |
    | https://example.com/logo.png
    |
    */

    private function universityLogoUrl(
        Request $request,
        mixed $logo
    ): ?string {
        $logo =
            trim(
                (string) (
                    $logo
                    ?? ''
                )
            );


        if ($logo === '') {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Already Absolute
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                $logo,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return $logo;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Slashes
        |--------------------------------------------------------------------------
        */

        $path =
            str_replace(
                '\\',
                '/',
                $logo
            );

        $path =
            ltrim(
                $path,
                '/'
            );


        /*
        |--------------------------------------------------------------------------
        | Remove Laravel Public Prefix
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                $path,
                'public/'
            )
        ) {
            $path =
                Str::after(
                    $path,
                    'public/'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | API Host
        |--------------------------------------------------------------------------
        |
        | This is better for mobile development than asset() when APP_URL
        | is localhost because the URL will use the same host the phone
        | used to reach Laravel.
        |
        */

        $baseUrl =
            rtrim(
                $request
                    ->getSchemeAndHttpHost(),
                '/'
            );


        /*
        |--------------------------------------------------------------------------
        | Already Starts With storage/
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                $path,
                'storage/'
            )
        ) {
            return
                $baseUrl
                .
                '/'
                .
                $path;
        }


        /*
        |--------------------------------------------------------------------------
        | Normal Public-Disk Path
        |--------------------------------------------------------------------------
        */

        return
            $baseUrl
            .
            '/storage/'
            .
            $path;
    }


    /*
    |--------------------------------------------------------------------------
    | Role And Account Type
    |--------------------------------------------------------------------------
    */

    private function roleAndType(
        mixed $account
    ): array {
        if (
            $account instanceof
            UniversityAdministrator
        ) {
            return [
                'university-administrator',
                'university_administrator',
            ];
        }


        if (
            $account instanceof
            Instructor
            &&
            $account->isInstructor()
        ) {
            return [
                'instructor',
                'instructor',
            ];
        }


        if (
            $account instanceof
            Coordinator
            &&
            $account
                ->isAttendanceCoordinator()
        ) {
            return [
                'coordinator-attendance',
                'coordinator',
            ];
        }


        return [
            null,
            null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Account Name
    |--------------------------------------------------------------------------
    */

    private function accountName(
        mixed $account
    ): string {
        if (
            $account instanceof
            UniversityAdministrator
        ) {
            return trim(
                implode(
                    ' ',
                    array_filter([
                        $account->first_name,
                        $account->middle_name,
                        $account->last_name,
                    ])
                )
            );
        }


        return trim(
            (string) $account->full_name
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Admin-Mobile Philippine Time
    |--------------------------------------------------------------------------
    |
    | Development machines are sometimes one calendar day ahead/behind after
    | a Windows time-zone or clock change. The authenticated mobile app sends
    | its local date/time with every request. We only accept that value when:
    |
    | 1. the phone is using UTC+08:00 (Philippines), and
    | 2. the phone/server dates differ by no more than one day.
    |
    | This prevents the dashboard from showing "tomorrow" while the phone is
    | still on the correct Philippine date.
    |
    */
    private function resolveMobileNow(
        Request $request
    ): Carbon {
        $serverNow =
            Carbon::now(
                'Asia/Manila'
            );

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
                Carbon::parse(
                    $clientDateTime
                )
                    ->setTimezone(
                        'Asia/Manila'
                    );

            $clientToday =
                Carbon::createFromFormat(
                    'Y-m-d',
                    $clientDate,
                    'Asia/Manila'
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
                $serverNow
                    ->copy()
                    ->startOfDay();

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


    private function resolveMobileToday(
        Request $request
    ): Carbon {
        return $this
            ->resolveMobileNow(
                $request
            )
            ->copy()
            ->startOfDay();
    }

}