<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Run Migration
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create(
            'attendance_schedules',
            function (
                Blueprint $table
            ) {
                /*
                |--------------------------------------------------------------------------
                | Primary Key
                |--------------------------------------------------------------------------
                */

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | University
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedBigInteger(
                        'university_id'
                    );


                /*
                |--------------------------------------------------------------------------
                | NSTP Component
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'component',
                        10
                    );


                /*
                |--------------------------------------------------------------------------
                | Attendance Date
                |--------------------------------------------------------------------------
                */

                $table->date(
                    'attendance_date'
                );


                /*
                |--------------------------------------------------------------------------
                | Manually Set Time In
                |--------------------------------------------------------------------------
                */

                $table->time(
                    'time_in'
                );


                /*
                |--------------------------------------------------------------------------
                | Manually Set Late Grace Period
                |--------------------------------------------------------------------------
                |
                | Examples:
                |
                | 0  = no grace period
                | 5  = 5 minutes
                | 10 = 10 minutes
                | 15 = 15 minutes
                | 30 = 30 minutes
                |
                */

                $table
                    ->unsignedSmallInteger(
                        'late_grace_minutes'
                    )
                    ->default(
                        0
                    );


                /*
                |--------------------------------------------------------------------------
                | Manually Set Time Out
                |--------------------------------------------------------------------------
                */

                $table->time(
                    'time_out'
                );


                /*
                |--------------------------------------------------------------------------
                | Created By
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'created_by_type'
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'created_by_id'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Updated By
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'updated_by_type'
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'updated_by_id'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | One Schedule Per University + Component + Date
                |--------------------------------------------------------------------------
                |
                | Example:
                |
                | University 1
                | ROTC
                | August 29, 2026
                |
                | can only have one active time configuration.
                |
                */

                $table->unique(
                    [
                        'university_id',
                        'component',
                        'attendance_date',
                    ],
                    'attendance_schedule_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Search Index
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'attendance_date',
                        'component',
                    ],
                    'attendance_schedule_date_component_index'
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reverse Migration
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::dropIfExists(
            'attendance_schedules'
        );
    }
};