<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAccountSettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Change Student Password
    |--------------------------------------------------------------------------
    |
    | PUT
    |
    | /api/mobile/account/password
    |
    | Protected by:
    |
    | auth:sanctum
    | EnsureMobileStudent
    |
    */

    public function changePassword(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Authenticated Student
        |--------------------------------------------------------------------------
        */

        $student =
            $request->user();


        if (
            !(
                $student instanceof User
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
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'current_password' => [
                        'required',
                        'string',
                        'min:8',
                        'max:255',
                    ],

                    'password' => [
                        'required',
                        'string',
                        'min:8',
                        'max:255',
                        'confirmed',
                    ],
                ],
                [
                    'current_password.required' =>
                        'Please enter your current password.',

                    'current_password.min' =>
                        'The current password must contain at least 8 characters.',

                    'password.required' =>
                        'Please enter your new password.',

                    'password.min' =>
                        'Your new password must contain at least 8 characters.',

                    'password.confirmed' =>
                        'The new password and confirmation password do not match.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Verify Current Password
        |--------------------------------------------------------------------------
        */

        if (
            !Hash::check(
                $validated[
                    'current_password'
                ],
                $student->password
            )
        ) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'The current password is incorrect.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Reusing Current Password
        |--------------------------------------------------------------------------
        */

        if (
            Hash::check(
                $validated[
                    'password'
                ],
                $student->password
            )
        ) {
            throw ValidationException::withMessages([
                'password' => [
                    'Your new password must be different from your current password.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        |
        | Hash explicitly before saving.
        |
        */

        $student
            ->forceFill([
                'password' =>
                    Hash::make(
                        $validated[
                            'password'
                        ]
                    ),
            ])
            ->save();


        /*
        |--------------------------------------------------------------------------
        | Revoke Other Mobile Sessions
        |--------------------------------------------------------------------------
        |
        | Keep the current device logged in.
        |
        | If the student was logged in on another phone, those older Sanctum
        | sessions will be revoked after the password changes.
        |
        */

        $currentToken =
            $student
                ->currentAccessToken();


        if (
            $currentToken
            &&
            isset(
                $currentToken->id
            )
        ) {
            $student
                ->tokens()
                ->where(
                    'id',
                    '!=',
                    $currentToken->id
                )
                ->delete();
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Your password has been updated successfully.',
        ]);
    }
}