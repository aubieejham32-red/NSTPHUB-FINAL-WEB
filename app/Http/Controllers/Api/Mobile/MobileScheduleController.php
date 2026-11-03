<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MobileScheduleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Student Schedule
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/schedules
    |
    */

    public function index(
        Request $request
    ): JsonResponse {
        try {

            /*
            |--------------------------------------------------------------------------
            | Authenticated Student
            |--------------------------------------------------------------------------
            */

            $student =
                $request->user();


            if (
                !(
                    $student instanceof User
                )
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Authenticated student account could not be found.',
                ], 401);
            }


            /*
            |--------------------------------------------------------------------------
            | Student Authorization
            |--------------------------------------------------------------------------
            */

            if (
                !$student->isStudent()
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'This account is not authorized as an NSTP student.',
                ], 403);
            }


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            */

            if (
                !$student->university_id
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Your account is not connected to a university.',
                ], 409);
            }


            /*
            |--------------------------------------------------------------------------
            | Student Component
            |--------------------------------------------------------------------------
            */

            $component =
                strtoupper(
                    trim(
                        (string)
                        (
                            $student->component
                            ??
                            ''
                        )
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Base Schedule Query
            |--------------------------------------------------------------------------
            |
            | SECURITY:
            |
            | Students can only see schedules belonging to their own university.
            |
            */

            $query =
                Schedule::query()
                    ->forUniversity(
                        (int)
                        $student->university_id
                    );


            /*
            |--------------------------------------------------------------------------
            | Component Filtering
            |--------------------------------------------------------------------------
            |
            | Example ROTC student:
            |
            | Can see:
            |
            | ALL
            | ROTC
            |
            | Cannot see:
            |
            | CWTS
            | LTS
            |
            */

            if (
                in_array(
                    $component,
                    Schedule::components(),
                    true
                )
            ) {
                $query->forComponent(
                    $component
                );

            } else {

                /*
                |--------------------------------------------------------------------------
                | Student Has No Valid Component Yet
                |--------------------------------------------------------------------------
                |
                | Only show schedules marked ALL.
                |
                */

                $query->forAllComponents();
            }


            /*
            |--------------------------------------------------------------------------
            | Order
            |--------------------------------------------------------------------------
            |
            | Chronological schedule:
            |
            | June 20
            | June 27
            | July 04
            | ...
            |
            */

            $schedules =
                $query
                    ->orderBy(
                        'schedule_date'
                    )
                    ->orderBy(
                        'start_time'
                    )
                    ->orderBy(
                        'created_at'
                    )
                    ->get();


            /*
            |--------------------------------------------------------------------------
            | Format Mobile Response
            |--------------------------------------------------------------------------
            */

            $formattedSchedules =
                $schedules
                    ->map(
                        function (
                            Schedule $schedule
                        ): array {

                            $scheduleDate =
                                $schedule
                                    ->schedule_date;


                            return [

                                /*
                                |--------------------------------------------------------------------------
                                | IDs
                                |--------------------------------------------------------------------------
                                */

                                'id' =>
                                    $schedule->id,

                                'university_id' =>
                                    $schedule->university_id,


                                /*
                                |--------------------------------------------------------------------------
                                | Component
                                |--------------------------------------------------------------------------
                                */

                                'component' =>
                                    strtoupper(
                                        trim(
                                            (string)
                                            $schedule->component
                                        )
                                    ),


                                /*
                                |--------------------------------------------------------------------------
                                | Event
                                |--------------------------------------------------------------------------
                                */

                                'title' =>
                                    $schedule->title,

                                'location' =>
                                    $schedule->location,


                                /*
                                |--------------------------------------------------------------------------
                                | Raw Date / Time
                                |--------------------------------------------------------------------------
                                */

                                'schedule_date' =>
                                    $scheduleDate
                                        ? $scheduleDate
                                            ->format(
                                                'Y-m-d'
                                            )
                                        : null,

                                'start_time' =>
                                    $schedule->start_time,

                                'end_time' =>
                                    $schedule->end_time,


                                /*
                                |--------------------------------------------------------------------------
                                | Mobile Display Date
                                |--------------------------------------------------------------------------
                                */

                                'date_label' =>
                                    $scheduleDate
                                        ? $scheduleDate
                                            ->format(
                                                'F d, Y'
                                            )
                                        : '-',

                                'day_label' =>
                                    $scheduleDate
                                        ? $scheduleDate
                                            ->format(
                                                'l'
                                            )
                                        : '-',


                                /*
                                |--------------------------------------------------------------------------
                                | Mobile Display Time
                                |--------------------------------------------------------------------------
                                */

                                'time_label' =>
                                    $this->formatTimeRange(
                                        $schedule->start_time,
                                        $schedule->end_time
                                    ),


                                /*
                                |--------------------------------------------------------------------------
                                | Status
                                |--------------------------------------------------------------------------
                                */

                                'is_today' =>
                                    $scheduleDate
                                        ? $scheduleDate
                                            ->isToday()
                                        : false,

                                'is_past' =>
                                    $scheduleDate
                                        ? $scheduleDate
                                            ->lt(
                                                today()
                                            )
                                        : false,

                                'is_upcoming' =>
                                    $scheduleDate
                                        ? $scheduleDate
                                            ->gte(
                                                today()
                                            )
                                        : false,
                            ];
                        }
                    )
                    ->values();


            /*
            |--------------------------------------------------------------------------
            | Counts
            |--------------------------------------------------------------------------
            */

            $upcomingCount =
                $formattedSchedules
                    ->where(
                        'is_upcoming',
                        true
                    )
                    ->count();


            $pastCount =
                $formattedSchedules
                    ->where(
                        'is_past',
                        true
                    )
                    ->count();


            /*
            |--------------------------------------------------------------------------
            | Schedule Title
            |--------------------------------------------------------------------------
            */

            $scheduleTitle =
                in_array(
                    $component,
                    Schedule::components(),
                    true
                )
                    ? $component
                        .
                        ' SCHEDULE'
                    : 'NSTP SCHEDULE';


            /*
            |--------------------------------------------------------------------------
            | JSON Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Student schedules loaded successfully.',

                'schedule_title' =>
                    $scheduleTitle,

                'component' =>
                    $component !== ''
                        ? $component
                        : null,

                'total' =>
                    $formattedSchedules
                        ->count(),

                'upcoming_count' =>
                    $upcomingCount,

                'past_count' =>
                    $pastCount,

                'schedules' =>
                    $formattedSchedules,
            ]);

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Laravel Log
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Mobile schedule API failed.',
                [
                    'message' =>
                        $exception->getMessage(),

                    'file' =>
                        $exception->getFile(),

                    'line' =>
                        $exception->getLine(),

                    'user_id' =>
                        optional(
                            $request->user()
                        )->id,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Always Return JSON
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    config(
                        'app.debug'
                    )
                        ? 'Schedule error: '
                            .
                            $exception->getMessage()
                        : 'Unable to load your NSTP schedules.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Format Time Range
    |--------------------------------------------------------------------------
    |
    | Database:
    |
    | 08:00:00
    | 12:00:00
    |
    | Mobile:
    |
    | 8:00 AM - 12:00 PM
    |
    */

    private function formatTimeRange(
        ?string $startTime,
        ?string $endTime
    ): string {
        $start =
            $this->formatTime(
                $startTime
            );


        $end =
            $this->formatTime(
                $endTime
            );


        if (
            $start
            &&
            $end
        ) {
            return
                $start
                .
                ' - '
                .
                $end;
        }


        if (
            $start
        ) {
            return $start;
        }


        if (
            $end
        ) {
            return $end;
        }


        return 'Time TBA';
    }


    /*
    |--------------------------------------------------------------------------
    | Format Individual Time
    |--------------------------------------------------------------------------
    */

    private function formatTime(
        ?string $time
    ): ?string {
        $time =
            trim(
                (string)
                $time
            );


        if (
            $time ===
            ''
        ) {
            return null;
        }


        try {
            return Carbon::parse(
                $time
            )
                ->format(
                    'g:i A'
                );

        } catch (
            Throwable
        ) {
            return $time;
        }
    }
}