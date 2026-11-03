<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssignStudentRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Clear Spatie Permission Cache
        |--------------------------------------------------------------------------
        */

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();


        /*
        |--------------------------------------------------------------------------
        | Make Sure Student Role Exists
        |--------------------------------------------------------------------------
        */

        $studentRole =
            Role::firstOrCreate([
                'name' =>
                    'student',

                'guard_name' =>
                    'web',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Assign Student Role Only To Student Users
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Do NOT assign student to every record in users.
        |
        | The project contains older web code that may have created User rows
        | for non-student purposes.
        |
        | A valid NSTP mobile student has:
        |
        | users.university_id IS NOT NULL
        |
        */

        User::query()
            ->whereNotNull(
                'university_id'
            )
            ->chunkById(
                100,

                function ($users) use (
                    $studentRole
                ) {
                    foreach (
                        $users
                        as $user
                    ) {
                        /*
                        |--------------------------------------------------------------------------
                        | Check Existing Role Directly
                        |--------------------------------------------------------------------------
                        */

                        $hasStudentRole =
                            $user
                                ->roles()
                                ->where(
                                    'roles.id',
                                    $studentRole->getKey()
                                )
                                ->exists();


                        /*
                        |--------------------------------------------------------------------------
                        | Assign Missing Student Role
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$hasStudentRole
                        ) {
                            $user
                                ->roles()
                                ->syncWithoutDetaching([
                                    $studentRole->getKey(),
                                ]);


                            /*
                            |--------------------------------------------------------------------------
                            | Remove Loaded Relationship
                            |--------------------------------------------------------------------------
                            */

                            $user->unsetRelation(
                                'roles'
                            );
                        }
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache Again
        |--------------------------------------------------------------------------
        */

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
    }
}