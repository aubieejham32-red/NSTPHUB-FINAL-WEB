<?php

namespace App\Http\Controllers\UniversityAdmin\Users;

use App\Http\Controllers\Controller;
use App\Models\RotcMsRecord;
use App\Models\RotcStudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use stdClass;

class StudentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Approved Registration Statuses
    |--------------------------------------------------------------------------
    */

    private const APPROVED_REGISTRATION_STATUSES = [
        'approved',
        'confirmed',
        'completed',
    ];


    /*
    |--------------------------------------------------------------------------
    | List Enrolled Students
    |--------------------------------------------------------------------------
    |
    | GET /university-admin/users/students
    |
    */

    public function index(): Response
    {
        $universityId =
            $this->currentUniversityId();

        $students =
            User::query()
                ->where(
                    'university_id',
                    $universityId
                )
                ->whereIn(
                    'registration_status',
                    self::APPROVED_REGISTRATION_STATUSES
                )
                ->orderBy(
                    'surname'
                )
                ->orderBy(
                    'first_name'
                )
                ->get()
                ->map(
                    function (
                        User $student
                    ): array {
                        return [
                            'id' =>
                                $student->id,

                            'student_id_number' =>
                                $student->getAttribute(
                                    'student_id_number'
                                ),

                            'id_number' =>
                                $student->getAttribute(
                                    'student_id_number'
                                ),

                            'full_name' =>
                                $student->full_name,

                            'surname' =>
                                $student->surname,

                            'first_name' =>
                                $student->first_name,

                            'middle_name' =>
                                $student->middle_name,

                            'course' =>
                                $student->course,

                            'year_level' =>
                                $student->year_level,

                            'section' =>
                                $student->section,

                            'year_section' =>
                                $this->yearSection(
                                    $student
                                ),

                            'component' =>
                                strtoupper(
                                    trim(
                                        (string)
                                        $student->component
                                    )
                                ),

                            'subject' =>
                                $student->subject,

                            'term' =>
                                $student->term,

                            'registration_status' =>
                                $student->getAttribute(
                                    'registration_status'
                                ),

                            'status' =>
                                $this->studentStatus(
                                    $student
                                ),
                        ];
                    }
                )
                ->values();

        return Inertia::render(
            'UniversityAdmin/Users/Students/ListOfStudents',
            [
                'students' =>
                    $students,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | View Student Profile
    |--------------------------------------------------------------------------
    |
    | GET /university-admin/users/students/{student}
    |
    | Vue:
    | StudentProfileInfo.vue
    |
    */

    public function show(
        int $student
    ): Response {
        $studentRecord =
            $this->approvedStudent(
                $student
            );

        return Inertia::render(
            'UniversityAdmin/Users/Students/StudentProfileInfo',
            $this->profilePageData(
                $studentRecord
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Student Profile
    |--------------------------------------------------------------------------
    |
    | GET /university-admin/users/students/{student}/edit
    |
    | Vue:
    | EditableProfileInfo.vue
    |
    */

    public function edit(
        int $student
    ): Response {
        $studentRecord =
            $this->approvedStudent(
                $student
            );

        return Inertia::render(
            'UniversityAdmin/Users/Students/EditableProfileInfo',
            $this->profilePageData(
                $studentRecord
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Student Profile
    |--------------------------------------------------------------------------
    |
    | PUT /university-admin/users/students/{student}
    |
    | PROTECTED:
    |
    | student_id_number
    | qr_token
    | signature_path
    | university_id
    |
    | These fields are intentionally never accepted from the request.
    |
    */

    public function update(
        Request $request,
        int $student
    ): RedirectResponse {
        $studentRecord =
            $this->approvedStudent(
                $student
            );

        $validated =
            $request->validate([

                /*
                |--------------------------------------------------------------------------
                | General Information
                |--------------------------------------------------------------------------
                */

                'surname' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'first_name' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'middle_name' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',

                    Rule::unique(
                        'users',
                        'email'
                    )
                        ->ignore(
                            $studentRecord->id
                        ),
                ],

                'course' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'year_level' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'section' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'component' => [
                    'required',

                    Rule::in([
                        'CWTS',
                        'LTS',
                        'ROTC',
                    ]),
                ],

                'subject' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'term' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'gender' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'birth_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                ],

                'contact_number' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'city_address' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'municipality' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'province' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'guardian_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'guardian_contact_number' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'guardian_address' => [
                    'nullable',
                    'string',
                    'max:500',
                ],


                /*
                |--------------------------------------------------------------------------
                | Student Status
                |--------------------------------------------------------------------------
                */

                'status' => [
                    'nullable',

                    Rule::in([
                        'ACTIVE',
                        'WARNING FOR DROPOUT',
                        'DROPOUT',
                    ]),
                ],


                /*
                |--------------------------------------------------------------------------
                | ROTC
                |--------------------------------------------------------------------------
                */

                'rotc' => [
                    'nullable',
                    'array',
                ],

                'rotc.place_of_birth' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'rotc.blood_type' => [
                    'nullable',
                    'string',
                    'max:10',
                ],

                'rotc.weight_kg' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:500',
                ],

                'rotc.height_cm' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:300',
                ],

                'rotc.complexion' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'rotc.religion' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'rotc.ms_level' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'rotc.nstp_id_no' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'rotc.cellphone_number' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'rotc.contact_email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'rotc.school_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],


                /*
                |--------------------------------------------------------------------------
                | Temporary Address
                |--------------------------------------------------------------------------
                */

                'rotc.temporary_address_line' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'rotc.temporary_municipality' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'rotc.temporary_province' => [
                    'nullable',
                    'string',
                    'max:150',
                ],


                /*
                |--------------------------------------------------------------------------
                | Permanent Address
                |--------------------------------------------------------------------------
                */

                'rotc.permanent_same_as_temporary' => [
                    'nullable',
                    'boolean',
                ],

                'rotc.permanent_address_line' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'rotc.permanent_municipality' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'rotc.permanent_province' => [
                    'nullable',
                    'string',
                    'max:150',
                ],


                /*
                |--------------------------------------------------------------------------
                | Parents
                |--------------------------------------------------------------------------
                */

                'rotc.father_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'rotc.father_occupation' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'rotc.mother_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'rotc.mother_occupation' => [
                    'nullable',
                    'string',
                    'max:255',
                ],


                /*
                |--------------------------------------------------------------------------
                | Emergency Contact
                |--------------------------------------------------------------------------
                */

                'rotc.emergency_contact_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'rotc.emergency_contact_relationship' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'rotc.emergency_contact_address' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'rotc.emergency_contact_number' => [
                    'nullable',
                    'string',
                    'max:30',
                ],


                /*
                |--------------------------------------------------------------------------
                | Advance Course
                |--------------------------------------------------------------------------
                */

                'rotc.willing_advance_course' => [
                    'nullable',
                    'boolean',
                ],


                /*
                |--------------------------------------------------------------------------
                | Military Science Records
                |--------------------------------------------------------------------------
                */

                'military_records' => [
                    'nullable',
                    'array',
                    'max:30',
                ],

                'military_records.*.id' => [
                    'nullable',
                    'integer',
                ],

                'military_records.*.record_order' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'military_records.*.ms_level' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'military_records.*.semester' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'military_records.*.school_year' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'military_records.*.grade' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100',
                ],

                'military_records.*.remarks' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ]);


        DB::transaction(
            function () use (
                $studentRecord,
                $validated
            ): void {

                /*
                |--------------------------------------------------------------------------
                | General Student Record
                |--------------------------------------------------------------------------
                */

                $surname =
                    trim(
                        (string)
                        (
                            $validated['surname']
                            ??
                            ''
                        )
                    );


                $firstName =
                    trim(
                        (string)
                        (
                            $validated['first_name']
                            ??
                            ''
                        )
                    );


                $middleName =
                    trim(
                        (string)
                        (
                            $validated['middle_name']
                            ??
                            ''
                        )
                    );


                $displayName =
                    collect([
                        $firstName,
                        $middleName,
                        $surname,
                    ])
                        ->filter(
                            fn (
                                $value
                            ) =>
                                filled(
                                    $value
                                )
                        )
                        ->implode(
                            ' '
                        );


                /*
                |--------------------------------------------------------------------------
                | Protected fields are intentionally NOT here:
                |--------------------------------------------------------------------------
                |
                | student_id_number
                | qr_token
                | signature_path
                | university_id
                |
                */

                $studentRecord->forceFill([

                    'name' =>
                        $displayName !== ''
                            ? $displayName
                            : $studentRecord->name,

                    'surname' =>
                        $surname,

                    'first_name' =>
                        $firstName,

                    'middle_name' =>
                        $middleName,

                    'email' =>
                        $validated['email'],

                    'course' =>
                        $validated['course']
                        ??
                        null,

                    'year_level' =>
                        $validated['year_level']
                        ??
                        null,

                    'section' =>
                        $validated['section']
                        ??
                        null,

                    'component' =>
                        strtoupper(
                            trim(
                                (string)
                                $validated['component']
                            )
                        ),

                    'subject' =>
                        $validated['subject']
                        ??
                        null,

                    'term' =>
                        $validated['term']
                        ??
                        null,

                    'gender' =>
                        $validated['gender']
                        ??
                        null,

                    'birth_date' =>
                        $validated['birth_date']
                        ??
                        null,

                    'contact_number' =>
                        $validated['contact_number']
                        ??
                        null,

                    'city_address' =>
                        $validated['city_address']
                        ??
                        null,

                    'municipality' =>
                        $validated['municipality']
                        ??
                        null,

                    'province' =>
                        $validated['province']
                        ??
                        null,

                    'guardian_name' =>
                        $validated['guardian_name']
                        ??
                        null,

                    'guardian_contact_number' =>
                        $validated['guardian_contact_number']
                        ??
                        null,

                    'guardian_address' =>
                        $validated['guardian_address']
                        ??
                        null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Student Status
                |--------------------------------------------------------------------------
                |
                | registration_status is NOT changed here.
                |
                | This prevents an approved/enrolled student from disappearing
                | from ListOfStudents.vue.
                |
                */

                $this->writeStudentStatus(
                    $studentRecord,
                    $validated['status']
                    ??
                    null
                );


                $studentRecord->save();


                /*
                |--------------------------------------------------------------------------
                | ROTC Only
                |--------------------------------------------------------------------------
                |
                | CWTS and LTS students stop here.
                |
                */

                if (
                    strtoupper(
                        trim(
                            (string)
                            $studentRecord->component
                        )
                    )
                    !==
                    'ROTC'
                ) {
                    return;
                }


                $rotc =
                    $validated['rotc']
                    ??
                    [];


                $rotcProfile =
                    RotcStudentProfile::query()
                        ->firstOrNew([
                            'user_id' =>
                                $studentRecord->id,
                        ]);


                if (
                    !$rotcProfile->exists
                ) {
                    $rotcProfile->status =
                        RotcStudentProfile::STATUS_DRAFT;
                }


                $sameAsTemporary =
                    (bool)
                    (
                        $rotc[
                            'permanent_same_as_temporary'
                        ]
                        ??
                        false
                    );


                $temporaryAddressLine =
                    $rotc[
                        'temporary_address_line'
                    ]
                    ??
                    null;


                $temporaryMunicipality =
                    $rotc[
                        'temporary_municipality'
                    ]
                    ??
                    null;


                $temporaryProvince =
                    $rotc[
                        'temporary_province'
                    ]
                    ??
                    null;


                $rotcProfile->fill([

                    /*
                    |--------------------------------------------------------------------------
                    | Student
                    |--------------------------------------------------------------------------
                    */

                    'user_id' =>
                        $studentRecord->id,


                    /*
                    |--------------------------------------------------------------------------
                    | ROTC Identification
                    |--------------------------------------------------------------------------
                    */

                    'nstp_id_no' =>
                        $rotc['nstp_id_no']
                        ??
                        null,

                    'ms_level' =>
                        $rotc['ms_level']
                        ??
                        null,


                    /*
                    |--------------------------------------------------------------------------
                    | Name
                    |--------------------------------------------------------------------------
                    */

                    'last_name' =>
                        $studentRecord->surname,

                    'first_name' =>
                        $studentRecord->first_name,

                    'middle_name' =>
                        $studentRecord->middle_name,


                    /*
                    |--------------------------------------------------------------------------
                    | Personal
                    |--------------------------------------------------------------------------
                    */

                    'gender' =>
                        $studentRecord->gender,

                    'blood_type' =>
                        $rotc['blood_type']
                        ??
                        null,

                    'date_of_birth' =>
                        $studentRecord->birth_date,

                    'place_of_birth' =>
                        $rotc['place_of_birth']
                        ??
                        null,


                    /*
                    |--------------------------------------------------------------------------
                    | Physical
                    |--------------------------------------------------------------------------
                    */

                    'height_cm' =>
                        $rotc['height_cm']
                        ??
                        null,

                    'weight_kg' =>
                        $rotc['weight_kg']
                        ??
                        null,

                    'complexion' =>
                        $rotc['complexion']
                        ??
                        null,


                    /*
                    |--------------------------------------------------------------------------
                    | Academic
                    |--------------------------------------------------------------------------
                    */

                    'school_name' =>
                        $rotc['school_name']
                        ??
                        $studentRecord
                            ->university
                            ?->getAttribute(
                                'name'
                            ),

                    'course' =>
                        $studentRecord->course,

                    'religion' =>
                        $rotc['religion']
                        ??
                        null,


                    /*
                    |--------------------------------------------------------------------------
                    | Contact
                    |--------------------------------------------------------------------------
                    */

                    'cellphone_number' =>
                        $rotc['cellphone_number']
                        ??
                        $studentRecord->contact_number,

                    'contact_email' =>
                        $rotc['contact_email']
                        ??
                        $studentRecord->email,


                    /*
                    |--------------------------------------------------------------------------
                    | Temporary Address
                    |--------------------------------------------------------------------------
                    */

                    'temporary_address_line' =>
                        $temporaryAddressLine,

                    'temporary_municipality' =>
                        $temporaryMunicipality,

                    'temporary_province' =>
                        $temporaryProvince,


                    /*
                    |--------------------------------------------------------------------------
                    | Permanent Address
                    |--------------------------------------------------------------------------
                    */

                    'permanent_same_as_temporary' =>
                        $sameAsTemporary,

                    'permanent_address_line' =>
                        $sameAsTemporary
                            ? $temporaryAddressLine
                            : (
                                $rotc[
                                    'permanent_address_line'
                                ]
                                ??
                                null
                            ),

                    'permanent_municipality' =>
                        $sameAsTemporary
                            ? $temporaryMunicipality
                            : (
                                $rotc[
                                    'permanent_municipality'
                                ]
                                ??
                                null
                            ),

                    'permanent_province' =>
                        $sameAsTemporary
                            ? $temporaryProvince
                            : (
                                $rotc[
                                    'permanent_province'
                                ]
                                ??
                                null
                            ),


                    /*
                    |--------------------------------------------------------------------------
                    | Parents
                    |--------------------------------------------------------------------------
                    */

                    'father_name' =>
                        $rotc['father_name']
                        ??
                        null,

                    'father_occupation' =>
                        $rotc['father_occupation']
                        ??
                        null,

                    'mother_name' =>
                        $rotc['mother_name']
                        ??
                        null,

                    'mother_occupation' =>
                        $rotc['mother_occupation']
                        ??
                        null,


                    /*
                    |--------------------------------------------------------------------------
                    | Emergency Contact
                    |--------------------------------------------------------------------------
                    */

                    'emergency_contact_name' =>
                        $rotc[
                            'emergency_contact_name'
                        ]
                        ??
                        null,

                    'emergency_contact_relationship' =>
                        $rotc[
                            'emergency_contact_relationship'
                        ]
                        ??
                        null,

                    'emergency_contact_address' =>
                        $rotc[
                            'emergency_contact_address'
                        ]
                        ??
                        null,

                    'emergency_contact_number' =>
                        $rotc[
                            'emergency_contact_number'
                        ]
                        ??
                        null,


                    /*
                    |--------------------------------------------------------------------------
                    | Advance Course
                    |--------------------------------------------------------------------------
                    */

                    'willing_advance_course' =>
                        (bool)
                        (
                            $rotc[
                                'willing_advance_course'
                            ]
                            ??
                            false
                        ),
                ]);


                $rotcProfile->save();


                /*
                |--------------------------------------------------------------------------
                | Military Science Records
                |--------------------------------------------------------------------------
                */

                foreach (
                    (
                        $validated[
                            'military_records'
                        ]
                        ??
                        []
                    )
                    as
                    $index =>
                    $recordData
                ) {

                    $hasRecordData =
                        collect([
                            $recordData[
                                'ms_level'
                            ]
                            ??
                            null,

                            $recordData[
                                'semester'
                            ]
                            ??
                            null,

                            $recordData[
                                'school_year'
                            ]
                            ??
                            null,

                            $recordData[
                                'grade'
                            ]
                            ??
                            null,

                            $recordData[
                                'remarks'
                            ]
                            ??
                            null,
                        ])
                            ->contains(
                                fn (
                                    $value
                                ) =>
                                    $value !== null
                                    &&
                                    $value !== ''
                            );


                    if (
                        !$hasRecordData
                    ) {
                        continue;
                    }


                    $record =
                        null;


                    /*
                    |--------------------------------------------------------------------------
                    | Existing Record Security
                    |--------------------------------------------------------------------------
                    |
                    | Only update a military record belonging to the current
                    | student's ROTC profile.
                    |
                    */

                    if (
                        !empty(
                            $recordData['id']
                        )
                    ) {

                        $record =
                            RotcMsRecord::query()
                                ->where(
                                    'rotc_student_profile_id',
                                    $rotcProfile->id
                                )
                                ->whereKey(
                                    $recordData['id']
                                )
                                ->first();
                    }


                    if (
                        !$record
                    ) {

                        $record =
                            new RotcMsRecord();

                        $record
                            ->rotc_student_profile_id =
                                $rotcProfile->id;
                    }


                    $record->fill([

                        'record_order' =>
                            $recordData[
                                'record_order'
                            ]
                            ??
                            $index + 1,

                        'ms_level' =>
                            $recordData[
                                'ms_level'
                            ]
                            ??
                            null,

                        'semester' =>
                            $recordData[
                                'semester'
                            ]
                            ??
                            null,

                        'school_year' =>
                            $recordData[
                                'school_year'
                            ]
                            ??
                            null,

                        'grade' =>
                            $recordData[
                                'grade'
                            ]
                            ??
                            null,

                        'remarks' =>
                            $recordData[
                                'remarks'
                            ]
                            ??
                            null,
                    ]);


                    $record->save();
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Return To View Profile
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.students.show',
                [
                    'student' =>
                        $studentRecord->id,
                ]
            )
            ->with(
                'success',
                'Student profile updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approved Student
    |--------------------------------------------------------------------------
    */

    private function approvedStudent(
        int $student
    ): User {

        $universityId =
            $this->currentUniversityId();


        $studentRecord =
            User::query()
                ->with(
                    'university'
                )
                ->where(
                    'university_id',
                    $universityId
                )
                ->whereIn(
                    'registration_status',
                    self::APPROVED_REGISTRATION_STATUSES
                )
                ->whereKey(
                    $student
                )
                ->firstOrFail();


        abort_unless(
            $studentRecord->isStudent(),
            404
        );


        return $studentRecord;
    }


    /*
    |--------------------------------------------------------------------------
    | Shared Data For View + Edit
    |--------------------------------------------------------------------------
    */

    private function profilePageData(
        User $studentRecord
    ): array {

        $component =
            strtoupper(
                trim(
                    (string)
                    $studentRecord->component
                )
            );


        $isRotc =
            $component ===
            'ROTC';


        /*
        |--------------------------------------------------------------------------
        | ROTC Profile
        |--------------------------------------------------------------------------
        */

        $rotcProfile =
            null;


        if (
            $isRotc
        ) {

            $rotcProfile =
                RotcStudentProfile::query()
                    ->with(
                        'msRecords'
                    )
                    ->where(
                        'user_id',
                        $studentRecord->id
                    )
                    ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | General Information
        |--------------------------------------------------------------------------
        */

        $studentData =
            $this->generalStudentData(
                $studentRecord
            );


        /*
        |--------------------------------------------------------------------------
        | ROTC Information
        |--------------------------------------------------------------------------
        */

        if (
            $isRotc
            &&
            $rotcProfile
        ) {

            $studentData =
                array_merge(
                    $studentData,
                    $this->rotcStudentData(
                        $studentRecord,
                        $rotcProfile
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CWTS / LTS Empty ROTC Objects
        |--------------------------------------------------------------------------
        */

        $temporaryAddress =
            new stdClass();

        $permanentAddress =
            new stdClass();

        $parents =
            new stdClass();

        $emergencyContact =
            new stdClass();

        $militaryScience =
            new stdClass();


        if (
            $isRotc
            &&
            $rotcProfile
        ) {

            $temporaryAddress =
                $this->temporaryAddress(
                    $rotcProfile
                );


            $permanentAddress =
                $this->permanentAddress(
                    $rotcProfile
                );


            $parents =
                $this->parentInformation(
                    $rotcProfile
                );


            $emergencyContact =
                $this->emergencyInformation(
                    $rotcProfile
                );


            $militaryScience =
                $this->militaryScienceInformation(
                    $rotcProfile
                );
        }


        /*
        |--------------------------------------------------------------------------
        | QR Code
        |--------------------------------------------------------------------------
        |
        | qr_token itself is NEVER sent to Vue.
        |
        */

        $qrCodeUrl =
            null;


        if (
            filled(
                $studentRecord->getAttribute(
                    'qr_token'
                )
            )
        ) {

            $qrCodeUrl =
                route(
                    'university-admin.student-registration.qr-code',
                    [
                        'student' =>
                            $studentRecord->id,
                    ]
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Digital Signature
        |--------------------------------------------------------------------------
        */

        $signatureUrl =
            null;


        if (
            filled(
                $studentRecord->signature_path
            )
        ) {

            $signatureUrl =
                route(
                    'university-admin.student-registration.signature',
                    [
                        'student' =>
                            $studentRecord->id,
                    ]
                );
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $university =
            $studentRecord->university;


        $universityData = [

            'id' =>
                $university?->id,

            'name' =>
                $university?->getAttribute(
                    'name'
                ),

            'acronym' =>
                $university?->getAttribute(
                    'acronym'
                ),

            'semester' =>
                $university?->getAttribute(
                    'semester'
                ),

            'academic_year' =>
                $university?->getAttribute(
                    'academic_year'
                ),
        ];


        return [

            'student' =>
                $studentData,

            'university' =>
                $universityData,

            'isRotc' =>
                $isRotc,

            'temporaryAddress' =>
                $temporaryAddress,

            'permanentAddress' =>
                $permanentAddress,

            'parents' =>
                $parents,

            'emergencyContact' =>
                $emergencyContact,

            'militaryScience' =>
                $militaryScience,

            'qrCodeUrl' =>
                $qrCodeUrl,

            'signatureUrl' =>
                $signatureUrl,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Current University
    |--------------------------------------------------------------------------
    */

    private function currentUniversityId(): int
    {

        $administrator =
            Auth::guard(
                'university_admin'
            )->user();


        abort_unless(
            $administrator,
            401
        );


        $universityId =
            (int)
            (
                $administrator->getAttribute(
                    'university_id'
                )
                ??
                0
            );


        abort_if(
            $universityId <=
            0,
            403,
            'Your university administrator account is not connected to a university.'
        );


        return $universityId;
    }


    /*
    |--------------------------------------------------------------------------
    | General Student Data
    |--------------------------------------------------------------------------
    */

    private function generalStudentData(
        User $student
    ): array {

        return [

            'id' =>
                $student->id,


            /*
            |--------------------------------------------------------------------------
            | Protected Student ID
            |--------------------------------------------------------------------------
            */

            'student_id_number' =>
                $student->getAttribute(
                    'student_id_number'
                ),

            'id_number' =>
                $student->getAttribute(
                    'student_id_number'
                ),


            /*
            |--------------------------------------------------------------------------
            | Name
            |--------------------------------------------------------------------------
            */

            'full_name' =>
                $student->full_name,

            'surname' =>
                $student->surname,

            'first_name' =>
                $student->first_name,

            'middle_name' =>
                $student->middle_name,

            'email' =>
                $student->email,


            /*
            |--------------------------------------------------------------------------
            | Registration
            |--------------------------------------------------------------------------
            */

            'registration_status' =>
                $student->getAttribute(
                    'registration_status'
                ),


            /*
            |--------------------------------------------------------------------------
            | NSTP
            |--------------------------------------------------------------------------
            */

            'subject' =>
                $student->subject,

            'component' =>
                strtoupper(
                    trim(
                        (string)
                        $student->component
                    )
                ),

            'term' =>
                $student->term,


            /*
            |--------------------------------------------------------------------------
            | Academic
            |--------------------------------------------------------------------------
            */

            'course' =>
                $student->course,

            'year_level' =>
                $student->year_level,

            'section' =>
                $student->section,

            'year_section' =>
                $this->yearSection(
                    $student
                ),


            /*
            |--------------------------------------------------------------------------
            | Personal
            |--------------------------------------------------------------------------
            */

            'gender' =>
                $student->gender,


            /*
            |--------------------------------------------------------------------------
            | Date For EditableProfileInfo.vue
            |--------------------------------------------------------------------------
            */

            'birth_date' =>
                $student->birth_date
                    ? $student
                        ->birth_date
                        ->format(
                            'Y-m-d'
                        )
                    : null,


            /*
            |--------------------------------------------------------------------------
            | Date For StudentProfileInfo.vue
            |--------------------------------------------------------------------------
            */

            'date_of_birth' =>
                $student->birth_date
                    ? $student
                        ->birth_date
                        ->format(
                            'm/d/Y'
                        )
                    : null,


            'contact_number' =>
                $student->contact_number,


            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'city_address' =>
                $student->city_address,

            'municipality' =>
                $student->municipality,

            'province' =>
                $student->province,

            'full_address' =>
                $student->full_address,


            /*
            |--------------------------------------------------------------------------
            | Guardian
            |--------------------------------------------------------------------------
            */

            'guardian_name' =>
                $student->guardian_name,

            'guardian_address' =>
                $student->guardian_address,

            'guardian_contact_number' =>
                $student
                    ->guardian_contact_number,


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status' =>
                $this->studentStatus(
                    $student
                ),


            /*
            |--------------------------------------------------------------------------
            | Profile Photo
            |--------------------------------------------------------------------------
            */

            'profile_photo_url' =>
                $this->publicFileUrl(
                    $student->profile_photo
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Student Data
    |--------------------------------------------------------------------------
    */

    private function rotcStudentData(
        User $student,
        RotcStudentProfile $profile
    ): array {

        return [

            'nstp_id_no' =>
                $profile->nstp_id_no,

            'ms_level' =>
                $profile->ms_level,

            'blood_type' =>
                $profile->blood_type,

            'place_of_birth' =>
                $profile->place_of_birth,

            'height' =>
                $this->measurement(
                    $profile->height_cm,
                    'cm'
                ),

            'height_cm' =>
                $profile->height_cm,

            'weight' =>
                $this->measurement(
                    $profile->weight_kg,
                    'kg'
                ),

            'weight_kg' =>
                $profile->weight_kg,

            'complexion' =>
                $profile->complexion,

            'religion' =>
                $profile->religion,

            'date_of_birth' =>
                $student->birth_date
                    ? $student
                        ->birth_date
                        ->format(
                            'm/d/Y'
                        )
                    : (
                        $profile->date_of_birth
                            ? $profile
                                ->date_of_birth
                                ->format(
                                    'm/d/Y'
                                )
                            : null
                    ),

            'contact_number' =>
                $student->contact_number
                ??
                $profile->cellphone_number,

            'contact_email' =>
                $profile->contact_email,

            'course' =>
                $student->course
                ??
                $profile->course,

            'school_name' =>
                $profile->school_name,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Temporary Address
    |--------------------------------------------------------------------------
    */

    private function temporaryAddress(
        RotcStudentProfile $profile
    ): array {

        return [

            'address' =>
                $profile
                    ->temporary_address_line,

            'street_address' =>
                $profile
                    ->temporary_address_line,

            'municipality' =>
                $profile
                    ->temporary_municipality,

            'province' =>
                $profile
                    ->temporary_province,

            'contact' =>
                $profile
                    ->cellphone_number,

            'contact_number' =>
                $profile
                    ->cellphone_number,

            'full_address' =>
                $profile
                    ->temporary_address,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Permanent Address
    |--------------------------------------------------------------------------
    */

    private function permanentAddress(
        RotcStudentProfile $profile
    ): array {

        if (
            $profile
                ->permanent_same_as_temporary
        ) {

            return [

                'address' =>
                    $profile
                        ->temporary_address_line,

                'street_address' =>
                    $profile
                        ->temporary_address_line,

                'municipality' =>
                    $profile
                        ->temporary_municipality,

                'province' =>
                    $profile
                        ->temporary_province,

                'contact' =>
                    $profile
                        ->cellphone_number,

                'contact_number' =>
                    $profile
                        ->cellphone_number,

                'full_address' =>
                    $profile
                        ->temporary_address,

                'same_as_temporary' =>
                    true,
            ];
        }


        return [

            'address' =>
                $profile
                    ->permanent_address_line,

            'street_address' =>
                $profile
                    ->permanent_address_line,

            'municipality' =>
                $profile
                    ->permanent_municipality,

            'province' =>
                $profile
                    ->permanent_province,

            'contact' =>
                $profile
                    ->cellphone_number,

            'contact_number' =>
                $profile
                    ->cellphone_number,

            'full_address' =>
                $profile
                    ->permanent_address,

            'same_as_temporary' =>
                false,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Parents
    |--------------------------------------------------------------------------
    */

    private function parentInformation(
        RotcStudentProfile $profile
    ): array {

        return [

            'father_name' =>
                $profile->father_name,

            'father_occupation' =>
                $profile
                    ->father_occupation,

            'mother_name' =>
                $profile->mother_name,

            'mother_occupation' =>
                $profile
                    ->mother_occupation,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Emergency Contact
    |--------------------------------------------------------------------------
    */

    private function emergencyInformation(
        RotcStudentProfile $profile
    ): array {

        return [

            'name' =>
                $profile
                    ->emergency_contact_name,

            'guardian_name' =>
                $profile
                    ->emergency_contact_name,

            'relationship' =>
                $profile
                    ->emergency_contact_relationship,

            'address' =>
                $profile
                    ->emergency_contact_address,

            'contact' =>
                $profile
                    ->emergency_contact_number,

            'contact_number' =>
                $profile
                    ->emergency_contact_number,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Military Science
    |--------------------------------------------------------------------------
    */

    private function militaryScienceInformation(
        RotcStudentProfile $profile
    ): array {

        $records =
            $profile
                ->msRecords
                ->map(
                    function (
                        RotcMsRecord $record
                    ): array {

                        return [

                            'id' =>
                                $record->id,

                            'record_order' =>
                                $record
                                    ->record_order,

                            'military_science' =>
                                $record
                                    ->ms_level,

                            'ms' =>
                                $record
                                    ->ms_level,

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
                        ];
                    }
                )
                ->values();


        $latestCompleted =
            $records->last();


        return [

            'current' => [

                'military_science' =>
                    $profile->ms_level,

                'ms' =>
                    $profile->ms_level,

                'ms_level' =>
                    $profile->ms_level,
            ],


            'completed' =>
                $latestCompleted
                ??
                new stdClass(),


            'records' =>
                $records,


            'willing_advance_course' =>
                (bool)
                $profile
                    ->willing_advance_course,


            'profile_status' =>
                $profile->status,


            'submitted_at' =>
                $profile->submitted_at
                    ? $profile
                        ->submitted_at
                        ->toISOString()
                    : null,


            'completed_at' =>
                $profile->completed_at
                    ? $profile
                        ->completed_at
                        ->toISOString()
                    : null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Save Student Status
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | registration_status stays:
    |
    | approved
    | confirmed
    | completed
    |
    | Otherwise the student would disappear from the enrolled student list.
    |
    */

    private function writeStudentStatus(
        User $student,
        ?string $status
    ): void {

        if (
            !$status
        ) {
            return;
        }


        $databaseValue =
            match (
                strtoupper(
                    trim(
                        $status
                    )
                )
            ) {

                'ACTIVE' =>
                    'active',

                'WARNING FOR DROPOUT' =>
                    'warning_for_dropout',

                'DROPOUT' =>
                    'dropout',

                default =>
                    null,
            };


        if (
            !$databaseValue
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | student_status
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'users',
                'student_status'
            )
        ) {

            $student->setAttribute(
                'student_status',
                $databaseValue
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | enrollment_status
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'users',
                'enrollment_status'
            )
        ) {

            $student->setAttribute(
                'enrollment_status',
                $databaseValue
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | status
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'users',
                'status'
            )
        ) {

            $student->setAttribute(
                'status',
                $databaseValue
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Year / Section
    |--------------------------------------------------------------------------
    */

    private function yearSection(
        User $student
    ): string {

        return collect([
            $student->year_level,
            $student->section,
        ])
            ->filter(
                fn (
                    $value
                ) =>
                    filled(
                        $value
                    )
            )
            ->implode(
                ' / '
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Student Status
    |--------------------------------------------------------------------------
    */

    private function studentStatus(
        User $student
    ): string {

        $status =
            strtoupper(
                trim(
                    (string)
                    (
                        $student->getAttribute(
                            'student_status'
                        )
                        ??
                        $student->getAttribute(
                            'enrollment_status'
                        )
                        ??
                        $student->getAttribute(
                            'status'
                        )
                        ??
                        $student->getAttribute(
                            'registration_status'
                        )
                        ??
                        'ACTIVE'
                    )
                )
            );


        $status =
            str_replace(
                [
                    '_',
                    '-',
                ],
                ' ',
                $status
            );


        if (
            in_array(
                $status,
                [
                    'ACTIVE',
                    'APPROVED',
                    'ENROLLED',
                    'CONFIRMED',
                    'COMPLETED',
                ],
                true
            )
        ) {

            return 'ACTIVE';
        }


        if (
            in_array(
                $status,
                [
                    'WARNING',
                    'WARNING FOR DROPOUT',
                    'AT RISK',
                    'AT RISK FOR DROPOUT',
                ],
                true
            )
        ) {

            return 'WARNING FOR DROPOUT';
        }


        if (
            in_array(
                $status,
                [
                    'DROPOUT',
                    'DROPPED',
                    'DROP OUT',
                ],
                true
            )
        ) {

            return 'DROPOUT';
        }


        return (
            $status
            ?:
            'ACTIVE'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Measurement
    |--------------------------------------------------------------------------
    */

    private function measurement(
        mixed $value,
        string $unit
    ): ?string {

        if (
            $value ===
                null
            ||
            $value ===
                ''
        ) {

            return null;
        }


        $number =
            number_format(
                (float)
                $value,
                2,
                '.',
                ''
            );


        $number =
            rtrim(
                rtrim(
                    $number,
                    '0'
                ),
                '.'
            );


        return (
            $number
            .
            ' '
            .
            $unit
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Public Storage URL
    |--------------------------------------------------------------------------
    */

    private function publicFileUrl(
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


        return Storage::disk(
            'public'
        )->url(
            $path
        );
    }
}