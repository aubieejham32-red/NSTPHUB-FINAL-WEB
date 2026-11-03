<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileStudent
{
    /**
     * Ensure the authenticated account is a valid NSTP HUB mobile student.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Authenticated Account
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication Required
        |--------------------------------------------------------------------------
        */

        if (
            !$user
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Authentication is required.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile Student Model Required
        |--------------------------------------------------------------------------
        |
        | NSTP HUB separates account types:
        |
        | App\Models\User
        |     = mobile student
        |
        | App\Models\SuperAdmin
        |     = super administrator
        |
        | App\Models\UniversityAdministrator
        |     = university administrator
        |
        | App\Models\Instructor
        |     = NSTP instructor
        |
        | App\Models\Coordinator
        |     = NSTP coordinator
        |
        */

        if (
            !(
                $user instanceof User
            )
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'This account is not authorized to use the NSTP HUB mobile application.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | University Connection Required
        |--------------------------------------------------------------------------
        |
        | The university is selected and verified before mobile login.
        |
        | A valid mobile student therefore must have:
        |
        | users.university_id
        |
        */

        if (
            $user->university_id ===
            null
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your student account is not connected to a university. Please sign in again using your university access code.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Keep Student Role Synchronized
        |--------------------------------------------------------------------------
        |
        | Spatie is still useful for permission metadata.
        |
        | However, the role is no longer used as the main proof that this
        | authenticated User is a mobile student.
        |
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
        | Query Database Directly
        |--------------------------------------------------------------------------
        |
        | We intentionally query the relationship rather than relying on a
        | possibly previously loaded $user->roles collection.
        |
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
        | Repair Missing Role
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
            | Remove Cached Relationship
            |--------------------------------------------------------------------------
            */

            $user->unsetRelation(
                'roles'
            );


            /*
            |--------------------------------------------------------------------------
            | Clear Spatie Permission Cache
            |--------------------------------------------------------------------------
            */

            app(
                PermissionRegistrar::class
            )->forgetCachedPermissions();
        }


        /*
        |--------------------------------------------------------------------------
        | Continue To Controller
        |--------------------------------------------------------------------------
        */

        return $next(
            $request
        );
    }
}