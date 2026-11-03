<?php

namespace App\Http\Controllers\UniversityAdmin\Registration;

use App\Http\Controllers\Controller;
use App\Models\UniversityAdministrator;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentRegistrationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration Timezone
    |--------------------------------------------------------------------------
    */

    private const REGISTRATION_TIMEZONE =
        'Asia/Manila';


    /*
    |--------------------------------------------------------------------------
    | Student Registration List
    |--------------------------------------------------------------------------
    */

    public function index(): InertiaResponse
    {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $university =
            $admin->university;


        /*
        |--------------------------------------------------------------------------
        | Students With Submitted Registration
        |--------------------------------------------------------------------------
        */

        $students =
            User::query()
                ->where(
                    'university_id',
                    $admin->university_id
                )
                ->whereNotNull(
                    'signature_path'
                )
                ->orderByDesc(
                    'registration_submitted_at'
                )
                ->orderByDesc(
                    'id'
                )
                ->get()
                ->map(
                    fn (
                        User $student
                    ): array =>
                        $this->studentData(
                            $student
                        )
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | University Admin sees only:
        |
        | PENDING
        | APPROVED
        |
        | We calculate statistics using display_status so counts exactly match
        | what is shown in StudentRegistration.vue.
        |
        */

        $statistics = [
            'cwts' =>
                $students
                    ->where(
                        'component',
                        'CWTS'
                    )
                    ->count(),

            'rotc' =>
                $students
                    ->where(
                        'component',
                        'ROTC'
                    )
                    ->count(),

            'lts' =>
                $students
                    ->where(
                        'component',
                        'LTS'
                    )
                    ->count(),

            'pending' =>
                $students
                    ->where(
                        'display_status',
                        'PENDING'
                    )
                    ->count(),

            'approved' =>
                $students
                    ->where(
                        'display_status',
                        'APPROVED'
                    )
                    ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | University Components
        |--------------------------------------------------------------------------
        */

        $components =
            collect(
                $university->components
                ??
                []
            )
                ->map(
                    fn (
                        $component
                    ) =>
                        strtoupper(
                            trim(
                                (string)
                                $component
                            )
                        )
                )
                ->filter()
                ->unique()
                ->values()
                ->all();


        /*
        |--------------------------------------------------------------------------
        | Render Student Registration
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Registration/StudentRegistration',
            [
                'students' =>
                    $students,

                'components' =>
                    $components,

                'statistics' =>
                    $statistics,

                'registrationDeadline' =>
                    $this->registrationDeadlineData(
                        $university
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Registration Deadline
    |--------------------------------------------------------------------------
    */

    public function storeDeadline(
        Request $request
    ): RedirectResponse {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'start_date' => [
                    'required',
                    'date',
                ],

                'end_date' => [
                    'required',
                    'date',
                    'after_or_equal:start_date',
                ],

                'end_time' => [
                    'required',
                    'date_format:H:i',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Start Date
        |--------------------------------------------------------------------------
        */

        $startDate =
            Carbon::parse(
                $validated[
                    'start_date'
                ],
                self::REGISTRATION_TIMEZONE
            )
                ->format(
                    'Y-m-d'
                );


        /*
        |--------------------------------------------------------------------------
        | Normalize End Date
        |--------------------------------------------------------------------------
        */

        $endDate =
            Carbon::parse(
                $validated[
                    'end_date'
                ],
                self::REGISTRATION_TIMEZONE
            )
                ->format(
                    'Y-m-d'
                );


        /*
        |--------------------------------------------------------------------------
        | Normalize End Time
        |--------------------------------------------------------------------------
        */

        $endTime =
            Carbon::createFromFormat(
                'H:i',
                $validated[
                    'end_time'
                ],
                self::REGISTRATION_TIMEZONE
            )
                ->format(
                    'H:i:s'
                );


        /*
        |--------------------------------------------------------------------------
        | Save University Registration Period
        |--------------------------------------------------------------------------
        */

        $admin
            ->university
            ->forceFill([
                'registration_start_date' =>
                    $startDate,

                'registration_end_date' =>
                    $endDate,

                'registration_end_time' =>
                    $endTime,
            ])
            ->save();


        $admin
            ->university
            ->refresh();


        return back()->with(
            'success',
            'NSTP student registration period has been set.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Registration Deadline
    |--------------------------------------------------------------------------
    */

    public function destroyDeadline(): RedirectResponse
    {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $admin
            ->university
            ->forceFill([
                'registration_start_date' =>
                    null,

                'registration_end_date' =>
                    null,

                'registration_end_time' =>
                    null,
            ])
            ->save();


        return back()->with(
            'success',
            'NSTP student registration period has been removed.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Student Approval Page
    |--------------------------------------------------------------------------
    */

    public function show(
        User $student
    ): InertiaResponse {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $this->ensureStudentBelongsToUniversity(
            student:
                $student,

            admin:
                $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Registration Must Be Submitted
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $student->signature_path
            )
        ) {
            abort(
                409,
                'This student has not submitted the NSTP registration yet.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Registration Timeline
        |--------------------------------------------------------------------------
        */

        $timeline =
            $this->timelineData(
                student:
                    $student,

                admin:
                    $admin
            );


        return Inertia::render(
            'UniversityAdmin/Registration/StudentApproval',
            [
                'student' =>
                    $this->studentData(
                        $student
                    ),

                'timeline' =>
                    $timeline,

                'signatureUrl' =>
                    filled(
                        $student->signature_path
                    )
                        ? route(
                            'university-admin.student-registration.signature',
                            $student
                        )
                        : '',

                'approveUrl' =>
                    route(
                        'university-admin.student-registration.approve',
                        $student
                    ),

                'backUrl' =>
                    route(
                        'university-admin.student-registration.index'
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve Student Registration
    |--------------------------------------------------------------------------
    */

    public function approve(
        User $student
    ): RedirectResponse {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $this->ensureStudentBelongsToUniversity(
            student:
                $student,

            admin:
                $admin
        );


        /*
        |--------------------------------------------------------------------------
        | Signature Required
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $student->signature_path
            )
        ) {
            return back()->withErrors([
                'approval' =>
                    'The student has not submitted a signed registration.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Current Internal Status
        |--------------------------------------------------------------------------
        */

        $status =
            $this->registrationStatus(
                $student
            );


        /*
        |--------------------------------------------------------------------------
        | Already Approved
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $status,

                [
                    'approved',
                    'confirmed',
                    'completed',
                ],

                true
            )
        ) {
            return redirect()
                ->route(
                    'university-admin.student-registration.registration',
                    $student
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Server-Side Deadline Protection
        |--------------------------------------------------------------------------
        */

        $timeline =
            $this->timelineData(
                student:
                    $student,

                admin:
                    $admin
            );


        if (
            !$timeline[
                'can_approve'
            ]
        ) {
            return back()->withErrors([
                'approval' =>
                    'Approval is locked until the registration editing deadline has ended.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Approve Registration
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $student
            ): void {

                $student
                    ->forceFill([
                        'registration_status' =>
                            'confirmed',

                        'confirmed_at' =>
                            Carbon::now(
                                self::REGISTRATION_TIMEZONE
                            ),
                    ])
                    ->save();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Continue To Registration Page
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.student-registration.registration',
                $student
            )
            ->with(
                'success',
                'Student registration approved successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Registration Review / Student ID Page
    |--------------------------------------------------------------------------
    */

    public function registrationPage(
        User $student
    ): InertiaResponse {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $this->ensureStudentBelongsToUniversity(
            student:
                $student,

            admin:
                $admin
        );


        $this->ensureStudentApproved(
            $student
        );


        return Inertia::render(
            'UniversityAdmin/Registration/RegistrationPage',
            [
                'student' =>
                    $this->studentData(
                        $student
                    ),

                'generatedStudentId' =>
                    $student
                        ->student_id_number
                    ??
                    '',

                'generateStudentIdUrl' =>
                    route(
                        'university-admin.student-registration.generate-student-id',
                        $student
                    ),

                'generateCredentialsUrl' =>
                    route(
                        'university-admin.student-registration.generate-credentials',
                        $student
                    ),

                'backUrl' =>
                    route(
                        'university-admin.student-registration.index'
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Student ID Number
    |--------------------------------------------------------------------------
    */

    public function generateStudentId(
        User $student
    ): RedirectResponse {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $this->ensureStudentBelongsToUniversity(
            student:
                $student,

            admin:
                $admin
        );


        $this->ensureStudentApproved(
            $student
        );


        /*
        |--------------------------------------------------------------------------
        | Do Not Replace Existing Student ID
        |--------------------------------------------------------------------------
        */

        if (
            filled(
                $student->student_id_number
            )
        ) {
            return back()->with(
                'success',
                'Student ID number already exists.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Year
        |--------------------------------------------------------------------------
        */

        $year =
            Carbon::now(
                self::REGISTRATION_TIMEZONE
            )
                ->format(
                    'y'
                );


        /*
        |--------------------------------------------------------------------------
        | Student Sequence
        |--------------------------------------------------------------------------
        */

        $sequence =
            str_pad(
                (string)
                $student->id,

                5,

                '0',

                STR_PAD_LEFT
            );


        /*
        |--------------------------------------------------------------------------
        | Student ID
        |--------------------------------------------------------------------------
        */

        /*
         * University-specific NSTP ID, for example:
         * NSTP-SNSU26-00001 or NSTP-CSU26-00027.
         * Use the student's university, not a client-supplied acronym.
         */
        $university = $student->university;

        $acronym = strtoupper(preg_replace(
            '/[^A-Za-z0-9]/',
            '',
            (string) ($university?->acronym ?? '')
        ));

        if ($acronym === '') {
            return back()->withErrors([
                'student_id_number' =>
                    'Set the university acronym before generating a student ID.',
            ]);
        }

        $studentIdNumber = 'NSTP-' . $acronym . $year . '-' . $sequence;


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $student
            ->forceFill([
                'student_id_number' =>
                    $studentIdNumber,
            ])
            ->save();


        return back()->with(
            'success',
            'Student ID number generated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate QR Credentials
    |--------------------------------------------------------------------------
    */

    public function generateCredentials(
        User $student
    ): RedirectResponse {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $this->ensureStudentBelongsToUniversity(
            student:
                $student,

            admin:
                $admin
        );


        $this->ensureStudentApproved(
            $student
        );


        /*
        |--------------------------------------------------------------------------
        | Student ID Required
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $student->student_id_number
            )
        ) {
            return back()->withErrors([
                'student_id_number' =>
                    'Generate the Student ID number first.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Secure QR Token
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $student->qr_token
            )
        ) {
            $student
                ->forceFill([
                    'qr_token' =>
                        Str::random(
                            64
                        ),
                ])
                ->save();
        }


        /*
        |--------------------------------------------------------------------------
        | Continue To ID / QR Page
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.student-registration.id-qr-management',
                $student
            )
            ->with(
                'success',
                'Student QR credentials generated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ID And QR Management
    |--------------------------------------------------------------------------
    */

    public function idQrManagement(
        User $student
    ): InertiaResponse {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $this->ensureStudentBelongsToUniversity(
            student:
                $student,

            admin:
                $admin
        );


        $this->ensureStudentApproved(
            $student
        );


        /*
        |--------------------------------------------------------------------------
        | Credentials Required
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $student->student_id_number
            )
            ||
            blank(
                $student->qr_token
            )
        ) {
            abort(
                409,
                'Generate the student ID and QR credentials first.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        $university =
            $admin->university;


        /*
        |--------------------------------------------------------------------------
        | University Logo
        |--------------------------------------------------------------------------
        */

        $universityLogoUrl =
            $this->publicStorageUrl(
                $university->logo
            );


        /*
        |--------------------------------------------------------------------------
        | NSTP Logo
        |--------------------------------------------------------------------------
        */

        $nstpLogoUrl =
            file_exists(
                public_path(
                    'images/nstphub_logo.png'
                )
            )
                ? asset(
                    'images/nstphub_logo.png'
                )
                : $universityLogoUrl;


        /*
        |--------------------------------------------------------------------------
        | Render ID And QR Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Registration/IDandQRManagement',
            [
                'student' => [
                    ...
                    $this->studentData(
                        $student
                    ),

                    'course_acronym' =>
                        $this->courseAcronym(
                            $student->course
                        ),
                ],

                'university' => [
                    'id' =>
                        $university->id,

                    'name' =>
                        $university->name,

                    'acronym' =>
                        $university->acronym,

                    'logo_url' =>
                        $universityLogoUrl,
                ],

                'studentIdNumber' =>
                    $student
                        ->student_id_number,

                'profilePhotoUrl' =>
                    $this->publicStorageUrl(
                        $student->profile_photo
                    ),

                'qrCodeUrl' =>
                    route(
                        'university-admin.student-registration.qr-code',
                        $student
                    ),

                'universityLogoUrl' =>
                    $universityLogoUrl,

                'nstpLogoUrl' =>
                    $nstpLogoUrl,

                'watermarkUrl' =>
                    $nstpLogoUrl,

                'doneUrl' =>
                    route(
                        'university-admin.student-registration.index'
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | QR Code
    |--------------------------------------------------------------------------
    */

    public function qrCode(
        User $student
    ): HttpResponse {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $this->ensureStudentBelongsToUniversity(
            student:
                $student,

            admin:
                $admin
        );


        /*
        |--------------------------------------------------------------------------
        | QR Token Required
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $student->qr_token
            )
        ) {
            abort(
                404,
                'Student QR code has not been generated.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Secure QR Payload
        |--------------------------------------------------------------------------
        */

        $payload =
            'NSTPHUB:ATTENDANCE:'
            .
            $student->qr_token;


        /*
        |--------------------------------------------------------------------------
        | Generate QR
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Return PNG
        |--------------------------------------------------------------------------
        */

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
    | Student Signature
    |--------------------------------------------------------------------------
    */

    public function signature(
        User $student
    ): BinaryFileResponse {
        $admin =
            $this->getAuthenticatedUniversityAdmin();


        $this->ensureStudentBelongsToUniversity(
            student:
                $student,

            admin:
                $admin
        );


        if (
            blank(
                $student->signature_path
            )
        ) {
            abort(
                404
            );
        }


        $disk =
            Storage::disk(
                'local'
            );


        if (
            !$disk->exists(
                $student->signature_path
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
            $student->signature_path
        );


        return response()->file(
            $disk->path(
                $student->signature_path
            ),

            [
                'Content-Type' =>
                    'image/svg+xml',

                'Cache-Control' =>
                    'private, no-store, no-cache, must-revalidate',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticated University Administrator
    |--------------------------------------------------------------------------
    */

    private function getAuthenticatedUniversityAdmin():
        UniversityAdministrator
    {
        $admin =
            Auth::guard(
                'university_admin'
            )->user();


        if (
            !$admin
        ) {
            abort(
                401,
                'You must be logged in as a University Administrator.'
            );
        }


        if (
            !(
                $admin instanceof
                UniversityAdministrator
            )
        ) {
            abort(
                403,
                'Invalid University Administrator account.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Fresh University Information
        |--------------------------------------------------------------------------
        */

        $admin->load(
            'university'
        );


        if (
            !$admin->university_id
            ||
            !$admin->university
        ) {
            abort(
                403,
                'University Administrator is not connected to a university.'
            );
        }


        return
            $admin;
    }


    /*
    |--------------------------------------------------------------------------
    | University Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureStudentBelongsToUniversity(
        User $student,
        UniversityAdministrator $admin
    ): void {
        if (
            (int)
            $student->university_id
            !==
            (int)
            $admin->university_id
        ) {
            abort(
                403,
                'You are not authorized to access this student.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Approved Registration Required
    |--------------------------------------------------------------------------
    */

    private function ensureStudentApproved(
        User $student
    ): void {
        if (
            $this->displayRegistrationStatus(
                $student
            )
            !==
            'APPROVED'
        ) {
            abort(
                409,
                'The student registration must be approved first.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Student Data
    |--------------------------------------------------------------------------
    */

    private function studentData(
        User $student
    ): array {
        return [
            'id' =>
                $student->id,

            'name' =>
                $student->name,

            'full_name' =>
                $student->full_name,

            'email' =>
                $student->email,

            'student_id_number' =>
                $student->student_id_number,


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
            | Name
            |--------------------------------------------------------------------------
            */

            'surname' =>
                $student->surname,

            'first_name' =>
                $student->first_name,

            'middle_name' =>
                $student->middle_name,


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


            /*
            |--------------------------------------------------------------------------
            | Personal
            |--------------------------------------------------------------------------
            */

            'gender' =>
                $student->gender,

            'birth_date' =>
                $student->birth_date
                    ?->format(
                        'Y-m-d'
                    ),

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
            | Profile Photo
            |--------------------------------------------------------------------------
            */

            'profile_photo' =>
                $student->profile_photo,

            'profile_photo_url' =>
                $this->publicStorageUrl(
                    $student->profile_photo
                ),


            /*
            |--------------------------------------------------------------------------
            | Internal Workflow Status
            |--------------------------------------------------------------------------
            |
            | This remains available for application logic.
            |
            */

            'registration_status' =>
                $this->registrationStatus(
                    $student
                ),


            /*
            |--------------------------------------------------------------------------
            | University Admin Display Status
            |--------------------------------------------------------------------------
            |
            | ONLY:
            |
            | PENDING
            | APPROVED
            |
            */

            'display_status' =>
                $this->displayRegistrationStatus(
                    $student
                ),


            /*
            |--------------------------------------------------------------------------
            | Registration Dates
            |--------------------------------------------------------------------------
            */

            'registration_submitted_at' =>
                $student
                    ->registration_submitted_at,

            'registration_completed_at' =>
                $student
                    ->registration_completed_at,

            'confirmed_at' =>
                $student
                    ->confirmed_at,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Internal Registration Status
    |--------------------------------------------------------------------------
    |
    | This method keeps the detailed status needed by the workflow.
    |
    */

    private function registrationStatus(
        User $student
    ): string {
        $stored =
            strtolower(
                trim(
                    (string) (
                        $student
                            ->registration_status
                        ??
                        ''
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Valid Stored Status
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $stored,

                [
                    'not_started',
                    'draft',
                    'pending',
                    'submitted',
                    'under_review',
                    'approved',
                    'confirmed',
                    'completed',
                ],

                true
            )
        ) {
            return
                $stored;
        }


        /*
        |--------------------------------------------------------------------------
        | Completed
        |--------------------------------------------------------------------------
        */

        if (
            $student
                ->registration_completed_at
            !==
            null
        ) {
            return
                'completed';
        }


        /*
        |--------------------------------------------------------------------------
        | Confirmed
        |--------------------------------------------------------------------------
        */

        if (
            $student
                ->confirmed_at
            !==
            null
        ) {
            return
                'confirmed';
        }


        /*
        |--------------------------------------------------------------------------
        | Submitted
        |--------------------------------------------------------------------------
        */

        if (
            filled(
                $student->signature_path
            )
        ) {
            return
                'under_review';
        }


        /*
        |--------------------------------------------------------------------------
        | Default
        |--------------------------------------------------------------------------
        */

        return
            'draft';
    }


    /*
    |--------------------------------------------------------------------------
    | University Admin Display Status
    |--------------------------------------------------------------------------
    |
    | This is deliberately separate from registrationStatus().
    |
    | Internal:
    |
    | not_started
    | draft
    | submitted
    | under_review
    | confirmed
    | completed
    |
    | Display:
    |
    | PENDING
    | APPROVED
    |
    */

    private function displayRegistrationStatus(
        User $student
    ): string {
        $status =
            $this->registrationStatus(
                $student
            );


        /*
        |--------------------------------------------------------------------------
        | Approved
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $status,

                [
                    'approved',
                    'confirmed',
                    'completed',
                ],

                true
            )
        ) {
            return
                'APPROVED';
        }


        /*
        |--------------------------------------------------------------------------
        | Everything Else
        |--------------------------------------------------------------------------
        */

        return
            'PENDING';
    }


    /*
    |--------------------------------------------------------------------------
    | Registration Timeline
    |--------------------------------------------------------------------------
    */

    private function timelineData(
        User $student,
        UniversityAdministrator $admin
    ): array {
        $university =
            $admin->university;


        /*
        |--------------------------------------------------------------------------
        | Philippine Time
        |--------------------------------------------------------------------------
        */

        $now =
            Carbon::now(
                self::REGISTRATION_TIMEZONE
            );


        /*
        |--------------------------------------------------------------------------
        | Registration Start
        |--------------------------------------------------------------------------
        */

        $startDate =
            $this->normalizeDate(
                $university
                    ->registration_start_date
            );


        /*
        |--------------------------------------------------------------------------
        | Registration End
        |--------------------------------------------------------------------------
        */

        $endDate =
            $this->normalizeDate(
                $university
                    ->registration_end_date
            );


        /*
        |--------------------------------------------------------------------------
        | End Time
        |--------------------------------------------------------------------------
        */

        $endTime =
            $this->normalizeTime(
                $university
                    ->registration_end_time
            );


        /*
        |--------------------------------------------------------------------------
        | Submitted Timeline Date
        |--------------------------------------------------------------------------
        */

        if (
            $startDate
        ) {
            $submittedAt =
                Carbon::createFromFormat(
                    'Y-m-d H:i:s',

                    $startDate
                    .
                    ' 00:00:00',

                    self::REGISTRATION_TIMEZONE
                );
        } elseif (
            $student
                ->registration_submitted_at
        ) {
            $submittedAt =
                Carbon::parse(
                    $student
                        ->registration_submitted_at,

                    self::REGISTRATION_TIMEZONE
                );
        } else {
            $submittedAt =
                Carbon::parse(
                    $student->updated_at,

                    self::REGISTRATION_TIMEZONE
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Exact Editable Deadline
        |--------------------------------------------------------------------------
        */

        if (
            $endDate
        ) {
            $editableUntil =
                Carbon::createFromFormat(
                    'Y-m-d H:i:s',

                    $endDate
                    .
                    ' '
                    .
                    $endTime,

                    self::REGISTRATION_TIMEZONE
                );
        } else {
            $editableUntil =
                $submittedAt
                    ->copy()
                    ->addDays(
                        7
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | Remaining Seconds
        |--------------------------------------------------------------------------
        */

        if (
            $now->greaterThanOrEqualTo(
                $editableUntil
            )
        ) {
            $remainingSeconds =
                0;
        } else {
            $remainingSeconds =
                (int)
                $now->diffInSeconds(
                    $editableUntil,
                    false
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Remaining Days
        |--------------------------------------------------------------------------
        */

        $remainingDays =
            $remainingSeconds > 0
                ? (int)
                    ceil(
                        $remainingSeconds
                        /
                        86400
                    )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | Already Approved
        |--------------------------------------------------------------------------
        */

        $alreadyApproved =
            $this->displayRegistrationStatus(
                $student
            )
            ===
            'APPROVED';


        /*
        |--------------------------------------------------------------------------
        | Can Approve
        |--------------------------------------------------------------------------
        */

        $canApprove =
            !$alreadyApproved
            &&
            $now->greaterThanOrEqualTo(
                $editableUntil
            );


        /*
        |--------------------------------------------------------------------------
        | Timeline Data
        |--------------------------------------------------------------------------
        */

        return [
            'submitted_at' =>
                $submittedAt
                    ->toIso8601String(),

            'editable_until' =>
                $editableUntil
                    ->toIso8601String(),

            'registration_start_date' =>
                $startDate,

            'registration_end_date' =>
                $endDate,

            'registration_end_time' =>
                $endTime,

            'remaining_days' =>
                $remainingDays,

            'remaining_seconds' =>
                $remainingSeconds,

            'can_approve' =>
                $canApprove,

            /*
            |--------------------------------------------------------------------------
            | Simplified Display Status
            |--------------------------------------------------------------------------
            */

            'display_status' =>
                $this->displayRegistrationStatus(
                    $student
                ),

            'timezone' =>
                self::REGISTRATION_TIMEZONE,

            'deadline_source' =>
                $endDate
                    ? 'university_registration_period'
                    : 'fallback_seven_days',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Registration Deadline Data
    |--------------------------------------------------------------------------
    */

    private function registrationDeadlineData(
        $university
    ): ?array {
        $startDate =
            $this->normalizeDate(
                $university
                    ->registration_start_date
            );


        $endDate =
            $this->normalizeDate(
                $university
                    ->registration_end_date
            );


        if (
            !$startDate
            &&
            !$endDate
        ) {
            return
                null;
        }


        return [
            'start_date' =>
                $startDate,

            'end_date' =>
                $endDate,

            'end_time' =>
                $this->normalizeTime(
                    $university
                        ->registration_end_time
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Date
    |--------------------------------------------------------------------------
    */

    private function normalizeDate(
        mixed $value
    ): ?string {
        if (
            blank(
                $value
            )
        ) {
            return
                null;
        }


        if (
            $value instanceof
            DateTimeInterface
        ) {
            return Carbon::instance(
                $value
            )
                ->format(
                    'Y-m-d'
                );
        }


        return Carbon::parse(
            (string)
            $value,

            self::REGISTRATION_TIMEZONE
        )
            ->format(
                'Y-m-d'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Time
    |--------------------------------------------------------------------------
    */

    private function normalizeTime(
        mixed $value
    ): string {
        if (
            blank(
                $value
            )
        ) {
            return
                '23:59:59';
        }


        if (
            $value instanceof
            DateTimeInterface
        ) {
            return Carbon::instance(
                $value
            )
                ->format(
                    'H:i:s'
                );
        }


        $time =
            trim(
                (string)
                $value
            );


        if (
            preg_match(
                '/^\d{2}:\d{2}$/',
                $time
            )
        ) {
            return
                $time
                .
                ':00';
        }


        if (
            preg_match(
                '/^\d{2}:\d{2}:\d{2}$/',
                $time
            )
        ) {
            return
                $time;
        }


        return Carbon::parse(
            $time,
            self::REGISTRATION_TIMEZONE
        )
            ->format(
                'H:i:s'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Public Storage URL
    |--------------------------------------------------------------------------
    */

    private function publicStorageUrl(
        ?string $path
    ): string {
        if (
            blank(
                $path
            )
        ) {
            return '';
        }


        if (
            Str::startsWith(
                $path,

                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return
                $path;
        }


        $normalizedPath =
            ltrim(
                $path,
                '/'
            );


        if (
            Str::startsWith(
                $normalizedPath,
                'storage/'
            )
        ) {
            $normalizedPath =
                Str::after(
                    $normalizedPath,
                    'storage/'
                );
        }


        return url(
            Storage::disk(
                'public'
            )->url(
                $normalizedPath
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Course Acronym
    |--------------------------------------------------------------------------
    */

    private function courseAcronym(
        ?string $course
    ): string {
        if (
            blank(
                $course
            )
        ) {
            return '';
        }


        if (
            Str::contains(
                strtolower(
                    $course
                ),
                'information technology'
            )
        ) {
            return
                'BSIT';
        }


        return collect(
            preg_split(
                '/\s+/',
                trim(
                    $course
                )
            )
        )
            ->reject(
                fn (
                    $word
                ) =>
                    in_array(
                        strtolower(
                            $word
                        ),

                        [
                            'of',
                            'in',
                            'and',
                            'the',
                        ],

                        true
                    )
            )
            ->map(
                fn (
                    $word
                ) =>
                    strtoupper(
                        substr(
                            $word,
                            0,
                            1
                        )
                    )
            )
            ->implode('');
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