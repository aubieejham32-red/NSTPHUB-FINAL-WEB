<?php

namespace App\Http\Controllers\InstructorCoordinators;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSchedule;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\RotcStudentProfile;
use App\Models\University;
use App\Models\UniversityAdministrator;
use App\Models\User;
use App\Support\NstpComponentAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class PdfExportController extends Controller
{
    private const APPROVED_REGISTRATION_STATUSES = [
        'approved',
        'confirmed',
        'completed',
    ];


    /*
    |--------------------------------------------------------------------------
    | DAILY ATTENDANCE PDF
    |--------------------------------------------------------------------------
    */

    public function dailyAttendance(
        Request $request
    ): Response {

        $context =
            $this->actorContext();


        $validated =
            $request->validate([
                'date' => [
                    'nullable',
                    'date_format:Y-m-d',
                ],

                'component' => [
                    'nullable',
                    'string',
                    Rule::in(
                        Attendance::components()
                    ),
                ],
            ]);


        $date =
            $validated['date']
            ??
            AttendanceSchedule::today();


        $component =
            $this->resolveComponent(
                $context,
                $validated['component']
                ??
                null
            );


        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */

        $students =
            User::query()
                ->where(
                    'university_id',
                    $context['university_id']
                )
                ->whereNotNull(
                    'component'
                )
                ->when(
                    $component !== '',
                    fn (
                        Builder $query
                    ) =>
                        $query->whereRaw(
                            'UPPER(component) = ?',
                            [
                                $component,
                            ]
                        )
                )
                ->orderBy(
                    'surname'
                )
                ->orderBy(
                    'first_name'
                )
                ->get();


        $studentIds =
            $students->pluck(
                'id'
            );


        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE FOR SELECTED DATE
        |--------------------------------------------------------------------------
        */

        $attendanceRecords =
            Attendance::query()
                ->whereDate(
                    'attendance_date',
                    $date
                )
                ->whereIn(
                    'user_id',
                    $studentIds
                )
                ->when(
                    $component !== '',
                    fn (
                        Builder $query
                    ) =>
                        $query->where(
                            'component',
                            $component
                        )
                )
                ->get()
                ->keyBy(
                    fn (
                        Attendance $attendance
                    ): string =>
                        $attendance->user_id
                        .
                        '|'
                        .
                        $this->normalizeComponent(
                            $attendance->component
                        )
                );


        /*
        |--------------------------------------------------------------------------
        | PDF ROWS
        |--------------------------------------------------------------------------
        */

        $rows =
            $students
                ->map(
                    function (
                        User $student
                    ) use (
                        $attendanceRecords
                    ): array {

                        $studentComponent =
                            $this->normalizeComponent(
                                $student->component
                            );


                        $record =
                            $attendanceRecords
                                ->get(
                                    $student->id
                                    .
                                    '|'
                                    .
                                    $studentComponent
                                );


                        [
                            $timeInRemark,
                            $timeOutRemark,
                        ] =
                            $this->rotcScores(
                                $record?->remark
                            );


                        return [
                            'student_id_number' =>
                                $this->studentIdNumber(
                                    $student
                                ),

                            'full_name' =>
                                $student->full_name,

                            'course_year_section' =>
                                $this->courseYearSection(
                                    $student
                                ),

                            'component' =>
                                $studentComponent,

                            'time_in' =>
                                $this->formatTime(
                                    $record?->time_in
                                ),

                            'time_out' =>
                                $this->formatTime(
                                    $record?->time_out
                                ),

                            'remark' =>
                                $record?->remark
                                ?:
                                'NO RECORD',

                            'notes' =>
                                $record?->notes
                                ?:
                                '',

                            'rotc_time_in_remark' =>
                                $timeInRemark,

                            'rotc_time_out_remark' =>
                                $timeOutRemark,
                        ];
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | UNIVERSITY
        |--------------------------------------------------------------------------
        */

        $university =
            University::query()
                ->findOrFail(
                    $context[
                        'university_id'
                    ]
                );


        $documentComponent =
            $component !== ''
                ? $component
                : 'CWTS / LTS / ROTC';


        $branding =
            $this->branding(
                $university,
                $documentComponent,
                $context
            );


        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'pdfs.nstp.attendance-daily',
                [
                    'branding' =>
                        $branding,

                    'title' =>
                        'DAILY STUDENT ATTENDANCE LIST',

                    'attendanceDate' =>
                        CarbonImmutable::parse(
                            $date
                        )->format(
                            'F d, Y'
                        ),

                    'component' =>
                        $documentComponent,

                    'rows' =>
                        $rows,

                    'isRotc' =>
                        $documentComponent
                        ===
                        Attendance::COMPONENT_ROTC,
                ]
            )
                ->setPaper(
                    'legal',
                    'landscape'
                );


        return $pdf->download(
            'nstp-attendance-'
            .
            strtolower(
                str_replace(
                    ' / ',
                    '-',
                    $documentComponent
                )
            )
            .
            '-'
            .
            $date
            .
            '.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INDIVIDUAL STUDENT ATTENDANCE PDF
    |--------------------------------------------------------------------------
    */

    public function studentAttendance(
        int $student
    ): Response {

        $context =
            $this->actorContext();


        $studentRecord =
            $this->findAccessibleStudent(
                $student,
                $context,
                false
            );


        $component =
            $this->normalizeComponent(
                $studentRecord->component
            );


        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE HISTORY
        |--------------------------------------------------------------------------
        */

        $records =
            Attendance::query()
                ->where(
                    'user_id',
                    $studentRecord->id
                )
                ->when(
                    $component !== '',
                    fn (
                        Builder $query
                    ) =>
                        $query->where(
                            'component',
                            $component
                        )
                )
                ->orderByDesc(
                    'attendance_date'
                )
                ->orderByDesc(
                    'id'
                )
                ->get()
                ->map(
                    function (
                        Attendance $record
                    ): array {

                        [
                            $timeInScore,
                            $timeOutScore,
                        ] =
                            $this->rotcScores(
                                $record->remark
                            );


                        return [
                            'date' =>
                                $record
                                    ->attendance_date
                                    ?->format(
                                        'F d, Y'
                                    )
                                ?:
                                '-',

                            'time_in' =>
                                $this->formatTime(
                                    $record->time_in
                                ),

                            'time_out' =>
                                $this->formatTime(
                                    $record->time_out
                                ),

                            'remark' =>
                                $record->remark
                                ?:
                                '-',

                            'notes' =>
                                $record->notes
                                ?:
                                '',

                            'rotc_time_in_remark' =>
                                $timeInScore,

                            'rotc_time_out_remark' =>
                                $timeOutScore,
                        ];
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $summary = [
            'present' =>
                $records
                    ->where(
                        'remark',
                        Attendance::REMARK_PRESENT
                    )
                    ->count(),

            'late' =>
                $records
                    ->where(
                        'remark',
                        Attendance::REMARK_LATE
                    )
                    ->count(),

            'excused' =>
                $records
                    ->where(
                        'remark',
                        Attendance::REMARK_EXCUSED
                    )
                    ->count(),

            'absent' =>
                $records
                    ->where(
                        'remark',
                        Attendance::REMARK_ABSENT
                    )
                    ->count(),

            'total' =>
                $records->count(),
        ];


        $university =
            University::query()
                ->findOrFail(
                    $context[
                        'university_id'
                    ]
                );


        $branding =
            $this->branding(
                $university,
                $component,
                $context
            );


        $pdf =
            Pdf::loadView(
                'pdfs.nstp.attendance-student',
                [
                    'branding' =>
                        $branding,

                    'title' =>
                        'STUDENT ATTENDANCE RECORD',

                    'student' =>
                        $studentRecord,

                    'component' =>
                        $component,

                    'records' =>
                        $records,

                    'summary' =>
                        $summary,

                    'isRotc' =>
                        $component
                        ===
                        Attendance::COMPONENT_ROTC,
                ]
            )
                ->setPaper(
                    'legal',
                    'landscape'
                );


        return $pdf->download(
            'attendance-'
            .
            $this->safeFilename(
                $studentRecord->full_name
            )
            .
            '.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT LIST PDF
    |--------------------------------------------------------------------------
    */

    public function studentList(
        Request $request
    ): Response {

        $context =
            $this->actorContext();


        $validated =
            $request->validate([
                'component' => [
                    'nullable',
                    'string',

                    Rule::in(
                        Attendance::components()
                    ),
                ],
            ]);


        $component =
            $this->resolveComponent(
                $context,
                $validated['component']
                ??
                null
            );


        $students =
            $this
                ->accessibleStudentsQuery(
                    $context,
                    true
                )
                ->when(
                    $component !== '',
                    fn (
                        Builder $query
                    ) =>
                        $query->whereRaw(
                            'UPPER(component) = ?',
                            [
                                $component,
                            ]
                        )
                )
                ->orderBy(
                    'surname'
                )
                ->orderBy(
                    'first_name'
                )
                ->get();


        $university =
            University::query()
                ->findOrFail(
                    $context[
                        'university_id'
                    ]
                );


        $documentComponent =
            $component !== ''
                ? $component
                : 'CWTS / LTS / ROTC';


        $branding =
            $this->branding(
                $university,
                $documentComponent,
                $context
            );


        $pdf =
            Pdf::loadView(
                'pdfs.nstp.student-list',
                [
                    'branding' =>
                        $branding,

                    'title' =>
                        'LIST OF ENROLLED STUDENTS',

                    'component' =>
                        $documentComponent,

                    'students' =>
                        $students,
                ]
            )
                ->setPaper(
                    'legal',
                    'landscape'
                );


        return $pdf->download(
            'nstp-student-list-'
            .
            strtolower(
                str_replace(
                    ' / ',
                    '-',
                    $documentComponent
                )
            )
            .
            '.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT PROFILE PDF
    |--------------------------------------------------------------------------
    */

    public function studentProfile(int $student): Response
    {
        // Keep the existing university / component authorization checks.
        $context = $this->actorContext();
        $studentRecord = $this->findAccessibleStudent($student, $context, true);
        $component = $this->normalizeComponent($studentRecord->component);
        $university = University::query()->findOrFail($context['university_id']);

        $rotcProfile = $component === Attendance::COMPONENT_ROTC
            ? RotcStudentProfile::query()
                ->with('msRecords')
                ->where('user_id', $studentRecord->id)
                ->first()
            : null;

        $branding = $this->branding($university, $component, $context);

        /*
         * Render the student's own registration signature from private storage.
         * Never use a public /storage URL for this sensitive personal information.
         * Missing signatures remain blank; officers must sign separately.
         */
        $studentSignature = null;
        $signaturePath = $studentRecord->signature_path;

        if (
            is_string($signaturePath)
            && preg_match(
                '~\Asignatures/general/' . preg_quote((string) $studentRecord->id, '~') . '/[a-f0-9-]+\.svg\z~i',
                $signaturePath
            )
            && Storage::disk('local')->exists($signaturePath)
        ) {
            $svg = Storage::disk('local')->get($signaturePath);

            // Signatures are generated by our application as a simple SVG
            // (paths and a background rectangle, never external resources).
            if (
                is_string($svg)
                && strlen($svg) <= 300000
                && str_contains($svg, '<svg ')
                && !preg_match('~<(?:script|foreignObject|image|use|iframe)\b|(?:href|onload|onclick)\s*=~i', $svg)
            ) {
                $studentSignature = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }

        $pdf = Pdf::loadView('pdfs.nstp.registration-form', [
            'student' => $studentRecord,
            'university' => $university,
            'component' => $component,
            'rotcProfile' => $rotcProfile,
            'branding' => $branding,
            'studentSignature' => $studentSignature,
        ])->setPaper([0, 0, 612, 936], 'portrait');

        return $pdf->download(
            'nstp-registration-' . $this->safeFilename($studentRecord->full_name) . '.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LOGGED-IN USER CONTEXT
    |--------------------------------------------------------------------------
    */

    private function actorContext(): array
    {

        if (
            Auth::guard(
                'university_admin'
            )->check()
        ) {

            $actor =
                Auth::guard(
                    'university_admin'
                )->user();


            $access =
                NstpComponentAccess::context(
                    request()
                );


            return [
                'actor' =>
                    $actor,

                'role' =>
                    'university-admin',

                'university_id' =>
                    (int) $access['university_id'],

                'component' =>
                    $access['selected_component'],
            ];
        }


        if (
            Auth::guard(
                'instructor'
            )->check()
        ) {

            $actor =
                Auth::guard(
                    'instructor'
                )->user();


            return [
                'actor' =>
                    $actor,

                'role' =>
                    'instructor',

                'university_id' =>
                    (int)
                    $actor->university_id,

                'component' =>
                    NstpComponentAccess::selected(
                        request(),
                        [
                            'components' =>
                                $actor->componentCodes(),
                        ]
                    ),
            ];
        }


        if (
            Auth::guard(
                'coordinator'
            )->check()
        ) {

            $actor =
                Auth::guard(
                    'coordinator'
                )->user();


            return [
                'actor' =>
                    $actor,

                'role' =>
                    'coordinator',

                'university_id' =>
                    (int)
                    $actor->university_id,

                'component' =>
                    $this
                        ->normalizeComponent(
                            $actor->component
                        ),
            ];
        }


        abort(
            401,
            'You must be logged in to download NSTP PDF records.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPONENT
    |--------------------------------------------------------------------------
    */

    private function resolveComponent(
        array $context,
        ?string $requested
    ): string {

        if (
            $context['role']
            !==
            'university-admin'
        ) {

            if (
                $context['component']
                ===
                ''
            ) {

                abort(
                    403,
                    'Your account is not assigned to an NSTP component.'
                );
            }


            return $context[
                'component'
            ];
        }


        return $this
            ->normalizeComponent(
                $requested
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSIBLE STUDENTS
    |--------------------------------------------------------------------------
    */

    private function accessibleStudentsQuery(
        array $context,
        bool $approvedOnly
    ): Builder {

        return User::query()
            ->where(
                'university_id',
                $context[
                    'university_id'
                ]
            )
            ->when(
                $context['role']
                !==
                'university-admin',

                fn (
                    Builder $query
                ) =>
                    $query->whereRaw(
                        'UPPER(component) = ?',
                        [
                            $context[
                                'component'
                            ],
                        ]
                    )
            )
            ->when(
                $approvedOnly,

                fn (
                    Builder $query
                ) =>
                    $query->whereIn(
                        'registration_status',
                        self::APPROVED_REGISTRATION_STATUSES
                    )
            );
    }


    private function findAccessibleStudent(
        int $student,
        array $context,
        bool $approvedOnly
    ): User {

        return $this
            ->accessibleStudentsQuery(
                $context,
                $approvedOnly
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
    | PDF BRANDING
    |--------------------------------------------------------------------------
    */

    private function branding(
        University $university,
        string $component,
        array $context
    ): array {

        $instructor =
            null;


        if (
            $context['role']
            ===
            'instructor'
        ) {

            $instructor =
                $context['actor'];
        }


        /*
        |--------------------------------------------------------------------------
        | FIND UNIVERSITY INSTRUCTOR
        |--------------------------------------------------------------------------
        */

        if (
            !$instructor
            &&
            in_array(
                $component,
                Attendance::components(),
                true
            )
        ) {

            $instructor =
                Instructor::query()
                    ->where(
                        'university_id',
                        $university->id
                    )
                    ->whereRaw(
                        'UPPER(component) = ?',
                        [
                            $component,
                        ]
                    )
                    ->orderBy(
                        'id'
                    )
                    ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | UNIVERSITY ADMIN
        |--------------------------------------------------------------------------
        */

        $administrator =
            UniversityAdministrator::query()
                ->where(
                    'university_id',
                    $university->id
                )
                ->orderBy(
                    'id'
                )
                ->first();


        return [
            'university_logo' =>
                $this->imageDataUri(
                    $this->universityLogoPath(
                        $university
                    )
                ),

            'rotc_logo' =>
                $this->imageDataUri(
                    public_path('images/rotc_logo.png')
                ),

            'nstphub_logo' =>
                $this->imageDataUri(
                    public_path(
                        'images/nstphub_logo.png'
                    )
                ),

            'component' =>
                $component,

            'university_name' =>
                (string)
                $university->name,

            'campus_type' =>
                (string)
                (
                    $university->campus_type
                    ?:
                    $university->type
                    ?:
                    ''
                ),

            'location' =>
                $this->universityLocation(
                    $university
                ),

            'instructor_name' =>
                $this->actorName(
                    $instructor
                ),

            'administrator_name' =>
                $this->actorName(
                    $administrator
                ),

            'generated_at' =>
                now(
                    'Asia/Manila'
                )->format(
                    'F d, Y h:i A'
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | UNIVERSITY LOGO
    |--------------------------------------------------------------------------
    */

    private function universityLogoPath(
        University $university
    ): ?string {

        $logo =
            trim(
                (string)
                $university->logo
            );


        if (
            $logo !== ''
            &&
            Storage::disk(
                'public'
            )->exists(
                $logo
            )
        ) {

            return Storage::disk(
                'public'
            )->path(
                $logo
            );
        }


        $fallback =
            public_path(
                'images/snsu logo.png'
            );


        return is_file(
            $fallback
        )
            ? $fallback
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | IMAGE TO DATA URI
    |--------------------------------------------------------------------------
    */

    private function imageDataUri(
        ?string $path
    ): ?string {

        if (
            !$path
            ||
            !is_file(
                $path
            )
            ||
            !is_readable(
                $path
            )
        ) {

            return null;
        }


        $mime =
            mime_content_type(
                $path
            )
            ?:
            'image/png';


        $contents =
            file_get_contents(
                $path
            );


        return $contents === false
            ? null
            : 'data:'
                .
                $mime
                .
                ';base64,'
                .
                base64_encode(
                    $contents
                );
    }


    /*
    |--------------------------------------------------------------------------
    | UNIVERSITY LOCATION
    |--------------------------------------------------------------------------
    */

    private function universityLocation(
        University $university
    ): string {

        if (
            filled(
                $university
                    ->complete_address
            )
        ) {

            return trim(
                (string)
                $university
                    ->complete_address
            );
        }


        return collect([
            $university->barangay,
            $university->city,
            $university->province,
            $university->region,
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
                ', '
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTOR NAME
    |--------------------------------------------------------------------------
    */

    private function actorName(
        ?object $actor
    ): string {

        if (
            !$actor
        ) {

            return (
                '____________________________'
            );
        }


        $fullName =
            trim(
                (string)
                (
                    $actor->full_name
                    ??
                    ''
                )
            );


        if (
            $fullName !== ''
        ) {

            return $fullName;
        }


        return trim(
            collect([
                $actor->first_name
                ??
                null,

                $actor->middle_name
                ??
                null,

                $actor->last_name
                ??
                null,
            ])
                ->filter(
                    fn (
                        $part
                    ) =>
                        filled(
                            $part
                        )
                )
                ->implode(
                    ' '
                )
        )
        ?:
        '____________________________';
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE COMPONENT
    |--------------------------------------------------------------------------
    */

    private function normalizeComponent(
        mixed $value
    ): string {

        $component =
            strtoupper(
                trim(
                    (string)
                    (
                        $value
                        ??
                        ''
                    )
                )
            );


        return in_array(
            $component,
            Attendance::components(),
            true
        )
            ? $component
            : '';
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT ID
    |--------------------------------------------------------------------------
    */

    private function studentIdNumber(
        User $student
    ): string {

        return trim(
            (string)
            (
                $student
                    ->getAttribute(
                        'student_id_number'
                    )
                ??
                $student
                    ->getAttribute(
                        'id_number'
                    )
                ??
                $student->id
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COURSE / YEAR / SECTION
    |--------------------------------------------------------------------------
    */

    private function courseYearSection(
        User $student
    ): string {

        $course =
            trim(
                (string)
                (
                    $student->course
                    ??
                    ''
                )
            );


        $year =
            trim(
                (string)
                (
                    $student->year_level
                    ??
                    ''
                )
            );


        $section =
            trim(
                (string)
                (
                    $student->section
                    ??
                    ''
                )
            );


        return collect([
            $course,

            $year !== ''
                ? 'Year '
                    .
                    $year
                : null,

            $section !== ''
                ? 'Sec. '
                    .
                    $section
                : null,
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
            )
        ?:
        '-';
    }


    /*
    |--------------------------------------------------------------------------
    | TIME FORMAT
    |--------------------------------------------------------------------------
    */

    private function formatTime(
        mixed $value
    ): string {

        $raw =
            trim(
                (string)
                (
                    $value
                    ??
                    ''
                )
            );


        if (
            $raw === ''
        ) {

            return '-';
        }


        try {

            return CarbonImmutable::parse(
                $raw
            )->format(
                'h:i A'
            );

        } catch (
            \Throwable
        ) {

            return $raw;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC REMARK SCORES
    |--------------------------------------------------------------------------
    |
    | PRESENT = 1 / 1
    | EXCUSED = 1 / 1
    | LATE    = 1 / -1
    | ABSENT  = -1 / -1
    |
    */

    private function rotcScores(
        ?string $remark
    ): array {

        return match (
            strtoupper(
                trim(
                    (string)
                    $remark
                )
            )
        ) {

            Attendance::REMARK_PRESENT,
            Attendance::REMARK_EXCUSED =>
                [
                    '1',
                    '1',
                ],

            Attendance::REMARK_LATE =>
                [
                    '1',
                    '-1',
                ],

            Attendance::REMARK_ABSENT =>
                [
                    '-1',
                    '-1',
                ],

            default =>
                [
                    '-',
                    '-',
                ],
        };
    }


    /*
    |--------------------------------------------------------------------------
    | SAFE PDF FILE NAME
    |--------------------------------------------------------------------------
    */

    private function safeFilename(
        string $value
    ): string {

        $value =
            preg_replace(
                '/[^A-Za-z0-9_-]+/',
                '-',
                trim(
                    $value
                )
            )
            ?:
            'student';


        return trim(
            $value,
            '-_'
        )
        ?:
        'student';
    }
}
