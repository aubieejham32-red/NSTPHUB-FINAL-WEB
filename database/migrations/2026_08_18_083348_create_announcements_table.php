<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'announcements',
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
                | Every announcement belongs to one university.
                |
                */

                $table->foreignId('university_id')
                    ->constrained('universities')
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Announcement Creator
                |--------------------------------------------------------------------------
                |
                | creator_id
                | creator_type
                |
                | Allows the creator to be:
                |
                | App\Models\UniversityAdministrator
                | App\Models\Instructor
                | App\Models\Coordinator
                |
                */

                $table->unsignedBigInteger('creator_id');

                $table->string(
                    'creator_type',
                    191
                );


                /*
                |--------------------------------------------------------------------------
                | Creator Role
                |--------------------------------------------------------------------------
                |
                | The only roles allowed to create/manage announcements:
                |
                | university-admin
                | instructor
                | coordinator-announcement
                |
                */

                $table->enum(
                    'creator_role',
                    [
                        'university-admin',
                        'instructor',
                        'coordinator-announcement',
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | Announcement Information
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'title',
                    255
                );

                $table->date(
                    'announcement_date'
                );

                $table->text(
                    'description'
                );


                /*
                |--------------------------------------------------------------------------
                | Recipient
                |--------------------------------------------------------------------------
                |
                | all_students
                | specific_student
                |
                */

                $table->enum(
                    'recipient',
                    [
                        'all_students',
                        'specific_student',
                    ]
                )->default(
                    'all_students'
                );


                /*
                |--------------------------------------------------------------------------
                | Student Email
                |--------------------------------------------------------------------------
                |
                | Only required when:
                |
                | recipient = specific_student
                |
                */

                $table->string(
                    'student_email',
                    191
                )->nullable();


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
                    ]
                );

                $table->index(
                    'creator_role'
                );

                $table->index(
                    'announcement_date'
                );

                $table->index(
                    'recipient'
                );

                $table->index(
                    'student_email'
                );
            }
        );
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'announcements'
        );
    }
};