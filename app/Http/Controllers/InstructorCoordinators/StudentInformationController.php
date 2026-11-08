<?php

namespace App\Http\Controllers\InstructorCoordinators;

use App\Http\Controllers\Controller;
use App\Models\RotcStudentProfile;
use App\Models\User;
use App\Support\NstpComponentAccess;
use App\Support\StudentProfileMedia;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use stdClass;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentInformationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    |
    | Keep role names identical to AnnouncementController.
    |
    */

    private const ROLE_UNIVERSITY_ADMIN =
        'university-admin';

    private const ROLE_INSTRUCTOR =
        'instructor';

    private const ROLE_COORDINATOR_ANNOUNCEMENT =
        'coordinator-announcement';

    private const ROLE_COORDINATOR_ATTENDANCE =
        'coordinator-attendance';

    private const ROLE_COORDINATOR_SCHEDULE =
        'coordinator-schedule';


    /*
    |--------------------------------------------------------------------------
    | NSTP Components
    |--------------------------------------------------------------------------
    */

    private const COMPONENT_CWTS =
        'CWTS';

    private const COMPONENT_LTS =
        'LTS';

    private const COMPONENT_ROTC =
        'ROTC';


    /*
    |--------------------------------------------------------------------------
    | Approved Registration Statuses
    |--------------------------------------------------------------------------
    |
    | These are approved/enrolled registrations.
    |
    | registration_status is NOT the same as:
    |
    | ACTIVE
    | WARNING FOR DROPOUT
    | DROPOUT
    |
    */

    private const APPROVED_REGISTRATION_STATUSES = [
        'approved',
        'confirmed',
        'completed',
    ];


    /*
    |--------------------------------------------------------------------------
    | Student List
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /instructor-coordinator/students
    |
    | Vue:
    |
    | InstructorCoordinators/StudentInformation/StudentList.vue
    |
    */

    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Current Instructor / Coordinator
        |--------------------------------------------------------------------------
        */

        $access =
            $this->resolveAccess();


        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | An Instructor/Coordinator sees ONLY:
        |
        | - Their university
        | - Their assigned NSTP component
        | - Approved / enrolled students
        |
        */

        $students =
            $this->accessibleStudentsQuery(
                $access
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

                            'email' =>
                                $student->email,

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


        /*
        |--------------------------------------------------------------------------
        | Render StudentList.vue
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'InstructorCoordinators/StudentInformation/StudentList',
            [
                /*
                |--------------------------------------------------------------------------
                | Same Layout Data Pattern As Announcement
                |--------------------------------------------------------------------------
                */

                'user' =>
                    $this->buildSidebarUser(
                        $access
                    ),

                'role' =>
                    $access['role'],

                'authRole' =>
                    $access['role'],

                'activeComponent' =>
                    $access['component'],

                'componentOptions' => [
                    $access['component'],
                ],

                /*
                |--------------------------------------------------------------------------
                | Student Information is available to IC roles
                |--------------------------------------------------------------------------
                */

                'canManage' =>
                    true,


                /*
                |--------------------------------------------------------------------------
                | Students
                |--------------------------------------------------------------------------
                */

                'students' =>
                    $students,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Student Profile
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /instructor-coordinator/students/{student}
    |
    | Vue:
    |
    | StudentProfile.vue
    |
    */

    public function show(
        int $student
    ): Response {
        $access =
            $this->resolveAccess();


        $studentRecord =
            $this->findAccessibleStudent(
                student:
                    $student,

                access:
                    $access
            );


        $profileData =
            $this->profileData(
                $studentRecord
            );


        return Inertia::render(
            'InstructorCoordinators/StudentInformation/StudentProfile',
            array_merge(
                $profileData,
                [
                    /*
                    |--------------------------------------------------------------------------
                    | Layout Role
                    |--------------------------------------------------------------------------
                    */

                    'user' =>
                        $this->buildSidebarUser(
                            $access
                        ),

                    'role' =>
                        $access['role'],

                    'authRole' =>
                        $access['role'],

                    'activeComponent' =>
                        $access['component'],


                    /*
                    |--------------------------------------------------------------------------
                    | Back
                    |--------------------------------------------------------------------------
                    */

                    'backUrl' =>
                        route(
                            'instructor-coordinator.students.index'
                        ),
                ]
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Student Profile Edit
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /instructor-coordinator/students/{student}/edit
    |
    | Vue:
    |
    | StudentProfileEdit.vue
    |
    */

    public function edit(
        int $student
    ): Response {
        $access =
            $this->resolveAccess();


        $studentRecord =
            $this->findAccessibleStudent(
                student:
                    $student,

                access:
                    $access
            );


        $profileData =
            $this->profileData(
                $studentRecord
            );


        return Inertia::render(
            'InstructorCoordinators/StudentInformation/StudentProfileEdit',
            array_merge(
                $profileData,
                [
                    /*
                    |--------------------------------------------------------------------------
                    | Layout
                    |--------------------------------------------------------------------------
                    */

                    'user' =>
                        $this->buildSidebarUser(
                            $access
                        ),

                    'role' =>
                        $access['role'],

                    'authRole' =>
                        $access['role'],

                    'activeComponent' =>
                        $access['component'],


                    /*
                    |--------------------------------------------------------------------------
                    | Navigation
                    |--------------------------------------------------------------------------
                    */

                    'backUrl' =>
                        route(
                            'instructor-coordinator.students.index'
                        ),

                    'profileUrl' =>
                        route(
                            'instructor-coordinator.students.show',
                            [
                                'student' =>
                                    $studentRecord->id,
                            ]
                        ),

                    'updateUrl' =>
                        route(
                            'instructor-coordinator.students.update',
                            [
                                'student' =>
                                    $studentRecord->id,
                            ]
                        ),
                ]
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Student
    |--------------------------------------------------------------------------
    |
    | PUT
    |
    | /instructor-coordinator/students/{student}
    |
    | NEVER EDIT:
    |
    | student_id_number
    | qr_token
    | signature_path
    | university_id
    | registration_status
    |
    */

    public function update(
        Request $request,
        int $student
    ): RedirectResponse {
        $access =
            $this->resolveAccess();


        $studentRecord =
            $this->findAccessibleStudent(
                student:
                    $student,

                access:
                    $access
            );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
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
                | Account
                |--------------------------------------------------------------------------
                */

                'email' => [
                    'required',
                    'email',
                    'max:255',

                    Rule::unique(
                        'users',
                        'email'
                    )->ignore(
                        $studentRecord->id
                    ),
                ],


                /*
                |--------------------------------------------------------------------------
                | Academic
                |--------------------------------------------------------------------------
                */

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
                        self::COMPONENT_CWTS,
                        self::COMPONENT_LTS,
                        self::COMPONENT_ROTC,
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


                /*
                |--------------------------------------------------------------------------
                | Personal
                |--------------------------------------------------------------------------
                */

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


                /*
                |--------------------------------------------------------------------------
                | Address
                |--------------------------------------------------------------------------
                */

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


                /*
                |--------------------------------------------------------------------------
                | Guardian
                |--------------------------------------------------------------------------
                */

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
                    'required',

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

                'rotc.willing_advance_course' => [
                    'nullable',
                    'boolean',
                ],


                /*
                |--------------------------------------------------------------------------
                | Military Records
                |--------------------------------------------------------------------------
                */

                'military_records' => [
                    'nullable',
                    'array',
                    'max:30',
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


        /*
        |--------------------------------------------------------------------------
        | Component Security
        |--------------------------------------------------------------------------
        |
        | Instructor/Coordinator must NOT move a student into another component.
        |
        | ROTC staff -> ROTC students only
        | CWTS staff -> CWTS students only
        | LTS staff  -> LTS students only
        |
        */

        $requestedComponent =
            strtoupper(
                trim(
                    (string)
                    $validated['component']
                )
            );


        if (
            $requestedComponent !==
            $access['component']
        ) {
            throw ValidationException::withMessages([
                'component' =>
                    'You cannot transfer a student to another NSTP component.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $studentRecord,
                $validated,
                $access
            ): void {
                /*
                |--------------------------------------------------------------------------
                | Build Name
                |--------------------------------------------------------------------------
                */

                $surname =
                    trim(
                        (string)
                        $validated['surname']
                    );


                $firstName =
                    trim(
                        (string)
                        $validated['first_name']
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


                $name =
                    collect([
                        $firstName,
                        $middleName,
                        $surname,
                    ])
                        ->filter()
                        ->implode(
                            ' '
                        );


                /*
                |--------------------------------------------------------------------------
                | Update User
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | No Student ID
                | No QR token
                | No Signature
                | No University
                | No registration_status
                |
                */

                $studentRecord->forceFill([
                    'name' =>
                        $name,

                    'surname' =>
                        $surname,

                    'first_name' =>
                        $firstName,

                    'middle_name' =>
                        $middleName !== ''
                            ? $middleName
                            : null,

                    'email' =>
                        strtolower(
                            trim(
                                (string)
                                $validated['email']
                            )
                        ),

                    'course' =>
                        $this->nullableString(
                            $validated['course']
                            ??
                            null
                        ),

                    'year_level' =>
                        $this->nullableString(
                            $validated['year_level']
                            ??
                            null
                        ),

                    'section' =>
                        $this->nullableString(
                            $validated['section']
                            ??
                            null
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | Keep Their Assigned Component
                    |--------------------------------------------------------------------------
                    */

                    'component' =>
                        $access['component'],

                    'subject' =>
                        $this->nullableString(
                            $validated['subject']
                            ??
                            null
                        ),

                    'term' =>
                        $this->nullableString(
                            $validated['term']
                            ??
                            null
                        ),

                    'gender' =>
                        $this->nullableString(
                            $validated['gender']
                            ??
                            null
                        ),

                    'birth_date' =>
                        $validated['birth_date']
                        ??
                        null,

                    'contact_number' =>
                        $this->nullableString(
                            $validated['contact_number']
                            ??
                            null
                        ),

                    'city_address' =>
                        $this->nullableString(
                            $validated['city_address']
                            ??
                            null
                        ),

                    'municipality' =>
                        $this->nullableString(
                            $validated['municipality']
                            ??
                            null
                        ),

                    'province' =>
                        $this->nullableString(
                            $validated['province']
                            ??
                            null
                        ),

                    'guardian_name' =>
                        $this->nullableString(
                            $validated['guardian_name']
                            ??
                            null
                        ),

                    'guardian_contact_number' =>
                        $this->nullableString(
                            $validated['guardian_contact_number']
                            ??
                            null
                        ),

                    'guardian_address' =>
                        $this->nullableString(
                            $validated['guardian_address']
                            ??
                            null
                        ),
                ]);


                /*
                |--------------------------------------------------------------------------
                | ACTIVE / WARNING / DROPOUT
                |--------------------------------------------------------------------------
                */

                $this->writeStudentStatus(
                    student:
                        $studentRecord,

                    status:
                        $validated['status']
                );


                $studentRecord->save();


                /*
                |--------------------------------------------------------------------------
                | ROTC Only
                |--------------------------------------------------------------------------
                */

                if (
                    $access['component'] ===
                    self::COMPONENT_ROTC
                ) {
                    $this->saveRotcInformation(
                        student:
                            $studentRecord,

                        validated:
                            $validated
                    );
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Return To Student Profile
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'instructor-coordinator.students.show',
                [
                    'student' =>
                        $studentRecord->id,
                ]
            )
            ->with(
                'success',
                'Student information updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | QR Code
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /instructor-coordinator/students/{student}/qr-code
    |
    */

    public function qrCode(
        int $student
    ): HttpResponse {
        $access =
            $this->resolveAccess();


        $studentRecord =
            $this->findAccessibleStudent(
                student:
                    $student,

                access:
                    $access
            );


        $token =
            trim(
                (string)
                $studentRecord->getAttribute(
                    'qr_token'
                )
            );


        if (
            $token ===
            ''
        ) {
            abort(
                404,
                'Student QR code has not been generated.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Same secure QR payload used by registration
        |--------------------------------------------------------------------------
        */

        $payload =
            'NSTPHUB:ATTENDANCE:'
            .
            $token;


        $qrCode =
            new QrCode(
                data:
                    $payload,

                encoding:
                    new Encoding(
                        'UTF-8'
                    ),

                errorCorrectionLevel:
                    ErrorCorrectionLevel::High,

                size:
                    420,

                margin:
                    12,

                roundBlockSizeMode:
                    RoundBlockSizeMode::Margin
            );


        $writer =
            new PngWriter();


        $result =
            $writer->write(
                $qrCode
            );


        return response(
            $result->getString(),
            200,
            [
                'Content-Type' =>
                    $result->getMimeType(),

                'Cache-Control' =>
                    'private, no-store, no-cache, must-revalidate',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /instructor-coordinator/students/{student}/signature
    |
    */

    public function signature(
        int $student
    ): BinaryFileResponse {
        $access =
            $this->resolveAccess();


        $studentRecord =
            $this->findAccessibleStudent(
                student:
                    $student,

                access:
                    $access
            );


        $signaturePath =
            trim(
                (string)
                $studentRecord->getAttribute(
                    'signature_path'
                )
            );


        if (
            $signaturePath ===
            ''
        ) {
            abort(
                404
            );
        }


        $disk =
            Storage::disk(
                'local'
            );

        if (!$disk->exists($signaturePath)) {
            $disk = Storage::disk('public');
        }


        if (
            !$disk->exists(
                $signaturePath
            )
        ) {
            abort(
                404
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Repair Legacy 400 x 200 Registration Signatures
        |--------------------------------------------------------------------------
        */

        $this->repairLegacyRegistrationSignatureSvg(
            $disk,
            $signaturePath
        );


        $mimeType =
            $disk->mimeType(
                $signaturePath
            )
            ?:
            'application/octet-stream';


        return response()->file(
            $disk->path(
                $signaturePath
            ),
            [
                'Content-Type' =>
                    $mimeType,

                'Cache-Control' =>
                    'private, no-store, no-cache, must-revalidate',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Instructor / Coordinator
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | This mirrors AnnouncementController's Instructor/Coordinator guard
    | behavior.
    |
    | Instructor is checked first.
    | Coordinator is checked second.
    |
    */

    private function resolveAccess(): array
    {
        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        |
        | University Admin stays logged in with the university_admin guard and
        | sees only students from the selected NSTP component.
        |
        */

        if (
            Auth::guard(
                'university_admin'
            )->check()
        ) {
            $actor =
                Auth::guard(
                    'university_admin'
                )->user();


            $context =
                NstpComponentAccess::context(
                    request()
                );


            return [
                'actor' =>
                    $actor,

                'guard' =>
                    'university_admin',

                'role' =>
                    self::ROLE_UNIVERSITY_ADMIN,

                'university_id' =>
                    (int) $context['university_id'],

                'component' =>
                    $context['selected_component'],
            ];
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
            $actor =
                Auth::guard(
                    'instructor'
                )->user();


            $componentOptions =
                $actor->componentCodes();


            $component =
                NstpComponentAccess::selected(
                    request(),
                    [
                        'components' =>
                            $componentOptions,
                    ]
                );


            return [
                'actor' =>
                    $actor,

                'guard' =>
                    'instructor',

                'role' =>
                    self::ROLE_INSTRUCTOR,

                'university_id' =>
                    $this->resolveUniversityId(
                        $actor
                    ),

                'component' =>
                    $component,
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
            $actor =
                Auth::guard(
                    'coordinator'
                )->user();


            $component =
                $this->validateAccountComponent(
                    $actor->component
                    ??
                    null
                );


            /*
            |--------------------------------------------------------------------------
            | Announcement Coordinator
            |--------------------------------------------------------------------------
            */

            if (
                $this->coordinatorHasRole(
                    $actor,
                    self::ROLE_COORDINATOR_ANNOUNCEMENT
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        self::ROLE_COORDINATOR_ANNOUNCEMENT,

                    'university_id' =>
                        $this->resolveUniversityId(
                            $actor
                        ),

                    'component' =>
                        $component,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Attendance Coordinator
            |--------------------------------------------------------------------------
            */

            if (
                $this->coordinatorHasRole(
                    $actor,
                    self::ROLE_COORDINATOR_ATTENDANCE
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        self::ROLE_COORDINATOR_ATTENDANCE,

                    'university_id' =>
                        $this->resolveUniversityId(
                            $actor
                        ),

                    'component' =>
                        $component,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Schedule Coordinator
            |--------------------------------------------------------------------------
            */

            if (
                $this->coordinatorHasRole(
                    $actor,
                    self::ROLE_COORDINATOR_SCHEDULE
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        self::ROLE_COORDINATOR_SCHEDULE,

                    'university_id' =>
                        $this->resolveUniversityId(
                            $actor
                        ),

                    'component' =>
                        $component,
                ];
            }


            abort(
                403,
                'Your coordinator account does not have a recognized NSTP role.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Not Logged In As Instructor / Coordinator
        |--------------------------------------------------------------------------
        */

        abort(
            401,
            'You must be logged in as a University Admin, Instructor, or Coordinator.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Students Query
    |--------------------------------------------------------------------------
    */

    private function accessibleStudentsQuery(
        array $access
    ): Builder {
        return User::query()
            ->where(
                'university_id',
                $access['university_id']
            )
            ->where(
                'component',
                $access['component']
            )
            ->whereIn(
                'registration_status',
                self::APPROVED_REGISTRATION_STATUSES
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Find Accessible Student
    |--------------------------------------------------------------------------
    |
    | This prevents manually typing another student's ID from another
    | university or another NSTP component.
    |
    */

    private function findAccessibleStudent(
        int $student,
        array $access
    ): User {
        return $this
            ->accessibleStudentsQuery(
                $access
            )
            ->with(
                'university'
            )
            ->whereKey(
                $student
            )
            ->firstOrFail();
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Data
    |--------------------------------------------------------------------------
    */

    private function profileData(
        User $student
    ): array {
        $component =
            strtoupper(
                trim(
                    (string)
                    $student->component
                )
            );


        $isRotc =
            $component ===
            self::COMPONENT_ROTC;


        /*
        |--------------------------------------------------------------------------
        | General Student
        |--------------------------------------------------------------------------
        */

        $studentData =
            $this->generalStudentData(
                $student
            );


        /*
        |--------------------------------------------------------------------------
        | ROTC
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
                        $student->id
                    )
                    ->first();
        }


        if (
            $rotcProfile
        ) {
            $studentData =
                array_merge(
                    $studentData,
                    $this->rotcStudentData(
                        student:
                            $student,

                        profile:
                            $rotcProfile
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Default ROTC Objects
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
                $this->parentsInformation(
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
        | Protected QR URL
        |--------------------------------------------------------------------------
        */

        $qrCodeUrl =
            null;


        if (
            filled(
                $student->getAttribute(
                    'qr_token'
                )
            )
        ) {
            $qrCodeUrl = StudentProfileMedia::qrCode(
                (string) $student->getAttribute('qr_token')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Protected Signature URL
        |--------------------------------------------------------------------------
        */

        $signatureUrl =
            null;


        if (
            filled(
                $student->getAttribute(
                    'signature_path'
                )
            )
        ) {
            $signatureUrl = StudentProfileMedia::signature(
                (string) $student->getAttribute('signature_path')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $university =
            $student->university;


        return [
            'student' =>
                $studentData,

            'university' => [
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
            ],

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
    | General Student Data
    |--------------------------------------------------------------------------
    */

    private function generalStudentData(
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

            'email' =>
                $student->email,

            'registration_status' =>
                $student->getAttribute(
                    'registration_status'
                ),

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

            'gender' =>
                $student->gender,

            /*
            |--------------------------------------------------------------------------
            | For StudentProfileEdit.vue
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
            | For StudentProfile.vue
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

            'city_address' =>
                $student->city_address,

            'municipality' =>
                $student->municipality,

            'province' =>
                $student->province,

            'full_address' =>
                $student->full_address,

            'guardian_name' =>
                $student->guardian_name,

            'guardian_address' =>
                $student->guardian_address,

            'guardian_contact_number' =>
                $student
                    ->guardian_contact_number,

            'status' =>
                $this->studentStatus(
                    $student
                ),

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

            'height_cm' =>
                $profile->height_cm,

            'height' =>
                $this->measurement(
                    $profile->height_cm,
                    'cm'
                ),

            'weight_kg' =>
                $profile->weight_kg,

            'weight' =>
                $this->measurement(
                    $profile->weight_kg,
                    'kg'
                ),

            'complexion' =>
                $profile->complexion,

            'religion' =>
                $profile->religion,

            'contact_number' =>
                $student->contact_number
                ??
                $profile->cellphone_number,

            'contact_email' =>
                $profile->contact_email,

            'school_name' =>
                $profile->school_name,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Temporary Address
    |--------------------------------------------------------------------------
    */

    private function temporaryAddress(
        RotcStudentProfile $profile
    ): array {
        return [
            'address' =>
                $profile->temporary_address_line,

            'street_address' =>
                $profile->temporary_address_line,

            'municipality' =>
                $profile->temporary_municipality,

            'province' =>
                $profile->temporary_province,

            'contact' =>
                $profile->cellphone_number,

            'contact_number' =>
                $profile->cellphone_number,

            'full_address' =>
                $profile->temporary_address,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Permanent Address
    |--------------------------------------------------------------------------
    */

    private function permanentAddress(
        RotcStudentProfile $profile
    ): array {
        if (
            $profile->permanent_same_as_temporary
        ) {
            return [
                'address' =>
                    $profile->temporary_address_line,

                'street_address' =>
                    $profile->temporary_address_line,

                'municipality' =>
                    $profile->temporary_municipality,

                'province' =>
                    $profile->temporary_province,

                'contact' =>
                    $profile->cellphone_number,

                'contact_number' =>
                    $profile->cellphone_number,

                'full_address' =>
                    $profile->temporary_address,

                'same_as_temporary' =>
                    true,
            ];
        }


        return [
            'address' =>
                $profile->permanent_address_line,

            'street_address' =>
                $profile->permanent_address_line,

            'municipality' =>
                $profile->permanent_municipality,

            'province' =>
                $profile->permanent_province,

            'contact' =>
                $profile->cellphone_number,

            'contact_number' =>
                $profile->cellphone_number,

            'full_address' =>
                $profile->permanent_address,

            'same_as_temporary' =>
                false,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Parents
    |--------------------------------------------------------------------------
    */

    private function parentsInformation(
        RotcStudentProfile $profile
    ): array {
        return [
            'father_name' =>
                $profile->father_name,

            'father_occupation' =>
                $profile->father_occupation,

            'mother_name' =>
                $profile->mother_name,

            'mother_occupation' =>
                $profile->mother_occupation,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Emergency
    |--------------------------------------------------------------------------
    */

    private function emergencyInformation(
        RotcStudentProfile $profile
    ): array {
        return [
            'name' =>
                $profile->emergency_contact_name,

            'guardian_name' =>
                $profile->emergency_contact_name,

            'parent_guardian' =>
                $profile->emergency_contact_name,

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
    | Military Science
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
                        $record
                    ): array {
                        return [
                            'id' =>
                                $record->id,

                            'record_order' =>
                                $record->record_order,

                            'military_science' =>
                                $record->ms_level,

                            'ms' =>
                                $record->ms_level,

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
                ->values();


        $completed =
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
                $completed
                ??
                new stdClass(),

            'records' =>
                $records,

            'willing_advance_course' =>
                (bool)
                $profile->willing_advance_course,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Save ROTC Information
    |--------------------------------------------------------------------------
    */

    private function saveRotcInformation(
        User $student,
        array $validated
    ): void {
        $data =
            $validated['rotc']
            ??
            [];


        $profile =
            RotcStudentProfile::query()
                ->firstOrNew([
                    'user_id' =>
                        $student->id,
                ]);


        /*
        |--------------------------------------------------------------------------
        | Synchronize General Information
        |--------------------------------------------------------------------------
        */

        $profile->user_id =
            $student->id;

        $profile->last_name =
            $student->surname;

        $profile->first_name =
            $student->first_name;

        $profile->middle_name =
            $student->middle_name;

        $profile->gender =
            $student->gender;

        $profile->date_of_birth =
            $student->birth_date;

        $profile->course =
            $student->course;


        /*
        |--------------------------------------------------------------------------
        | ROTC
        |--------------------------------------------------------------------------
        */

        $profile->nstp_id_no =
            $this->nullableString(
                $data['nstp_id_no']
                ??
                null
            );

        $profile->ms_level =
            $this->nullableString(
                $data['ms_level']
                ??
                null
            );

        $profile->blood_type =
            $this->nullableString(
                $data['blood_type']
                ??
                null
            );

        $profile->place_of_birth =
            $this->nullableString(
                $data['place_of_birth']
                ??
                null
            );

        $profile->height_cm =
            $data['height_cm']
            ??
            null;

        $profile->weight_kg =
            $data['weight_kg']
            ??
            null;

        $profile->complexion =
            $this->nullableString(
                $data['complexion']
                ??
                null
            );

        $profile->religion =
            $this->nullableString(
                $data['religion']
                ??
                null
            );

        $profile->cellphone_number =
            $this->nullableString(
                $data['cellphone_number']
                ??
                $student->contact_number
            );

        $profile->contact_email =
            $this->nullableString(
                $data['contact_email']
                ??
                $student->email
            );

        $profile->school_name =
            $this->nullableString(
                $data['school_name']
                ??
                $student
                    ->university
                    ?->getAttribute(
                        'name'
                    )
            );


        /*
        |--------------------------------------------------------------------------
        | Temporary Address
        |--------------------------------------------------------------------------
        */

        $profile->temporary_address_line =
            $this->nullableString(
                $data[
                    'temporary_address_line'
                ]
                ??
                null
            );

        $profile->temporary_municipality =
            $this->nullableString(
                $data[
                    'temporary_municipality'
                ]
                ??
                null
            );

        $profile->temporary_province =
            $this->nullableString(
                $data[
                    'temporary_province'
                ]
                ??
                null
            );


        /*
        |--------------------------------------------------------------------------
        | Permanent Address
        |--------------------------------------------------------------------------
        */

        $same =
            (bool)
            (
                $data[
                    'permanent_same_as_temporary'
                ]
                ??
                false
            );


        $profile->permanent_same_as_temporary =
            $same;


        if (
            $same
        ) {
            $profile->permanent_address_line =
                $profile->temporary_address_line;

            $profile->permanent_municipality =
                $profile->temporary_municipality;

            $profile->permanent_province =
                $profile->temporary_province;
        } else {
            $profile->permanent_address_line =
                $this->nullableString(
                    $data[
                        'permanent_address_line'
                    ]
                    ??
                    null
                );

            $profile->permanent_municipality =
                $this->nullableString(
                    $data[
                        'permanent_municipality'
                    ]
                    ??
                    null
                );

            $profile->permanent_province =
                $this->nullableString(
                    $data[
                        'permanent_province'
                    ]
                    ??
                    null
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Parents
        |--------------------------------------------------------------------------
        */

        $profile->father_name =
            $this->nullableString(
                $data['father_name']
                ??
                null
            );

        $profile->father_occupation =
            $this->nullableString(
                $data['father_occupation']
                ??
                null
            );

        $profile->mother_name =
            $this->nullableString(
                $data['mother_name']
                ??
                null
            );

        $profile->mother_occupation =
            $this->nullableString(
                $data['mother_occupation']
                ??
                null
            );


        /*
        |--------------------------------------------------------------------------
        | Emergency
        |--------------------------------------------------------------------------
        */

        $profile->emergency_contact_name =
            $this->nullableString(
                $data[
                    'emergency_contact_name'
                ]
                ??
                null
            );

        $profile
            ->emergency_contact_relationship =
            $this->nullableString(
                $data[
                    'emergency_contact_relationship'
                ]
                ??
                null
            );

        $profile->emergency_contact_address =
            $this->nullableString(
                $data[
                    'emergency_contact_address'
                ]
                ??
                null
            );

        $profile->emergency_contact_number =
            $this->nullableString(
                $data[
                    'emergency_contact_number'
                ]
                ??
                null
            );


        /*
        |--------------------------------------------------------------------------
        | Advance Course
        |--------------------------------------------------------------------------
        */

        $profile->willing_advance_course =
            (bool)
            (
                $data[
                    'willing_advance_course'
                ]
                ??
                false
            );


        $profile->save();


        /*
        |--------------------------------------------------------------------------
        | Military Records
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists(
                'military_records',
                $validated
            )
        ) {
            $profile
                ->msRecords()
                ->delete();


            foreach (
                $validated[
                    'military_records'
                ]
                ??
                []
                as $index =>
                $record
            ) {
                $hasContent =
                    collect([
                        $record['ms_level']
                        ??
                        null,

                        $record['semester']
                        ??
                        null,

                        $record['school_year']
                        ??
                        null,

                        $record['grade']
                        ??
                        null,

                        $record['remarks']
                        ??
                        null,
                    ])
                        ->contains(
                            fn (
                                $value
                            ) =>
                                filled(
                                    $value
                                )
                        );


                if (
                    !$hasContent
                ) {
                    continue;
                }


                $profile
                    ->msRecords()
                    ->create([
                        'record_order' =>
                            $index + 1,

                        'ms_level' =>
                            $this->nullableString(
                                $record['ms_level']
                                ??
                                null
                            ),

                        'semester' =>
                            $this->nullableString(
                                $record['semester']
                                ??
                                null
                            ),

                        'school_year' =>
                            $this->nullableString(
                                $record['school_year']
                                ??
                                null
                            ),

                        'grade' =>
                            $record['grade']
                            ??
                            null,

                        'remarks' =>
                            $this->nullableString(
                                $record['remarks']
                                ??
                                null
                            ),
                    ]);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Student Status
    |--------------------------------------------------------------------------
    */

    private function writeStudentStatus(
        User $student,
        string $status
    ): void {
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
        | Prefer Student Status
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
        | Enrollment Status
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
        | Generic Status
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
    | Student Display Status
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
    | Sidebar User
    |--------------------------------------------------------------------------
    |
    | Keeps Admin_IC_Layout.vue consistent with Announcement.
    |
    */

    private function buildSidebarUser(
        array $access
    ): array {
        $actor =
            $access['actor'];


        /*
        |--------------------------------------------------------------------------
        | Full Name
        |--------------------------------------------------------------------------
        */

        $fullName =
            $actor->full_name
            ??
            $actor->name
            ??
            null;


        if (
            !$fullName
        ) {
            $fullName =
                collect([
                    $actor->first_name
                    ??
                    null,

                    $actor->middle_name
                    ??
                    null,

                    $actor->last_name
                    ??
                    $actor->surname
                    ??
                    null,
                ])
                    ->filter()
                    ->implode(
                        ' '
                    );
        }


        return [
            'id' =>
                $actor->getKey(),

            'university_id' =>
                $access[
                    'university_id'
                ],

            'full_name' =>
                $fullName
                ?:
                'NSTP USER',

            'name' =>
                $fullName
                ?:
                'NSTP USER',

            'username' =>
                $actor->username
                ??
                '',

            'email' =>
                $actor->email
                ??
                '',

            'phone_number' =>
                $actor->phone_number
                ??
                $actor->phone
                ??
                '',

            'profile_photo' =>
                $actor->profile_photo
                ??
                $actor->photo
                ??
                null,

            'component' =>
                $access['component'],

            'role' =>
                $access['role'],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Coordinator Role Check
    |--------------------------------------------------------------------------
    */

    private function coordinatorHasRole(
        object $coordinator,
        string $role
    ): bool {
        if (
            !method_exists(
                $coordinator,
                'hasRole'
            )
        ) {
            return false;
        }


        return $coordinator->hasRole(
            $role
        );
    }


    /*
    |--------------------------------------------------------------------------
    | University
    |--------------------------------------------------------------------------
    */

    private function resolveUniversityId(
        object $actor
    ): int {
        $universityId =
            (int)
            (
                $actor->university_id
                ??
                0
            );


        if (
            $universityId <=
            0
        ) {
            abort(
                403,
                'Your account is not assigned to a university.'
            );
        }


        return $universityId;
    }


    /*
    |--------------------------------------------------------------------------
    | Account Component
    |--------------------------------------------------------------------------
    */

    private function validateAccountComponent(
        mixed $component
    ): string {
        $component =
            strtoupper(
                trim(
                    (string)
                    $component
                )
            );


        if (
            !in_array(
                $component,
                [
                    self::COMPONENT_CWTS,
                    self::COMPONENT_LTS,
                    self::COMPONENT_ROTC,
                ],
                true
            )
        ) {
            abort(
                403,
                'Your account does not have a valid NSTP component.'
            );
        }


        return $component;
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
            ->filter()
            ->implode(
                ' / '
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
    | Nullable String
    |--------------------------------------------------------------------------
    */

    private function nullableString(
        mixed $value
    ): ?string {
        if (
            $value ===
            null
        ) {
            return null;
        }


        $value =
            trim(
                (string)
                $value
            );


        return (
            $value !==
            ''
                ? $value
                : null
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Public File URL
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

    /*
    |--------------------------------------------------------------------------
    | Repair Legacy Registration Signature SVG
    |--------------------------------------------------------------------------
    |
    | Older registration signatures were saved with a 400 x 200 viewBox even
    | though the React Native drawing coordinates use a 1000 x 400 canvas.
    | That caused large parts of the signature to be clipped in the web portal.
    |
    | This repair runs only for SVG signature files. If a legacy file is found,
    | it is corrected once in private storage so every page that displays the
    | same signature benefits immediately.
    |
    */

    private function repairLegacyRegistrationSignatureSvg(
        mixed $disk,
        string $signaturePath
    ): void {
        if (
            !str_ends_with(
                strtolower(
                    $signaturePath
                ),
                '.svg'
            )
        ) {
            return;
        }


        try {
            $svg =
                $disk->get(
                    $signaturePath
                );


            if (
                !is_string(
                    $svg
                )
                ||
                trim(
                    $svg
                ) === ''
            ) {
                return;
            }


            $originalSvg =
                $svg;


            /*
            |------------------------------------------------------------------
            | Correct Legacy ViewBox
            |------------------------------------------------------------------
            */

            $svg =
                preg_replace(
                    '/viewBox=["\']0\s+0\s+400\s+200["\']/i',
                    'viewBox="0 0 1000 400"',
                    $svg
                )
                ??
                $svg;


            /*
            |------------------------------------------------------------------
            | Correct Legacy Width / Height
            |------------------------------------------------------------------
            */

            $svg =
                preg_replace(
                    '/width=["\']400["\']\s+height=["\']200["\']/i',
                    'width="1000" height="400"',
                    $svg
                )
                ??
                $svg;


            /*
            |------------------------------------------------------------------
            | Correct Legacy White Background Rectangle
            |------------------------------------------------------------------
            */

            $svg =
                preg_replace(
                    '/<rect\s+width=["\']400["\']\s+height=["\']200["\']/i',
                    '<rect width="1000" height="400"',
                    $svg
                )
                ??
                $svg;


            /*
            |------------------------------------------------------------------
            | Keep Signature Proportions While Scaling
            |------------------------------------------------------------------
            */

            if (
                !preg_match(
                    '/preserveAspectRatio=/i',
                    $svg
                )
            ) {
                $svg =
                    preg_replace(
                        '/<svg\b([^>]*)>/i',
                        '<svg$1 preserveAspectRatio="xMidYMid meet">',
                        $svg,
                        1
                    )
                    ??
                    $svg;
            }


            /*
            |------------------------------------------------------------------
            | Save Only When A Repair Was Actually Needed
            |------------------------------------------------------------------
            */

            if (
                $svg !==
                $originalSvg
            ) {
                $disk->put(
                    $signaturePath,
                    $svg
                );
            }

        } catch (\Throwable $exception) {
            /*
            |------------------------------------------------------------------
            | Do Not Break The Student Page If Repair Fails
            |------------------------------------------------------------------
            */

            report(
                $exception
            );
        }
    }

}
