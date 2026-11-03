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
            'reports',
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
                | University
                |--------------------------------------------------------------------------
                |
                | universities.id
                |
                | Keeping university_id directly on the report makes university
                | filtering secure and efficient.
                |
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
                | Instructor
                |--------------------------------------------------------------------------
                |
                | The instructor can later be deleted without deleting the
                | student's report history.
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
                | ROTC
                | LTS
                | CWTS
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
                | Date Of Occurrence
                |--------------------------------------------------------------------------
                */

                $table
                    ->date(
                        'occurrence_date'
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Report Concern
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'subject',
                        150
                    );


                $table
                    ->text(
                        'description'
                    );


                /*
                |--------------------------------------------------------------------------
                | Optional Attachment
                |--------------------------------------------------------------------------
                |
                | Example:
                |
                | reports/3/proof-image.jpg
                |
                */

                $table
                    ->string(
                        'attachment_path'
                    )
                    ->nullable();


                $table
                    ->string(
                        'attachment_original_name'
                    )
                    ->nullable();


                $table
                    ->string(
                        'attachment_mime_type',
                        100
                    )
                    ->nullable();


                $table
                    ->unsignedBigInteger(
                        'attachment_size'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                |
                | PENDING
                | IN REVIEW
                | RESOLVED
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
                | Feedback
                |--------------------------------------------------------------------------
                |
                | Response from the NSTP office / reviewer.
                |
                */

                $table
                    ->text(
                        'feedback'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Reviewed By
                |--------------------------------------------------------------------------
                |
                | Polymorphic reviewer:
                |
                | reviewed_by_type
                | reviewed_by_id
                |
                | This supports:
                |
                | UniversityAdministrator
                | Instructor
                | Coordinator
                |
                */

                $table
                    ->nullableMorphs(
                        'reviewed_by'
                    );


                /*
                |--------------------------------------------------------------------------
                | Review Dates
                |--------------------------------------------------------------------------
                */

                $table
                    ->timestamp(
                        'reviewed_at'
                    )
                    ->nullable();


                $table
                    ->timestamp(
                        'resolved_at'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Laravel Timestamps
                |--------------------------------------------------------------------------
                |
                | created_at = date report was submitted
                | updated_at = latest report update
                |
                */

                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'university_id',
                        'status',
                    ],
                    'reports_university_status_index'
                );


                $table->index(
                    [
                        'user_id',
                        'occurrence_date',
                    ],
                    'reports_student_occurrence_index'
                );


                $table->index(
                    [
                        'instructor_id',
                        'status',
                    ],
                    'reports_instructor_status_index'
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
            'reports'
        );
    }
};