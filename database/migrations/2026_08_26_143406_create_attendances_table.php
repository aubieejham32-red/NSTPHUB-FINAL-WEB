<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Run The Migrations
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            |
            | The student is stored in the users table.
            |
            */

            $table
                ->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | NSTP Component
            |--------------------------------------------------------------------------
            |
            | Supported components:
            |
            | ROTC
            | LTS
            | CWTS
            |
            */

            $table->enum(
                'component',
                [
                    'ROTC',
                    'LTS',
                    'CWTS',
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Attendance Date
            |--------------------------------------------------------------------------
            */

            $table->date('attendance_date');


            /*
            |--------------------------------------------------------------------------
            | Time In
            |--------------------------------------------------------------------------
            */

            $table
                ->time('time_in')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Time Out
            |--------------------------------------------------------------------------
            */

            $table
                ->time('time_out')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Attendance Remark
            |--------------------------------------------------------------------------
            |
            | This is the DAILY attendance result.
            |
            */

            $table
                ->enum(
                    'remark',
                    [
                        'PRESENT',
                        'LATE',
                        'ABSENT',
                        'EXCUSED',
                    ]
                )
                ->default('PRESENT');


            /*
            |--------------------------------------------------------------------------
            | Optional Notes
            |--------------------------------------------------------------------------
            */

            $table
                ->text('notes')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Recorded By
            |--------------------------------------------------------------------------
            |
            | Your application has different authentication models:
            |
            | Instructor
            | Coordinator
            | UniversityAdministrator
            |
            | Therefore we store the model type and its ID.
            |
            */

            $table
                ->string(
                    'recorded_by_type',
                    100
                )
                ->nullable();

            $table
                ->unsignedBigInteger(
                    'recorded_by_id'
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
            | Prevent Duplicate Daily Attendance
            |--------------------------------------------------------------------------
            |
            | One student should only have one attendance record for the
            | same component on the same date.
            |
            */

            $table->unique(
                [
                    'user_id',
                    'component',
                    'attendance_date',
                ],
                'attendance_student_component_date_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Database Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'component',
                    'attendance_date',
                ],
                'attendance_component_date_index'
            );

            $table->index(
                [
                    'user_id',
                    'attendance_date',
                ],
                'attendance_user_date_index'
            );

            $table->index(
                [
                    'remark',
                    'attendance_date',
                ],
                'attendance_remark_date_index'
            );

            $table->index(
                [
                    'recorded_by_type',
                    'recorded_by_id',
                ],
                'attendance_recorded_by_index'
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Reverse The Migrations
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};