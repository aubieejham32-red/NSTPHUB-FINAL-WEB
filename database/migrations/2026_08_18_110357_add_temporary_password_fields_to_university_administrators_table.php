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
        | Temporary Password
        |--------------------------------------------------------------------------
        |
        | must_change_password already exists from:
        |
        | 2026_07_18_152426_add_must_change_password_to_university_administrators.php
        |
        | Therefore this migration ONLY adds temporary_password.
        |
        */

        if (
            !Schema::hasColumn(
                'university_administrators',
                'temporary_password'
            )
        ) {
            Schema::table(
                'university_administrators',
                function (Blueprint $table) {

                    $table
                        ->text('temporary_password')
                        ->nullable()
                        ->after('password');

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
        | Only Remove What This Migration Added
        |--------------------------------------------------------------------------
        |
        | Do NOT remove must_change_password here because it belongs
        | to an older migration.
        |
        */

        if (
            Schema::hasColumn(
                'university_administrators',
                'temporary_password'
            )
        ) {
            Schema::table(
                'university_administrators',
                function (Blueprint $table) {

                    $table->dropColumn(
                        'temporary_password'
                    );

                }
            );
        }
    }
};