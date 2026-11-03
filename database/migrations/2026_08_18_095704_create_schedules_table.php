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
            'schedules',
            function (Blueprint $table) {

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
                |
                | Schedule belongs to one university.
                |
                */

                $table
                    ->foreignId(
                        'university_id'
                    )
                    ->constrained(
                        'universities'
                    )
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Creator
                |--------------------------------------------------------------------------
                |
                | Can be:
                |
                | UniversityAdministrator
                | Instructor
                | Coordinator
                |
                | Polymorphic relationship:
                |
                | creator_id
                | creator_type
                |
                */

                $table->unsignedBigInteger(
                    'creator_id'
                );

                $table->string(
                    'creator_type',
                    191
                );


                /*
                |--------------------------------------------------------------------------
                | Creator Role
                |--------------------------------------------------------------------------
                |
                | Only these roles can create schedules:
                |
                | university-admin
                | instructor
                | coordinator-schedule
                |
                */

                $table->enum(
                    'creator_role',
                    [
                        'university-admin',

                        'instructor',

                        'coordinator-schedule',
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | NSTP Component
                |--------------------------------------------------------------------------
                |
                | ALL
                | CWTS
                | LTS
                | ROTC
                |
                */

                $table->enum(
                    'component',
                    [
                        'ALL',

                        'CWTS',

                        'LTS',

                        'ROTC',
                    ]
                )->default(
                    'ALL'
                );


                /*
                |--------------------------------------------------------------------------
                | Schedule Title
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'title',
                    255
                );


                /*
                |--------------------------------------------------------------------------
                | Schedule Date
                |--------------------------------------------------------------------------
                */

                $table->date(
                    'schedule_date'
                );


                /*
                |--------------------------------------------------------------------------
                | Start Time
                |--------------------------------------------------------------------------
                */

                $table->time(
                    'start_time'
                );


                /*
                |--------------------------------------------------------------------------
                | End Time
                |--------------------------------------------------------------------------
                */

                $table->time(
                    'end_time'
                );


                /*
                |--------------------------------------------------------------------------
                | Location
                |--------------------------------------------------------------------------
                |
                | Example:
                |
                | EB 510
                | Covered Court
                | Gymnasium
                |
                */

                $table->string(
                    'location',
                    255
                );


                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'creator_id',
                        'creator_type',
                    ],
                    'schedules_creator_index'
                );


                $table->index(
                    'creator_role'
                );


                $table->index(
                    'component'
                );


                $table->index(
                    'schedule_date'
                );


                $table->index(
                    [
                        'university_id',
                        'component',
                        'schedule_date',
                    ],
                    'schedules_access_index'
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
            'schedules'
        );
    }
};