<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'instructor_components',
            function (Blueprint $table): void {

                $table->id();

                $table
                    ->foreignId('instructor_id')
                    ->constrained('instructors')
                    ->cascadeOnDelete();

                $table
                    ->string('component', 10);

                $table->timestamps();

                $table->unique([
                    'instructor_id',
                    'component',
                ]);
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Copy Existing Single Component
        |--------------------------------------------------------------------------
        |
        | This prevents your current instructors from losing their assigned
        | component after introducing multiple-component support.
        |
        */

        if (
            Schema::hasTable('instructors')
            &&
            Schema::hasColumn(
                'instructors',
                'component'
            )
        ) {

            $instructors =
                DB::table('instructors')
                    ->whereNotNull('component')
                    ->get([
                        'id',
                        'component',
                    ]);


            foreach ($instructors as $instructor) {

                $component =
                    strtoupper(
                        trim(
                            (string)
                            $instructor->component
                        )
                    );


                if (
                    !in_array(
                        $component,
                        [
                            'LTS',
                            'CWTS',
                            'ROTC',
                        ],
                        true
                    )
                ) {
                    continue;
                }


                DB::table(
                    'instructor_components'
                )->updateOrInsert(
                    [
                        'instructor_id' =>
                            $instructor->id,

                        'component' =>
                            $component,
                    ],
                    [
                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]
                );
            }
        }
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'instructor_components'
        );
    }
};