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
        /*
        |--------------------------------------------------------------------------
        | Instructors
        |--------------------------------------------------------------------------
        */

        if (
            !Schema::hasColumn(
                'instructors',
                'profile_photo'
            )
        ) {

            Schema::table(
                'instructors',
                function (Blueprint $table) {

                    $table
                        ->string(
                            'profile_photo',
                            255
                        )
                        ->nullable();

                }
            );
        }


        if (
            !Schema::hasColumn(
                'instructors',
                'remember_token'
            )
        ) {

            Schema::table(
                'instructors',
                function (Blueprint $table) {

                    $table->rememberToken();

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinators
        |--------------------------------------------------------------------------
        */

        if (
            !Schema::hasColumn(
                'coordinators',
                'profile_photo'
            )
        ) {

            Schema::table(
                'coordinators',
                function (Blueprint $table) {

                    $table
                        ->string(
                            'profile_photo',
                            255
                        )
                        ->nullable();

                }
            );
        }


        if (
            !Schema::hasColumn(
                'coordinators',
                'remember_token'
            )
        ) {

            Schema::table(
                'coordinators',
                function (Blueprint $table) {

                    $table->rememberToken();

                }
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Reverse Migration
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Instructors
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'instructors',
                'profile_photo'
            )
        ) {

            Schema::table(
                'instructors',
                function (Blueprint $table) {

                    $table->dropColumn(
                        'profile_photo'
                    );

                }
            );
        }


        if (
            Schema::hasColumn(
                'instructors',
                'remember_token'
            )
        ) {

            Schema::table(
                'instructors',
                function (Blueprint $table) {

                    $table->dropColumn(
                        'remember_token'
                    );

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinators
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'coordinators',
                'profile_photo'
            )
        ) {

            Schema::table(
                'coordinators',
                function (Blueprint $table) {

                    $table->dropColumn(
                        'profile_photo'
                    );

                }
            );
        }


        if (
            Schema::hasColumn(
                'coordinators',
                'remember_token'
            )
        ) {

            Schema::table(
                'coordinators',
                function (Blueprint $table) {

                    $table->dropColumn(
                        'remember_token'
                    );

                }
            );
        }
    }
};