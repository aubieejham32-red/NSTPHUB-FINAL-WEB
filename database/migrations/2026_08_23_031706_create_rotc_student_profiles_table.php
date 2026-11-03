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
            'rotc_student_profiles',
            function (Blueprint $table) {

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
                | One user can only have ONE ROTC profile.
                |
                | users.id
                |     ↓
                | rotc_student_profiles.user_id
                |
                */

                $table
                    ->foreignId('user_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | ROTC Identification
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'nstp_id_no',
                        50
                    )
                    ->nullable()
                    ->unique();


                $table
                    ->string(
                        'ms_level',
                        30
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Name
                |--------------------------------------------------------------------------
                |
                | These are stored here as part of the submitted ROTC profile.
                |
                | Your general registration already stores names in users,
                | but keeping the ROTC submission snapshot is useful for
                | military records.
                |
                */

                $table
                    ->string(
                        'last_name',
                        100
                    )
                    ->nullable();


                $table
                    ->string(
                        'first_name',
                        100
                    )
                    ->nullable();


                $table
                    ->string(
                        'middle_name',
                        100
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Name Extension
                |--------------------------------------------------------------------------
                |
                | Examples:
                |
                | Jr.
                | Sr.
                | III
                | IV
                |
                */

                $table
                    ->string(
                        'name_extension',
                        30
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Personal Details
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'gender',
                        20
                    )
                    ->nullable();


                $table
                    ->string(
                        'blood_type',
                        10
                    )
                    ->nullable();


                $table
                    ->date(
                        'date_of_birth'
                    )
                    ->nullable();


                $table
                    ->string(
                        'place_of_birth',
                        150
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Physical Attributes
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'height_cm',
                        6,
                        2
                    )
                    ->nullable();


                $table
                    ->decimal(
                        'weight_kg',
                        6,
                        2
                    )
                    ->nullable();


                $table
                    ->string(
                        'complexion',
                        50
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Academic Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'school_name',
                        200
                    )
                    ->nullable();


                $table
                    ->string(
                        'course',
                        150
                    )
                    ->nullable();


                $table
                    ->string(
                        'religion',
                        100
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Contact Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'cellphone_number',
                        30
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | ROTC Contact Email
                |--------------------------------------------------------------------------
                |
                | This is intentionally separate from users.email.
                |
                | users.email remains the actual NSTP HUB login email.
                |
                */

                $table
                    ->string(
                        'contact_email',
                        191
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Temporary Address
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'temporary_address_line',
                        255
                    )
                    ->nullable();


                $table
                    ->string(
                        'temporary_municipality',
                        150
                    )
                    ->nullable();


                $table
                    ->string(
                        'temporary_province',
                        150
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Permanent Address Option
                |--------------------------------------------------------------------------
                |
                | true:
                | Permanent address is the same as temporary address.
                |
                | false:
                | Student entered a separate permanent address.
                |
                */

                $table
                    ->boolean(
                        'permanent_same_as_temporary'
                    )
                    ->default(false);


                /*
                |--------------------------------------------------------------------------
                | Permanent Address
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'permanent_address_line',
                        255
                    )
                    ->nullable();


                $table
                    ->string(
                        'permanent_municipality',
                        150
                    )
                    ->nullable();


                $table
                    ->string(
                        'permanent_province',
                        150
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Father
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'father_name',
                        150
                    )
                    ->nullable();


                $table
                    ->string(
                        'father_occupation',
                        150
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Mother
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'mother_name',
                        150
                    )
                    ->nullable();


                $table
                    ->string(
                        'mother_occupation',
                        150
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Emergency Contact
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'emergency_contact_name',
                        150
                    )
                    ->nullable();


                $table
                    ->string(
                        'emergency_contact_relationship',
                        100
                    )
                    ->nullable();


                $table
                    ->string(
                        'emergency_contact_number',
                        30
                    )
                    ->nullable();


                $table
                    ->string(
                        'emergency_contact_address',
                        255
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Advance Course
                |--------------------------------------------------------------------------
                |
                | Screenshot:
                |
                | "Are you willing to take the advance course?"
                |
                */

                $table
                    ->boolean(
                        'willing_advance_course'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Profile Completion
                |--------------------------------------------------------------------------
                |
                | This is separate from general NSTP registration.
                |
                | A ROTC student may already have their general NSTP
                | registration confirmed but still need to finish ROTC.
                |
                */

                $table
                    ->string(
                        'status',
                        30
                    )
                    ->default(
                        'draft'
                    )
                    ->index();


                $table
                    ->timestamp(
                        'submitted_at'
                    )
                    ->nullable();


                $table
                    ->timestamp(
                        'completed_at'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();
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
            'rotc_student_profiles'
        );
    }
};