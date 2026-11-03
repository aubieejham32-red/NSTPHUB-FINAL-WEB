<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssignInstructorRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Clear Spatie Cache
        |--------------------------------------------------------------------------
        */

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();


        /*
        |--------------------------------------------------------------------------
        | Make Sure Instructor Role Exists
        |--------------------------------------------------------------------------
        */

        Role::firstOrCreate(
            [
                'name' =>
                    'instructor',

                'guard_name' =>
                    'instructor',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Get All Instructors
        |--------------------------------------------------------------------------
        */

        Instructor::query()
            ->chunkById(
                100,
                function ($instructors) {

                    foreach (
                        $instructors
                        as $instructor
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Assign Instructor Role
                        |--------------------------------------------------------------------------
                        */

                        $instructor->syncRoles([
                            'instructor',
                        ]);

                    }

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Clear Cache Again
        |--------------------------------------------------------------------------
        */

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
    }
}