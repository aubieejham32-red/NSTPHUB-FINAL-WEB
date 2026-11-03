<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\Report;
use App\Models\User;
use App\Models\UniversityAdministrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class MobileReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    */

    private const TIMEZONE = 'Asia/Manila';


    /*
    |--------------------------------------------------------------------------
    | Student Reports
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/reports
    |
    */

    public function index(
        Request $request
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | The authenticated student receives ONLY their own reports.
        |
        */

        $reports =
            Report::query()
                ->where(
                    'user_id',
                    $student->id
                )
                ->where(
                    'university_id',
                    $student->university_id
                )
                ->with([
                    'student',
                    'instructor',
                    'reviewedBy',
                ])
                ->latest(
                    'created_at'
                )
                ->latest(
                    'id'
                )
                ->get()
                ->map(
                    fn (Report $report): array =>
                        $this->reportPayload(
                            $request,
                            $report
                        )
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Student Component
        |--------------------------------------------------------------------------
        */

        $studentComponent =
            $this->normalizeComponent(
                $student->component
                ??
                ''
            );


        /*
        |--------------------------------------------------------------------------
        | Allowed Components
        |--------------------------------------------------------------------------
        |
        | If the student already has a valid registered NSTP component,
        | mobile reporting is restricted to that component.
        |
        */

        $allowedComponents =
            in_array(
                $studentComponent,
                Report::components(),
                true
            )
                ? [
                    $studentComponent,
                ]
                : Report::components();


        /*
        |--------------------------------------------------------------------------
        | Available Instructors
        |--------------------------------------------------------------------------
        */

        $instructorsQuery =
            Instructor::query()
                ->where(
                    'university_id',
                    $student->university_id
                );


        // Multi-component assignments are stored in instructor_components.
        // Include legacy primary-component records for backward compatibility.
        if (in_array($studentComponent, Report::components(), true)) {
            $instructorsQuery->where(function ($query) use ($studentComponent) {
                $query->whereHas('managedComponents', function ($components) use ($studentComponent) {
                    $components->where('component', $studentComponent);
                })->orWhere(function ($legacy) use ($studentComponent) {
                    $legacy->where('component', $studentComponent);
                });
            });
        }

        $instructors =
            $instructorsQuery
                ->orderBy(
                    'full_name'
                )
                ->orderBy(
                    'id'
                )
                ->get()
                ->map(
                    function (
                        Instructor $instructor
                    ): array {
                        return [
                            'id' =>
                                $instructor->id,

                            'full_name' =>
                                $this->personName(
                                    $instructor,
                                    'Instructor'
                                ),

                            'email' =>
                                $instructor->email,

                            'component' =>
                                $this->normalizeComponent(
                                    $instructor->component
                                    ??
                                    ''
                                ),

                            'components' => $instructor->componentCodes(),
                        ];
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total' =>
                $reports->count(),

            'pending' =>
                $reports
                    ->where(
                        'status',
                        Report::STATUS_PENDING
                    )
                    ->count(),

            'in_review' =>
                $reports
                    ->where(
                        'status',
                        Report::STATUS_IN_REVIEW
                    )
                    ->count(),

            'resolved' =>
                $reports
                    ->where(
                        'status',
                        Report::STATUS_RESOLVED
                    )
                    ->count(),
        ];


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Student reports retrieved successfully.',

            'student' => [
                'id' =>
                    $student->id,

                'student_id_number' =>
                    $this->studentIdNumber(
                        $student
                    ),

                'full_name' =>
                    $this->personName(
                        $student,
                        'Student'
                    ),

                'email' =>
                    $student->email,

                'component' =>
                    $studentComponent,

                'profile_photo_url' =>
                    $this->publicFileUrl(
                        $request,
                        $student->profile_photo
                        ??
                        null
                    ),
            ],

            'allowed_components' =>
                $allowedComponents,

            'instructors' =>
                $instructors,

            'summary' =>
                $summary,

            'reports' =>
                $reports,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Submit Report
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /api/mobile/reports
    |
    */

    public function store(
        Request $request
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $validated =
            $this->validateReport(
                $request,
                $student
            );


        $attachmentPath =
            null;


        try {
            DB::beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Attachment
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'attachment'
                )
            ) {
                $attachmentPath =
                    $this->storeAttachment(
                        $request,
                        $student
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Create
            |--------------------------------------------------------------------------
            */

            $report =
                Report::create([
                    'user_id' =>
                        $student->id,

                    'university_id' =>
                        $student->university_id,

                    'instructor_id' =>
                        $validated[
                            'instructor_id'
                        ],

                    'component' =>
                        $validated[
                            'component'
                        ],

                    'occurrence_date' =>
                        $validated[
                            'occurrence_date'
                        ],

                    'subject' =>
                        trim(
                            $validated[
                                'subject'
                            ]
                        ),

                    'description' =>
                        trim(
                            $validated[
                                'description'
                            ]
                        ),

                    'attachment_path' =>
                        $attachmentPath,

                    'attachment_original_name' =>
                        $request
                            ->file(
                                'attachment'
                            )
                            ?->getClientOriginalName(),

                    'attachment_mime_type' =>
                        $request
                            ->file(
                                'attachment'
                            )
                            ?->getMimeType(),

                    'attachment_size' =>
                        $request
                            ->file(
                                'attachment'
                            )
                            ?->getSize(),

                    'status' =>
                        Report::STATUS_PENDING,

                    'feedback' =>
                        null,

                    'reviewed_by_type' =>
                        null,

                    'reviewed_by_id' =>
                        null,

                    'reviewed_at' =>
                        null,

                    'resolved_at' =>
                        null,
                ]);


            DB::commit();


            $report->load([
                'student',
                'instructor',
                'reviewedBy',
            ]);


            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Your report was submitted successfully.',

                'report' =>
                    $this->reportPayload(
                        $request,
                        $report
                    ),
            ], 201);

        } catch (
            Throwable $error
        ) {
            DB::rollBack();


            if (
                filled(
                    $attachmentPath
                )
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $attachmentPath
                );
            }


            report(
                $error
            );


            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unable to submit the report. Please try again.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Report
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /api/mobile/reports/{report}
    |
    | We intentionally use POST for the React Native multipart upload.
    |
    */

    public function update(
        Request $request,
        Report $report
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $this->ensureStudentOwnsReport(
            $report,
            $student
        );


        /*
        |--------------------------------------------------------------------------
        | Reviewed Reports Cannot Be Changed By Student
        |--------------------------------------------------------------------------
        */

        if (
            $report->status !==
            Report::STATUS_PENDING
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Only pending reports can be edited.',
            ], 422);
        }


        $validated =
            $this->validateReport(
                $request,
                $student
            );


        $newAttachmentPath =
            null;


        $oldAttachmentPath =
            $report->attachment_path;


        try {
            DB::beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Lock Report
            |--------------------------------------------------------------------------
            */

            $lockedReport =
                Report::query()
                    ->whereKey(
                        $report->id
                    )
                    ->where(
                        'user_id',
                        $student->id
                    )
                    ->where(
                        'university_id',
                        $student->university_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | New Attachment
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'attachment'
                )
            ) {
                $newAttachmentPath =
                    $this->storeAttachment(
                        $request,
                        $student
                    );


                $lockedReport->attachment_path =
                    $newAttachmentPath;


                $lockedReport->attachment_original_name =
                    $request
                        ->file(
                            'attachment'
                        )
                        ?->getClientOriginalName();


                $lockedReport->attachment_mime_type =
                    $request
                        ->file(
                            'attachment'
                        )
                        ?->getMimeType();


                $lockedReport->attachment_size =
                    $request
                        ->file(
                            'attachment'
                        )
                        ?->getSize();
            }


            /*
            |--------------------------------------------------------------------------
            | Update Fields
            |--------------------------------------------------------------------------
            */

            $lockedReport->instructor_id =
                $validated[
                    'instructor_id'
                ];


            $lockedReport->component =
                $validated[
                    'component'
                ];


            $lockedReport->occurrence_date =
                $validated[
                    'occurrence_date'
                ];


            $lockedReport->subject =
                trim(
                    $validated[
                        'subject'
                    ]
                );


            $lockedReport->description =
                trim(
                    $validated[
                        'description'
                    ]
                );


            $lockedReport->save();


            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | Delete Replaced Attachment
            |--------------------------------------------------------------------------
            */

            if (
                filled(
                    $newAttachmentPath
                )
                &&
                filled(
                    $oldAttachmentPath
                )
                &&
                $newAttachmentPath !==
                $oldAttachmentPath
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $oldAttachmentPath
                );
            }


            $lockedReport->load([
                'student',
                'instructor',
                'reviewedBy',
            ]);


            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Your report was updated successfully.',

                'report' =>
                    $this->reportPayload(
                        $request,
                        $lockedReport
                    ),
            ]);

        } catch (
            Throwable $error
        ) {
            DB::rollBack();


            if (
                filled(
                    $newAttachmentPath
                )
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $newAttachmentPath
                );
            }


            report(
                $error
            );


            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unable to update the report. Please try again.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Report
    |--------------------------------------------------------------------------
    |
    | DELETE
    |
    | /api/mobile/reports/{report}
    |
    */

    public function destroy(
        Request $request,
        Report $report
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $this->ensureStudentOwnsReport(
            $report,
            $student
        );


        /*
        |--------------------------------------------------------------------------
        | Do Not Delete Reviewed Reports
        |--------------------------------------------------------------------------
        */

        if (
            $report->status !==
            Report::STATUS_PENDING
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Only pending reports can be deleted.',
            ], 422);
        }


        $attachmentPath =
            $report->attachment_path;


        try {
            DB::transaction(
                function () use (
                    $report,
                    $student
                ): void {
                    $lockedReport =
                        Report::query()
                            ->whereKey(
                                $report->id
                            )
                            ->where(
                                'user_id',
                                $student->id
                            )
                            ->where(
                                'university_id',
                                $student->university_id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();


                    $lockedReport->delete();
                }
            );


            if (
                filled(
                    $attachmentPath
                )
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $attachmentPath
                );
            }


            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Your report was deleted successfully.',
            ]);

        } catch (
            Throwable $error
        ) {
            report(
                $error
            );


            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unable to delete the report. Please try again.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Report
    |--------------------------------------------------------------------------
    */

    private function validateReport(
        Request $request,
        User $student
    ): array {
        $validated =
            $request->validate(
                [
                    'component' => [
                        'required',
                        'string',
                        Rule::in(
                            Report::components()
                        ),
                    ],

                    'instructor_id' => [
                        'required',
                        'integer',

                        Rule::exists(
                            'instructors',
                            'id'
                        )->where(
                            fn ($query) =>
                                $query->where(
                                    'university_id',
                                    $student->university_id
                                )
                        ),
                    ],

                    'occurrence_date' => [
                        'required',
                        'date_format:Y-m-d',
                        'before_or_equal:today',
                    ],

                    'subject' => [
                        'required',
                        'string',
                        'min:2',
                        'max:255',
                    ],

                    'description' => [
                        'required',
                        'string',
                        'min:5',
                        'max:5000',
                    ],

                    'attachment' => [
                        'nullable',
                        'file',
                        'max:10240',
                        'mimes:jpg,jpeg,png,webp,pdf,doc,docx',
                    ],
                ],
                [
                    'component.required' =>
                        'Please select your NSTP component.',

                    'component.in' =>
                        'The selected NSTP component is invalid.',

                    'instructor_id.required' =>
                        'Please select your instructor.',

                    'instructor_id.exists' =>
                        'The selected instructor is invalid.',

                    'occurrence_date.required' =>
                        'Please enter the date of occurrence.',

                    'occurrence_date.date_format' =>
                        'The date of occurrence must be a valid date.',

                    'occurrence_date.before_or_equal' =>
                        'The date of occurrence cannot be in the future.',

                    'subject.required' =>
                        'Please tell us what your report is about.',

                    'description.required' =>
                        'Please describe your concern.',

                    'attachment.max' =>
                        'The attachment must not be larger than 10 MB.',

                    'attachment.mimes' =>
                        'Only JPG, PNG, WEBP, PDF, DOC, and DOCX files are allowed.',
                ]
            );


        $validated[
            'component'
        ] =
            $this->normalizeComponent(
                $validated[
                    'component'
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Enforce Student's Registered Component
        |--------------------------------------------------------------------------
        */

        $studentComponent =
            $this->normalizeComponent(
                $student->component
                ??
                ''
            );


        if (
            in_array(
                $studentComponent,
                Report::components(),
                true
            )
            &&
            $validated[
                'component'
            ] !==
            $studentComponent
        ) {
            throw ValidationException::withMessages([
                'component' => [
                    'You may only submit a report for your registered NSTP component.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Instructor Must Belong To Same Component
        |--------------------------------------------------------------------------
        */

        $instructor =
            Instructor::query()
                ->whereKey(
                    $validated[
                        'instructor_id'
                    ]
                )
                ->where(
                    'university_id',
                    $student->university_id
                )
                ->first();


        if (
            !$instructor
        ) {
            throw ValidationException::withMessages([
                'instructor_id' => [
                    'The selected instructor could not be found.',
                ],
            ]);
        }


        if (!$instructor->managesComponent($validated['component'])) {
            throw ValidationException::withMessages([
                'instructor_id' => [
                    'The selected instructor is not assigned to this NSTP component.',
                ],
            ]);
        }

        return $validated;
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticated Student
    |--------------------------------------------------------------------------
    */

    private function authenticatedStudent(
        Request $request
    ): User {
        $authenticated =
            $request->user();


        abort_unless(
            $authenticated instanceof User,
            403,
            'This account cannot use the student mobile application.'
        );


        $student =
            User::query()
                ->with(
                    'university'
                )
                ->find(
                    $authenticated->id
                );


        abort_unless(
            $student instanceof User,
            404,
            'Student account could not be found.'
        );


        abort_unless(
            $student->isStudent(),
            403,
            'Only student accounts can access reports.'
        );


        abort_if(
            empty(
                $student->university_id
            ),
            403,
            'Your student account is not assigned to a university.'
        );


        return $student;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Ownership
    |--------------------------------------------------------------------------
    */

    private function ensureStudentOwnsReport(
        Report $report,
        User $student
    ): void {
        abort_unless(
            (int) $report->user_id ===
                (int) $student->id
            &&
            (int) $report->university_id ===
                (int) $student->university_id,
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Attachment
    |--------------------------------------------------------------------------
    */

    private function storeAttachment(
        Request $request,
        User $student
    ): ?string {
        $file =
            $request->file(
                'attachment'
            );


        if (
            !$file
        ) {
            return null;
        }


        return $file->store(
            sprintf(
                'reports/%d/%d',
                $student->university_id,
                $student->id
            ),
            'public'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Report Payload
    |--------------------------------------------------------------------------
    */

    private function reportPayload(
        Request $request,
        Report $report
    ): array {
        $student =
            $report->student;


        $instructor =
            $report->instructor;


        $reviewer =
            $this->resolveReportReviewer(
                $report
            );


        $attachmentUrl =
            $this->publicFileUrl(
                $request,
                $report->attachment_path
            );


        $isImage =
            filled(
                $report->attachment_mime_type
            )
            &&
            str_starts_with(
                strtolower(
                    $report->attachment_mime_type
                ),
                'image/'
            );


        $reviewerName =
            $reviewer
                ? $this->personName(
                    $reviewer,
                    'NSTP Staff'
                )
                : null;


        $reviewerRole =
            $reviewer
                ? $this->reviewerRole(
                    $reviewer
                )
                : null;


        $feedbackDate =
            $report->resolved_at
            ??
            $report->reviewed_at
            ??
            $report->updated_at;


        $feedbackPayload =
            filled(
                $report->feedback
            )
                ? [
                    'name' =>
                        $reviewerName
                        ??
                        'NSTP Staff',

                    'role' =>
                        $reviewerRole
                        ??
                        'NSTP STAFF',

                    'profile_photo_url' =>
                        $reviewer
                            ? $this->publicFileUrl(
                                $request,
                                $reviewer->profile_photo
                                ??
                                $reviewer->photo
                                ??
                                null
                            )
                            : null,

                    'date' =>
                        $this->formatDateTime(
                            $feedbackDate
                        ),

                    'message' =>
                        $report->feedback,
                ]
                : null;


        return [
            'id' =>
                $report->id,

            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            */

            'studentName' =>
                $student
                    ? $this->personName(
                        $student,
                        'Student'
                    )
                    : 'Student',

            'student_id_number' =>
                $student
                    ? $this->studentIdNumber(
                        $student
                    )
                    : null,

            'email' =>
                $student?->email,

            'profile_photo_url' =>
                $student
                    ? $this->publicFileUrl(
                        $request,
                        $student->profile_photo
                        ??
                        null
                    )
                    : null,

            /*
            |--------------------------------------------------------------------------
            | Submitted Date
            |--------------------------------------------------------------------------
            */

            'submittedDate' =>
                $this->formatDateTime(
                    $report->created_at
                ),

            'created_at' =>
                $report
                    ->created_at
                    ?->toISOString(),

            'updated_at' =>
                $report
                    ->updated_at
                    ?->toISOString(),

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status' =>
                strtoupper(
                    trim(
                        $report->status
                    )
                ),

            /*
            |--------------------------------------------------------------------------
            | Component
            |--------------------------------------------------------------------------
            */

            'component' =>
                $this->normalizeComponent(
                    $report->component
                ),

            /*
            |--------------------------------------------------------------------------
            | Instructor
            |--------------------------------------------------------------------------
            */

            'instructor_id' =>
                $report->instructor_id,

            'instructor' =>
                $instructor
                    ? $this->personName(
                        $instructor,
                        'Instructor'
                    )
                    : 'Instructor',

            /*
            |--------------------------------------------------------------------------
            | Report Information
            |--------------------------------------------------------------------------
            */

            'occurrence_date' =>
                $report
                    ->occurrence_date
                    ?->format(
                        'Y-m-d'
                    ),

            'occurrenceDate' =>
                $report
                    ->occurrence_date
                    ?->format(
                        'm/d/Y'
                    ),

            'concern' =>
                $report->subject,

            'subject' =>
                $report->subject,

            'description' =>
                $report->description,

            /*
            |--------------------------------------------------------------------------
            | Attachment
            |--------------------------------------------------------------------------
            */

            'attachment_url' =>
                $attachmentUrl,

            'evidenceUri' =>
                $isImage
                    ? $attachmentUrl
                    : null,

            'attachmentName' =>
                $report
                    ->attachment_original_name,

            'attachment_mime_type' =>
                $report
                    ->attachment_mime_type,

            'attachment_size' =>
                $report
                    ->attachment_size,

            /*
            |--------------------------------------------------------------------------
            | Feedback
            |--------------------------------------------------------------------------
            */

            'feedback' =>
                $report->feedback,

            'response' =>
                $report->status !==
                    Report::STATUS_RESOLVED
                    ? $feedbackPayload
                    : null,

            'resolution' =>
                $report->status ===
                    Report::STATUS_RESOLVED
                    ? $feedbackPayload
                    : null,

            'reviewed_at' =>
                $report
                    ->reviewed_at
                    ?->toISOString(),

            'resolved_at' =>
                $report
                    ->resolved_at
                    ?->toISOString(),

            /*
            |--------------------------------------------------------------------------
            | Student Permissions
            |--------------------------------------------------------------------------
            */

            'can_edit' =>
                $report->status ===
                Report::STATUS_PENDING,

            'can_delete' =>
                $report->status ===
                Report::STATUS_PENDING,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Public File URL
    |--------------------------------------------------------------------------
    |
    | Uses the request host so the mobile phone receives:
    |
    | http://192.168.x.x:8000/storage/...
    |
    | instead of:
    |
    | http://localhost:8000/storage/...
    |
    */

    private function publicFileUrl(
        Request $request,
        ?string $path
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | No File
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $path
            )
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Clean Path
        |--------------------------------------------------------------------------
        */

        $path =
            trim(
                str_replace(
                    '\\',
                    '/',
                    $path
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Already A Complete URL
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Mobile Request Host
        |--------------------------------------------------------------------------
        |
        | Because the React Native application connects to Laravel using the
        | computer's LAN IP address, the returned file URL must use that same
        | host instead of localhost.
        |
        | Example:
        |
        | http://192.168.254.158:8000
        |
        */

        $host =
            rtrim(
                $request->getSchemeAndHttpHost(),
                '/'
            );


        /*
        |--------------------------------------------------------------------------
        | Existing /storage/... URL Path
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $path,
                '/storage/'
            )
        ) {
            return (
                $host
                .
                $path
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Existing storage/... URL Path
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $path,
                'storage/'
            )
        ) {
            return (
                $host
                .
                '/'
                .
                ltrim(
                    $path,
                    '/'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Public Disk Path
        |--------------------------------------------------------------------------
        |
        | Database value:
        |
        | reports/11/123/example.jpg
        |
        | Mobile URL:
        |
        | http://192.168.254.158:8000/storage/reports/11/123/example.jpg
        |
        */

        return (
            $host
            .
            '/storage/'
            .
            ltrim(
                $path,
                '/'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Person Name
    |--------------------------------------------------------------------------
    */

    private function personName(
        object $person,
        string $fallback
    ): string {
        $fullName =
            $person->full_name
            ??
            null;


        if (
            filled(
                $fullName
            )
        ) {
            return trim(
                $fullName
            );
        }


        $parts = [
            $person->first_name
            ??
            null,

            $person->middle_name
            ??
            null,

            $person->surname
            ??
            $person->last_name
            ??
            null,
        ];


        $builtName =
            collect(
                $parts
            )
                ->filter()
                ->implode(
                    ' '
                );


        if (
            filled(
                $builtName
            )
        ) {
            return $builtName;
        }


        return (
            $person->name
            ??
            $person->username
            ??
            $fallback
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Student ID Number
    |--------------------------------------------------------------------------
    */

    private function studentIdNumber(
        User $student
    ): ?string {
        $value =
            $student->student_id_number
            ??
            $student->id_number
            ??
            null;


        return filled(
            $value
        )
            ? trim(
                (string) $value
            )
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Report Reviewer
    |--------------------------------------------------------------------------
    |
    | New reports normally resolve through the polymorphic reviewedBy
    | relationship. Older report rows may have feedback but incomplete reviewer
    | metadata. This helper recovers the actual reviewer whenever possible so
    | the mobile app displays the University Administrator's name instead of a
    | generic "NSTP Staff" label.
    |
    */

    private function resolveReportReviewer(
        Report $report
    ): ?object {
        try {
            $reviewer =
                $report->reviewedBy;

            if (
                $reviewer
            ) {
                return $reviewer;
            }
        } catch (
            Throwable $error
        ) {
            report(
                $error
            );
        }


        $reviewerId =
            $report->reviewed_by_id;


        if (
            filled(
                $reviewerId
            )
        ) {
            $reviewerType =
                strtolower(
                    class_basename(
                        (string) $report->reviewed_by_type
                    )
                );


            $reviewerClass =
                match (
                    $reviewerType
                ) {
                    'universityadministrator',
                    'university_administrator',
                    'university-admin',
                    'university_admin' =>
                        UniversityAdministrator::class,

                    'instructor' =>
                        Instructor::class,

                    'coordinator' =>
                        Coordinator::class,

                    default =>
                        null,
                };


            if (
                $reviewerClass
            ) {
                $recoveredReviewer =
                    $reviewerClass::query()
                        ->find(
                            $reviewerId
                        );


                if (
                    $recoveredReviewer
                ) {
                    return $recoveredReviewer;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Legacy Feedback Fallback
        |--------------------------------------------------------------------------
        |
        | Some older report rows were saved before reviewer metadata was reliably
        | stored. If the university has exactly one University Administrator, that
        | administrator is an unambiguous fallback for the old feedback record.
        |
        */

        if (
            filled(
                $report->feedback
            )
            &&
            filled(
                $report->university_id
            )
        ) {
            $administrators =
                UniversityAdministrator::query()
                    ->where(
                        'university_id',
                        $report->university_id
                    )
                    ->orderBy(
                        'id'
                    )
                    ->limit(
                        2
                    )
                    ->get();


            if (
                $administrators->count() ===
                1
            ) {
                return $administrators->first();
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewer Role
    |--------------------------------------------------------------------------
    */

    private function reviewerRole(
        object $reviewer
    ): string {
        return match (
            class_basename(
                $reviewer
            )
        ) {
            'UniversityAdministrator' =>
                'UNIVERSITY ADMINISTRATOR',

            'Instructor' =>
                'INSTRUCTOR',

            'Coordinator' =>
                'COORDINATOR',

            default =>
                'NSTP STAFF',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Component
    |--------------------------------------------------------------------------
    */

    private function normalizeComponent(
        ?string $component
    ): string {
        return strtoupper(
            trim(
                (string) $component
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Date
    |--------------------------------------------------------------------------
    */

    private function formatDateTime(
        $value
    ): ?string {
        if (
            !$value
        ) {
            return null;
        }


        return $value
            ->copy()
            ->timezone(
                self::TIMEZONE
            )
            ->format(
                'F d, Y'
            );
    }
}