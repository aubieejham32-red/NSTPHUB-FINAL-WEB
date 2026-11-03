<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Up
    |--------------------------------------------------------------------------
    |
    | The StudentRegistrationController generates a 64-character secure
    | random QR token.
    |
    | The existing qr_token column is too short, causing:
    |
    | SQLSTATE[22001]
    | Data too long for column 'qr_token'
    |
    | We use VARCHAR(128) so the current 64-character token fits safely while
    | leaving room for future token formats.
    |
    */

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        */

        if (
            !Schema::hasColumn(
                'users',
                'qr_token'
            )
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Enlarge QR Token
        |--------------------------------------------------------------------------
        |
        | MySQL keeps the existing index on the column while MODIFY changes
        | its permitted length.
        |
        */

        DB::statement(
            '
                ALTER TABLE `users`
                MODIFY `qr_token` VARCHAR(128) NULL
            '
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Down
    |--------------------------------------------------------------------------
    |
    | We intentionally restore VARCHAR(50) only for rollback compatibility.
    |
    | WARNING:
    | Existing 64-character QR tokens would not fit after rolling back.
    |
    */

    public function down(): void
    {
        if (
            !Schema::hasColumn(
                'users',
                'qr_token'
            )
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Clear Long Values Before Shrinking
        |--------------------------------------------------------------------------
        */

        DB::table(
            'users'
        )
            ->whereRaw(
                'CHAR_LENGTH(`qr_token`) > 50'
            )
            ->update([
                'qr_token' =>
                    null,
            ]);


        DB::statement(
            '
                ALTER TABLE `users`
                MODIFY `qr_token` VARCHAR(50) NULL
            '
        );
    }
};