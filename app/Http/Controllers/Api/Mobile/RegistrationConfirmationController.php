<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\RotcStudentProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegistrationConfirmationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration Confirmation
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/registration-confirmation
    |
    | Used by:
    |
    | app/Registration/RegistrationConfirmation.js
    |
    |
    | Flow:
    |
    | Student
    |    ↓
    | General Registration
    |    ↓
    | Confirmed?
    |    │
    |    ├── NO
    |    │    ↓
    |    │ RegistrationWaitEdit
    |    │
    |    └── YES
    |         ↓
    |      Component?
    |         │
    |         ├── CWTS / LTS
    |         │      ↓
    |         │   Homepage
    |         │
    |         └── ROTC
    |                ↓
    |          ROTC Enrollment
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
                    'This account is not authorized to access student registration.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Load University
        |--------------------------------------------------------------------------
        */

        $user->load(
            'university'
        );


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
        | General Registration Required
        |--------------------------------------------------------------------------
        */

        if (
            !$this->hasGeneralRegistration(
                $user
            )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Complete your general NSTP registration first.',

                'next_screen' =>
                    '/Registration/GeneralRegistrationScreen',
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | Registration Status
        |--------------------------------------------------------------------------
        */

        $status =
            $this->registrationStatus(
                $user
            );


        $isConfirmed =
            $this->isConfirmedStatus(
                $status
            );


        /*
        |--------------------------------------------------------------------------
        | Component
        |--------------------------------------------------------------------------
        */

        $component =
            strtoupper(
                trim(
                    (string)
                    $user->component
                )
            );


        $isRotc =
            $component ===
            'ROTC';


        /*
        |--------------------------------------------------------------------------
        | ROTC Profile
        |--------------------------------------------------------------------------
        |
        | This record only exists for ROTC students.
        |
        */

        $rotcProfile =
            null;


        if ($isRotc) {
            $rotcProfile =
                RotcStudentProfile::query()
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->with([
                        'msRecords',
                    ])
                    ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | ROTC Enrollment State
        |--------------------------------------------------------------------------
        */

        $rotcEnrollmentStarted =
            $rotcProfile !==
            null;


        $rotcEnrollmentCompleted =
            $rotcProfile
                ? $rotcProfile
                    ->isCompleted()
                : false;


        /*
        |--------------------------------------------------------------------------
        | Student NSTP ID
        |--------------------------------------------------------------------------
        |
        | ROTC:
        |
        | rotc_student_profiles.nstp_id_no
        |
        | CWTS / LTS:
        |
        | Supports possible student ID columns if you already have one.
        |
        */

        $studentId =
            $rotcProfile
                ?->nstp_id_no
            ??
            $user->getAttribute(
                'student_id'
            )
            ??
            $user->getAttribute(
                'student_number'
            )
            ??
            $user->getAttribute(
                'id_no'
            );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,


            /*
            |--------------------------------------------------------------------------
            | Registration
            |--------------------------------------------------------------------------
            */

            'registration' => [
                'id' =>
                    $user->id,

                'subject' =>
                    $user->subject,

                'component' =>
                    $component,

                'term' =>
                    $user->term,

                'registration_status' =>
                    $status,

                'is_confirmed' =>
                    $isConfirmed,


                /*
                |--------------------------------------------------------------------------
                | Student ID
                |--------------------------------------------------------------------------
                */

                'student_id' =>
                    $studentId,

                'student_number' =>
                    $studentId,

                'id_no' =>
                    $studentId,


                /*
                |--------------------------------------------------------------------------
                | Student Name
                |--------------------------------------------------------------------------
                */

                'surname' =>
                    $user->surname,

                'first_name' =>
                    $user->first_name,

                'middle_name' =>
                    $user->middle_name,

                'full_name' =>
                    $user->full_name,


                /*
                |--------------------------------------------------------------------------
                | Academic
                |--------------------------------------------------------------------------
                */

                'course' =>
                    $user->course,

                'year_level' =>
                    $user->year_level,

                'section' =>
                    $user->section,


                /*
                |--------------------------------------------------------------------------
                | Confirmation
                |--------------------------------------------------------------------------
                */

                'confirmed_at' =>
                    $this->dateTimeValue(
                        $user->getAttribute(
                            'confirmed_at'
                        )
                    ),

                'registration_completed_at' =>
                    $this->dateTimeValue(
                        $user->getAttribute(
                            'registration_completed_at'
                        )
                    ),

                'registration_submitted_at' =>
                    $this->dateTimeValue(
                        $user->getAttribute(
                            'registration_submitted_at'
                        )
                    ),


                /*
                |--------------------------------------------------------------------------
                | Director
                |--------------------------------------------------------------------------
                */

                'confirmed_by_name' =>
                    $this->confirmedByName(
                        $user
                    ),

                'confirmed_by_credentials' =>
                    $this->confirmedByCredentials(
                        $user
                    ),

                'confirmed_by' => [
                    'name' =>
                        $this->confirmedByName(
                            $user
                        ),

                    'credentials' =>
                        $this->confirmedByCredentials(
                            $user
                        ),
                ],
            ],


            /*
            |--------------------------------------------------------------------------
            | Confirmation State
            |--------------------------------------------------------------------------
            */

            'confirmation' => [
                'status' =>
                    $status,

                'is_confirmed' =>
                    $isConfirmed,

                'component' =>
                    $component,

                'is_rotc' =>
                    $isRotc,

                /*
                |--------------------------------------------------------------------------
                | Type
                |--------------------------------------------------------------------------
                |
                | waiting
                | standard
                | rotc
                |
                */

                'type' =>
                    $this->confirmationType(
                        status: $status,
                        component: $component
                    ),

                /*
                |--------------------------------------------------------------------------
                | Access
                |--------------------------------------------------------------------------
                */

                'can_access_homepage' =>
                    $this->canAccessHomepage(
                        status: $status,
                        component: $component,
                        rotcCompleted:
                            $rotcEnrollmentCompleted
                    ),

                'requires_rotc_enrollment' =>
                    $isConfirmed
                    &&
                    $isRotc
                    &&
                    !$rotcEnrollmentCompleted,
            ],


            /*
            |--------------------------------------------------------------------------
            | ROTC
            |--------------------------------------------------------------------------
            */

            'rotc' =>
                $isRotc
                    ? [
                        'required' =>
                            $isConfirmed,

                        'started' =>
                            $rotcEnrollmentStarted,

                        'completed' =>
                            $rotcEnrollmentCompleted,

                        'profile' =>
                            $rotcProfile
                                ? $this->rotcResponse(
                                    $rotcProfile
                                )
                                : null,
                    ]
                    : null,


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            |
            | NstpRegistrationHeader.js can use this directly.
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

                'components' =>
                    $university->components
                    ??
                    [],

                'logo' =>
                    $university->logo,

                'logo_url' =>
                    $this->universityLogoUrl(
                        request: $request,
                        logo: $university->logo
                    ),
            ],


            /*
            |--------------------------------------------------------------------------
            | Next Screen
            |--------------------------------------------------------------------------
            */

            'next_screen' =>
                $this->nextScreen(
                    status: $status,
                    component: $component,
                    rotcStarted:
                        $rotcEnrollmentStarted,
                    rotcCompleted:
                        $rotcEnrollmentCompleted
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Has General Registration
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
    | Registration Status
    |--------------------------------------------------------------------------
    */

    private function registrationStatus(
        User $user
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Stored Registration Status
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
        | Confirmation Timestamp
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
        | Registration Completion Timestamp
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
        | Draft
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
    | Confirmed?
    |--------------------------------------------------------------------------
    */

    private function isConfirmedStatus(
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


    /*
    |--------------------------------------------------------------------------
    | Confirmation Type
    |--------------------------------------------------------------------------
    */

    private function confirmationType(
        string $status,
        string $component
    ): string {
        if (
            !$this->isConfirmedStatus(
                $status
            )
        ) {
            return
                'waiting';
        }


        if (
            strtoupper(
                trim(
                    $component
                )
            )
            ===
            'ROTC'
        ) {
            return
                'rotc';
        }


        return
            'standard';
    }


    /*
    |--------------------------------------------------------------------------
    | Can Access Homepage?
    |--------------------------------------------------------------------------
    |
    | CWTS / LTS:
    |
    | General registration confirmation is enough.
    |
    | ROTC:
    |
    | General confirmation is NOT enough.
    | ROTC enrollment must also be completed.
    |
    */

    private function canAccessHomepage(
        string $status,
        string $component,
        bool $rotcCompleted
    ): bool {
        if (
            !$this->isConfirmedStatus(
                $status
            )
        ) {
            return false;
        }


        if (
            strtoupper(
                trim(
                    $component
                )
            )
            !==
            'ROTC'
        ) {
            return true;
        }


        return
            $rotcCompleted;
    }


    /*
    |--------------------------------------------------------------------------
    | Next Screen
    |--------------------------------------------------------------------------
    */

    private function nextScreen(
        string $status,
        string $component,
        bool $rotcStarted,
        bool $rotcCompleted
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Not Confirmed Yet
        |--------------------------------------------------------------------------
        */

        if (
            !$this->isConfirmedStatus(
                $status
            )
        ) {
            return
                '/Registration/RegistrationWaitEdit';
        }


        /*
        |--------------------------------------------------------------------------
        | CWTS / LTS
        |--------------------------------------------------------------------------
        */

        if (
            strtoupper(
                trim(
                    $component
                )
            )
            !==
            'ROTC'
        ) {
            return
                '/(tabs)/HomepageScreen';
        }


        /*
        |--------------------------------------------------------------------------
        | ROTC Finished
        |--------------------------------------------------------------------------
        */

        if (
            $rotcCompleted
        ) {
            return
                '/Registration/ROTC/RotcConfirmEnrollment';
        }


        /*
        |--------------------------------------------------------------------------
        | ROTC Enrollment
        |--------------------------------------------------------------------------
        |
        | Whether draft or never started, the student returns to the ROTC
        | enrollment form.
        |
        */

        return
            '/Registration/ROTC/RotcEnrollment';
    }


    /*
    |--------------------------------------------------------------------------
    | Director Name
    |--------------------------------------------------------------------------
    |
    | These fields can be replaced later by a proper confirmed_by foreign
    | key when your NSTP Director approval system is implemented.
    |
    */

    private function confirmedByName(
        User $user
    ): string {
        $name =
            trim(
                (string) (
                    $user->getAttribute(
                        'confirmed_by_name'
                    )
                    ??
                    ''
                )
            );


        if (
            $name !==
            ''
        ) {
            return
                $name;
        }


        return
            'NSTP Director';
    }


    /*
    |--------------------------------------------------------------------------
    | Director Credentials
    |--------------------------------------------------------------------------
    */

    private function confirmedByCredentials(
        User $user
    ): ?string {
        $credentials =
            trim(
                (string) (
                    $user->getAttribute(
                        'confirmed_by_credentials'
                    )
                    ??
                    ''
                )
            );


        return
            $credentials !==
                ''
                ? $credentials
                : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Date / Time Value
    |--------------------------------------------------------------------------
    */

    private function dateTimeValue(
        mixed $value
    ): ?string {
        if (!$value) {
            return null;
        }


        try {
            return Carbon::parse(
                $value
            )
                ->toIso8601String();

        } catch (\Throwable) {
            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Profile Response
    |--------------------------------------------------------------------------
    */

    private function rotcResponse(
        RotcStudentProfile $profile
    ): array {
        return [
            'id' =>
                $profile->id,

            'nstp_id_no' =>
                $profile->nstp_id_no,

            'ms_level' =>
                $profile->ms_level,

            'status' =>
                $profile->status,

            'submitted_at' =>
                $profile->submitted_at
                    ?->toIso8601String(),

            'completed_at' =>
                $profile->completed_at
                    ?->toIso8601String(),

            'is_completed' =>
                $profile->isCompleted(),

            /*
            |--------------------------------------------------------------------------
            | MS Records
            |--------------------------------------------------------------------------
            */

            'ms_records' =>
                $profile
                    ->msRecords
                    ->map(
                        fn ($record) => [
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
                        ]
                    )
                    ->values()
                    ->all(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University Logo URL
    |--------------------------------------------------------------------------
    */

    private function universityLogoUrl(
        Request $request,
        ?string $logo
    ): ?string {
        if (!$logo) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Absolute URL
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                $logo,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return
                $logo;
        }


        /*
        |--------------------------------------------------------------------------
        | Public University Logo API
        |--------------------------------------------------------------------------
        |
        | This uses your existing public route:
        |
        | /api/mobile/universities/{university}/logo
        |
        */

        $universityId =
            $request
                ->user()
                ?->university_id;


        if ($universityId) {
            return
                $request
                    ->getSchemeAndHttpHost()
                .
                '/api/mobile/universities/'
                .
                $universityId
                .
                '/logo';
        }


        /*
        |--------------------------------------------------------------------------
        | Public Storage Fallback
        |--------------------------------------------------------------------------
        */

        $storageUrl =
            Storage::disk(
                'public'
            )
                ->url(
                    $logo
                );


        return
            $request
                ->getSchemeAndHttpHost()
            .
            $storageUrl;
    }
}