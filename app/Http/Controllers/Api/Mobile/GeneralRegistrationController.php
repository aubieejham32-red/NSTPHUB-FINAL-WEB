<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class GeneralRegistrationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Get Current Student General Registration
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/general-registration
    |
    */

    public function show(
        Request $request
    ): JsonResponse {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Authentication is required.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Student Only
        |--------------------------------------------------------------------------
        */

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,

                'message' =>
                    'This account is not authorized to use student registration.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Registration
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'registration' =>
                $this->registrationResponse(
                    $user
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Save General Registration
    |--------------------------------------------------------------------------
    |
    | POST / PUT
    |
    | /api/mobile/general-registration
    |
    |
    | This saves:
    |
    | subject
    | component
    | term
    |
    | surname
    | first_name
    | middle_name
    |
    | course
    | year_level
    | section
    |
    | gender
    | birth_date
    |
    | city_address
    | municipality
    | province
    |
    | contact_number
    |
    | guardian_name
    | guardian_address
    | guardian_contact_number
    |
    */

    public function save(
        Request $request
    ): JsonResponse {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Authentication is required.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Student Only
        |--------------------------------------------------------------------------
        */

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Only student accounts may submit NSTP registration.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | University Required
        |--------------------------------------------------------------------------
        */

        $university =
            $user
                ->university()
                ->first();


        if (!$university) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Your student account is not connected to a university.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Mobile Payload
        |--------------------------------------------------------------------------
        |
        | React Native currently uses:
        |
        | year
        |
        | while users table uses:
        |
        | year_level
        |
        |
        | It also currently sends:
        |
        | guardian_contact
        |
        | while users table uses:
        |
        | guardian_contact_number
        |
        */

        $input =
            $request->all();


        if (
            empty(
                $input['year_level']
            )
            &&
            !empty(
                $input['year']
            )
        ) {
            $input['year_level'] =
                $input['year'];
        }


        if (
            empty(
                $input[
                    'guardian_contact_number'
                ]
            )
            &&
            !empty(
                $input[
                    'guardian_contact'
                ]
            )
        ) {
            $input[
                'guardian_contact_number'
            ] =
                $input[
                    'guardian_contact'
                ];
        }


        /*
        |--------------------------------------------------------------------------
        | Birth Date
        |--------------------------------------------------------------------------
        |
        | GeneralRegistrationScreen currently creates:
        |
        | birthdate: {
        |     month: "07",
        |     day: "15",
        |     year: "2005"
        | }
        |
        | Laravel stores:
        |
        | birth_date = 2005-07-15
        |
        */

        if (
            empty(
                $input['birth_date']
            )
            &&
            isset(
                $input['birthdate']
            )
            &&
            is_array(
                $input['birthdate']
            )
        ) {
            $month =
                preg_replace(
                    '/[^0-9]/',
                    '',
                    (string) (
                        $input['birthdate']['month']
                        ??
                        ''
                    )
                );


            $day =
                preg_replace(
                    '/[^0-9]/',
                    '',
                    (string) (
                        $input['birthdate']['day']
                        ??
                        ''
                    )
                );


            $year =
                preg_replace(
                    '/[^0-9]/',
                    '',
                    (string) (
                        $input['birthdate']['year']
                        ??
                        ''
                    )
                );


            if (
                $month !== ''
                &&
                $day !== ''
                &&
                $year !== ''
            ) {
                $input['birth_date'] =
                    sprintf(
                        '%04d-%02d-%02d',
                        (int) $year,
                        (int) $month,
                        (int) $day
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Values
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $input['component']
            )
        ) {
            $input['component'] =
                strtoupper(
                    trim(
                        (string)
                        $input['component']
                    )
                );
        }


        if (
            isset(
                $input['subject']
            )
        ) {
            $input['subject'] =
                strtoupper(
                    trim(
                        (string)
                        $input['subject']
                    )
                );
        }


        if (
            isset(
                $input['term']
            )
        ) {
            $input['term'] =
                strtoupper(
                    trim(
                        (string)
                        $input['term']
                    )
                );
        }


        if (
            isset(
                $input['gender']
            )
        ) {
            $input['gender'] =
                strtolower(
                    trim(
                        (string)
                        $input['gender']
                    )
                );
        }


        if (
            isset(
                $input['email']
            )
        ) {
            $input['email'] =
                strtolower(
                    trim(
                        (string)
                        $input['email']
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validator =
            Validator::make(
                $input,
                [
                    'subject' => [
                        'required',
                        'string',

                        Rule::in([
                            'NSTP 1',
                            'NSTP 2',
                        ]),
                    ],

                    'component' => [
                        'required',
                        'string',
                        'max:20',
                    ],

                    'term' => [
                        'required',
                        'string',

                        Rule::in([
                            '1ST SEM',
                            '2ND SEM',
                            'SUMMER',
                        ]),
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Name
                    |--------------------------------------------------------------------------
                    */

                    'surname' => [
                        'required',
                        'string',
                        'max:100',
                    ],

                    'first_name' => [
                        'required',
                        'string',
                        'max:100',
                    ],

                    'middle_name' => [
                        'nullable',
                        'string',
                        'max:100',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Academic
                    |--------------------------------------------------------------------------
                    */

                    'course' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'year_level' => [
                        'required',
                        'string',
                        'max:30',
                    ],

                    'section' => [
                        'required',
                        'string',
                        'max:50',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Personal
                    |--------------------------------------------------------------------------
                    */

                    'gender' => [
                        'required',

                        Rule::in([
                            'male',
                            'female',
                        ]),
                    ],

                    'birth_date' => [
                        'required',
                        'date',
                        'before_or_equal:today',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Address
                    |--------------------------------------------------------------------------
                    */

                    'city_address' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'municipality' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'province' => [
                        'required',
                        'string',
                        'max:150',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Contact
                    |--------------------------------------------------------------------------
                    */

                    'email' => [
                        'required',
                        'email',
                        'max:255',
                    ],

                    'contact_number' => [
                        'required',
                        'string',
                        'max:30',

                        'regex:/^[0-9+\-\s()]+$/',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Guardian
                    |--------------------------------------------------------------------------
                    */

                    'guardian_name' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'guardian_address' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'guardian_contact_number' => [
                        'required',
                        'string',
                        'max:30',

                        'regex:/^[0-9+\-\s()]+$/',
                    ],
                ],
                [
                    'contact_number.regex' =>
                        'The contact number format is invalid.',

                    'guardian_contact_number.regex' =>
                        'The parent or guardian contact number format is invalid.',
                ]
            );


        if (
            $validator->fails()
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Please correct the registration information.',

                'errors' =>
                    $validator->errors(),
            ], 422);
        }


        $validated =
            $validator
                ->validated();


        /*
        |--------------------------------------------------------------------------
        | Account Email Protection
        |--------------------------------------------------------------------------
        |
        | users.email is the student's actual login email.
        |
        | We should not silently replace a Google/login email from the
        | general registration form.
        |
        */

        if (
            strtolower(
                trim(
                    (string)
                    $user->email
                )
            )
            !==
            strtolower(
                trim(
                    (string)
                    $validated['email']
                )
            )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'The registration email must match the email address of your NSTP HUB account.',

                'errors' => [
                    'email' => [
                        'Use the same email address that you used to sign in.',
                    ],
                ],
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Component Against University
        |--------------------------------------------------------------------------
        */

        $allowedComponents =
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
                ->filter()
                ->unique()
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        |
        | Only used when the university does not yet have its components
        | configured.
        |
        */

        if (
            $allowedComponents
                ->isEmpty()
        ) {
            $allowedComponents =
                collect([
                    'CWTS',
                    'LTS',
                    'ROTC',
                ]);
        }


        if (
            !$allowedComponents
                ->contains(
                    $validated['component']
                )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'The selected NSTP component is not available for your university.',

                'errors' => [
                    'component' => [
                        'Please choose an NSTP component offered by your university.',
                    ],
                ],
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Do Not Allow Editing After Confirmation
        |--------------------------------------------------------------------------
        */

        if (
            $this->isConfirmed(
                $user
            )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Your NSTP registration has already been confirmed and can no longer be changed.',
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $user,
                $validated
            ) {
                $user->subject =
                    $validated['subject'];


                $user->component =
                    $validated['component'];


                $user->term =
                    $validated['term'];


                /*
                |--------------------------------------------------------------------------
                | Name
                |--------------------------------------------------------------------------
                */

                $user->surname =
                    trim(
                        $validated['surname']
                    );


                $user->first_name =
                    trim(
                        $validated['first_name']
                    );


                $user->middle_name =
                    isset(
                        $validated['middle_name']
                    )
                        ? trim(
                            $validated['middle_name']
                        )
                        : null;


                /*
                |--------------------------------------------------------------------------
                | Academic
                |--------------------------------------------------------------------------
                */

                $user->course =
                    trim(
                        $validated['course']
                    );


                $user->year_level =
                    trim(
                        $validated['year_level']
                    );


                $user->section =
                    trim(
                        $validated['section']
                    );


                /*
                |--------------------------------------------------------------------------
                | Personal
                |--------------------------------------------------------------------------
                */

                $user->gender =
                    $validated['gender'];


                $user->birth_date =
                    $validated['birth_date'];


                $user->contact_number =
                    trim(
                        $validated['contact_number']
                    );


                /*
                |--------------------------------------------------------------------------
                | Address
                |--------------------------------------------------------------------------
                */

                $user->city_address =
                    trim(
                        $validated['city_address']
                    );


                $user->municipality =
                    trim(
                        $validated['municipality']
                    );


                $user->province =
                    trim(
                        $validated['province']
                    );


                /*
                |--------------------------------------------------------------------------
                | Guardian
                |--------------------------------------------------------------------------
                */

                $user->guardian_name =
                    trim(
                        $validated['guardian_name']
                    );


                $user->guardian_address =
                    trim(
                        $validated['guardian_address']
                    );


                $user->guardian_contact_number =
                    trim(
                        $validated[
                            'guardian_contact_number'
                        ]
                    );


                /*
                |--------------------------------------------------------------------------
                | Optional Registration Status
                |--------------------------------------------------------------------------
                |
                | If you already added registration_status to users, we preserve
                | compatibility with it.
                |
                | If the column does not exist, nothing is written.
                |
                */

                if (
                    array_key_exists(
                        'registration_status',
                        $user->getAttributes()
                    )
                ) {
                    $existingStatus =
                        strtolower(
                            trim(
                                (string)
                                $user->registration_status
                            )
                        );


                    if (
                        $existingStatus === ''
                        ||
                        $existingStatus ===
                            'not_started'
                    ) {
                        $user->registration_status =
                            'draft';
                    }
                }


                $user->save();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Refresh
        |--------------------------------------------------------------------------
        */

        $user->refresh();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Your general NSTP registration has been saved successfully.',

            'registration' =>
                $this->registrationResponse(
                    $user
                ),

            'next_screen' =>
                '/Registration/GeneralRegistrationSignature',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Save Registration Signature / Submit Registration
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /api/mobile/general-registration/signature
    |
    | React Native sends:
    |
    | {
    |     "paths": [
    |         "M 10 20 L 11 21 ...",
    |         "M 30 40 L 31 41 ..."
    |     ],
    |
    |     "certified": true
    | }
    |
    */

    public function signature(
        Request $request
    ): JsonResponse {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Authentication is required.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Student Only
        |--------------------------------------------------------------------------
        */

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Only student accounts may submit NSTP registration.',
            ], 403);
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
                    'Complete your general registration before submitting your signature.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Already Confirmed
        |--------------------------------------------------------------------------
        */

        if (
            $this->isConfirmed(
                $user
            )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Your registration has already been confirmed.',
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Signature
        |--------------------------------------------------------------------------
        */

        $validator =
            Validator::make(
                $request->all(),
                [
                    'paths' => [
                        'required',
                        'array',
                        'min:1',
                        'max:200',
                    ],

                    'paths.*' => [
                        'required',
                        'string',
                        'max:50000',
                    ],

                    'certified' => [
                        'required',
                        'accepted',
                    ],
                ]
            );


        if (
            $validator->fails()
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Please provide a valid signature and certification.',

                'errors' =>
                    $validator->errors(),
            ], 422);
        }


        $validated =
            $validator
                ->validated();


        /*
        |--------------------------------------------------------------------------
        | Sanitize SVG Paths
        |--------------------------------------------------------------------------
        |
        | React Native currently creates M/L SVG drawing commands.
        |
        | The whitelist below prevents HTML/XML/script injection from being
        | placed inside the generated SVG file.
        |
        */

        $safePaths = [];


        foreach (
            $validated['paths']
            as $path
        ) {
            $path =
                trim(
                    (string)
                    $path
                );


            if (
                $path === ''
                ||
                !preg_match(
                    '/^[MmLlHhVvCcSsQqTtAaZz0-9eE+\-.,\s]+$/',
                    $path
                )
            ) {
                return response()->json([
                    'success' => false,

                    'message' =>
                        'The submitted signature contains invalid drawing data.',
                ], 422);
            }


            $safePaths[] =
                $path;
        }


        /*
        |--------------------------------------------------------------------------
        | Build SVG
        |--------------------------------------------------------------------------
        */

        $svgPaths =
            collect(
                $safePaths
            )
                ->map(
                    function (
                        string $path
                    ): string {
                        $escaped =
                            htmlspecialchars(
                                $path,
                                ENT_QUOTES
                                |
                                ENT_XML1,
                                'UTF-8'
                            );


                        return
                            '<path d="' .
                            $escaped .
                            '" stroke="#233E47" stroke-width="2.4" ' .
                            'stroke-linecap="round" stroke-linejoin="round" ' .
                            'fill="none" />';
                    }
                )
                ->implode(
                    PHP_EOL
                );


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT: Match The React Native Signature Canvas
        |--------------------------------------------------------------------------
        |
        | GeneralRegistrationSignature uses a fixed virtual canvas of:
        |
        | 1000 x 400
        |
        | The old backend used a 400 x 200 SVG viewBox. That clipped every
        | coordinate outside x=400 / y=200, which is why the web portal showed
        | only the lower/right portion of the student's signature.
        |
        */

        $svg =
            '<?xml version="1.0" encoding="UTF-8"?>' .
            PHP_EOL .
            '<svg xmlns="http://www.w3.org/2000/svg" ' .
            'viewBox="0 0 1000 400" width="1000" height="400" ' .
            'preserveAspectRatio="xMidYMid meet">' .
            PHP_EOL .
            '<rect width="1000" height="400" fill="#FFFFFF" />' .
            PHP_EOL .
            $svgPaths .
            PHP_EOL .
            '</svg>';


        /*
        |--------------------------------------------------------------------------
        | Private Signature Storage
        |--------------------------------------------------------------------------
        |
        | Signatures should NOT be publicly accessible through /storage.
        |
        | Stored using the local disk:
        |
        | storage/app/private/signatures/general/...
        |
        | depending on your Laravel filesystem configuration.
        |
        */

        $newSignaturePath =
            'signatures/general/' .
            $user->id .
            '/' .
            Str::uuid() .
            '.svg';


        $oldSignaturePath =
            $user->signature_path;


        try {
            Storage::disk(
                'local'
            )->put(
                $newSignaturePath,
                $svg
            );


            DB::transaction(
                function () use (
                    $user,
                    $newSignaturePath
                ) {
                    $user->signature_path =
                        $newSignaturePath;


                    /*
                    |--------------------------------------------------------------------------
                    | Registration Status
                    |--------------------------------------------------------------------------
                    |
                    | If these fields already exist in your users table, use them.
                    |
                    */

                    if (
                        array_key_exists(
                            'registration_status',
                            $user->getAttributes()
                        )
                    ) {
                        $user->registration_status =
                            'under_review';
                    }


                    if (
                        array_key_exists(
                            'registration_submitted_at',
                            $user->getAttributes()
                        )
                    ) {
                        $user->registration_submitted_at =
                            now();
                    }


                    $user->save();
                }
            );


        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Remove File If Database Save Failed
            |--------------------------------------------------------------------------
            */

            if (
                Storage::disk(
                    'local'
                )->exists(
                    $newSignaturePath
                )
            ) {
                Storage::disk(
                    'local'
                )->delete(
                    $newSignaturePath
                );
            }


            report(
                $exception
            );


            return response()->json([
                'success' => false,

                'message' =>
                    'Your registration signature could not be saved.',
            ], 500);
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Previous Signature
        |--------------------------------------------------------------------------
        */

        if (
            $oldSignaturePath
            &&
            $oldSignaturePath !==
                $newSignaturePath
            &&
            Str::startsWith(
                $oldSignaturePath,
                'signatures/general/'
            )
            &&
            Storage::disk(
                'local'
            )->exists(
                $oldSignaturePath
            )
        ) {
            Storage::disk(
                'local'
            )->delete(
                $oldSignaturePath
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Refresh
        |--------------------------------------------------------------------------
        */

        $user->refresh();


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Your NSTP registration has been submitted successfully and is now waiting for review.',

            'registration' =>
                $this->registrationResponse(
                    $user
                ),

            'next_screen' =>
                '/Registration/RegistrationWaitEdit',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update NSTP Component While Waiting
    |--------------------------------------------------------------------------
    |
    | PATCH
    |
    | /api/mobile/general-registration/component
    |
    | Used by:
    |
    | RegistrationWaitEdit.js
    |
    */

    public function updateComponent(
        Request $request
    ): JsonResponse {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Authentication is required.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Student Only
        |--------------------------------------------------------------------------
        */

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Only student accounts may update NSTP registration.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Changes After Confirmation
        |--------------------------------------------------------------------------
        */

        if (
            $this->isConfirmed(
                $user
            )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Your registration has already been confirmed. The NSTP component can no longer be changed.',
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $university =
            $user
                ->university()
                ->first();


        if (!$university) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Your student account is not connected to a university.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validator =
            Validator::make(
                [
                    'component' =>
                        strtoupper(
                            trim(
                                (string)
                                $request->input(
                                    'component'
                                )
                            )
                        ),
                ],
                [
                    'component' => [
                        'required',
                        'string',
                        'max:20',
                    ],
                ]
            );


        if (
            $validator->fails()
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Please select an NSTP component.',

                'errors' =>
                    $validator->errors(),
            ], 422);
        }


        $component =
            $validator
                ->validated()[
                    'component'
                ];


        /*
        |--------------------------------------------------------------------------
        | University Components
        |--------------------------------------------------------------------------
        */

        $allowedComponents =
            collect(
                $university->components
                ??
                []
            )
                ->map(
                    fn ($item) =>
                        strtoupper(
                            trim(
                                (string)
                                $item
                            )
                        )
                )
                ->filter()
                ->unique()
                ->values();


        if (
            $allowedComponents
                ->isEmpty()
        ) {
            $allowedComponents =
                collect([
                    'CWTS',
                    'LTS',
                    'ROTC',
                ]);
        }


        if (
            !$allowedComponents
                ->contains(
                    $component
                )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'That NSTP component is not available for your university.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $user->component =
            $component;


        $user->save();


        $user->refresh();


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Your NSTP component has been updated successfully.',

            'registration' =>
                $this->registrationResponse(
                    $user
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
    | Is Registration Confirmed
    |--------------------------------------------------------------------------
    |
    | Compatible with a future/existing registration_status column.
    |
    */

    private function isConfirmed(
        User $user
    ): bool {
        $status =
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


        return in_array(
            $status,
            [
                'confirmed',
                'completed',
            ],
            true
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
            $storedStatus !==
            ''
        ) {
            return
                $storedStatus;
        }


        /*
        |--------------------------------------------------------------------------
        | Confirmed Status Not Yet Stored
        |--------------------------------------------------------------------------
        |
        | When registration_status does not exist yet:
        |
        | no general info     = not_started
        | general info saved  = draft
        | signature submitted = under_review
        |
        */

        if (
            $user->signature_path
        ) {
            return
                'under_review';
        }


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
    | Registration API Response
    |--------------------------------------------------------------------------
    */

    private function registrationResponse(
        User $user
    ): array {
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
            | Registration Details
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

            /*
            |--------------------------------------------------------------------------
            | Compatibility Alias
            |--------------------------------------------------------------------------
            |
            | Current React Native form uses "year".
            |
            */

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

            /*
            |--------------------------------------------------------------------------
            | Compatibility Alias
            |--------------------------------------------------------------------------
            */

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
            | Status
            |--------------------------------------------------------------------------
            */

            'registration_status' =>
                $this->registrationStatus(
                    $user
                ),

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
        ];
    }
}