<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Create Roles
        |--------------------------------------------------------------------------
        */

        $this->call([
            RoleSeeder::class,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Assign Student Roles
        |--------------------------------------------------------------------------
        |
        | All records inside users are NSTP student accounts.
        |
        */

        $this->call([
            AssignStudentRolesSeeder::class,
        ]);
    }
}