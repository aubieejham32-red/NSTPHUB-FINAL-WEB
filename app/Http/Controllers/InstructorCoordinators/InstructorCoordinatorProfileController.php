<?php

namespace App\Http\Controllers\InstructorCoordinators;

use App\Http\Controllers\Controller;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\UniversityAdministrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class InstructorCoordinatorProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Profile
    |--------------------------------------------------------------------------
    |
    | GET:
    |
    | /instructor-coordinator/profile
    |
    */

    public function index(
        Request $request
    ): Response|RedirectResponse|JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Get Authenticated Staff
        |--------------------------------------------------------------------------
        */

        $staff =
            $this->getAuthenticatedStaff();


        /*
        |--------------------------------------------------------------------------
        | Not Authenticated
        |--------------------------------------------------------------------------
        */

        if (
            !$staff
        ) {

            return redirect()->route(
                'instructor-coordinator.access-code'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $staff['user'];


        /*
        |--------------------------------------------------------------------------
        | Load University
        |--------------------------------------------------------------------------
        */

        $user->loadMissing(
            'university'
        );


        /*
        |--------------------------------------------------------------------------
        | University Must Exist
        |--------------------------------------------------------------------------
        */

        if (
            !$user->university
        ) {

            $this->logoutStaffAccount(
                $staff['account_type']
            );


            $this->clearStaffSession(
                $request
            );


            return redirect()
                ->route(
                    'instructor-coordinator.access-code'
                )
                ->withErrors([
                    'access_code' =>
                        'The university connected to this account could not be found.',
                ]);
        }


        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'role' => $staff['role'],
                'account_type' => $staff['account_type'],
                'account' => $this->mobileProfilePayload($user, $staff),
                'university' => [
                    'id' => (int) $user->university->id,
                    'name' => $user->university->name,
                    'acronym' => $user->university->acronym,
                    'academic_year' => $user->university->academic_year,
                    'semester' => $user->university->semester,
                    'status' => $user->university->status,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Render Profile
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/Profile/InstructorCoordinatorProfile',
            [
                /*
                |--------------------------------------------------------------------------
                | User
                |--------------------------------------------------------------------------
                */

                'user' =>
                    $user,


                /*
                |--------------------------------------------------------------------------
                | Role
                |--------------------------------------------------------------------------
                */

                'role' =>
                    $staff['role'],


                /*
                |--------------------------------------------------------------------------
                | Account Type
                |--------------------------------------------------------------------------
                */

                'accountType' =>
                    $staff['account_type'],


                /*
                |--------------------------------------------------------------------------
                | University
                |--------------------------------------------------------------------------
                */

                'university' =>
                    $user->university,


                /*
                |--------------------------------------------------------------------------
                | Auth Data
                |--------------------------------------------------------------------------
                */

                'auth' => [

                    'user' =>
                        $user,

                    'university' =>
                        $user->university,

                    'role' =>
                        $staff['role'],

                    'account_type' =>
                        $staff['account_type'],

                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Profile Photo
    |--------------------------------------------------------------------------
    |
    | POST:
    |
    | /instructor-coordinator/profile/photo
    |
    */

    public function updatePhoto(
        Request $request
    ): RedirectResponse|JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Get Authenticated Staff
        |--------------------------------------------------------------------------
        */

        $staff =
            $this->getAuthenticatedStaff();


        /*
        |--------------------------------------------------------------------------
        | Not Authenticated
        |--------------------------------------------------------------------------
        */

        if (
            !$staff
        ) {

            return redirect()->route(
                'instructor-coordinator.access-code'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Profile Photo
        |--------------------------------------------------------------------------
        */

        $request->validate(
            [
                'profile_photo' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],
            ],
            [
                'profile_photo.required' =>
                    'Please select a profile photo.',

                'profile_photo.image' =>
                    'The selected file must be an image.',

                'profile_photo.mimes' =>
                    'The profile photo must be JPG, JPEG, PNG, or WEBP.',

                'profile_photo.max' =>
                    'The profile photo must not exceed 5 MB.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $staff['user'];


        /*
        |--------------------------------------------------------------------------
        | Determine Storage Folder
        |--------------------------------------------------------------------------
        */

        $folder = match ($staff['account_type']) {
            'instructor' => 'profile_photos/instructors',
            'coordinator' => 'profile_photos/coordinators',
            default => 'profile_photos/university-administrators',
        };


        /*
        |--------------------------------------------------------------------------
        | Existing Photo
        |--------------------------------------------------------------------------
        */

        $photoColumn = $staff['account_type'] === 'university_administrator'
            ? 'photo'
            : 'profile_photo';

        $oldPhoto = $user->{$photoColumn};


        /*
        |--------------------------------------------------------------------------
        | Store New Photo
        |--------------------------------------------------------------------------
        */

        $newPhoto =
            $request
                ->file(
                    'profile_photo'
                )
                ->store(
                    $folder,
                    'public'
                );


        /*
        |--------------------------------------------------------------------------
        | Save New Photo Path
        |--------------------------------------------------------------------------
        */

        $user->{$photoColumn} = $newPhoto;

        $user->save();


        /*
        |--------------------------------------------------------------------------
        | Delete Previous Profile Photo
        |--------------------------------------------------------------------------
        */

        $this->deleteOldProfilePhoto(
            $oldPhoto,
            $newPhoto
        );


        /*
        |--------------------------------------------------------------------------
        | Return To Profile
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {
            $user->refresh()->loadMissing('university');

            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully.',
                'account' => $this->mobileProfilePayload($user, $staff),
            ]);
        }

        return redirect()
            ->route(
                'instructor-coordinator.profile'
            )
            ->with(
                'success',
                'Profile photo updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Change Password Page
    |--------------------------------------------------------------------------
    |
    | GET:
    |
    | /instructor-coordinator/change-password
    |
    */

    public function editPassword(
        Request $request
    ): Response|RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Get Authenticated Staff
        |--------------------------------------------------------------------------
        */

        $staff =
            $this->getAuthenticatedStaff();


        /*
        |--------------------------------------------------------------------------
        | Not Authenticated
        |--------------------------------------------------------------------------
        */

        if (
            !$staff
        ) {

            return redirect()->route(
                'instructor-coordinator.access-code'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $staff['user'];


        /*
        |--------------------------------------------------------------------------
        | Load University
        |--------------------------------------------------------------------------
        */

        $user->loadMissing(
            'university'
        );


        /*
        |--------------------------------------------------------------------------
        | University Must Exist
        |--------------------------------------------------------------------------
        */

        if (
            !$user->university
        ) {

            $this->logoutStaffAccount(
                $staff['account_type']
            );


            $this->clearStaffSession(
                $request
            );


            return redirect()
                ->route(
                    'instructor-coordinator.access-code'
                )
                ->withErrors([
                    'access_code' =>
                        'The university connected to this account could not be found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Render Change Password Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/Profile/InstructorCoordinatorChangePassword',
            [
                /*
                |--------------------------------------------------------------------------
                | User
                |--------------------------------------------------------------------------
                */

                'user' =>
                    $user,


                /*
                |--------------------------------------------------------------------------
                | Role
                |--------------------------------------------------------------------------
                */

                'role' =>
                    $staff['role'],


                /*
                |--------------------------------------------------------------------------
                | Account Type
                |--------------------------------------------------------------------------
                */

                'accountType' =>
                    $staff['account_type'],


                /*
                |--------------------------------------------------------------------------
                | University
                |--------------------------------------------------------------------------
                */

                'university' =>
                    $user->university,


                /*
                |--------------------------------------------------------------------------
                | Auth Data
                |--------------------------------------------------------------------------
                */

                'auth' => [

                    'user' =>
                        $user,

                    'university' =>
                        $user->university,

                    'role' =>
                        $staff['role'],

                    'account_type' =>
                        $staff['account_type'],

                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    |
    | POST:
    |
    | /instructor-coordinator/change-password
    |
    |
    | IMPORTANT:
    |
    | The temporary password created by the University Administrator is
    | simply the Instructor / Coordinator's current password.
    |
    | It remains valid until the user changes it here.
    |
    */

    public function updatePassword(
        Request $request
    ): RedirectResponse|JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Get Authenticated Staff
        |--------------------------------------------------------------------------
        */

        $staff =
            $this->getAuthenticatedStaff();


        /*
        |--------------------------------------------------------------------------
        | Not Authenticated
        |--------------------------------------------------------------------------
        */

        if (
            !$staff
        ) {

            return redirect()->route(
                'instructor-coordinator.access-code'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $staff['user'];


        /*
        |--------------------------------------------------------------------------
        | Validate Password Fields
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'current_password' => [
                        'required',
                        'string',
                    ],

                    'password' => [
                        'required',
                        'string',
                        'confirmed',

                        Password::min(
                            8
                        ),
                    ],

                    'password_confirmation' => [
                        'required',
                        'string',
                    ],
                ],
                [
                    'current_password.required' =>
                        'Please enter your current password.',

                    'password.required' =>
                        'Please enter your new password.',

                    'password.min' =>
                        'The new password must contain at least 8 characters.',

                    'password.confirmed' =>
                        'The password confirmation does not match.',

                    'password_confirmation.required' =>
                        'Please confirm your new password.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Verify Current Password
        |--------------------------------------------------------------------------
        |
        | This accepts the original temporary password if the user has not
        | changed their password yet.
        |
        */

        if (
            !Hash::check(
                $validated['current_password'],
                $user->password
            )
        ) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The current password you entered is incorrect.',
                    'errors' => [
                        'current_password' => ['The current password you entered is incorrect.'],
                    ],
                ], 422);
            }

            return back()
                ->withErrors([
                    'current_password' =>
                        'The current password you entered is incorrect.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | New Password Must Be Different
        |--------------------------------------------------------------------------
        */

        if (
            Hash::check(
                $validated['password'],
                $user->password
            )
        ) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your new password must be different from your current password.',
                    'errors' => [
                        'password' => ['Your new password must be different from your current password.'],
                    ],
                ], 422);
            }

            return back()
                ->withErrors([
                    'password' =>
                        'Your new password must be different from your current password.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Save New Password
        |--------------------------------------------------------------------------
        |
        | Instructor and Coordinator models use:
        |
        | 'password' => 'hashed'
        |
        | so Laravel automatically hashes this value.
        |
        */

        $user->password =
            $validated['password'];


        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Refresh Authenticated User
        |--------------------------------------------------------------------------
        */

        if (
            $staff['account_type'] ===
            'instructor'
        ) {

            Auth::guard(
                'instructor'
            )->setUser(
                $user->fresh()
            );

        } else {

            Auth::guard(
                'coordinator'
            )->setUser(
                $user->fresh()
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Keep Staff Session Information
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put([
                'staff_account_type' =>
                    $staff['account_type'],

                'staff_role' =>
                    $staff['role'],

                'staff_id' =>
                    $user->id,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Redirect To Profile
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'instructor-coordinator.profile'
            )
            ->with(
                'success',
                'Password changed successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Authenticated Staff
    |--------------------------------------------------------------------------
    |
    | Returns:
    |
    | [
    |     'user'         => Instructor|Coordinator,
    |     'role'         => string,
    |     'account_type' => string,
    | ]
    |
    */

    private function getAuthenticatedStaff():
        ?array
    {
        $apiUser = request()->user();

        if ($apiUser instanceof UniversityAdministrator) {
            return [
                'user' => $apiUser,
                'role' => 'university-admin',
                'account_type' => 'university_administrator',
            ];
        }

        if ($apiUser instanceof Instructor && $this->accountIsActive($apiUser->status) && $apiUser->isInstructor()) {
            return [
                'user' => $apiUser,
                'role' => 'instructor',
                'account_type' => 'instructor',
            ];
        }

        if ($apiUser instanceof Coordinator && $this->accountIsActive($apiUser->status)) {
            $role = $this->getCoordinatorRole($apiUser);

            if ($role) {
                return [
                    'user' => $apiUser,
                    'role' => $role,
                    'account_type' => 'coordinator',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'instructor'
            )->check()
        ) {

            /** @var Instructor|null $instructor */

            $instructor =
                Auth::guard(
                    'instructor'
                )->user();


            /*
            |--------------------------------------------------------------------------
            | Instructor Must Exist
            |--------------------------------------------------------------------------
            */

            if (
                !$instructor
            ) {

                return null;
            }


            /*
            |--------------------------------------------------------------------------
            | Instructor Must Be Active
            |--------------------------------------------------------------------------
            */

            if (
                !$this->accountIsActive(
                    $instructor->status
                )
            ) {

                Auth::guard(
                    'instructor'
                )->logout();


                return null;
            }


            /*
            |--------------------------------------------------------------------------
            | Instructor Must Have Instructor Role
            |--------------------------------------------------------------------------
            */

            if (
                !$instructor->isInstructor()
            ) {

                Auth::guard(
                    'instructor'
                )->logout();


                return null;
            }


            /*
            |--------------------------------------------------------------------------
            | Return Instructor
            |--------------------------------------------------------------------------
            */

            return [

                'user' =>
                    $instructor,

                'role' =>
                    'instructor',

                'account_type' =>
                    'instructor',

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'coordinator'
            )->check()
        ) {

            /** @var Coordinator|null $coordinator */

            $coordinator =
                Auth::guard(
                    'coordinator'
                )->user();


            /*
            |--------------------------------------------------------------------------
            | Coordinator Must Exist
            |--------------------------------------------------------------------------
            */

            if (
                !$coordinator
            ) {

                return null;
            }


            /*
            |--------------------------------------------------------------------------
            | Coordinator Must Be Active
            |--------------------------------------------------------------------------
            */

            if (
                !$this->accountIsActive(
                    $coordinator->status
                )
            ) {

                Auth::guard(
                    'coordinator'
                )->logout();


                return null;
            }


            /*
            |--------------------------------------------------------------------------
            | Resolve Coordinator Role
            |--------------------------------------------------------------------------
            */

            $role =
                $this->getCoordinatorRole(
                    $coordinator
                );


            /*
            |--------------------------------------------------------------------------
            | Coordinator Must Have Valid Role
            |--------------------------------------------------------------------------
            */

            if (
                !$role
            ) {

                Auth::guard(
                    'coordinator'
                )->logout();


                return null;
            }


            /*
            |--------------------------------------------------------------------------
            | Return Coordinator
            |--------------------------------------------------------------------------
            */

            return [

                'user' =>
                    $coordinator,

                'role' =>
                    $role,

                'account_type' =>
                    'coordinator',

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Not Authenticated
        |--------------------------------------------------------------------------
        */

        return null;
    }


    private function mobileProfilePayload(object $user, array $staff): array
    {
        $photo = $staff['account_type'] === 'university_administrator'
            ? ($user->photo ?? null)
            : ($user->profile_photo ?? null);

        $name = $staff['account_type'] === 'university_administrator'
            ? trim(implode(' ', array_filter([
                $user->first_name ?? null,
                $user->middle_name ?? null,
                $user->last_name ?? null,
            ])))
            : ($user->full_name ?? $user->username ?? $user->email);

        return [
            'id' => (int) $user->getKey(),
            'name' => $name,
            'full_name' => $name,
            'first_name' => $user->first_name ?? null,
            'middle_name' => $user->middle_name ?? null,
            'last_name' => $user->last_name ?? null,
            'email' => $user->email ?? null,
            'username' => $user->username ?? null,
            'phone' => $user->phone ?? $user->phone_number ?? null,
            'phone_number' => $user->phone_number ?? $user->phone ?? null,
            'component' => $user->component ?? null,
            'status' => $user->status ?? 'ACTIVE',
            'role' => $staff['role'],
            'account_type' => $staff['account_type'],
            'profile_photo' => $photo,
            'photo' => $photo,
            'profile_photo_url' => $photo ? Storage::disk('public')->url($photo) : null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Coordinator Role
    |--------------------------------------------------------------------------
    */

    private function getCoordinatorRole(
        Coordinator $coordinator
    ): ?string {

        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        if (
            $coordinator
                ->isAttendanceCoordinator()
        ) {

            return
                'coordinator-attendance';
        }


        /*
        |--------------------------------------------------------------------------
        | Announcement
        |--------------------------------------------------------------------------
        */

        if (
            $coordinator
                ->isAnnouncementCoordinator()
        ) {

            return
                'coordinator-announcement';
        }


        /*
        |--------------------------------------------------------------------------
        | Schedule
        |--------------------------------------------------------------------------
        */

        if (
            $coordinator
                ->isScheduleCoordinator()
        ) {

            return
                'coordinator-schedule';
        }


        /*
        |--------------------------------------------------------------------------
        | Invalid Role
        |--------------------------------------------------------------------------
        */

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Account Active Check
    |--------------------------------------------------------------------------
    */

    private function accountIsActive(
        mixed $status
    ): bool {

        return strtolower(
            trim(
                (string) $status
            )
        ) === 'active';
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Old Profile Photo
    |--------------------------------------------------------------------------
    */

    private function deleteOldProfilePhoto(
        ?string $oldPhoto,
        string $newPhoto
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Nothing To Delete
        |--------------------------------------------------------------------------
        */

        if (
            !$oldPhoto ||
            $oldPhoto ===
                $newPhoto
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | External URL
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $oldPhoto,
                'http://'
            ) ||
            str_starts_with(
                $oldPhoto,
                'https://'
            )
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Path
        |--------------------------------------------------------------------------
        */

        $path =
            $oldPhoto;


        /*
        |--------------------------------------------------------------------------
        | Remove /storage/
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $path,
                '/storage/'
            )
        ) {

            $path =
                substr(
                    $path,
                    strlen(
                        '/storage/'
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Ignore Other Absolute Public Paths
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $path,
                '/'
            )
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Delete Stored File
        |--------------------------------------------------------------------------
        */

        if (
            Storage::disk(
                'public'
            )->exists(
                $path
            )
        ) {

            Storage::disk(
                'public'
            )->delete(
                $path
            );

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Logout Current Staff Account
    |--------------------------------------------------------------------------
    */

    private function logoutStaffAccount(
        string $accountType
    ): void {

        if (
            $accountType ===
            'instructor'
        ) {

            Auth::guard(
                'instructor'
            )->logout();


            return;
        }


        if (
            $accountType ===
            'coordinator'
        ) {

            Auth::guard(
                'coordinator'
            )->logout();

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Staff Session
    |--------------------------------------------------------------------------
    */

    private function clearStaffSession(
        Request $request
    ): void {

        $request
            ->session()
            ->forget([
                'staff_access_verified',

                'staff_university_id',

                'staff_university_name',

                'staff_university_acronym',

                'staff_account_type',

                'staff_role',

                'staff_id',
            ]);
    }
}