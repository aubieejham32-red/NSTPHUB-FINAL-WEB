<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Add Time In / Time Out Windows
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::table(
            'attendance_schedules',
            function (
                Blueprint $table
            ): void {
                /*
                |--------------------------------------------------------------------------
                | Time In Window
                |--------------------------------------------------------------------------
                */

                $table
                    ->time(
                        'start_time_in'
                    )
                    ->nullable()
                    ->after(
                        'attendance_date'
                    );

                $table
                    ->time(
                        'end_time_in'
                    )
                    ->nullable()
                    ->after(
                        'start_time_in'
                    );

                $table
                    ->unsignedSmallInteger(
                        'time_in_extension_minutes'
                    )
                    ->default(
                        0
                    )
                    ->after(
                        'end_time_in'
                    );

                /*
                |--------------------------------------------------------------------------
                | Time Out Window
                |--------------------------------------------------------------------------
                */

                $table
                    ->time(
                        'start_time_out'
                    )
                    ->nullable()
                    ->after(
                        'time_in_extension_minutes'
                    );

                $table
                    ->time(
                        'end_time_out'
                    )
                    ->nullable()
                    ->after(
                        'start_time_out'
                    );

                $table
                    ->unsignedSmallInteger(
                        'time_out_extension_minutes'
                    )
                    ->default(
                        0
                    )
                    ->after(
                        'end_time_out'
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Saved Schedules
        |--------------------------------------------------------------------------
        */

        DB::table(
            'attendance_schedules'
        )
            ->select(
                [
                    'id',
                    'attendance_date',
                    'time_in',
                    'late_grace_minutes',
                    'time_out',
                ]
            )
            ->orderBy(
                'id'
            )
            ->get()
            ->each(
                function (
                    object $schedule
                ): void {
                    $date =
                        CarbonImmutable::parse(
                            (string) $schedule
                                ->attendance_date,
                            'Asia/Manila'
                        )
                            ->toDateString();

                    $startTimeIn =
                        CarbonImmutable::parse(
                            $date
                            .
                            ' '
                            .
                            (string) $schedule
                                ->time_in,
                            'Asia/Manila'
                        );

                    $endTimeIn =
                        $startTimeIn
                            ->addMinutes(
                                max(
                                    0,
                                    (int) $schedule
                                        ->late_grace_minutes
                                )
                            );

                    $startTimeOut =
                        CarbonImmutable::parse(
                            $date
                            .
                            ' '
                            .
                            (string) $schedule
                                ->time_out,
                            'Asia/Manila'
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Preserve Previous Late Window
                    |--------------------------------------------------------------------------
                    */

                    $timeInExtensionMinutes =
                        max(
                            0,
                            min(
                                180,
                                (int) floor(
                                    (
                                        $startTimeOut
                                            ->getTimestamp()
                                        -
                                        $endTimeIn
                                            ->getTimestamp()
                                    )
                                    /
                                    60
                                )
                            )
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Existing Time Out Had No Closing Window
                    |--------------------------------------------------------------------------
                    |
                    | Existing records are temporarily converted to 11:59 PM.
                    | You can edit today's schedule from the Attendance page.
                    |
                    */

                    $endTimeOut =
                        CarbonImmutable::parse(
                            $date
                            .
                            ' 23:59:00',
                            'Asia/Manila'
                        );

                    DB::table(
                        'attendance_schedules'
                    )
                        ->where(
                            'id',
                            $schedule->id
                        )
                        ->update(
                            [
                                'start_time_in' =>
                                    $startTimeIn
                                        ->format(
                                            'H:i:s'
                                        ),

                                'end_time_in' =>
                                    $endTimeIn
                                        ->format(
                                            'H:i:s'
                                        ),

                                'time_in_extension_minutes' =>
                                    $timeInExtensionMinutes,

                                'start_time_out' =>
                                    $startTimeOut
                                        ->format(
                                            'H:i:s'
                                        ),

                                'end_time_out' =>
                                    $endTimeOut
                                        ->format(
                                            'H:i:s'
                                        ),

                                'time_out_extension_minutes' =>
                                    0,
                            ]
                        );
                }
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Rollback
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::table(
            'attendance_schedules',
            function (
                Blueprint $table
            ): void {
                $table->dropColumn(
                    [
                        'start_time_in',
                        'end_time_in',
                        'time_in_extension_minutes',
                        'start_time_out',
                        'end_time_out',
                        'time_out_extension_minutes',
                    ]
                );
            }
        );
    }
};