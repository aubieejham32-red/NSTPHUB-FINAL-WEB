<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class MobileAttendanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Student Attendance Record
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/attendance-records
    |
    | Security:
    |
    | - auth:sanctum is applied in routes/api.php
    | - EnsureMobileStudent is applied in routes/api.php
    | - this controller only queries attendance rows that belong to the
    |   authenticated user_id
    |
    */

    public function index(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Authenticated Student
        |--------------------------------------------------------------------------
        */

        $authenticated =
            $request->user();


        if (
            !(
                $authenticated
                instanceof User
            )
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'This account is not authorized to use the NSTP HUB mobile application.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Reload Student
        |--------------------------------------------------------------------------
        */

        $student =
            User::query()
                ->with(
                    'university'
                )
                ->find(
                    $authenticated->id
                );


        if (
            !$student
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Student account could not be found.',
            ], 404);
        }


        if (
            !$student->isStudent()
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Only student accounts may access attendance records.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Optional Exact Date Filter
        |--------------------------------------------------------------------------
        |
        | This is optional because the React Native screen downloads the
        | student's own attendance history and performs its friendly date
        | search locally.
        |
        | Example:
        |
        | /api/mobile/attendance-records?date=2026-07-10
        |
        */

        $validated =
            $request->validate([
                'date' => [
                    'nullable',
                    'date_format:Y-m-d',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Student-Owned Attendance Query
        |--------------------------------------------------------------------------
        */

        $attendanceQuery =
            Attendance::query()
                ->where(
                    'user_id',
                    $student->id
                );


        if (
            isset(
                $validated['date']
            )
            &&
            $validated['date'] !==
            ''
        ) {
            $attendanceQuery
                ->forDate(
                    $validated['date']
                );
        }


        $records =
            $attendanceQuery
                ->orderByDesc(
                    'attendance_date'
                )
                ->orderByDesc(
                    'id'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total' =>
                $records->count(),

            'present' =>
                $records
                    ->where(
                        'remark',
                        Attendance::REMARK_PRESENT
                    )
                    ->count(),

            'late' =>
                $records
                    ->where(
                        'remark',
                        Attendance::REMARK_LATE
                    )
                    ->count(),

            'excused' =>
                $records
                    ->where(
                        'remark',
                        Attendance::REMARK_EXCUSED
                    )
                    ->count(),

            'absent' =>
                $records
                    ->where(
                        'remark',
                        Attendance::REMARK_ABSENT
                    )
                    ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Mobile-Friendly Attendance Rows
        |--------------------------------------------------------------------------
        */

        $attendance =
            $records
                ->map(
                    function (
                        Attendance $record
                    ): array {
                        $attendanceDate =
                            $record
                                ->attendance_date;


                        return [
                            'id' =>
                                $record->id,

                            'attendance_date' =>
                                $attendanceDate
                                    ? $attendanceDate
                                        ->format(
                                            'Y-m-d'
                                        )
                                    : null,

                            'date_label' =>
                                $attendanceDate
                                    ? $attendanceDate
                                        ->format(
                                            'F j, Y'
                                        )
                                    : '—',

                            'time_in' =>
                                $record->time_in,

                            'time_in_label' =>
                                $this->formatTime(
                                    $record->time_in
                                ),

                            'time_out' =>
                                $record->time_out,

                            'time_out_label' =>
                                $this->formatTime(
                                    $record->time_out
                                ),

                            'remark' =>
                                strtoupper(
                                    trim(
                                        (string) (
                                            $record->remark
                                            ??
                                            ''
                                        )
                                    )
                                ),

                            'notes' =>
                                $record->notes,
                        ];
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Attendance records loaded successfully.',

            'student' => [
                'id' =>
                    $student->id,

                'component' =>
                    $student->nstp_component,

                'status' =>
                    strtoupper(
                        trim(
                            (string) (
                                $student->nstp_status
                                ??
                                'ACTIVE'
                            )
                        )
                    ),

                'status_label' =>
                    $student->nstp_status_label,
            ],

            'university' =>
                $student->university
                    ? [
                        'id' =>
                            $student
                                ->university
                                ->id,

                        'name' =>
                            $student
                                ->university
                                ->name,

                        'acronym' =>
                            $student
                                ->university
                                ->acronym,

                        'academic_year' =>
                            $student
                                ->university
                                ->academic_year,

                        'semester' =>
                            $student
                                ->university
                                ->semester,
                    ]
                    : null,

            'summary' =>
                $summary,

            'attendance' =>
                $attendance,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Format Time
    |--------------------------------------------------------------------------
    |
    | Supports MySQL TIME strings such as:
    |
    | 13:20:00
    |
    | and returns:
    |
    | 1:20 PM
    |
    */

    private function formatTime(
        mixed $value
    ): string {
        if (
            $value ===
            null
            ||
            trim(
                (string) $value
            ) ===
            ''
        ) {
            return '—';
        }


        try {
            if (
                $value
                instanceof DateTimeInterface
            ) {
                return CarbonImmutable::instance(
                    $value
                )
                    ->setTimezone(
                        'Asia/Manila'
                    )
                    ->format(
                        'g:i A'
                    );
            }


            return CarbonImmutable::parse(
                (string) $value,
                'Asia/Manila'
            )
                ->format(
                    'g:i A'
                );

        } catch (
            Throwable
        ) {
            return trim(
                (string) $value
            );
        }
    }
}
