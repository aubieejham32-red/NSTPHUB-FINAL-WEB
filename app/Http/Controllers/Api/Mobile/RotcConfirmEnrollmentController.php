<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\RotcStudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RotcConfirmEnrollmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ROTC Enrollment Confirmation
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/rotc-enrollment/confirmation
    |
    | Used by:
    |
    | app/Registration/ROTC/RotcConfirmEnrollment.js
    |
    |
    | This endpoint is available only when:
    |
    | 1. The account is authenticated.
    | 2. The account belongs to a student.
    | 3. The student's NSTP component is ROTC.
    | 4. A ROTC profile exists.
    | 5. ROTC enrollment has been completed.
    |
    */

    public function show(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Authenticated User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        if (!$user) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Authentication is required.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Student Role
        |--------------------------------------------------------------------------
        */

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,

                'message' =>
                    'This account is not authorized to access ROTC enrollment.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Fresh Student Record
        |--------------------------------------------------------------------------
        */

        $user =
            User::query()
                ->whereKey(
                    $user->id
                )
                ->with(
                    'university'
                )
                ->first();


        if (!$user) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Student account could not be found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | University Required
        |--------------------------------------------------------------------------
        */

        $university =
            $user->university;


        if (!$university) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Your student account is not connected to a university.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | ROTC Component Required
        |--------------------------------------------------------------------------
        */

        $component =
            strtoupper(
                trim(
                    (string)
                    $user->component
                )
            );


        if (
            $component !==
            'ROTC'
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'This confirmation page is available only to ROTC students.',

                'component' =>
                    $component,

                'next_screen' =>
                    '/Registration/RegistrationConfirmation',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | General Registration Must Be Confirmed
        |--------------------------------------------------------------------------
        */

        $generalRegistrationStatus =
            $this->generalRegistrationStatus(
                $user
            );


        if (
            !$this->isGeneralRegistrationConfirmed(
                $generalRegistrationStatus
            )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Your general NSTP registration has not yet been confirmed.',

                'registration_status' =>
                    $generalRegistrationStatus,

                'next_screen' =>
                    '/Registration/RegistrationWaitEdit',
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | ROTC Student Profile
        |--------------------------------------------------------------------------
        */

        $profile =
            RotcStudentProfile::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->with([
                    'msRecords',
                ])
                ->first();


        /*
        |--------------------------------------------------------------------------
        | ROTC Profile Does Not Exist
        |--------------------------------------------------------------------------
        */

        if (!$profile) {
            return response()->json([
                'success' => false,

                'message' =>
                    'You have not started your ROTC enrollment yet.',

                'rotc_status' =>
                    null,

                'next_screen' =>
                    '/Registration/ROTC/RotcEnrollment',
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | ROTC Enrollment Not Completed
        |--------------------------------------------------------------------------
        |
        | RotcConfirmEnrollment must NEVER display a fake confirmed state.
        |
        | The database must contain:
        |
        | status = completed
        | completed_at != null
        |
        */

        if (!$profile->isCompleted()) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Your ROTC enrollment has not yet been completed.',

                'rotc_status' =>
                    $profile->status,

                'progress' => [
                    'completed' =>
                        false,

                    'status' =>
                        $profile->status,

                    'submitted_at' =>
                        $profile
                            ->submitted_at
                            ?->toIso8601String(),

                    'completed_at' =>
                        $profile
                            ->completed_at
                            ?->toIso8601String(),
                ],

                'next_screen' =>
                    '/Registration/ROTC/RotcEnrollment',
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | Cadet Name
        |--------------------------------------------------------------------------
        */

        $cadetName =
            $this->cadetName(
                $profile
            );


        /*
        |--------------------------------------------------------------------------
        | Confirmation Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'ROTC enrollment confirmed successfully.',


            /*
            |--------------------------------------------------------------------------
            | Confirmation
            |--------------------------------------------------------------------------
            */

            'confirmation' => [
                'confirmed' =>
                    true,

                'status' =>
                    'confirmed',

                'title' =>
                    'You\'re officially enrolled',

                'component' =>
                    'ROTC',

                'can_access_homepage' =>
                    true,
            ],


            /*
            |--------------------------------------------------------------------------
            | Cadet Information
            |--------------------------------------------------------------------------
            */

            'cadet' => [
                'user_id' =>
                    $user->id,

                'rotc_profile_id' =>
                    $profile->id,

                'name' =>
                    $cadetName,

                'last_name' =>
                    $profile->last_name,

                'first_name' =>
                    $profile->first_name,

                'middle_name' =>
                    $profile->middle_name,

                'name_extension' =>
                    $profile->name_extension,

                'nstp_id_no' =>
                    $profile->nstp_id_no,

                'ms_level' =>
                    $profile->ms_level,

                'term' =>
                    $user->term,

                'component' =>
                    'ROTC',

                'status' =>
                    'Confirmed',
            ],


            /*
            |--------------------------------------------------------------------------
            | General NSTP Registration
            |--------------------------------------------------------------------------
            */

            'general_registration' => [
                'subject' =>
                    $user->subject,

                'component' =>
                    $component,

                'term' =>
                    $user->term,

                'status' =>
                    $generalRegistrationStatus,

                'course' =>
                    $user->course,

                'year_level' =>
                    $user->year_level,

                'section' =>
                    $user->section,
            ],


            /*
            |--------------------------------------------------------------------------
            | ROTC Enrollment
            |--------------------------------------------------------------------------
            */

            'rotc_enrollment' => [
                'id' =>
                    $profile->id,

                'nstp_id_no' =>
                    $profile->nstp_id_no,

                'ms_level' =>
                    $profile->ms_level,

                'status' =>
                    $profile->status,

                'submitted_at' =>
                    $profile
                        ->submitted_at
                        ?->toIso8601String(),

                'completed_at' =>
                    $profile
                        ->completed_at
                        ?->toIso8601String(),

                'completed' =>
                    $profile->isCompleted(),

                'willing_advance_course' =>
                    $profile->willing_advance_course,
            ],


            /*
            |--------------------------------------------------------------------------
            | MS Records
            |--------------------------------------------------------------------------
            */

            'ms_records' =>
                $profile
                    ->msRecords
                    ->map(
                        function ($record) {
                            return [
                                'id' =>
                                    $record->id,

                                'record_order' =>
                                    $record->record_order,

                                'ms_level' =>
                                    $record->ms_level,

                                'semester' =>
                                    $record->semester,

                                'school_year' =>
                                    $record->school_year,

                                'grade' =>
                                    $record->grade,

                                'remarks' =>
                                    $record->remarks,
                            ];
                        }
                    )
                    ->values()
                    ->all(),


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            |
            | Used by:
            |
            | NstpRegistrationHeader.js
            |
            */

            'university' => [
                'id' =>
                    $university->id,

                'name' =>
                    $university->name,

                'acronym' =>
                    $university->acronym,

                'type' =>
                    $university->type,

                'campus_type' =>
                    $university->campus_type,

                'academic_year' =>
                    $university->academic_year,

                'semester' =>
                    $university->semester,

                'logo' =>
                    $university->logo,

                'logo_url' =>
                    $request
                        ->getSchemeAndHttpHost()
                    .
                    '/api/mobile/universities/'
                    .
                    $university->id
                    .
                    '/logo',
            ],


            /*
            |--------------------------------------------------------------------------
            | Navigation
            |--------------------------------------------------------------------------
            */

            'next_screen' =>
                '/(tabs)/HomepageScreen',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Cadet Name
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | Siega, Aubrey O.
    |
    */

    private function cadetName(
        RotcStudentProfile $profile
    ): string {
        $lastName =
            trim(
                (string)
                $profile->last_name
            );


        $firstName =
            trim(
                (string)
                $profile->first_name
            );


        $middleName =
            trim(
                (string)
                $profile->middle_name
            );


        $extension =
            trim(
                (string)
                $profile->name_extension
            );


        /*
        |--------------------------------------------------------------------------
        | Middle Initial
        |--------------------------------------------------------------------------
        */

        $middleInitial =
            '';


        if (
            $middleName !==
            ''
        ) {
            $middleInitial =
                strtoupper(
                    mb_substr(
                        $middleName,
                        0,
                        1
                    )
                )
                .
                '.';
        }


        /*
        |--------------------------------------------------------------------------
        | Given Name Portion
        |--------------------------------------------------------------------------
        */

        $givenName =
            collect([
                $firstName,
                $middleInitial,
                $extension,
            ])
                ->filter(
                    fn ($value) =>
                        trim(
                            (string)
                            $value
                        )
                        !==
                        ''
                )
                ->implode(
                    ' '
                );


        /*
        |--------------------------------------------------------------------------
        | Full Name
        |--------------------------------------------------------------------------
        */

        if (
            $lastName !==
            ''
            &&
            $givenName !==
            ''
        ) {
            return
                $lastName
                .
                ', '
                .
                $givenName;
        }


        return
            trim(
                $lastName
                .
                ' '
                .
                $givenName
            );
    }


    /*
    |--------------------------------------------------------------------------
    | General Registration Exists
    |--------------------------------------------------------------------------
    */

    private function hasGeneralRegistration(
        User $user
    ): bool {
        return
            filled(
                $user->subject
            )
            &&
            filled(
                $user->component
            )
            &&
            filled(
                $user->term
            )
            &&
            filled(
                $user->surname
            )
            &&
            filled(
                $user->first_name
            )
            &&
            filled(
                $user->course
            )
            &&
            filled(
                $user->year_level
            )
            &&
            filled(
                $user->section
            );
    }


    /*
    |--------------------------------------------------------------------------
    | General Registration Status
    |--------------------------------------------------------------------------
    */

    private function generalRegistrationStatus(
        User $user
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Stored Status
        |--------------------------------------------------------------------------
        */

        $storedStatus =
            strtolower(
                trim(
                    (string) (
                        $user->getAttribute(
                            'registration_status'
                        )
                        ??
                        ''
                    )
                )
            );


        if (
            in_array(
                $storedStatus,
                [
                    'not_started',
                    'draft',
                    'submitted',
                    'under_review',
                    'confirmed',
                    'completed',
                ],
                true
            )
        ) {
            return
                $storedStatus;
        }


        /*
        |--------------------------------------------------------------------------
        | Confirmed Timestamp
        |--------------------------------------------------------------------------
        */

        if (
            $user->getAttribute(
                'confirmed_at'
            )
            !==
            null
        ) {
            return
                'confirmed';
        }


        /*
        |--------------------------------------------------------------------------
        | Completed General Registration
        |--------------------------------------------------------------------------
        */

        if (
            $user->getAttribute(
                'registration_completed_at'
            )
            !==
            null
        ) {
            return
                'completed';
        }


        /*
        |--------------------------------------------------------------------------
        | Submitted Signature
        |--------------------------------------------------------------------------
        */

        if (
            filled(
                $user->signature_path
            )
            ||
            $user->getAttribute(
                'registration_submitted_at'
            )
            !==
            null
        ) {
            return
                'under_review';
        }


        /*
        |--------------------------------------------------------------------------
        | Draft General Registration
        |--------------------------------------------------------------------------
        */

        if (
            $this->hasGeneralRegistration(
                $user
            )
        ) {
            return
                'draft';
        }


        return
            'not_started';
    }


    /*
    |--------------------------------------------------------------------------
    | General Registration Confirmed?
    |--------------------------------------------------------------------------
    */

    private function isGeneralRegistrationConfirmed(
        string $status
    ): bool {
        return in_array(
            strtolower(
                trim(
                    $status
                )
            ),
            [
                'confirmed',
                'completed',
            ],
            true
        );
    }
}