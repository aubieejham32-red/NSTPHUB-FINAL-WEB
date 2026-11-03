<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Up
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | University Registration Period
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'universities',
            function (
                Blueprint $table
            ): void {
                if (
                    !Schema::hasColumn(
                        'universities',
                        'registration_start_date'
                    )
                ) {
                    $table
                        ->date(
                            'registration_start_date'
                        )
                        ->nullable();
                }


                if (
                    !Schema::hasColumn(
                        'universities',
                        'registration_end_date'
                    )
                ) {
                    $table
                        ->date(
                            'registration_end_date'
                        )
                        ->nullable();
                }


                if (
                    !Schema::hasColumn(
                        'universities',
                        'registration_end_time'
                    )
                ) {
                    $table
                        ->time(
                            'registration_end_time'
                        )
                        ->nullable();
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Student Registration Management
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'users',
            function (
                Blueprint $table
            ): void {
                if (
                    !Schema::hasColumn(
                        'users',
                        'registration_status'
                    )
                ) {
                    $table
                        ->string(
                            'registration_status',
                            30
                        )
                        ->default(
                            'not_started'
                        )
                        ->index();
                }


                if (
                    !Schema::hasColumn(
                        'users',
                        'registration_submitted_at'
                    )
                ) {
                    $table
                        ->timestamp(
                            'registration_submitted_at'
                        )
                        ->nullable();
                }


                if (
                    !Schema::hasColumn(
                        'users',
                        'registration_completed_at'
                    )
                ) {
                    $table
                        ->timestamp(
                            'registration_completed_at'
                        )
                        ->nullable();
                }


                if (
                    !Schema::hasColumn(
                        'users',
                        'confirmed_at'
                    )
                ) {
                    $table
                        ->timestamp(
                            'confirmed_at'
                        )
                        ->nullable();
                }


                if (
                    !Schema::hasColumn(
                        'users',
                        'student_id_number'
                    )
                ) {
                    $table
                        ->string(
                            'student_id_number',
                            50
                        )
                        ->nullable()
                        ->unique();
                }


                if (
                    !Schema::hasColumn(
                        'users',
                        'qr_token'
                    )
                ) {
                    $table
                        ->string(
                            'qr_token',
                            128
                        )
                        ->nullable()
                        ->unique();
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Down
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::table(
            'universities',
            function (
                Blueprint $table
            ): void {
                $columns = [];


                foreach (
                    [
                        'registration_start_date',
                        'registration_end_date',
                        'registration_end_time',
                    ]
                    as $column
                ) {
                    if (
                        Schema::hasColumn(
                            'universities',
                            $column
                        )
                    ) {
                        $columns[] =
                            $column;
                    }
                }


                if (
                    !empty(
                        $columns
                    )
                ) {
                    $table
                        ->dropColumn(
                            $columns
                        );
                }
            }
        );


        Schema::table(
            'users',
            function (
                Blueprint $table
            ): void {
                $columns = [];


                foreach (
                    [
                        'registration_status',
                        'registration_submitted_at',
                        'registration_completed_at',
                        'confirmed_at',
                        'student_id_number',
                        'qr_token',
                    ]
                    as $column
                ) {
                    if (
                        Schema::hasColumn(
                            'users',
                            $column
                        )
                    ) {
                        $columns[] =
                            $column;
                    }
                }


                if (
                    !empty(
                        $columns
                    )
                ) {
                    $table
                        ->dropColumn(
                            $columns
                        );
                }
            }
        );
    }
};
