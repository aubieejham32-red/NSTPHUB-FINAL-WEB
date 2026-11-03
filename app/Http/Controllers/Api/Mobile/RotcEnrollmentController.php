<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\RotcMsRecord;
use App\Models\RotcStudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RotcEnrollmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show ROTC Enrollment
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request
    ): JsonResponse {
        [
            $user,
            $error,
        ] =
            $this->resolveEligibleStudent(
                $request
            );


        if (
            $error
        ) {
            return $error;
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Profile
        |--------------------------------------------------------------------------
        */

        $profile =
            RotcStudentProfile::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->with(
                    'msRecords'
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Completed Enrollment
        |--------------------------------------------------------------------------
        */

        if (
            $profile
            &&
            $profile->isCompleted()
        ) {
            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Your ROTC enrollment has already been completed.',

                'university' =>
                    $this->universityResponse(
                        $request,
                        $user
                    ),

                'rotc_profile' =>
                    $this->profileResponse(
                        $profile
                    ),

                'step_1' =>
                    $this->stepOneResponse(
                        $user,
                        $profile
                    ),

                'step_2' =>
                    $this->stepTwoResponse(
                        $user,
                        $profile
                    ),

                'step_3' =>
                    $this->stepThreeResponse(
                        $user,
                        $profile
                    ),

                'step_4' =>
                    $this->stepFourResponse(
                        $user,
                        $profile
                    ),

                'progress' => [
                    'current_step' =>
                        4,

                    'total_steps' =>
                        4,

                    'status' =>
                        $profile->status,

                    'is_completed' =>
                        true,
                ],

                'next_screen' =>
                    '/Registration/ROTC/RotcConfirmEnrollment',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Current Step
        |--------------------------------------------------------------------------
        */

        $currentStep =
            $this->determineCurrentStep(
                $profile
            );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'ROTC enrollment information loaded successfully.',

            'university' =>
                $this->universityResponse(
                    $request,
                    $user
                ),

            'general_registration' => [
                'subject' =>
                    $user->subject,

                'component' =>
                    strtoupper(
                        trim(
                            (string)
                            $user->component
                        )
                    ),

                'term' =>
                    $user->term,

                'status' =>
                    $this->generalRegistrationStatus(
                        $user
                    ),
            ],

            'rotc_profile' =>
                $profile
                    ? $this->profileResponse(
                        $profile
                    )
                    : null,

            'step_1' =>
                $this->stepOneResponse(
                    $user,
                    $profile
                ),

            'step_2' =>
                $this->stepTwoResponse(
                    $user,
                    $profile
                ),

            'step_3' =>
                $this->stepThreeResponse(
                    $user,
                    $profile
                ),

            'step_4' =>
                $this->stepFourResponse(
                    $user,
                    $profile
                ),

            'progress' => [
                'current_step' =>
                    $currentStep,

                'total_steps' =>
                    4,

                'profile_exists' =>
                    $profile !==
                    null,

                'status' =>
                    $profile
                        ? $profile->status
                        : RotcStudentProfile::STATUS_DRAFT,

                'is_completed' =>
                    false,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Save Step 1
    |--------------------------------------------------------------------------
    */

    public function saveStepOne(
        Request $request
    ): JsonResponse {
        [
            $user,
            $error,
        ] =
            $this->resolveEligibleStudent(
                $request
            );


        if (
            $error
        ) {
            return $error;
        }


        /*
        |--------------------------------------------------------------------------
        | NSTP ID Must Already Exist
        |--------------------------------------------------------------------------
        |
        | The NSTP ID is generated by the University Admin.
        |
        | The student is NOT allowed to choose their own ID.
        |
        */

        if (
            blank(
                $user->student_id_number
            )
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your NSTP Student ID number has not yet been generated by the university administrator.',
            ], 409);
        }


        $existingProfile =
            RotcStudentProfile::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->first();


        if (
            $existingProfile
            &&
            $existingProfile->isCompleted()
        ) {
            return $this->completedResponse(
                $existingProfile
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $input =
            $request->all();


        if (
            isset(
                $input[
                    'ms_level'
                ]
            )
        ) {
            $input[
                'ms_level'
            ] =
                strtoupper(
                    trim(
                        (string)
                        $input[
                            'ms_level'
                        ]
                    )
                );
        }


        if (
            isset(
                $input[
                    'gender'
                ]
            )
        ) {
            $input[
                'gender'
            ] =
                strtolower(
                    trim(
                        (string)
                        $input[
                            'gender'
                        ]
                    )
                );
        }


        if (
            isset(
                $input[
                    'blood_type'
                ]
            )
        ) {
            $bloodType =
                trim(
                    (string)
                    $input[
                        'blood_type'
                    ]
                );


            $input[
                'blood_type'
            ] =
                strtolower(
                    $bloodType
                )
                ===
                'unknown'
                    ? 'Unknown'
                    : strtoupper(
                        $bloodType
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validator =
            Validator::make(
                $input,
                [
                    'ms_level' => [
                        'required',

                        Rule::in([
                            'MS 1',
                            'MS 2',
                            'MS 3',
                            'MS 4',
                        ]),
                    ],

                    'last_name' => [
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

                    'name_extension' => [
                        'nullable',
                        'string',
                        'max:30',
                    ],

                    'gender' => [
                        'required',

                        Rule::in([
                            'male',
                            'female',
                        ]),
                    ],

                    'blood_type' => [
                        'required',

                        Rule::in([
                            'A+',
                            'A-',
                            'B+',
                            'B-',
                            'AB+',
                            'AB-',
                            'O+',
                            'O-',
                            'Unknown',
                        ]),
                    ],

                    'date_of_birth' => [
                        'required',
                        'date',
                        'before:today',
                    ],

                    'place_of_birth' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'height_cm' => [
                        'required',
                        'numeric',
                        'between:50,250',
                    ],

                    'weight_kg' => [
                        'required',
                        'numeric',
                        'between:20,300',
                    ],

                    'complexion' => [
                        'required',
                        'string',
                        'max:50',
                    ],

                    'school_name' => [
                        'required',
                        'string',
                        'max:200',
                    ],

                    'course' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'religion' => [
                        'nullable',
                        'string',
                        'max:100',
                    ],
                ]
            );


        if (
            $validator->fails()
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Please correct the ROTC Step 1 information.',

                'errors' =>
                    $validator->errors(),
            ], 422);
        }


        $validated =
            $validator
                ->validated();


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $profile =
            DB::transaction(
                function () use (
                    $user,
                    $validated
                ) {
                    $profile =
                        RotcStudentProfile::query()
                            ->where(
                                'user_id',
                                $user->id
                            )
                            ->lockForUpdate()
                            ->first();


                    if (
                        !$profile
                    ) {
                        $profile =
                            new RotcStudentProfile();

                        $profile
                            ->user_id =
                            $user->id;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Server-Controlled NSTP ID
                    |--------------------------------------------------------------------------
                    */

                    $profile
                        ->nstp_id_no =
                        $user
                            ->student_id_number;


                    $profile
                        ->ms_level =
                        $validated[
                            'ms_level'
                        ];


                    $profile
                        ->last_name =
                        trim(
                            $validated[
                                'last_name'
                            ]
                        );


                    $profile
                        ->first_name =
                        trim(
                            $validated[
                                'first_name'
                            ]
                        );


                    $profile
                        ->middle_name =
                        filled(
                            $validated[
                                'middle_name'
                            ]
                            ??
                            null
                        )
                            ? trim(
                                $validated[
                                    'middle_name'
                                ]
                            )
                            : null;


                    $profile
                        ->name_extension =
                        filled(
                            $validated[
                                'name_extension'
                            ]
                            ??
                            null
                        )
                            ? trim(
                                $validated[
                                    'name_extension'
                                ]
                            )
                            : null;


                    $profile
                        ->gender =
                        $validated[
                            'gender'
                        ];


                    $profile
                        ->blood_type =
                        $validated[
                            'blood_type'
                        ];


                    $profile
                        ->date_of_birth =
                        $validated[
                            'date_of_birth'
                        ];


                    $profile
                        ->place_of_birth =
                        trim(
                            $validated[
                                'place_of_birth'
                            ]
                        );


                    $profile
                        ->height_cm =
                        $validated[
                            'height_cm'
                        ];


                    $profile
                        ->weight_kg =
                        $validated[
                            'weight_kg'
                        ];


                    $profile
                        ->complexion =
                        trim(
                            $validated[
                                'complexion'
                            ]
                        );


                    $profile
                        ->school_name =
                        trim(
                            $validated[
                                'school_name'
                            ]
                        );


                    $profile
                        ->course =
                        trim(
                            $validated[
                                'course'
                            ]
                        );


                    $profile
                        ->religion =
                        filled(
                            $validated[
                                'religion'
                            ]
                            ??
                            null
                        )
                            ? trim(
                                $validated[
                                    'religion'
                                ]
                            )
                            : null;


                    $profile
                        ->status =
                        RotcStudentProfile::STATUS_DRAFT;


                    $profile
                        ->save();


                    return $profile;
                }
            );


        $profile->load(
            'msRecords'
        );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'ROTC Step 1 saved successfully.',

            'rotc_profile' =>
                $this->profileResponse(
                    $profile
                ),

            'progress' => [
                'completed_step' =>
                    1,

                'next_step' =>
                    2,

                'total_steps' =>
                    4,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Save Step 2
    |--------------------------------------------------------------------------
    */

    public function saveStepTwo(
        Request $request
    ): JsonResponse {
        [
            $user,
            $error,
        ] =
            $this->resolveEligibleStudent(
                $request
            );


        if (
            $error
        ) {
            return $error;
        }


        $profile =
            $this->profileForStudent(
                $user
            );


        if (
            !$profile
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Please complete ROTC Step 1 first.',
            ], 409);
        }


        if (
            $profile->isCompleted()
        ) {
            return $this->completedResponse(
                $profile
            );
        }


        $input =
            $request->all();


        $samePermanent =
            filter_var(
                $input[
                    'permanent_same_as_temporary'
                ]
                ??
                false,

                FILTER_VALIDATE_BOOLEAN
            );


        $validator =
            Validator::make(
                $input,
                [
                    'cellphone_number' => [
                        'required',
                        'string',
                        'max:30',
                        'regex:/^[0-9+\-\s()]+$/',
                    ],

                    'contact_email' => [
                        'required',
                        'email',
                        'max:255',
                    ],

                    'temporary_address_line' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'temporary_municipality' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'temporary_province' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'permanent_same_as_temporary' => [
                        'required',
                        'boolean',
                    ],

                    'permanent_address_line' => [
                        Rule::requiredIf(
                            !$samePermanent
                        ),
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'permanent_municipality' => [
                        Rule::requiredIf(
                            !$samePermanent
                        ),
                        'nullable',
                        'string',
                        'max:150',
                    ],

                    'permanent_province' => [
                        Rule::requiredIf(
                            !$samePermanent
                        ),
                        'nullable',
                        'string',
                        'max:150',
                    ],
                ]
            );


        if (
            $validator->fails()
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Please correct the ROTC Step 2 information.',

                'errors' =>
                    $validator->errors(),
            ], 422);
        }


        $validated =
            $validator
                ->validated();


        DB::transaction(
            function () use (
                $profile,
                $validated,
                $samePermanent
            ) {
                $locked =
                    RotcStudentProfile::query()
                        ->whereKey(
                            $profile->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $locked
                    ->cellphone_number =
                    trim(
                        $validated[
                            'cellphone_number'
                        ]
                    );


                $locked
                    ->contact_email =
                    strtolower(
                        trim(
                            $validated[
                                'contact_email'
                            ]
                        )
                    );


                $locked
                    ->temporary_address_line =
                    trim(
                        $validated[
                            'temporary_address_line'
                        ]
                    );


                $locked
                    ->temporary_municipality =
                    trim(
                        $validated[
                            'temporary_municipality'
                        ]
                    );


                $locked
                    ->temporary_province =
                    trim(
                        $validated[
                            'temporary_province'
                        ]
                    );


                $locked
                    ->permanent_same_as_temporary =
                    $samePermanent;


                if (
                    $samePermanent
                ) {
                    $locked
                        ->permanent_address_line =
                        $locked
                            ->temporary_address_line;


                    $locked
                        ->permanent_municipality =
                        $locked
                            ->temporary_municipality;


                    $locked
                        ->permanent_province =
                        $locked
                            ->temporary_province;

                } else {
                    $locked
                        ->permanent_address_line =
                        trim(
                            $validated[
                                'permanent_address_line'
                            ]
                        );


                    $locked
                        ->permanent_municipality =
                        trim(
                            $validated[
                                'permanent_municipality'
                            ]
                        );


                    $locked
                        ->permanent_province =
                        trim(
                            $validated[
                                'permanent_province'
                            ]
                        );
                }


                $locked->save();
            }
        );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'ROTC Step 2 saved successfully.',

            'progress' => [
                'completed_step' =>
                    2,

                'next_step' =>
                    3,

                'total_steps' =>
                    4,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Save Step 3
    |--------------------------------------------------------------------------
    */

    public function saveStepThree(
        Request $request
    ): JsonResponse {
        [
            $user,
            $error,
        ] =
            $this->resolveEligibleStudent(
                $request
            );


        if (
            $error
        ) {
            return $error;
        }


        $profile =
            $this->profileForStudent(
                $user
            );


        if (
            !$profile
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Please complete the previous ROTC steps first.',
            ], 409);
        }


        if (
            $profile->isCompleted()
        ) {
            return $this->completedResponse(
                $profile
            );
        }


        $validator =
            Validator::make(
                $request->all(),
                [
                    'father_name' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'father_occupation' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'mother_name' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'mother_occupation' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'emergency_contact_name' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'emergency_contact_relationship' => [
                        'required',
                        'string',
                        'max:100',
                    ],

                    'emergency_contact_number' => [
                        'required',
                        'string',
                        'max:30',
                        'regex:/^[0-9+\-\s()]+$/',
                    ],

                    'emergency_contact_address' => [
                        'required',
                        'string',
                        'max:255',
                    ],
                ]
            );


        if (
            $validator->fails()
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Please correct the ROTC Step 3 information.',

                'errors' =>
                    $validator->errors(),
            ], 422);
        }


        $validated =
            $validator
                ->validated();


        DB::transaction(
            function () use (
                $profile,
                $validated
            ) {
                $locked =
                    RotcStudentProfile::query()
                        ->whereKey(
                            $profile->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $locked
                    ->father_name =
                    trim(
                        $validated[
                            'father_name'
                        ]
                    );


                $locked
                    ->father_occupation =
                    trim(
                        $validated[
                            'father_occupation'
                        ]
                    );


                $locked
                    ->mother_name =
                    trim(
                        $validated[
                            'mother_name'
                        ]
                    );


                $locked
                    ->mother_occupation =
                    trim(
                        $validated[
                            'mother_occupation'
                        ]
                    );


                $locked
                    ->emergency_contact_name =
                    trim(
                        $validated[
                            'emergency_contact_name'
                        ]
                    );


                $locked
                    ->emergency_contact_relationship =
                    trim(
                        $validated[
                            'emergency_contact_relationship'
                        ]
                    );


                $locked
                    ->emergency_contact_number =
                    trim(
                        $validated[
                            'emergency_contact_number'
                        ]
                    );


                $locked
                    ->emergency_contact_address =
                    trim(
                        $validated[
                            'emergency_contact_address'
                        ]
                    );


                $locked->save();
            }
        );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'ROTC Step 3 saved successfully.',

            'progress' => [
                'completed_step' =>
                    3,

                'next_step' =>
                    4,

                'total_steps' =>
                    4,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Save Step 4 / Final Submit
    |--------------------------------------------------------------------------
    */

    public function saveStepFour(
        Request $request
    ): JsonResponse {
        [
            $user,
            $error,
        ] =
            $this->resolveEligibleStudent(
                $request
            );


        if (
            $error
        ) {
            return $error;
        }


        $profile =
            $this->profileForStudent(
                $user
            );


        if (
            !$profile
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Please complete ROTC Steps 1 to 3 first.',
            ], 409);
        }


        /*
        |--------------------------------------------------------------------------
        | Idempotent Final Submission
        |--------------------------------------------------------------------------
        */

        if (
            $profile->isCompleted()
        ) {
            return $this->completedResponse(
                $profile
            );
        }


        $validator =
            Validator::make(
                $request->all(),
                [
                    'ms_records' => [
                        'sometimes',
                        'nullable',
                        'array',
                        'max:8',
                    ],

                    'ms_records.*.ms_level' => [
                        'required',

                        Rule::in([
                            'MS 1',
                            'MS 2',
                            'MS 3',
                            'MS 4',
                        ]),
                    ],

                    'ms_records.*.semester' => [
                        'required',

                        Rule::in([
                            '1st Sem',
                            '2nd Sem',
                            'Summer',
                        ]),
                    ],

                    'ms_records.*.school_year' => [
                        'required',
                        'string',
                        'max:20',
                    ],

                    'ms_records.*.grade' => [
                        'required',
                        'numeric',
                        'between:0,100',
                    ],

                    'ms_records.*.remarks' => [
                        'required',
                        'string',
                        'max:100',
                    ],

                    'willing_advance_course' => [
                        'required',
                        'boolean',
                    ],
                ]
            );


        if (
            $validator->fails()
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Please correct the ROTC Step 4 information.',

                'errors' =>
                    $validator->errors(),
            ], 422);
        }


        $validated =
            $validator
                ->validated();


        /*
        |--------------------------------------------------------------------------
        | Ensure Previous Steps Are Complete
        |--------------------------------------------------------------------------
        */

        if (
            !$this->hasStepOne(
                $profile
            )
            ||
            !$this->hasStepTwo(
                $profile
            )
            ||
            !$this->hasStepThree(
                $profile
            )
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Please complete ROTC Steps 1 to 3 before final submission.',
            ], 409);
        }


        DB::transaction(
            function () use (
                $profile,
                $validated,
                $user
            ) {
                $locked =
                    RotcStudentProfile::query()
                        ->whereKey(
                            $profile->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Replace MS Records
                |--------------------------------------------------------------------------
                */

                $locked
                    ->msRecords()
                    ->delete();


                foreach (
                    ($validated['ms_records'] ?? [])
                    as $index =>
                        $record
                ) {
                    RotcMsRecord::create([
                        'rotc_student_profile_id' =>
                            $locked->id,

                        'record_order' =>
                            $index + 1,

                        'ms_level' =>
                            $record[
                                'ms_level'
                            ],

                        'semester' =>
                            $record[
                                'semester'
                            ],

                        'school_year' =>
                            trim(
                                $record[
                                    'school_year'
                                ]
                            ),

                        'grade' =>
                            $record[
                                'grade'
                            ],

                        'remarks' =>
                            trim(
                                $record[
                                    'remarks'
                                ]
                            ),
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Final ROTC Status
                |--------------------------------------------------------------------------
                */

                $locked
                    ->willing_advance_course =
                    array_key_exists('willing_advance_course', $validated)
                        && $validated['willing_advance_course'] !== null
                        ? (bool) $validated['willing_advance_course']
                        : null;


                $locked
                    ->status =
                    RotcStudentProfile::STATUS_COMPLETED;


                $locked
                    ->submitted_at =
                    $locked
                        ->submitted_at
                    ??
                    now();


                $locked
                    ->completed_at =
                    now();


                $locked->save();


                /*
                |--------------------------------------------------------------------------
                | Mark Main Registration Completed
                |--------------------------------------------------------------------------
                |
                | This is important because MobileAuthController uses the users
                | registration workflow to determine whether login may proceed
                | directly to Homepage.
                |
                */

                $updates = [];


                if (
                    Schema::hasColumn(
                        'users',
                        'registration_status'
                    )
                ) {
                    $updates[
                        'registration_status'
                    ] =
                        'completed';
                }


                if (
                    Schema::hasColumn(
                        'users',
                        'registration_completed_at'
                    )
                ) {
                    $updates[
                        'registration_completed_at'
                    ] =
                        now();
                }


                if (
                    !empty(
                        $updates
                    )
                ) {
                    User::query()
                        ->whereKey(
                            $user->id
                        )
                        ->update(
                            $updates
                        );
                }
            }
        );


        $profile =
            RotcStudentProfile::query()
                ->whereKey(
                    $profile->id
                )
                ->with(
                    'msRecords'
                )
                ->firstOrFail();


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Your ROTC enrollment has been completed successfully.',

            'rotc_profile' =>
                $this->profileResponse(
                    $profile
                ),

            'progress' => [
                'completed_step' =>
                    4,

                'next_step' =>
                    null,

                'total_steps' =>
                    4,

                'status' =>
                    RotcStudentProfile::STATUS_COMPLETED,

                'is_completed' =>
                    true,
            ],

            'next_screen' =>
                '/Registration/ROTC/RotcConfirmEnrollment',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Eligible ROTC Student
    |--------------------------------------------------------------------------
    */

    private function resolveEligibleStudent(
        Request $request
    ): array {
        $authenticated =
            $request->user();


        if (
            !$authenticated
        ) {
            return [
                null,

                response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Authentication is required.',
                ], 401),
            ];
        }


        if (
            !(
                $authenticated
                instanceof User
            )
        ) {
            return [
                null,

                response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Invalid student account.',
                ], 403),
            ];
        }


        if (
            !$authenticated
                ->isStudent()
        ) {
            return [
                null,

                response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Only student accounts may use ROTC enrollment.',
                ], 403),
            ];
        }


        $user =
            User::query()
                ->whereKey(
                    $authenticated->id
                )
                ->with(
                    'university'
                )
                ->first();


        if (
            !$user
        ) {
            return [
                null,

                response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Student account could not be found.',
                ], 404),
            ];
        }


        if (
            !$user->university
        ) {
            return [
                null,

                response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Your student account is not connected to a university.',
                ], 422),
            ];
        }


        if (
            !$this->hasGeneralRegistration(
                $user
            )
        ) {
            return [
                null,

                response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Complete your general NSTP registration first.',

                    'next_screen' =>
                        '/Registration/GeneralRegistrationScreen',
                ], 409),
            ];
        }


        $registrationStatus =
            $this->generalRegistrationStatus(
                $user
            );


        if (
            !$this->isGeneralRegistrationConfirmed(
                $registrationStatus
            )
        ) {
            return [
                null,

                response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Your general NSTP registration must first be confirmed by the NSTP Director.',

                    'registration_status' =>
                        $registrationStatus,

                    'next_screen' =>
                        '/Registration/RegistrationWaitEdit',
                ], 409),
            ];
        }


        if (
            !$this->isRotcStudent(
                $user
            )
        ) {
            return [
                null,

                response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'ROTC enrollment is available only to ROTC students.',

                    'component' =>
                        $user->component,

                    'next_screen' =>
                        '/Registration/RegistrationConfirmation',
                ], 403),
            ];
        }


        return [
            $user,
            null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    private function profileForStudent(
        User $user
    ): ?RotcStudentProfile {
        return RotcStudentProfile::query()
            ->where(
                'user_id',
                $user->id
            )
            ->with(
                'msRecords'
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Current Step
    |--------------------------------------------------------------------------
    */

    private function determineCurrentStep(
        ?RotcStudentProfile $profile
    ): int {
        if (
            !$profile
            ||
            !$this->hasStepOne(
                $profile
            )
        ) {
            return 1;
        }


        if (
            !$this->hasStepTwo(
                $profile
            )
        ) {
            return 2;
        }


        if (
            !$this->hasStepThree(
                $profile
            )
        ) {
            return 3;
        }


        return 4;
    }


    /*
    |--------------------------------------------------------------------------
    | Step Completion Helpers
    |--------------------------------------------------------------------------
    */

    private function hasStepOne(
        RotcStudentProfile $profile
    ): bool {
        return
            filled(
                $profile->nstp_id_no
            )
            &&
            filled(
                $profile->ms_level
            )
            &&
            filled(
                $profile->last_name
            )
            &&
            filled(
                $profile->first_name
            )
            &&
            filled(
                $profile->gender
            )
            &&
            filled(
                $profile->blood_type
            )
            &&
            $profile->date_of_birth !==
                null
            &&
            filled(
                $profile->place_of_birth
            )
            &&
            filled(
                $profile->height_cm
            )
            &&
            filled(
                $profile->weight_kg
            )
            &&
            filled(
                $profile->complexion
            )
            &&
            filled(
                $profile->school_name
            )
            &&
            filled(
                $profile->course
            );
    }


    private function hasStepTwo(
        RotcStudentProfile $profile
    ): bool {
        if (
            !filled(
                $profile
                    ->cellphone_number
            )
            ||
            !filled(
                $profile
                    ->contact_email
            )
            ||
            !filled(
                $profile
                    ->temporary_address_line
            )
            ||
            !filled(
                $profile
                    ->temporary_municipality
            )
            ||
            !filled(
                $profile
                    ->temporary_province
            )
        ) {
            return false;
        }


        if (
            !$profile
                ->permanent_same_as_temporary
        ) {
            return
                filled(
                    $profile
                        ->permanent_address_line
                )
                &&
                filled(
                    $profile
                        ->permanent_municipality
                )
                &&
                filled(
                    $profile
                        ->permanent_province
                );
        }


        return true;
    }


    private function hasStepThree(
        RotcStudentProfile $profile
    ): bool {
        return
            filled(
                $profile
                    ->father_name
            )
            &&
            filled(
                $profile
                    ->father_occupation
            )
            &&
            filled(
                $profile
                    ->mother_name
            )
            &&
            filled(
                $profile
                    ->mother_occupation
            )
            &&
            filled(
                $profile
                    ->emergency_contact_name
            )
            &&
            filled(
                $profile
                    ->emergency_contact_relationship
            )
            &&
            filled(
                $profile
                    ->emergency_contact_number
            )
            &&
            filled(
                $profile
                    ->emergency_contact_address
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Step Responses
    |--------------------------------------------------------------------------
    */

    private function stepOneResponse(
        User $user,
        ?RotcStudentProfile $profile
    ): array {
        return [
            /*
            |--------------------------------------------------------------------------
            | Admin-Generated ID
            |--------------------------------------------------------------------------
            */

            'nstp_id_no' =>
                $profile
                    ?->nstp_id_no
                ??
                $user
                    ->student_id_number,

            'ms_level' =>
                $profile
                    ?->ms_level
                ??
                'MS 1',

            'last_name' =>
                $profile
                    ?->last_name
                ??
                $user
                    ->surname,

            'first_name' =>
                $profile
                    ?->first_name
                ??
                $user
                    ->first_name,

            'middle_name' =>
                $profile
                    ?->middle_name
                ??
                $user
                    ->middle_name,

            'name_extension' =>
                $profile
                    ?->name_extension,

            'gender' =>
                $profile
                    ?->gender
                ??
                $user
                    ->gender,

            'blood_type' =>
                $profile
                    ?->blood_type,

            'date_of_birth' =>
                $profile
                    ?->date_of_birth
                    ?->format(
                        'Y-m-d'
                    )
                ??
                $user
                    ->birth_date
                    ?->format(
                        'Y-m-d'
                    ),

            'place_of_birth' =>
                $profile
                    ?->place_of_birth,

            'height_cm' =>
                $profile
                    ?->height_cm,

            'weight_kg' =>
                $profile
                    ?->weight_kg,

            'complexion' =>
                $profile
                    ?->complexion,

            'school_name' =>
                $profile
                    ?->school_name
                ??
                $user
                    ->university
                    ?->name,

            'course' =>
                $profile
                    ?->course
                ??
                $user
                    ->course,

            'religion' =>
                $profile
                    ?->religion,
        ];
    }


    private function stepTwoResponse(
        User $user,
        ?RotcStudentProfile $profile
    ): array {
        return [
            'cellphone_number' =>
                $profile
                    ?->cellphone_number
                ??
                $user
                    ->contact_number,

            'contact_email' =>
                $profile
                    ?->contact_email
                ??
                $user
                    ->email,

            'temporary_address_line' =>
                $profile
                    ?->temporary_address_line
                ??
                $user
                    ->city_address,

            'temporary_municipality' =>
                $profile
                    ?->temporary_municipality
                ??
                $user
                    ->municipality,

            'temporary_province' =>
                $profile
                    ?->temporary_province
                ??
                $user
                    ->province,

            'permanent_same_as_temporary' =>
                $profile
                    ? $profile
                        ->permanent_same_as_temporary
                    : true,

            'permanent_address_line' =>
                $profile
                    ?->permanent_address_line,

            'permanent_municipality' =>
                $profile
                    ?->permanent_municipality,

            'permanent_province' =>
                $profile
                    ?->permanent_province,
        ];
    }


    private function stepThreeResponse(
        User $user,
        ?RotcStudentProfile $profile
    ): array {
        return [
            'father_name' =>
                $profile
                    ?->father_name,

            'father_occupation' =>
                $profile
                    ?->father_occupation,

            'mother_name' =>
                $profile
                    ?->mother_name,

            'mother_occupation' =>
                $profile
                    ?->mother_occupation,

            /*
            |--------------------------------------------------------------------------
            | General registration guardian is useful as emergency default.
            |--------------------------------------------------------------------------
            */

            'emergency_contact_name' =>
                $profile
                    ?->emergency_contact_name
                ??
                $user
                    ->guardian_name,

            'emergency_contact_relationship' =>
                $profile
                    ?->emergency_contact_relationship,

            'emergency_contact_number' =>
                $profile
                    ?->emergency_contact_number
                ??
                $user
                    ->guardian_contact_number,

            'emergency_contact_address' =>
                $profile
                    ?->emergency_contact_address
                ??
                $user
                    ->guardian_address,
        ];
    }


    private function stepFourResponse(
        User $user,
        ?RotcStudentProfile $profile
    ): array {
        return [
            'willing_advance_course' =>
                $profile
                    ?->willing_advance_course,

            'ms_records' =>
                $profile
                    ? $profile
                        ->msRecords
                        ->map(
                            fn (
                                RotcMsRecord $record
                            ) => [
                                'id' =>
                                    $record->id,

                                'record_order' =>
                                    $record
                                        ->record_order,

                                'ms_level' =>
                                    $record
                                        ->ms_level,

                                'semester' =>
                                    $record
                                        ->semester,

                                'school_year' =>
                                    $record
                                        ->school_year,

                                'grade' =>
                                    $record
                                        ->grade,

                                'remarks' =>
                                    $record
                                        ->remarks,
                            ]
                        )
                        ->values()
                        ->all()
                    : [],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University Response
    |--------------------------------------------------------------------------
    */

    private function universityResponse(
        Request $request,
        User $user
    ): array {
        $university =
            $user->university;


        return [
            'id' =>
                $university->id,

            'name' =>
                $university->name,

            'acronym' =>
                $university->acronym,

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
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | General Registration
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


    private function isRotcStudent(
        User $user
    ): bool {
        return strtoupper(
            trim(
                (string)
                $user->component
            )
        )
        ===
        'ROTC';
    }


    private function generalRegistrationStatus(
        User $user
    ): string {
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


        if (
            $status ===
            'completed'
        ) {
            return 'completed';
        }


        if (
            $status ===
            'confirmed'
            ||
            $user->getAttribute(
                'confirmed_at'
            )
            !==
            null
        ) {
            return 'confirmed';
        }


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
            return $status;
        }


        if (
            filled(
                $user
                    ->signature_path
            )
        ) {
            return 'under_review';
        }


        if (
            $this->hasGeneralRegistration(
                $user
            )
        ) {
            return 'draft';
        }


        return 'not_started';
    }


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


    /*
    |--------------------------------------------------------------------------
    | Completed Response
    |--------------------------------------------------------------------------
    */

    private function completedResponse(
        RotcStudentProfile $profile
    ): JsonResponse {
        return response()->json([
            'success' =>
                true,

            'message' =>
                'Your ROTC enrollment has already been completed.',

            'next_screen' =>
                '/Registration/ROTC/RotcConfirmEnrollment',

            'rotc_profile' =>
                $this->profileResponse(
                    $profile
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Full Profile
    |--------------------------------------------------------------------------
    */

    private function profileResponse(
        RotcStudentProfile $profile
    ): array {
        $profile->loadMissing(
            'msRecords'
        );


        return [
            'id' =>
                $profile->id,

            'user_id' =>
                $profile->user_id,

            'status' =>
                $profile->status,

            'nstp_id_no' =>
                $profile->nstp_id_no,

            'ms_level' =>
                $profile->ms_level,

            'last_name' =>
                $profile->last_name,

            'first_name' =>
                $profile->first_name,

            'middle_name' =>
                $profile->middle_name,

            'name_extension' =>
                $profile->name_extension,

            'gender' =>
                $profile->gender,

            'blood_type' =>
                $profile->blood_type,

            'date_of_birth' =>
                $profile
                    ->date_of_birth
                    ?->format(
                        'Y-m-d'
                    ),

            'place_of_birth' =>
                $profile->place_of_birth,

            'height_cm' =>
                $profile->height_cm,

            'weight_kg' =>
                $profile->weight_kg,

            'complexion' =>
                $profile->complexion,

            'school_name' =>
                $profile->school_name,

            'course' =>
                $profile->course,

            'religion' =>
                $profile->religion,

            'cellphone_number' =>
                $profile
                    ->cellphone_number,

            'contact_email' =>
                $profile
                    ->contact_email,

            'temporary_address_line' =>
                $profile
                    ->temporary_address_line,

            'temporary_municipality' =>
                $profile
                    ->temporary_municipality,

            'temporary_province' =>
                $profile
                    ->temporary_province,

            'permanent_same_as_temporary' =>
                $profile
                    ->permanent_same_as_temporary,

            'permanent_address_line' =>
                $profile
                    ->permanent_address_line,

            'permanent_municipality' =>
                $profile
                    ->permanent_municipality,

            'permanent_province' =>
                $profile
                    ->permanent_province,

            'father_name' =>
                $profile
                    ->father_name,

            'father_occupation' =>
                $profile
                    ->father_occupation,

            'mother_name' =>
                $profile
                    ->mother_name,

            'mother_occupation' =>
                $profile
                    ->mother_occupation,

            'emergency_contact_name' =>
                $profile
                    ->emergency_contact_name,

            'emergency_contact_relationship' =>
                $profile
                    ->emergency_contact_relationship,

            'emergency_contact_number' =>
                $profile
                    ->emergency_contact_number,

            'emergency_contact_address' =>
                $profile
                    ->emergency_contact_address,

            'willing_advance_course' =>
                $profile
                    ->willing_advance_course,

            'submitted_at' =>
                $profile
                    ->submitted_at
                    ?->toIso8601String(),

            'completed_at' =>
                $profile
                    ->completed_at
                    ?->toIso8601String(),

            'ms_records' =>
                $profile
                    ->msRecords
                    ->map(
                        fn (
                            RotcMsRecord $record
                        ) => [
                            'id' =>
                                $record->id,

                            'record_order' =>
                                $record
                                    ->record_order,

                            'ms_level' =>
                                $record
                                    ->ms_level,

                            'semester' =>
                                $record
                                    ->semester,

                            'school_year' =>
                                $record
                                    ->school_year,

                            'grade' =>
                                $record
                                    ->grade,

                            'remarks' =>
                                $record
                                    ->remarks,
                        ]
                    )
                    ->values()
                    ->all(),
        ];
    }
}