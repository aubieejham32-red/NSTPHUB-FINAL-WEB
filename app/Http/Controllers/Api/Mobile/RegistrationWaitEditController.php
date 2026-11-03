<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegistrationWaitEditController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Registration Wait / Edit Information
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/registration-wait-edit
    |
    | Used by:
    |
    | app/Registration/RegistrationWaitEdit.js
    |
    */

    public function show(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Authenticated Student
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        if (!$user) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Authentication is required.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Student Authorization
        |--------------------------------------------------------------------------
        */

        if (!$user->isStudent()) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'This account is not authorized to access NSTP student registration.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $user->load(
            'university'
        );


        $university =
            $user->university;


        if (!$university) {
            return response()->json([
                'success' =>
                    false,

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
                'success' =>
                    false,

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


        /*
        |--------------------------------------------------------------------------
        | Available Components
        |--------------------------------------------------------------------------
        */

        $availableComponents =
            $this->availableComponents(
                $user
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

            'registration' =>
                $this->registrationResponse(
                    $user
                ),


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | This now includes:
            |
            | logo
            | logo_url
            |
            | NstpRegistrationHeader.js uses university.logo_url.
            |
            */

            'university' =>
                $this->universityResponse(
                    request: $request,

                    university:
                        $university,

                    availableComponents:
                        $availableComponents
                ),


            /*
            |--------------------------------------------------------------------------
            | Wait / Edit State
            |--------------------------------------------------------------------------
            */

            'wait_edit' => [
                'status' =>
                    $status,

                'can_edit_component' =>
                    !$this->isConfirmedStatus(
                        $status
                    ),

                'available_components' =>
                    $availableComponents,

                'is_submitted' =>
                    $this->isSubmitted(
                        $user
                    ),

                'is_under_review' =>
                    $status ===
                    'under_review',

                'is_confirmed' =>
                    $this->isConfirmedStatus(
                        $status
                    ),
            ],


            /*
            |--------------------------------------------------------------------------
            | Next Screen
            |--------------------------------------------------------------------------
            */

            'next_screen' =>
                $this->nextScreen(
                    user:
                        $user,

                    status:
                        $status
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Component While Waiting
    |--------------------------------------------------------------------------
    |
    | PATCH
    |
    | /api/mobile/registration-wait-edit/component
    |
    | Request:
    |
    | {
    |     "component": "ROTC"
    | }
    |
    */

    public function updateComponent(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Authenticated Student
        |--------------------------------------------------------------------------
        */

        $authenticatedUser =
            $request->user();


        if (!$authenticatedUser) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Authentication is required.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Student Authorization
        |--------------------------------------------------------------------------
        */

        if (
            !$authenticatedUser
                ->isStudent()
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Only student accounts may update NSTP registration.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Component
        |--------------------------------------------------------------------------
        */

        $requestedComponent =
            strtoupper(
                trim(
                    (string)
                    $request->input(
                        'component'
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        return DB::transaction(
            function () use (
                $request,
                $authenticatedUser,
                $requestedComponent
            ): JsonResponse {

                /*
                |--------------------------------------------------------------------------
                | Lock Student Row
                |--------------------------------------------------------------------------
                */

                $user =
                    User::query()
                        ->whereKey(
                            $authenticatedUser->id
                        )
                        ->lockForUpdate()
                        ->first();


                if (!$user) {
                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Student account could not be found.',
                    ], 404);
                }


                /*
                |--------------------------------------------------------------------------
                | Student Authorization
                |--------------------------------------------------------------------------
                */

                if (!$user->isStudent()) {
                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'This account is not authorized to modify NSTP registration.',
                    ], 403);
                }


                /*
                |--------------------------------------------------------------------------
                | University
                |--------------------------------------------------------------------------
                */

                $user->load(
                    'university'
                );


                $university =
                    $user->university;


                if (!$university) {
                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Your student account is not connected to a university.',
                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Registration Required
                |--------------------------------------------------------------------------
                */

                if (
                    !$this->hasGeneralRegistration(
                        $user
                    )
                ) {
                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Complete your general registration before changing your NSTP component.',
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


                /*
                |--------------------------------------------------------------------------
                | Confirmed Registrations Cannot Be Edited
                |--------------------------------------------------------------------------
                */

                if (
                    $this->isConfirmedStatus(
                        $status
                    )
                ) {
                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Your registration has already been confirmed. The NSTP component can no longer be changed.',
                    ], 409);
                }


                /*
                |--------------------------------------------------------------------------
                | Available Components
                |--------------------------------------------------------------------------
                */

                $availableComponents =
                    $this->availableComponents(
                        $user
                    );


                /*
                |--------------------------------------------------------------------------
                | Validation
                |--------------------------------------------------------------------------
                */

                $validator =
                    Validator::make(
                        [
                            'component' =>
                                $requestedComponent,
                        ],
                        [
                            'component' => [
                                'required',
                                'string',

                                Rule::in(
                                    $availableComponents
                                ),
                            ],
                        ],
                        [
                            'component.required' =>
                                'Please select an NSTP component.',

                            'component.in' =>
                                'The selected NSTP component is not offered by your university.',
                        ]
                    );


                if (
                    $validator->fails()
                ) {
                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Unable to change NSTP component.',

                        'errors' =>
                            $validator->errors(),
                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Valid Component
                |--------------------------------------------------------------------------
                */

                $component =
                    $validator
                        ->validated()[
                            'component'
                        ];


                /*
                |--------------------------------------------------------------------------
                | No Change
                |--------------------------------------------------------------------------
                */

                if (
                    strtoupper(
                        trim(
                            (string)
                            $user->component
                        )
                    )
                    ===
                    $component
                ) {
                    return response()->json([
                        'success' =>
                            true,

                        'message' =>
                            'Your NSTP component is already set to '
                            .
                            $component
                            .
                            '.',


                        /*
                        |--------------------------------------------------------------------------
                        | Registration
                        |--------------------------------------------------------------------------
                        */

                        'registration' =>
                            $this->registrationResponse(
                                $user
                            ),


                        /*
                        |--------------------------------------------------------------------------
                        | University
                        |--------------------------------------------------------------------------
                        */

                        'university' =>
                            $this->universityResponse(
                                request:
                                    $request,

                                university:
                                    $university,

                                availableComponents:
                                    $availableComponents
                            ),


                        /*
                        |--------------------------------------------------------------------------
                        | Wait/Edit
                        |--------------------------------------------------------------------------
                        */

                        'wait_edit' => [
                            'status' =>
                                $status,

                            'can_edit_component' =>
                                true,

                            'available_components' =>
                                $availableComponents,
                        ],


                        /*
                        |--------------------------------------------------------------------------
                        | Next Screen
                        |--------------------------------------------------------------------------
                        */

                        'next_screen' =>
                            $this->nextScreen(
                                user:
                                    $user,

                                status:
                                    $status
                            ),
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Save Component
                |--------------------------------------------------------------------------
                */

                $previousComponent =
                    $user->component;


                $user->component =
                    $component;


                $user->save();


                /*
                |--------------------------------------------------------------------------
                | Refresh
                |--------------------------------------------------------------------------
                */

                $user->refresh();


                $user->load(
                    'university'
                );


                $university =
                    $user->university;


                /*
                |--------------------------------------------------------------------------
                | Updated Status
                |--------------------------------------------------------------------------
                */

                $updatedStatus =
                    $this->registrationStatus(
                        $user
                    );


                /*
                |--------------------------------------------------------------------------
                | Updated Components
                |--------------------------------------------------------------------------
                */

                $updatedAvailableComponents =
                    $this->availableComponents(
                        $user
                    );


                /*
                |--------------------------------------------------------------------------
                | Response
                |--------------------------------------------------------------------------
                */

                return response()->json([
                    'success' =>
                        true,

                    'message' =>
                        'Your NSTP component has been changed successfully.',


                    /*
                    |--------------------------------------------------------------------------
                    | Change
                    |--------------------------------------------------------------------------
                    */

                    'change' => [
                        'previous_component' =>
                            $previousComponent,

                        'new_component' =>
                            $component,
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Registration
                    |--------------------------------------------------------------------------
                    */

                    'registration' =>
                        $this->registrationResponse(
                            $user
                        ),


                    /*
                    |--------------------------------------------------------------------------
                    | University
                    |--------------------------------------------------------------------------
                    |
                    | logo_url is also returned after editing.
                    |
                    */

                    'university' =>
                        $this->universityResponse(
                            request:
                                $request,

                            university:
                                $university,

                            availableComponents:
                                $updatedAvailableComponents
                        ),


                    /*
                    |--------------------------------------------------------------------------
                    | Wait / Edit
                    |--------------------------------------------------------------------------
                    */

                    'wait_edit' => [
                        'status' =>
                            $updatedStatus,

                        'can_edit_component' =>
                            !$this->isConfirmedStatus(
                                $updatedStatus
                            ),

                        'available_components' =>
                            $updatedAvailableComponents,

                        'is_submitted' =>
                            $this->isSubmitted(
                                $user
                            ),

                        'is_under_review' =>
                            $updatedStatus ===
                            'under_review',

                        'is_confirmed' =>
                            $this->isConfirmedStatus(
                                $updatedStatus
                            ),
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Next Screen
                    |--------------------------------------------------------------------------
                    */

                    'next_screen' =>
                        $this->nextScreen(
                            user:
                                $user,

                            status:
                                $updatedStatus
                        ),
                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | University Response
    |--------------------------------------------------------------------------
    |
    | Centralized university information for React Native.
    |
    | This guarantees that RegistrationWaitEdit.js receives:
    |
    | university.logo
    | university.logo_url
    |
    */

    private function universityResponse(
        Request $request,
        University $university,
        array $availableComponents
    ): array {
        return [
            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            'id' =>
                $university->id,

            'name' =>
                $university->name,

            'acronym' =>
                $university->acronym,


            /*
            |--------------------------------------------------------------------------
            | Logo
            |--------------------------------------------------------------------------
            */

            'logo' =>
                $university->logo,

            'logo_url' =>
                $this->universityLogoUrl(
                    request:
                        $request,

                    university:
                        $university
                ),


            /*
            |--------------------------------------------------------------------------
            | Academic Information
            |--------------------------------------------------------------------------
            */

            'academic_year' =>
                $university->academic_year,

            'semester' =>
                $university->semester,


            /*
            |--------------------------------------------------------------------------
            | NSTP Components
            |--------------------------------------------------------------------------
            */

            'components' =>
                $availableComponents,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University Logo URL
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | http://192.168.254.117:8000
    | /api/mobile/universities/11/logo
    |
    | We intentionally use the current request's host instead of APP_URL.
    |
    | This is important during mobile development because APP_URL may be:
    |
    | http://localhost
    |
    | which the physical phone cannot access.
    |
    */

    private function universityLogoUrl(
        Request $request,
        University $university
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | University Has No Logo
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $university->logo
            )
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Already An External URL
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                (string)
                $university->logo,

                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return
                (string)
                $university->logo;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile Laravel Logo Endpoint
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | http://192.168.254.117:8000/api/mobile/universities/11/logo
        |
        */

        return
            rtrim(
                $request
                    ->getSchemeAndHttpHost(),

                '/'
            )
            .
            '/api/mobile/universities/'
            .
            $university->id
            .
            '/logo';
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
    | Submitted
    |--------------------------------------------------------------------------
    */

    private function isSubmitted(
        User $user
    ): bool {
        if (
            filled(
                $user->signature_path
            )
        ) {
            return true;
        }


        $submittedAt =
            $user->getAttribute(
                'registration_submitted_at'
            );


        return
            $submittedAt !==
            null;
    }


    /*
    |--------------------------------------------------------------------------
    | Registration Status
    |--------------------------------------------------------------------------
    */

    private function registrationStatus(
        User $user
    ): string {
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


        /*
        |--------------------------------------------------------------------------
        | Stored Status
        |--------------------------------------------------------------------------
        */

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
        | Completed Timestamp
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
        | Signature / Submission
        |--------------------------------------------------------------------------
        */

        if (
            $this->isSubmitted(
                $user
            )
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
    | Confirmed Status
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
    | Available Components
    |--------------------------------------------------------------------------
    */

    private function availableComponents(
        User $user
    ): array {
        $university =
            $user->university;


        if (!$university) {
            return [];
        }


        $components =
            collect(
                $university->components
                ??
                []
            )
                ->map(
                    fn ($component) =>
                        strtoupper(
                            trim(
                                (string)
                                $component
                            )
                        )
                )
                ->filter(
                    fn ($component) =>
                        in_array(
                            $component,

                            [
                                'CWTS',
                                'LTS',
                                'ROTC',
                            ],

                            true
                        )
                )
                ->unique()
                ->values()
                ->all();


        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $components
            )
        ) {
            return [
                'CWTS',
                'LTS',
                'ROTC',
            ];
        }


        return
            $components;
    }


    /*
    |--------------------------------------------------------------------------
    | Next Screen
    |--------------------------------------------------------------------------
    */

    private function nextScreen(
        User $user,
        string $status
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Not Started
        |--------------------------------------------------------------------------
        */

        if (
            $status ===
            'not_started'
        ) {
            return
                '/Registration/PreRegistrationScreen';
        }


        /*
        |--------------------------------------------------------------------------
        | Draft
        |--------------------------------------------------------------------------
        */

        if (
            $status ===
            'draft'
        ) {
            return
                '/Registration/GeneralRegistrationSignature';
        }


        /*
        |--------------------------------------------------------------------------
        | Submitted / Under Review
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $status,

                [
                    'submitted',
                    'under_review',
                ],

                true
            )
        ) {
            return
                '/Registration/RegistrationWaitEdit';
        }


        /*
        |--------------------------------------------------------------------------
        | Confirmed / Completed
        |--------------------------------------------------------------------------
        */

        if (
            $this->isConfirmedStatus(
                $status
            )
        ) {
            return
                '/Registration/RegistrationConfirmation';
        }


        return
            '/Registration/RegistrationWaitEdit';
    }


    /*
    |--------------------------------------------------------------------------
    | Registration Response
    |--------------------------------------------------------------------------
    */

    private function registrationResponse(
        User $user
    ): array {
        $status =
            $this->registrationStatus(
                $user
            );


        return [
            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            */

            'id' =>
                $user->id,


            /*
            |--------------------------------------------------------------------------
            | Registration
            |--------------------------------------------------------------------------
            */

            'subject' =>
                $user->subject,

            'component' =>
                $user->component,

            'term' =>
                $user->term,


            /*
            |--------------------------------------------------------------------------
            | Name
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

            'year' =>
                $user->year_level,

            'section' =>
                $user->section,


            /*
            |--------------------------------------------------------------------------
            | Personal
            |--------------------------------------------------------------------------
            */

            'gender' =>
                $user->gender,

            'birth_date' =>
                $user->birth_date
                    ?->format(
                        'Y-m-d'
                    ),

            'email' =>
                $user->email,

            'contact_number' =>
                $user->contact_number,


            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'city_address' =>
                $user->city_address,

            'municipality' =>
                $user->municipality,

            'province' =>
                $user->province,

            'full_address' =>
                $user->full_address,


            /*
            |--------------------------------------------------------------------------
            | Guardian
            |--------------------------------------------------------------------------
            */

            'guardian_name' =>
                $user->guardian_name,

            'guardian_address' =>
                $user->guardian_address,

            'guardian_contact_number' =>
                $user->guardian_contact_number,

            'guardian_contact' =>
                $user->guardian_contact_number,


            /*
            |--------------------------------------------------------------------------
            | Signature
            |--------------------------------------------------------------------------
            */

            'has_signature' =>
                filled(
                    $user->signature_path
                ),


            /*
            |--------------------------------------------------------------------------
            | Registration Status
            |--------------------------------------------------------------------------
            */

            'registration_status' =>
                $status,

            'registration_submitted_at' =>
                $user->getAttribute(
                    'registration_submitted_at'
                ),

            'registration_completed_at' =>
                $user->getAttribute(
                    'registration_completed_at'
                ),

            'confirmed_at' =>
                $user->getAttribute(
                    'confirmed_at'
                ),

            'can_edit_component' =>
                !$this->isConfirmedStatus(
                    $status
                ),
        ];
    }
}