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
        Schema::table('users', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            |
            | Every student belongs to a university.
            |
            */

            $table->foreignId('university_id')
                ->nullable()
                ->after('id')
                ->constrained('universities')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | NSTP Student Identification
            |--------------------------------------------------------------------------
            |
            | student_id_number:
            | Example: NSTP-26-04821
            |
            | qr_token:
            | Unique secure value that we will use for the student's QR code.
            |
            */

            $table->string('student_id_number', 50)
                ->nullable()
                ->unique()
                ->after('university_id');

            $table->uuid('qr_token')
                ->nullable()
                ->unique()
                ->after('student_id_number');


            /*
            |--------------------------------------------------------------------------
            | NSTP Registration Details
            |--------------------------------------------------------------------------
            |
            | Based on your registration form:
            |
            | Subject    : NSTP 1
            | Component  : ROTC / CWTS / LTS
            | Term       : 1st Semester / 2nd Semester
            |
            */

            $table->string('subject', 50)
                ->nullable()
                ->after('qr_token');

            $table->string('component', 30)
                ->nullable()
                ->index()
                ->after('subject');

            $table->string('term', 50)
                ->nullable()
                ->after('component');


            /*
            |--------------------------------------------------------------------------
            | Student Name
            |--------------------------------------------------------------------------
            */

            $table->string('surname', 100)
                ->nullable()
                ->after('term');

            $table->string('first_name', 100)
                ->nullable()
                ->after('surname');

            $table->string('middle_name', 100)
                ->nullable()
                ->after('first_name');


            /*
            |--------------------------------------------------------------------------
            | Academic Information
            |--------------------------------------------------------------------------
            */

            $table->string('course', 255)
                ->nullable()
                ->after('middle_name');

            $table->string('year_level', 30)
                ->nullable()
                ->after('course');

            $table->string('section', 50)
                ->nullable()
                ->after('year_level');


            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            $table->string('gender', 20)
                ->nullable()
                ->after('section');

            $table->date('birth_date')
                ->nullable()
                ->after('gender');

            $table->string('contact_number', 30)
                ->nullable()
                ->after('birth_date');


            /*
            |--------------------------------------------------------------------------
            | Student Address
            |--------------------------------------------------------------------------
            |
            | These fields correspond to:
            |
            | City Address
            | Municipality
            | Province
            |
            */

            $table->text('city_address')
                ->nullable()
                ->after('contact_number');

            $table->string('municipality', 150)
                ->nullable()
                ->after('city_address');

            $table->string('province', 150)
                ->nullable()
                ->after('municipality');


            /*
            |--------------------------------------------------------------------------
            | Parent / Guardian / Emergency Contact
            |--------------------------------------------------------------------------
            */

            $table->string('guardian_name', 255)
                ->nullable()
                ->after('province');

            $table->text('guardian_address')
                ->nullable()
                ->after('guardian_name');

            $table->string('guardian_contact_number', 30)
                ->nullable()
                ->after('guardian_address');


            /*
            |--------------------------------------------------------------------------
            | Student Profile Photo
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | We do NOT save the actual image inside MySQL.
            |
            | We save the file in Laravel storage and only save its path here.
            |
            | Example:
            |
            | student-profile-photos/5/profile.jpg
            |
            */

            $table->string('profile_photo', 2048)
                ->nullable()
                ->after('guardian_contact_number');


            /*
            |--------------------------------------------------------------------------
            | Student Signature
            |--------------------------------------------------------------------------
            |
            | The signature image will also be stored in Laravel storage.
            |
            | Example:
            |
            | student-signatures/5/signature.png
            |
            */

            $table->string('signature_path', 2048)
                ->nullable()
                ->after('profile_photo');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Remove Foreign Key First
            |--------------------------------------------------------------------------
            */

            $table->dropConstrainedForeignId('university_id');


            /*
            |--------------------------------------------------------------------------
            | Remove Unique Indexes
            |--------------------------------------------------------------------------
            */

            $table->dropUnique([
                'student_id_number',
            ]);

            $table->dropUnique([
                'qr_token',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Remove Component Index
            |--------------------------------------------------------------------------
            */

            $table->dropIndex([
                'component',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Remove Student Columns
            |--------------------------------------------------------------------------
            */

            $table->dropColumn([
                'student_id_number',
                'qr_token',

                'subject',
                'component',
                'term',

                'surname',
                'first_name',
                'middle_name',

                'course',
                'year_level',
                'section',

                'gender',
                'birth_date',
                'contact_number',

                'city_address',
                'municipality',
                'province',

                'guardian_name',
                'guardian_address',
                'guardian_contact_number',

                'profile_photo',
                'signature_path',
            ]);
        });
    }
};