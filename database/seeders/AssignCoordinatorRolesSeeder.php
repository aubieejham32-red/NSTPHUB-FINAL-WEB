<?php

namespace Database\Seeders;

use App\Models\Coordinator;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssignCoordinatorRolesSeeder extends Seeder
{
    /*
    |--------------------------------------------------------------------------
    | Coordinator Guard
    |--------------------------------------------------------------------------
    */

    private const COORDINATOR_GUARD =
        'coordinator';


    /*
    |--------------------------------------------------------------------------
    | Valid Coordinator Roles
    |--------------------------------------------------------------------------
    */

    private const COORDINATOR_ROLES = [
        'coordinator-attendance',
        'coordinator-announcement',
        'coordinator-schedule',
    ];


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
        | Make Sure Coordinator Roles Exist
        |--------------------------------------------------------------------------
        */

        foreach (
            self::COORDINATOR_ROLES
            as $roleName
        ) {

            Role::firstOrCreate(
                [
                    'name' =>
                        $roleName,

                    'guard_name' =>
                        self::COORDINATOR_GUARD,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Process Existing Coordinators
        |--------------------------------------------------------------------------
        |
        | This will automatically read the existing coordinators.role
        | column and convert it into the proper Spatie role.
        |
        */

        Coordinator::query()
            ->orderBy(
                'id'
            )
            ->chunkById(
                100,
                function (
                    $coordinators
                ) {

                    foreach (
                        $coordinators
                        as $coordinator
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Get Existing Role Column
                        |--------------------------------------------------------------------------
                        */

                        $existingRole =
                            $coordinator->getAttribute(
                                'role'
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Normalize Role
                        |--------------------------------------------------------------------------
                        */

                        $roleName =
                            $this->normalizeCoordinatorRole(
                                $existingRole
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Skip Invalid / Empty Roles
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$roleName ||
                            !in_array(
                                $roleName,
                                self::COORDINATOR_ROLES,
                                true
                            )
                        ) {

                            $this->command?->warn(
                                'Coordinator ID ' .
                                $coordinator->id .
                                ' was skipped because it does not have a valid coordinator role.'
                            );

                            continue;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Make Sure Role Exists
                        |--------------------------------------------------------------------------
                        */

                        $role =
                            Role::firstOrCreate(
                                [
                                    'name' =>
                                        $roleName,

                                    'guard_name' =>
                                        self::COORDINATOR_GUARD,
                                ]
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Assign Correct Spatie Role
                        |--------------------------------------------------------------------------
                        |
                        | syncRoles() ensures the Coordinator has exactly
                        | one Coordinator role.
                        |
                        */

                        $coordinator->syncRoles([
                            $role,
                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | Success Message
                        |--------------------------------------------------------------------------
                        */

                        $this->command?->info(
                            'Coordinator ID ' .
                            $coordinator->id .
                            ' assigned role: ' .
                            $roleName
                        );
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


        /*
        |--------------------------------------------------------------------------
        | Completed
        |--------------------------------------------------------------------------
        */

        $this->command?->info(
            'Existing Coordinator Spatie roles have been assigned.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Coordinator Role
    |--------------------------------------------------------------------------
    |
    | Examples:
    |
    | Coordinator-Attendance
    | coordinator-attendance
    | COORDINATOR_ATTENDANCE
    | Coordinator Attendance
    |
    | All become:
    |
    | coordinator-attendance
    |
    */

    private function normalizeCoordinatorRole(
        mixed $role
    ): ?string {

        /*
        |--------------------------------------------------------------------------
        | Must Be String
        |--------------------------------------------------------------------------
        */

        if (
            !is_string(
                $role
            )
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Lowercase And Trim
        |--------------------------------------------------------------------------
        */

        $role =
            strtolower(
                trim(
                    $role
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Convert Spaces / Underscores To Hyphens
        |--------------------------------------------------------------------------
        */

        $role =
            str_replace(
                [
                    '_',
                    ' ',
                ],
                '-',
                $role
            );


        /*
        |--------------------------------------------------------------------------
        | Remove Duplicate Hyphens
        |--------------------------------------------------------------------------
        */

        $role =
            preg_replace(
                '/-+/',
                '-',
                $role
            );


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return
            !empty(
                $role
            )
                ? $role
                : null;
    }
}