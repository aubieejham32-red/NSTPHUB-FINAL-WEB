<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\UniversityAdministrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Future Academic Years
    |--------------------------------------------------------------------------
    |
    | Current Academic Year + next 5 Academic Years.
    |
    | Example:
    |
    | 2026-2027
    | 2027-2028
    | 2028-2029
    | 2029-2030
    | 2030-2031
    | 2031-2032
    |
    */

    private const FUTURE_ACADEMIC_YEARS = 5;


    /*
    |--------------------------------------------------------------------------
    | Semester Options
    |--------------------------------------------------------------------------
    |
    | These semesters will always be available even if there are no
    | universities registered yet for the selected Academic Year.
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
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Current Academic Year Start
        |--------------------------------------------------------------------------
        |
        | June - December:
        |
        | 2026 -> 2026-2027
        |
        | January - May:
        |
        | 2027 -> 2026-2027
        |
        */

        $currentAcademicStartYear =
            now()->month >= 6
                ? now()->year
                : now()->year - 1;


        /*
        |--------------------------------------------------------------------------
        | Current Academic Year
        |--------------------------------------------------------------------------
        */

        $currentAcademicYear =
            $currentAcademicStartYear .
            '-' .
            (
                $currentAcademicStartYear +
                1
            );


        /*
        |--------------------------------------------------------------------------
        | Generate Current + Future Academic Years
        |--------------------------------------------------------------------------
        */

        $generatedAcademicYears =
            collect();


        for (
            $offset = 0;
            $offset <=
            self::FUTURE_ACADEMIC_YEARS;
            $offset++
        ) {

            $startYear =
                $currentAcademicStartYear +
                $offset;


            $generatedAcademicYears->push(
                $startYear .
                '-' .
                (
                    $startYear +
                    1
                )
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Existing Academic Years From Database
        |--------------------------------------------------------------------------
        |
        | We also keep older Academic Years already stored in the database.
        |
        */

        $databaseAcademicYears =
            University::query()
                ->whereNotNull(
                    'academic_year'
                )
                ->where(
                    'academic_year',
                    '!=',
                    ''
                )
                ->pluck(
                    'academic_year'
                )
                ->map(
                    function (
                        $academicYear
                    ) {

                        return trim(
                            (string)
                            $academicYear
                        );

                    }
                )
                ->filter();


        /*
        |--------------------------------------------------------------------------
        | Merge Academic Years
        |--------------------------------------------------------------------------
        |
        | Includes:
        |
        | - Existing database years
        | - Current Academic Year
        | - Future Academic Years
        |
        */

        $academicYears =
            $databaseAcademicYears
                ->merge(
                    $generatedAcademicYears
                )
                ->unique()
                ->sortByDesc(
                    function (
                        $academicYear
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Extract Starting Year
                        |--------------------------------------------------------------------------
                        |
                        | 2026-2027
                        |
                        | becomes:
                        |
                        | 2026
                        |
                        */

                        $parts =
                            explode(
                                '-',
                                $academicYear
                            );


                        return (int)
                            (
                                $parts[0]
                                ?? 0
                            );

                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Requested Academic Year
        |--------------------------------------------------------------------------
        */

        $requestedAcademicYear =
            trim(
                (string)
                $request->query(
                    'academic_year',
                    ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Selected Academic Year
        |--------------------------------------------------------------------------
        */

        if (
            $requestedAcademicYear !== '' &&
            $academicYears->contains(
                $requestedAcademicYear
            )
        ) {

            $academicYear =
                $requestedAcademicYear;

        } else {

            /*
            |--------------------------------------------------------------------------
            | Default To Current Academic Year
            |--------------------------------------------------------------------------
            */

            $academicYear =
                $currentAcademicYear;

        }


        /*
        |--------------------------------------------------------------------------
        | Semester Options
        |--------------------------------------------------------------------------
        |
        | Always:
        |
        | 1st Semester
        | 2nd Semester
        |
        */

        $semesterOptions =
            collect(
                self::SEMESTERS
            );


        /*
        |--------------------------------------------------------------------------
        | Default Semester
        |--------------------------------------------------------------------------
        |
        | June - December:
        |
        | 1st Semester
        |
        | January - May:
        |
        | 2nd Semester
        |
        */

        $defaultSemester =
            now()->month >= 6
                ? '1st Semester'
                : '2nd Semester';


        /*
        |--------------------------------------------------------------------------
        | Requested Semester
        |--------------------------------------------------------------------------
        */

        $requestedSemester =
            trim(
                (string)
                $request->query(
                    'semester',
                    ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize Requested Semester
        |--------------------------------------------------------------------------
        */

        $requestedSemester =
            $this->normalizeSemester(
                $requestedSemester
            );


        /*
        |--------------------------------------------------------------------------
        | Selected Semester
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $requestedSemester,
                self::SEMESTERS,
                true
            )
        ) {

            $semester =
                $requestedSemester;

        } else {

            $semester =
                $defaultSemester;

        }


        /*
        |--------------------------------------------------------------------------
        | Base University Query
        |--------------------------------------------------------------------------
        |
        | All dashboard data below uses the selected:
        |
        | Academic Year
        | Semester
        |
        */

        $universityQuery =
            University::query()
                ->where(
                    'academic_year',
                    $academicYear
                )
                ->whereRaw(
                    'LOWER(TRIM(semester)) = ?',
                    [
                        strtolower(
                            $semester
                        ),
                    ]
                );


        /*
        |--------------------------------------------------------------------------
        | Total Universities
        |--------------------------------------------------------------------------
        */

        $totalUniversities =
            (clone $universityQuery)
                ->count();


        /*
        |--------------------------------------------------------------------------
        | University IDs
        |--------------------------------------------------------------------------
        */

        $universityIds =
            (clone $universityQuery)
                ->pluck(
                    'id'
                );


        /*
        |--------------------------------------------------------------------------
        | University Administrators
        |--------------------------------------------------------------------------
        */

        $totalAdmins =
            UniversityAdministrator::query()
                ->whereIn(
                    'university_id',
                    $universityIds
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Access Codes
        |--------------------------------------------------------------------------
        */

        $accessCodes =
            (clone $universityQuery)
                ->whereNotNull(
                    'access_code'
                )
                ->where(
                    'access_code',
                    '!=',
                    ''
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Pending Universities
        |--------------------------------------------------------------------------
        */

        $pendingAdmins =
            (clone $universityQuery)
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    [
                        'pending',
                    ]
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        |
        | Temporary student calculation using max_students.
        |
        | Replace later when Student model is available.
        |
        */

        $students =
            (clone $universityQuery)
                ->sum(
                    'max_students'
                );


        /*
        |--------------------------------------------------------------------------
        | CWTS
        |--------------------------------------------------------------------------
        */

        $cwts =
            (clone $universityQuery)
                ->whereJsonContains(
                    'components',
                    'CWTS'
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | LTS
        |--------------------------------------------------------------------------
        */

        $lts =
            (clone $universityQuery)
                ->whereJsonContains(
                    'components',
                    'LTS'
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | ROTC
        |--------------------------------------------------------------------------
        */

        $rotc =
            (clone $universityQuery)
                ->whereJsonContains(
                    'components',
                    'ROTC'
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Region Coverage
        |--------------------------------------------------------------------------
        */

        $regionCounts =
            (clone $universityQuery)
                ->select(
                    'region',
                    DB::raw(
                        'COUNT(*) as total'
                    )
                )
                ->whereNotNull(
                    'region'
                )
                ->where(
                    'region',
                    '!=',
                    ''
                )
                ->groupBy(
                    'region'
                )
                ->orderByDesc(
                    'total'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Highest Region Count
        |--------------------------------------------------------------------------
        */

        $highest =
            max(
                (int)
                $regionCounts->max(
                    'total'
                ),
                1
            );


        /*
        |--------------------------------------------------------------------------
        | Regions
        |--------------------------------------------------------------------------
        */

        $regions =
            $regionCounts
                ->map(
                    function (
                        $region
                    ) use (
                        $highest
                    ) {

                        return [

                            'name' =>
                                $region->region,

                            'total' =>
                                (int)
                                $region->total,

                            'percent' =>
                                round(
                                    (
                                        $region->total /
                                        $highest
                                    ) * 100
                                ),

                        ];

                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Component Total
        |--------------------------------------------------------------------------
        */

        $componentTotal =
            max(
                $cwts +
                $lts +
                $rotc,
                1
            );


        /*
        |--------------------------------------------------------------------------
        | Component Distribution
        |--------------------------------------------------------------------------
        */

        $components = [

            /*
            |--------------------------------------------------------------------------
            | CWTS
            |--------------------------------------------------------------------------
            */

            [

                'name' =>
                    'CWTS • Civic Welfare',

                'percent' =>
                    round(
                        (
                            $cwts /
                            $componentTotal
                        ) * 100
                    ),

                'class' =>
                    'cwts-fill',

            ],


            /*
            |--------------------------------------------------------------------------
            | LTS
            |--------------------------------------------------------------------------
            */

            [

                'name' =>
                    'LTS • Literacy Training',

                'percent' =>
                    round(
                        (
                            $lts /
                            $componentTotal
                        ) * 100
                    ),

                'class' =>
                    'lts-fill',

            ],


            /*
            |--------------------------------------------------------------------------
            | ROTC
            |--------------------------------------------------------------------------
            */

            [

                'name' =>
                    'ROTC • Reserve Officers',

                'percent' =>
                    round(
                        (
                            $rotc /
                            $componentTotal
                        ) * 100
                    ),

                'class' =>
                    'rotc-fill',

            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | Recently Added Universities
        |--------------------------------------------------------------------------
        */

        $recentUniversities =
            (clone $universityQuery)
                ->with(
                    'administrator'
                )
                ->latest(
                    'created_at'
                )
                ->take(
                    5
                )
                ->get()
                ->map(
                    function (
                        $university
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Administrator
                        |--------------------------------------------------------------------------
                        */

                        $administrator =
                            $university
                                ->administrator;


                        /*
                        |--------------------------------------------------------------------------
                        | Administrator Name
                        |--------------------------------------------------------------------------
                        */

                        $administratorName =
                            null;


                        if (
                            $administrator
                        ) {

                            /*
                            |--------------------------------------------------------------------------
                            | Use full_name If Available
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !empty(
                                    $administrator
                                        ->full_name
                                )
                            ) {

                                $administratorName =
                                    $administrator
                                        ->full_name;

                            } else {

                                /*
                                |--------------------------------------------------------------------------
                                | Build Full Name
                                |--------------------------------------------------------------------------
                                */

                                $administratorName =
                                    collect([
                                        $administrator
                                            ->first_name
                                            ?? null,

                                        $administrator
                                            ->middle_name
                                            ?? null,

                                        $administrator
                                            ->last_name
                                            ?? null,
                                    ])
                                        ->filter()
                                        ->implode(
                                            ' '
                                        );

                            }

                        }


                        return [

                            'id' =>
                                $university->id,

                            'name' =>
                                $university->name,

                            'logo' =>
                                $university->logo,

                            'region' =>
                                $university->region,

                            'admin' =>
                                $administratorName,

                            'access_code' =>
                                $university
                                    ->access_code,

                            'status' =>
                                $university->status,

                        ];

                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Recent Activity
        |--------------------------------------------------------------------------
        */

        $activities =
            (clone $universityQuery)
                ->latest(
                    'created_at'
                )
                ->take(
                    6
                )
                ->get()
                ->map(
                    function (
                        $university
                    ) {

                        return [

                            'id' =>
                                $university->id,

                            'color' =>
                                'green',

                            'message' =>
                                '<strong>' .
                                e(
                                    $university->name
                                ) .
                                '</strong> has been registered in NSTP Hub.',

                            'time' =>
                                $university
                                    ->created_at
                                    ->diffForHumans(),

                        ];

                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Universities This Semester
        |--------------------------------------------------------------------------
        */

        $newUniversities =
            (clone $universityQuery)
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Dashboard Description
        |--------------------------------------------------------------------------
        */

        if (
            $totalUniversities === 0
        ) {

            $description =
                'No partner universities are registered for this academic period yet.';

        } elseif (
            $totalUniversities === 1
        ) {

            $description =
                '1 partner university is actively administering<br>NSTP across the Philippines.';

        } else {

            $description =
                $totalUniversities .
                ' partner universities are actively administering<br>NSTP across the Philippines.';

        }


        /*
        |--------------------------------------------------------------------------
        | Render Dashboard
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'SuperAdmin/SuperAdminDashboard',
            [

                /*
                |--------------------------------------------------------------------------
                | Dashboard
                |--------------------------------------------------------------------------
                */

                'dashboard' => [

                    'academic_year' =>
                        $academicYear,

                    'semester' =>
                        strtoupper(
                            $semester
                        ),

                    'totalUniversities' =>
                        $totalUniversities,

                    'students' =>
                        number_format(
                            $students
                        ),

                    'admins' =>
                        $totalAdmins,

                    'accessCodes' =>
                        $accessCodes,

                    'pendingAdmins' =>
                        $pendingAdmins,

                    'expiringCodes' =>
                        0,

                    'newUniversities' =>
                        $newUniversities,

                    'cwts' =>
                        $cwts,

                    'lts' =>
                        $lts,

                    'rotc' =>
                        $rotc,

                    'description' =>
                        $description,

                ],


                /*
                |--------------------------------------------------------------------------
                | Selected Academic Period
                |--------------------------------------------------------------------------
                */

                'periodFilters' => [

                    'academic_year' =>
                        $academicYear,

                    /*
                    |--------------------------------------------------------------------------
                    | Keep Original Case For Vue v-model
                    |--------------------------------------------------------------------------
                    */

                    'semester' =>
                        $semester,

                ],


                /*
                |--------------------------------------------------------------------------
                | Academic Year Options
                |--------------------------------------------------------------------------
                |
                | Includes:
                |
                | Previous DB years
                | Current year
                | Future 5 years
                |
                */

                'academicYears' =>
                    $academicYears,


                /*
                |--------------------------------------------------------------------------
                | Semester Options
                |--------------------------------------------------------------------------
                */

                'semesterOptions' =>
                    $semesterOptions,


                /*
                |--------------------------------------------------------------------------
                | Regions
                |--------------------------------------------------------------------------
                */

                'regions' =>
                    $regions,


                /*
                |--------------------------------------------------------------------------
                | Components
                |--------------------------------------------------------------------------
                */

                'components' =>
                    $components,


                /*
                |--------------------------------------------------------------------------
                | Recently Added Universities
                |--------------------------------------------------------------------------
                */

                'recentUniversities' =>
                    $recentUniversities,


                /*
                |--------------------------------------------------------------------------
                | Recent Activities
                |--------------------------------------------------------------------------
                */

                'activities' =>
                    $activities,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Semester
    |--------------------------------------------------------------------------
    |
    | Converts different possible formats into one consistent value.
    |
    | Example:
    |
    | 1ST SEMESTER
    | First Semester
    | semester 1
    |
    | becomes:
    |
    | 1st Semester
    |
    */

    private function normalizeSemester(
        string $semester
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $normalized =
            strtolower(
                trim(
                    $semester
                )
            );


        /*
        |--------------------------------------------------------------------------
        | First Semester
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $normalized,
                [
                    '1st semester',
                    'first semester',
                    'semester 1',
                    '1',
                ],
                true
            )
        ) {

            return '1st Semester';

        }


        /*
        |--------------------------------------------------------------------------
        | Second Semester
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $normalized,
                [
                    '2nd semester',
                    'second semester',
                    'semester 2',
                    '2',
                ],
                true
            )
        ) {

            return '2nd Semester';

        }


        /*
        |--------------------------------------------------------------------------
        | Invalid Semester
        |--------------------------------------------------------------------------
        */

        return '';

    }
}