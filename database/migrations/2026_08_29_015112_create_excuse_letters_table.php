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
            'excuse_letters',
            function (
                Blueprint $table
            ): void {

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
                    ->foreignId(
                        'university_id'
                    )
                    ->constrained(
                        'universities'
                    )
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Student
                |--------------------------------------------------------------------------
                |
                | users.id
                |
                */

                $table
                    ->foreignId(
                        'user_id'
                    )
                    ->constrained(
                        'users'
                    )
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Instructor
                |--------------------------------------------------------------------------
                |
                | Nullable because an excuse letter may be submitted before an
                | instructor is assigned.
                |
                */

                $table
                    ->foreignId(
                        'instructor_id'
                    )
                    ->nullable()
                    ->constrained(
                        'instructors'
                    )
                    ->cascadeOnUpdate()
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | NSTP Component
                |--------------------------------------------------------------------------
                |
                | CWTS
                | LTS
                | ROTC
                |
                */

                $table
                    ->string(
                        'component',
                        20
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Date Of Absence
                |--------------------------------------------------------------------------
                */

                $table
                    ->date(
                        'absence_date'
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Reason
                |--------------------------------------------------------------------------
                |
                | Examples:
                |
                | Family Emergency
                | Medical
                | Personal Emergency
                |
                */

                $table
                    ->string(
                        'reason',
                        150
                    );


                /*
                |--------------------------------------------------------------------------
                | Explanation
                |--------------------------------------------------------------------------
                */

                $table
                    ->text(
                        'explanation'
                    );


                /*
                |--------------------------------------------------------------------------
                | Supporting Evidence
                |--------------------------------------------------------------------------
                |
                | Optional:
                |
                | JPG
                | JPEG
                | PNG
                | WEBP
                | PDF
                |
                */

                $table
                    ->string(
                        'evidence_path'
                    )
                    ->nullable();


                $table
                    ->string(
                        'evidence_original_name'
                    )
                    ->nullable();


                $table
                    ->string(
                        'evidence_mime_type',
                        100
                    )
                    ->nullable();


                $table
                    ->unsignedBigInteger(
                        'evidence_size'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                |
                | PENDING
                | APPROVED
                | REJECTED
                |
                */

                $table
                    ->string(
                        'status',
                        30
                    )
                    ->default(
                        'PENDING'
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Reviewer Feedback
                |--------------------------------------------------------------------------
                */

                $table
                    ->text(
                        'feedback'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Reviewer
                |--------------------------------------------------------------------------
                |
                | Creates:
                |
                | reviewer_type
                | reviewer_id
                |
                | Possible reviewer models:
                |
                | UniversityAdministrator
                | Instructor
                | Coordinator
                |
                */

                $table
                    ->nullableMorphs(
                        'reviewer'
                    );


                /*
                |--------------------------------------------------------------------------
                | Reviewer Spatie Role
                |--------------------------------------------------------------------------
                |
                | Important for Coordinator because one Coordinator model can
                | represent different roles.
                |
                | Examples:
                |
                | university-admin
                | instructor
                | coordinator-attendance
                |
                */

                $table
                    ->string(
                        'reviewer_role',
                        80
                    )
                    ->nullable()
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Reviewed At
                |--------------------------------------------------------------------------
                */

                $table
                    ->timestamp(
                        'reviewed_at'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Laravel Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Useful Compound Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'university_id',
                        'status',
                    ],
                    'excuse_letters_university_status_index'
                );


                $table->index(
                    [
                        'university_id',
                        'component',
                    ],
                    'excuse_letters_university_component_index'
                );


                $table->index(
                    [
                        'user_id',
                        'status',
                    ],
                    'excuse_letters_student_status_index'
                );


                $table->index(
                    [
                        'instructor_id',
                        'status',
                    ],
                    'excuse_letters_instructor_status_index'
                );


                $table->index(
                    [
                        'component',
                        'status',
                    ],
                    'excuse_letters_component_status_index'
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
            'excuse_letters'
        );
    }
};