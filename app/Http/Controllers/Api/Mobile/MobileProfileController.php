<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\RotcStudentProfile;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class MobileProfileController extends Controller
{
    private const TIMEZONE = 'Asia/Manila';

    private const PUBLIC_DISK = 'public';

    private const PRIVATE_DISK = 'local';

    private const SIGNATURE_CANVAS_WIDTH = 1000;

    private const SIGNATURE_CANVAS_HEIGHT = 400;

    private const COMPONENTS = [
        'CWTS',
        'LTS',
        'ROTC',
    ];


    /*
    |--------------------------------------------------------------------------
    | Show Student Profile
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $rotcProfile =
            $this->rotcProfileFor(
                $student
            );


        return response()->json(
            $this->responsePayload(
                $request,
                $student,
                $rotcProfile,
                'Student profile retrieved successfully.'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Student Profile
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $isRotc =
            $this->component(
                $student
            ) ===
            'ROTC';


        /*
        |--------------------------------------------------------------------------
        | Common Fields
        |--------------------------------------------------------------------------
        */

        $rules = [
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
                'max:20',
            ],

            'gender' => [
                'nullable',
                'string',
                'max:30',
            ],

            'date_of_birth' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'course' => [
                'nullable',
                'string',
                'max:255',
            ],

            'cellphone_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'temporary_address_line' => [
                'nullable',
                'string',
                'max:255',
            ],

            'temporary_municipality' => [
                'nullable',
                'string',
                'max:150',
            ],

            'temporary_province' => [
                'nullable',
                'string',
                'max:150',
            ],

            'emergency_contact_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'emergency_contact_relationship' => [
                'nullable',
                'string',
                'max:100',
            ],

            'emergency_contact_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'emergency_contact_address' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | ROTC Fields
        |--------------------------------------------------------------------------
        */

        if (
            $isRotc
        ) {
            $rules = [
                ...$rules,

                'blood_type' => [
                    'nullable',
                    'string',
                    'max:10',
                ],

                'place_of_birth' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'height_cm' => [
                    'nullable',
                    'numeric',
                    'between:50,250',
                ],

                'weight_kg' => [
                    'nullable',
                    'numeric',
                    'between:20,300',
                ],

                'complexion' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'school_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'religion' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'permanent_same_as_temporary' => [
                    'required',
                    'boolean',
                ],

                'permanent_address_line' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'permanent_municipality' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'permanent_province' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'father_name' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'father_occupation' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'mother_name' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'mother_occupation' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'willing_advance_course' => [
                    'required',
                    'boolean',
                ],
            ];
        }


        $validated =
            $request->validate(
                $rules
            );


        try {
            $rotcProfile =
                DB::transaction(
                    function () use (
                        $student,
                        $validated,
                        $isRotc
                    ): ?RotcStudentProfile {

                        /*
                        |--------------------------------------------------------------------------
                        | Shared User Fields
                        |--------------------------------------------------------------------------
                        */

                        $this->updateSharedUserFields(
                            $student,
                            $validated
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | CWTS / LTS
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$isRotc
                        ) {
                            return null;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | ROTC Profile
                        |--------------------------------------------------------------------------
                        */

                        $profile =
                            RotcStudentProfile::query()
                                ->where(
                                    'user_id',
                                    $student->id
                                )
                                ->lockForUpdate()
                                ->first();


                        if (
                            !$profile
                        ) {
                            $profile =
                                new RotcStudentProfile();


                            $profile->user_id =
                                $student->id;


                            $profile->nstp_id_no =
                                $this->studentIdNumber(
                                    $student,
                                    null
                                );


                            $profile->ms_level =
                                'MS 1';


                            $profile->status =
                                RotcStudentProfile::STATUS_DRAFT;
                        }


                        $profile->fill(
                            collect(
                                $validated
                            )
                                ->only([
                                    'last_name',
                                    'first_name',
                                    'middle_name',
                                    'name_extension',
                                    'gender',
                                    'blood_type',
                                    'date_of_birth',
                                    'place_of_birth',
                                    'height_cm',
                                    'weight_kg',
                                    'complexion',
                                    'school_name',
                                    'course',
                                    'religion',
                                    'cellphone_number',
                                    'contact_email',
                                    'temporary_address_line',
                                    'temporary_municipality',
                                    'temporary_province',
                                    'permanent_same_as_temporary',
                                    'permanent_address_line',
                                    'permanent_municipality',
                                    'permanent_province',
                                    'father_name',
                                    'father_occupation',
                                    'mother_name',
                                    'mother_occupation',
                                    'emergency_contact_name',
                                    'emergency_contact_relationship',
                                    'emergency_contact_number',
                                    'emergency_contact_address',
                                    'willing_advance_course',
                                ])
                                ->all()
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | Permanent Address
                        |--------------------------------------------------------------------------
                        */

                        if (
                            (
                                $validated[
                                    'permanent_same_as_temporary'
                                ]
                                ??
                                false
                            ) ===
                            true
                        ) {
                            $profile->permanent_address_line =
                                $validated[
                                    'temporary_address_line'
                                ]
                                ??
                                null;


                            $profile->permanent_municipality =
                                $validated[
                                    'temporary_municipality'
                                ]
                                ??
                                null;


                            $profile->permanent_province =
                                $validated[
                                    'temporary_province'
                                ]
                                ??
                                null;
                        }


                        $profile->save();


                        return $profile;
                    }
                );


            $student->refresh();


            if (
                $rotcProfile
            ) {
                $rotcProfile->load(
                    'msRecords'
                );
            }


            return response()->json(
                $this->responsePayload(
                    $request,
                    $student,
                    $rotcProfile,
                    'Your student information was updated successfully.'
                )
            );

        } catch (
            Throwable $throwable
        ) {
            report(
                $throwable
            );


            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your student information could not be updated.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Digital Signature
    |--------------------------------------------------------------------------
    |
    | Preferred request:
    |
    | {
    |     "paths": [
    |         "M 10 20 L 11 21 ...",
    |         "M 30 40 L 31 41 ..."
    |     ]
    | }
    |
    | This matches the actual finger-drawing signature pad.
    |
    | The old multipart "signature" image upload is still supported so older
    | mobile builds will not immediately break.
    |
    */

    public function updateSignature(
        Request $request
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Actual Drawn Signature
        |--------------------------------------------------------------------------
        */

        if (
            $request->has(
                'paths'
            )
        ) {
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
                    ],
                    [
                        'paths.required' =>
                            'Please draw your digital signature before saving.',

                        'paths.min' =>
                            'Please draw your digital signature before saving.',
                    ]
                );


            if (
                $validator->fails()
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Please provide a valid digital signature.',

                    'errors' =>
                        $validator->errors(),
                ], 422);
            }


            $safePaths = [];


            foreach (
                $validator
                    ->validated()[
                        'paths'
                    ]
                as $path
            ) {
                $path =
                    trim(
                        (string)
                        $path
                    );


                /*
                |--------------------------------------------------------------------------
                | SVG Path Whitelist
                |--------------------------------------------------------------------------
                */

                if (
                    $path ===
                    ''
                    ||
                    !preg_match(
                        '/^[MmLlHhVvCcSsQqTtAaZz0-9eE+\-.,\s]+$/',
                        $path
                    )
                ) {
                    return response()->json([
                        'success' =>
                            false,

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

            $svg =
                $this->buildSignatureSvg(
                    $safePaths
                );


            /*
            |--------------------------------------------------------------------------
            | Private Signature Path
            |--------------------------------------------------------------------------
            */

            $newPath =
                'signatures/profile'
                .
                '/'
                .
                $student->id
                .
                '/'
                .
                Str::uuid()
                .
                '.svg';


            $oldPath =
                trim(
                    (string) (
                        $student->signature_path
                        ??
                        ''
                    )
                );


            try {
                /*
                |--------------------------------------------------------------------------
                | Store Privately
                |--------------------------------------------------------------------------
                */

                Storage::disk(
                    self::PRIVATE_DISK
                )->put(
                    $newPath,
                    $svg
                );


                DB::transaction(
                    function () use (
                        $student,
                        $newPath
                    ): void {
                        $student->signature_path =
                            $newPath;


                        $student->save();
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Remove Previous Signature
                |--------------------------------------------------------------------------
                */

                $this->deletePreviousSignatureFile(
                    $oldPath,
                    $newPath
                );


                $student->refresh();


                return response()->json([
                    'success' =>
                        true,

                    'message' =>
                        'Your digital signature was updated successfully.',

                    'signature_data_uri' =>
                        $this->signatureDataUri(
                            $student->signature_path
                        ),

                    'signature_svg' =>
                        $this->signatureSvgContents(
                            $student->signature_path
                        ),

                    'signature_url' =>
                        $this->signaturePublicUrl(
                            $request,
                            $student->signature_path
                        ),
                ]);

            } catch (
                Throwable $throwable
            ) {
                if (
                    Storage::disk(
                        self::PRIVATE_DISK
                    )->exists(
                        $newPath
                    )
                ) {
                    Storage::disk(
                        self::PRIVATE_DISK
                    )->delete(
                        $newPath
                    );
                }


                report(
                    $throwable
                );


                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Your digital signature could not be updated.',
                ], 500);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Legacy Image Signature Upload
        |--------------------------------------------------------------------------
        */

        if (
            $request->hasFile(
                'signature'
            )
        ) {
            $request->validate([
                'signature' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],
            ], [
                'signature.required' =>
                    'Please select your digital signature image.',

                'signature.image' =>
                    'The selected signature must be an image.',

                'signature.mimes' =>
                    'The signature must be a JPG, PNG, or WEBP image.',

                'signature.max' =>
                    'The signature image must not be larger than 5 MB.',
            ]);


            $signature =
                $request->file(
                    'signature'
                );


            $oldPath =
                trim(
                    (string) (
                        $student->signature_path
                        ??
                        ''
                    )
                );


            $newPath =
                null;


            try {
                $newPath =
                    $signature->store(
                        'signatures/profile'
                        .
                        '/'
                        .
                        $student->id,

                        self::PRIVATE_DISK
                    );


                $student->signature_path =
                    $newPath;


                $student->save();


                $this->deletePreviousSignatureFile(
                    $oldPath,
                    $newPath
                );


                $student->refresh();


                return response()->json([
                    'success' =>
                        true,

                    'message' =>
                        'Your digital signature was updated successfully.',

                    'signature_data_uri' =>
                        $this->signatureDataUri(
                            $student->signature_path
                        ),

                    'signature_svg' =>
                        $this->signatureSvgContents(
                            $student->signature_path
                        ),

                    'signature_url' =>
                        $this->signaturePublicUrl(
                            $request,
                            $student->signature_path
                        ),
                ]);

            } catch (
                Throwable $throwable
            ) {
                if (
                    $newPath
                    &&
                    Storage::disk(
                        self::PRIVATE_DISK
                    )->exists(
                        $newPath
                    )
                ) {
                    Storage::disk(
                        self::PRIVATE_DISK
                    )->delete(
                        $newPath
                    );
                }


                report(
                    $throwable
                );


                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Your digital signature could not be updated.',
                ], 500);
            }
        }


        return response()->json([
            'success' =>
                false,

            'message' =>
                'Please draw your digital signature before saving.',
        ], 422);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Profile Photo
    |--------------------------------------------------------------------------
    */

    public function updateProfilePhoto(
        Request $request
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $request->validate([
            'profile_photo' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'profile_photo.required' =>
                'Please select a profile photo.',

            'profile_photo.image' =>
                'The selected profile photo must be an image.',

            'profile_photo.mimes' =>
                'The profile photo must be a JPG, PNG, or WEBP image.',

            'profile_photo.max' =>
                'The profile photo must not be larger than 5 MB.',
        ]);


        $photo =
            $request->file(
                'profile_photo'
            );


        $oldPath =
            trim(
                (string) (
                    $student->profile_photo
                    ??
                    ''
                )
            );


        $newPath =
            null;


        try {
            /*
            |--------------------------------------------------------------------------
            | Store Public Profile Photo
            |--------------------------------------------------------------------------
            */

            $newPath =
                $photo->store(
                    'student-profile-photos'
                    .
                    '/'
                    .
                    $student->id,

                    self::PUBLIC_DISK
                );


            $student->profile_photo =
                $newPath;


            $student->save();


            /*
            |--------------------------------------------------------------------------
            | Delete Old Photo
            |--------------------------------------------------------------------------
            */

            $this->deletePreviousFile(
                $oldPath,
                $newPath
            );


            $student->refresh();


            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Your profile photo was updated successfully.',

                'profile_photo_url' =>
                    $this->publicFileUrl(
                        $request,
                        $student->profile_photo
                    ),
            ]);

        } catch (
            Throwable $throwable
        ) {
            if (
                $newPath
                &&
                Storage::disk(
                    self::PUBLIC_DISK
                )->exists(
                    $newPath
                )
            ) {
                Storage::disk(
                    self::PUBLIC_DISK
                )->delete(
                    $newPath
                );
            }


            report(
                $throwable
            );


            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your profile photo could not be updated.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Shared User Fields
    |--------------------------------------------------------------------------
    */

    private function updateSharedUserFields(
        User $student,
        array $validated
    ): void {
        $student->surname =
            $validated[
                'last_name'
            ];


        $student->first_name =
            $validated[
                'first_name'
            ];


        $student->middle_name =
            $validated[
                'middle_name'
            ]
            ??
            null;


        $student->gender =
            $validated[
                'gender'
            ]
            ??
            null;


        $student->birth_date =
            $validated[
                'date_of_birth'
            ]
            ??
            null;


        if (
            array_key_exists(
                'course',
                $validated
            )
            &&
            $validated[
                'course'
            ] !==
            null
        ) {
            $student->course =
                $validated[
                    'course'
                ];
        }


        $student->contact_number =
            $validated[
                'cellphone_number'
            ]
            ??
            null;


        $student->city_address =
            $validated[
                'temporary_address_line'
            ]
            ??
            null;


        $student->municipality =
            $validated[
                'temporary_municipality'
            ]
            ??
            null;


        $student->province =
            $validated[
                'temporary_province'
            ]
            ??
            null;


        /*
        |--------------------------------------------------------------------------
        | Emergency Contact
        |--------------------------------------------------------------------------
        */

        $student->guardian_name =
            $validated[
                'emergency_contact_name'
            ]
            ??
            $student->guardian_name;


        $student->guardian_address =
            $validated[
                'emergency_contact_address'
            ]
            ??
            $student->guardian_address;


        $student->guardian_contact_number =
            $validated[
                'emergency_contact_number'
            ]
            ??
            $student->guardian_contact_number;


        $student->save();
    }


    /*
    |--------------------------------------------------------------------------
    | Response Payload
    |--------------------------------------------------------------------------
    */

    private function responsePayload(
        Request $request,
        User $student,
        ?RotcStudentProfile $rotcProfile,
        string $message
    ): array {
        $component =
            $this->component(
                $student
            );


        $isRotc =
            $component ===
            'ROTC';


        return [
            'success' =>
                true,

            'message' =>
                $message,

            'component' =>
                $component,

            'is_rotc' =>
                $isRotc,


            /*
            |--------------------------------------------------------------------------
            | Component Features
            |--------------------------------------------------------------------------
            */

            'features' => [
                'military_science_records' =>
                    $isRotc,

                'advance_course' =>
                    $isRotc,
            ],


            'student' =>
                $this->studentPayload(
                    $request,
                    $student,
                    $rotcProfile
                ),


            'profile' =>
                $this->profilePayload(
                    $student,
                    $rotcProfile
                ),


            /*
            |--------------------------------------------------------------------------
            | ROTC Military Science Records
            |--------------------------------------------------------------------------
            */

            'ms_records' =>
                $isRotc
                &&
                $rotcProfile
                    ? $rotcProfile
                        ->msRecords
                        ->map(
                            fn (
                                $record
                            ): array => [
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
                    : [],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticated NSTP Student
    |--------------------------------------------------------------------------
    */

    private function authenticatedStudent(
        Request $request
    ): User {
        $authenticated =
            $request->user();


        if (
            !$authenticated
            instanceof User
        ) {
            abort(
                403,
                'Only NSTP student accounts may access this page.'
            );
        }


        $student =
            User::query()
                ->with(
                    'university'
                )
                ->find(
                    $authenticated->id
                );


        if (
            !$student
        ) {
            abort(
                404,
                'Student account could not be found.'
            );
        }


        if (
            !$student->isStudent()
        ) {
            abort(
                403,
                'Only NSTP student accounts may access this page.'
            );
        }


        if (
            !in_array(
                $this->component(
                    $student
                ),
                self::COMPONENTS,
                true
            )
        ) {
            abort(
                422,
                'Your NSTP component must be CWTS, LTS, or ROTC before opening the profile.'
            );
        }


        return $student;
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Profile
    |--------------------------------------------------------------------------
    */

    private function rotcProfileFor(
        User $student
    ): ?RotcStudentProfile {
        if (
            $this->component(
                $student
            ) !==
            'ROTC'
        ) {
            return null;
        }


        return RotcStudentProfile::query()
            ->where(
                'user_id',
                $student->id
            )
            ->with(
                'msRecords'
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Student Payload
    |--------------------------------------------------------------------------
    */

    private function studentPayload(
        Request $request,
        User $student,
        ?RotcStudentProfile $rotcProfile
    ): array {
        return [
            'id' =>
                $student->id,


            'student_id_number' =>
                $this->studentIdNumber(
                    $student,
                    $rotcProfile
                ),


            'full_name' =>
                $this->profileFullName(
                    $student,
                    $rotcProfile
                ),


            'email' =>
                $student->email,


            'subject' =>
                $student->subject,


            'term' =>
                $student->term,


            'component' =>
                $this->component(
                    $student
                ),


            'course' =>
                $rotcProfile
                    ?->course
                ?:
                $student->course,


            'year_level' =>
                $student->year_level,


            'section' =>
                $student->section,


            /*
            |--------------------------------------------------------------------------
            | Profile Photo
            |--------------------------------------------------------------------------
            */

            'profile_photo_url' =>
                $this->publicFileUrl(
                    $request,
                    $student->profile_photo
                ),


            /*
            |--------------------------------------------------------------------------
            | Signature
            |--------------------------------------------------------------------------
            |
            | New signatures are private.
            |
            | signature_svg:
            | Best choice for React Native using SvgXml.
            |
            | signature_data_uri:
            | Kept for compatibility.
            |
            | signature_url:
            | Only available for an older public signature.
            |
            */

            'signature_url' =>
                $this->signaturePublicUrl(
                    $request,
                    $student->signature_path
                ),


            'signature_data_uri' =>
                $this->signatureDataUri(
                    $student->signature_path
                ),


            'signature_svg' =>
                $this->signatureSvgContents(
                    $student->signature_path
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Payload
    |--------------------------------------------------------------------------
    */

    private function profilePayload(
        User $student,
        ?RotcStudentProfile $rotcProfile
    ): array {
        $isRotc =
            $this->component(
                $student
            ) ===
            'ROTC';


        return [
            'profile_type' =>
                $isRotc
                    ? 'ROTC'
                    : 'GENERAL',


            'id' =>
                $rotcProfile
                    ?->id,


            'user_id' =>
                $student->id,


            'nstp_id_no' =>
                $this->studentIdNumber(
                    $student,
                    $rotcProfile
                ),


            'ms_level' =>
                $isRotc
                    ? (
                        $rotcProfile
                            ?->ms_level
                        ?:
                        'MS 1'
                    )
                    : null,


            'last_name' =>
                $rotcProfile
                    ?->last_name
                ?:
                $student->surname,


            'first_name' =>
                $rotcProfile
                    ?->first_name
                ?:
                $student->first_name,


            'middle_name' =>
                $rotcProfile
                    ?->middle_name
                ?:
                $student->middle_name,


            'name_extension' =>
                $rotcProfile
                    ?->name_extension,


            'gender' =>
                $rotcProfile
                    ?->gender
                ?:
                $student->gender,


            'blood_type' =>
                $isRotc
                    ? $rotcProfile
                        ?->blood_type
                    : null,


            'date_of_birth' =>
                $rotcProfile
                    ?->date_of_birth
                    ?->format(
                        'Y-m-d'
                    )
                ?:
                $student
                    ->birth_date
                    ?->format(
                        'Y-m-d'
                    ),


            'place_of_birth' =>
                $isRotc
                    ? $rotcProfile
                        ?->place_of_birth
                    : null,


            'height_cm' =>
                $isRotc
                    ? $rotcProfile
                        ?->height_cm
                    : null,


            'weight_kg' =>
                $isRotc
                    ? $rotcProfile
                        ?->weight_kg
                    : null,


            'complexion' =>
                $isRotc
                    ? $rotcProfile
                        ?->complexion
                    : null,


            'school_name' =>
                $rotcProfile
                    ?->school_name
                ?:
                $student
                    ->university
                    ?->name,


            'course' =>
                $rotcProfile
                    ?->course
                ?:
                $student->course,


            'religion' =>
                $isRotc
                    ? $rotcProfile
                        ?->religion
                    : null,


            'cellphone_number' =>
                $rotcProfile
                    ?->cellphone_number
                ?:
                $student->contact_number,


            /*
            |--------------------------------------------------------------------------
            | Contact Email
            |--------------------------------------------------------------------------
            |
            | This endpoint does not change the login email.
            |
            */

            'contact_email' =>
                $rotcProfile
                    ?->contact_email
                ?:
                $student->email,


            'temporary_address_line' =>
                $rotcProfile
                    ?->temporary_address_line
                ?:
                $student->city_address,


            'temporary_municipality' =>
                $rotcProfile
                    ?->temporary_municipality
                ?:
                $student->municipality,


            'temporary_province' =>
                $rotcProfile
                    ?->temporary_province
                ?:
                $student->province,


            /*
            |--------------------------------------------------------------------------
            | Permanent Address
            |--------------------------------------------------------------------------
            */

            'permanent_same_as_temporary' =>
                $isRotc
                    ? (bool) (
                        $rotcProfile
                            ?->permanent_same_as_temporary
                        ??
                        false
                    )
                    : true,


            'permanent_address_line' =>
                $isRotc
                    ? $rotcProfile
                        ?->permanent_address_line
                    : $student
                        ->city_address,


            'permanent_municipality' =>
                $isRotc
                    ? $rotcProfile
                        ?->permanent_municipality
                    : $student
                        ->municipality,


            'permanent_province' =>
                $isRotc
                    ? $rotcProfile
                        ?->permanent_province
                    : $student
                        ->province,


            /*
            |--------------------------------------------------------------------------
            | Parent Information
            |--------------------------------------------------------------------------
            */

            'father_name' =>
                $isRotc
                    ? $rotcProfile
                        ?->father_name
                    : null,


            'father_occupation' =>
                $isRotc
                    ? $rotcProfile
                        ?->father_occupation
                    : null,


            'mother_name' =>
                $isRotc
                    ? $rotcProfile
                        ?->mother_name
                    : null,


            'mother_occupation' =>
                $isRotc
                    ? $rotcProfile
                        ?->mother_occupation
                    : null,


            /*
            |--------------------------------------------------------------------------
            | Emergency Contact
            |--------------------------------------------------------------------------
            */

            'emergency_contact_name' =>
                $rotcProfile
                    ?->emergency_contact_name
                ?:
                $student
                    ->guardian_name,


            'emergency_contact_relationship' =>
                $rotcProfile
                    ?->emergency_contact_relationship
                ?:
                (
                    $student
                        ->guardian_name
                        ? 'Parent / Guardian'
                        : null
                ),


            'emergency_contact_number' =>
                $rotcProfile
                    ?->emergency_contact_number
                ?:
                $student
                    ->guardian_contact_number,


            'emergency_contact_address' =>
                $rotcProfile
                    ?->emergency_contact_address
                ?:
                $student
                    ->guardian_address,


            /*
            |--------------------------------------------------------------------------
            | Advance Course
            |--------------------------------------------------------------------------
            */

            'willing_advance_course' =>
                $isRotc
                    ? (bool) (
                        $rotcProfile
                            ?->willing_advance_course
                        ??
                        false
                    )
                    : false,


            'status' =>
                $isRotc
                    ? (
                        $rotcProfile
                            ?->status
                        ?:
                        RotcStudentProfile::STATUS_DRAFT
                    )
                    : null,


            'submitted_at' =>
                $isRotc
                    ? $rotcProfile
                        ?->submitted_at
                        ?->timezone(
                            self::TIMEZONE
                        )
                        ?->toIso8601String()
                    : null,


            'completed_at' =>
                $isRotc
                    ? $rotcProfile
                        ?->completed_at
                        ?->timezone(
                            self::TIMEZONE
                        )
                        ?->toIso8601String()
                    : null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Full Name
    |--------------------------------------------------------------------------
    */

    private function profileFullName(
        User $student,
        ?RotcStudentProfile $rotcProfile
    ): string {
        $last =
            trim(
                (string) (
                    $rotcProfile
                        ?->last_name
                    ?:
                    $student->surname
                )
            );


        $first =
            trim(
                (string) (
                    $rotcProfile
                        ?->first_name
                    ?:
                    $student->first_name
                )
            );


        $middle =
            trim(
                (string) (
                    $rotcProfile
                        ?->middle_name
                    ?:
                    $student->middle_name
                )
            );


        $extension =
            trim(
                (string) (
                    $rotcProfile
                        ?->name_extension
                    ??
                    ''
                )
            );


        $given =
            collect([
                $first,
                $middle,
                $extension,
            ])
                ->filter()
                ->implode(
                    ' '
                );


        if (
            $last !==
            ''
            &&
            $given !==
            ''
        ) {
            return (
                $last
                .
                ', '
                .
                $given
            );
        }


        return $student->full_name;
    }


    /*
    |--------------------------------------------------------------------------
    | Student ID
    |--------------------------------------------------------------------------
    */

    private function studentIdNumber(
        User $student,
        ?RotcStudentProfile $rotcProfile
    ): string {
        $profileId =
            trim(
                (string) (
                    $rotcProfile
                        ?->nstp_id_no
                    ??
                    ''
                )
            );


        if (
            $profileId !==
            ''
        ) {
            return $profileId;
        }


        $userId =
            trim(
                (string) (
                    $student
                        ->student_id_number
                    ??
                    ''
                )
            );


        if (
            $userId !==
            ''
        ) {
            return $userId;
        }


        return sprintf(
            'NSTP-%s-%05d',
            CarbonImmutable::now(
                self::TIMEZONE
            )->format(
                'y'
            ),
            $student->id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Component
    |--------------------------------------------------------------------------
    */

    private function component(
        User $student
    ): string {
        return strtoupper(
            trim(
                (string) (
                    $student->component
                    ??
                    ''
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Build Signature SVG
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | React Native draws using:
    |
    | 1000 x 400
    |
    | Laravel must use exactly the same virtual canvas.
    |
    */

    private function buildSignatureSvg(
        array $paths
    ): string {
        $svgPaths =
            collect(
                $paths
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


                        return (
                            '<path d="'
                            .
                            $escaped
                            .
                            '" stroke="#233E47" stroke-width="6" '
                            .
                            'stroke-linecap="round" stroke-linejoin="round" '
                            .
                            'fill="none" />'
                        );
                    }
                )
                ->implode(
                    PHP_EOL
                );


        return (
            '<?xml version="1.0" encoding="UTF-8"?>'
            .
            PHP_EOL
            .
            '<svg xmlns="http://www.w3.org/2000/svg" '
            .
            'viewBox="0 0 '
            .
            self::SIGNATURE_CANVAS_WIDTH
            .
            ' '
            .
            self::SIGNATURE_CANVAS_HEIGHT
            .
            '" width="'
            .
            self::SIGNATURE_CANVAS_WIDTH
            .
            '" height="'
            .
            self::SIGNATURE_CANVAS_HEIGHT
            .
            '" preserveAspectRatio="none">'
            .
            PHP_EOL
            .
            $svgPaths
            .
            PHP_EOL
            .
            '</svg>'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Previous Profile Photo
    |--------------------------------------------------------------------------
    */

    private function deletePreviousFile(
        ?string $oldPath,
        ?string $newPath
    ): void {
        $oldPath =
            trim(
                (string)
                $oldPath
            );


        if (
            $oldPath ===
            ''
            ||
            $oldPath ===
            $newPath
            ||
            str_starts_with(
                $oldPath,
                'http://'
            )
            ||
            str_starts_with(
                $oldPath,
                'https://'
            )
            ||
            str_starts_with(
                $oldPath,
                'data:'
            )
        ) {
            return;
        }


        $normalized =
            $this->normalizeStoredPath(
                $oldPath
            );


        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        |
        | Only delete files from the profile-photo directory.
        |
        */

        if (
            $normalized ===
            ''
            ||
            !str_starts_with(
                $normalized,
                'student-profile-photos/'
            )
        ) {
            return;
        }


        $disk =
            Storage::disk(
                self::PUBLIC_DISK
            );


        if (
            $disk->exists(
                $normalized
            )
        ) {
            $disk->delete(
                $normalized
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Previous Signature
    |--------------------------------------------------------------------------
    |
    | Supports:
    |
    | signatures/general/...   old registration signature
    | signatures/profile/...   new profile signature
    | student-signatures/...   old public image signature
    |
    */

    private function deletePreviousSignatureFile(
        ?string $oldPath,
        ?string $newPath
    ): void {
        $oldPath =
            trim(
                (string)
                $oldPath
            );


        if (
            $oldPath ===
            ''
            ||
            $oldPath ===
            $newPath
            ||
            str_starts_with(
                $oldPath,
                'http://'
            )
            ||
            str_starts_with(
                $oldPath,
                'https://'
            )
            ||
            str_starts_with(
                $oldPath,
                'data:'
            )
        ) {
            return;
        }


        $normalized =
            $this->normalizeStoredPath(
                $oldPath
            );


        if (
            $normalized ===
            ''
            ||
            (
                !str_starts_with(
                    $normalized,
                    'signatures/'
                )
                &&
                !str_starts_with(
                    $normalized,
                    'student-signatures/'
                )
            )
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Try Private and Public Storage
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                self::PRIVATE_DISK,
                self::PUBLIC_DISK,
            ]
            as $diskName
        ) {
            $disk =
                Storage::disk(
                    $diskName
                );


            if (
                $disk->exists(
                    $normalized
                )
            ) {
                $disk->delete(
                    $normalized
                );


                return;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Stored File Path
    |--------------------------------------------------------------------------
    */

    private function normalizeStoredPath(
        ?string $path
    ): string {
        $path =
            trim(
                (string)
                $path
            );


        if (
            $path ===
            ''
        ) {
            return '';
        }


        $normalized =
            preg_replace(
                '#^/?storage/#',
                '',
                $path
            );


        return ltrim(
            (string)
            $normalized,
            '/'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Public File URL
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Do NOT use:
    |
    | Storage::disk('public')->url(...)
    |
    | because Laravel can build that URL from APP_URL.
    |
    | If APP_URL is:
    |
    | http://127.0.0.1:8000
    |
    | the Android phone cannot use it.
    |
    | We instead use the host from the actual API request.
    |
    */

    private function publicFileUrl(
        Request $request,
        ?string $path
    ): ?string {
        $path =
            trim(
                (string)
                $path
            );


        if (
            $path ===
            ''
        ) {
            return null;
        }


        if (
            str_starts_with(
                $path,
                'data:'
            )
        ) {
            return $path;
        }


        if (
            str_starts_with(
                $path,
                'http://'
            )
            ||
            str_starts_with(
                $path,
                'https://'
            )
        ) {
            return $path;
        }


        $normalized =
            $this->normalizeStoredPath(
                $path
            );


        if (
            $normalized ===
            ''
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Example Result
        |--------------------------------------------------------------------------
        |
        | http://192.168.254.117:8000/storage/student-profile-photos/3/image.jpg
        |
        */

        return (
            rtrim(
                $request
                    ->getSchemeAndHttpHost(),
                '/'
            )
            .
            '/storage/'
            .
            ltrim(
                $normalized,
                '/'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Signature Public URL
    |--------------------------------------------------------------------------
    |
    | New signatures are PRIVATE.
    |
    | This only returns a URL if an older signature still exists on the
    | public disk.
    |
    */

    private function signaturePublicUrl(
        Request $request,
        ?string $path
    ): ?string {
        $path =
            trim(
                (string)
                $path
            );


        if (
            $path ===
            ''
            ||
            str_starts_with(
                $path,
                'data:'
            )
        ) {
            return null;
        }


        if (
            str_starts_with(
                $path,
                'http://'
            )
            ||
            str_starts_with(
                $path,
                'https://'
            )
        ) {
            return $path;
        }


        $normalized =
            $this->normalizeStoredPath(
                $path
            );


        if (
            $normalized ===
            ''
        ) {
            return null;
        }


        if (
            Storage::disk(
                self::PUBLIC_DISK
            )->exists(
                $normalized
            )
        ) {
            return $this->publicFileUrl(
                $request,
                $normalized
            );
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Signature SVG Contents
    |--------------------------------------------------------------------------
    |
    | React Native <Image> does not reliably render SVG data URIs.
    |
    | The updated mobile Profile screen can use:
    |
    | <SvgXml xml={student.signature_svg} />
    |
    */

    private function signatureSvgContents(
        ?string $path
    ): ?string {
        $path =
            trim(
                (string)
                $path
            );


        if (
            $path ===
            ''
            ||
            str_starts_with(
                $path,
                'http://'
            )
            ||
            str_starts_with(
                $path,
                'https://'
            )
            ||
            str_starts_with(
                $path,
                'data:'
            )
        ) {
            return null;
        }


        $normalized =
            $this->normalizeStoredPath(
                $path
            );


        if (
            $normalized ===
            ''
            ||
            !str_ends_with(
                strtolower(
                    $normalized
                ),
                '.svg'
            )
        ) {
            return null;
        }


        foreach (
            [
                self::PRIVATE_DISK,
                self::PUBLIC_DISK,
            ]
            as $diskName
        ) {
            $disk =
                Storage::disk(
                    $diskName
                );


            if (
                !$disk->exists(
                    $normalized
                )
            ) {
                continue;
            }


            try {
                return $this->repairLegacySignatureSvg(
                    $disk->get(
                        $normalized
                    )
                );

            } catch (
                Throwable
            ) {
                return null;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Signature Data URI
    |--------------------------------------------------------------------------
    |
    | Signatures are read from private storage and returned only through the
    | authenticated profile API.
    |
    | Private disk is checked first.
    |
    | Public disk is checked second for compatibility with older signatures.
    |
    */

    private function signatureDataUri(
        ?string $path
    ): ?string {
        $path =
            trim(
                (string)
                $path
            );


        if (
            $path ===
            ''
        ) {
            return null;
        }


        if (
            str_starts_with(
                $path,
                'data:image/'
            )
        ) {
            return $path;
        }


        if (
            str_starts_with(
                $path,
                'http://'
            )
            ||
            str_starts_with(
                $path,
                'https://'
            )
        ) {
            return null;
        }


        $normalized =
            $this->normalizeStoredPath(
                $path
            );


        if (
            $normalized ===
            ''
        ) {
            return null;
        }


        foreach (
            [
                self::PRIVATE_DISK,
                self::PUBLIC_DISK,
            ]
            as $diskName
        ) {
            $disk =
                Storage::disk(
                    $diskName
                );


            if (
                !$disk->exists(
                    $normalized
                )
            ) {
                continue;
            }


            try {
                $contents =
                    $disk->get(
                        $normalized
                    );


                /*
                |--------------------------------------------------------------------------
                | Repair Old SVG
                |--------------------------------------------------------------------------
                */

                if (
                    str_ends_with(
                        strtolower(
                            $normalized
                        ),
                        '.svg'
                    )
                ) {
                    $contents =
                        $this->repairLegacySignatureSvg(
                            $contents
                        );
                }


                $mime =
                    $disk->mimeType(
                        $normalized
                    );


                if (
                    !$mime
                ) {
                    $mime =
                        str_ends_with(
                            strtolower(
                                $normalized
                            ),
                            '.svg'
                        )
                            ? 'image/svg+xml'
                            : 'image/png';
                }


                return (
                    'data:'
                    .
                    $mime
                    .
                    ';base64,'
                    .
                    base64_encode(
                        $contents
                    )
                );

            } catch (
                Throwable
            ) {
                return null;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Repair Legacy Registration Signature SVG
    |--------------------------------------------------------------------------
    |
    | Old backend:
    |
    | viewBox="0 0 400 200"
    |
    | Actual React Native drawing coordinates:
    |
    | 1000 x 400
    |
    | This repairs existing signatures when they are read so students do not
    | need to sign again just because the previous canvas size was wrong.
    |
    */

    private function repairLegacySignatureSvg(
        string $svg
    ): string {
        /*
        |--------------------------------------------------------------------------
        | ViewBox
        |--------------------------------------------------------------------------
        */

        $svg =
            preg_replace(
                '/viewBox="0\s+0\s+400\s+200"/i',
                'viewBox="0 0 1000 400"',
                $svg
            )
            ??
            $svg;


        /*
        |--------------------------------------------------------------------------
        | Width / Height
        |--------------------------------------------------------------------------
        */

        $svg =
            preg_replace(
                '/width="400"\s+height="200"/i',
                'width="1000" height="400"',
                $svg
            )
            ??
            $svg;


        /*
        |--------------------------------------------------------------------------
        | Old White Background Rectangle
        |--------------------------------------------------------------------------
        */

        $svg =
            preg_replace(
                '/<rect\s+width="400"\s+height="200"/i',
                '<rect width="1000" height="400"',
                $svg
            )
            ??
            $svg;


        return $svg;
    }
}