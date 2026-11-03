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
        | Add NSTP Status To Students
        |--------------------------------------------------------------------------
        |
        | The status belongs to the users table because NSTP students are
        | stored as User models.
        |
        | Valid application values:
        |
        | ACTIVE
        | WARNING FOR DROPOUT
        | DROPOUT
        |
        */

        if (
            !Schema::hasColumn(
                'users',
                'nstp_status'
            )
        ) {
            Schema::table(
                'users',
                function (
                    Blueprint $table
                ): void {
                    $table
                        ->string(
                            'nstp_status',
                            50
                        )
                        ->default(
                            'ACTIVE'
                        )
                        ->nullable(
                            false
                        );
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
        if (
            Schema::hasColumn(
                'users',
                'nstp_status'
            )
        ) {
            Schema::table(
                'users',
                function (
                    Blueprint $table
                ): void {
                    $table->dropColumn(
                        'nstp_status'
                    );
                }
            );
        }
    }
};