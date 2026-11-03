<?php

namespace App\Http\Controllers\UniversityAdmin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\UniversityAdministrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Student Reports
    |--------------------------------------------------------------------------
    |
    | Supported URLs:
    |
    | /university-admin/reports/lts
    | /university-admin/reports/cwts
    | /university-admin/reports/rotc
    |
    */

    public function index(
        Request $request,
        string $component
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Authenticated University Administrator
        |--------------------------------------------------------------------------
        */

        $administrator =
            $this->authenticatedAdministrator();


        /*
        |--------------------------------------------------------------------------
        | Normalize Component
        |--------------------------------------------------------------------------
        */

        $component =
            strtoupper(
                trim(
                    $component
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Make Sure Component Is Valid
        |--------------------------------------------------------------------------
        */

        abort_unless(
            in_array(
                $component,
                Report::components(),
                true
            ),
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Load Reports
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | The University Administrator only receives reports belonging to
        | their own university.
        |
        */

        $reports =
            Report::query()
                ->where(
                    'university_id',
                    $administrator->university_id
                )
                ->where(
                    'component',
                    $component
                )
                ->with(
                    [
                        'student',
                        'instructor',
                        'reviewedBy',
                    ]
                )
                ->latest(
                    'created_at'
                )
                ->get()
                ->map(
                    function (
                        Report $report
                    ): array {
                        return $this->reportPayload(
                            $report
                        );
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Reviewer Information
        |--------------------------------------------------------------------------
        */

        $reviewer = [
            'id' =>
                $administrator->id,

            'full_name' =>
                $administrator->full_name
                ?: $administrator->username
                ?: 'University Administrator',

            'email' =>
                $administrator->email,

            'role' =>
                'UNIVERSITY ADMINISTRATOR',

            'role_label' =>
                'UNIVERSITY ADMINISTRATOR',

            'profile_photo_url' =>
                $this->publicFileUrl(
                    $administrator->profile_photo
                    ??
                    $administrator->photo
                ),
        ];


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/StudentReports',
            [
                'component' =>
                    $component,

                'reports' =>
                    $reports,

                'reviewer' =>
                    $reviewer,

                'statusRouteBase' =>
                    '/university-admin/reports',

                'feedbackRouteBase' =>
                    '/university-admin/reports',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Report Status
    |--------------------------------------------------------------------------
    |
    | PATCH
    |
    | /university-admin/reports/{report}/status
    |
    */

    public function updateStatus(
        Request $request,
        Report $report
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */

        $administrator =
            $this->authenticatedAdministrator();


        /*
        |--------------------------------------------------------------------------
        | University Ownership
        |--------------------------------------------------------------------------
        */

        $this->ensureReportBelongsToAdministrator(
            $report,
            $administrator
        );


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'status' => [
                        'required',
                        'string',
                        Rule::in(
                            Report::statuses()
                        ),
                    ],
                ],
                [
                    'status.required' =>
                        'Please select a report status.',

                    'status.in' =>
                        'The selected report status is invalid.',
                ]
            );


        $status =
            strtoupper(
                trim(
                    $validated[
                        'status'
                    ]
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Update Within Transaction
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $report,
                $administrator,
                $status
            ): void {
                $lockedReport =
                    Report::query()
                        ->whereKey(
                            $report->id
                        )
                        ->where(
                            'university_id',
                            $administrator->university_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Pending
                |--------------------------------------------------------------------------
                */

                if (
                    $status ===
                    Report::STATUS_PENDING
                ) {
                    $lockedReport->status =
                        Report::STATUS_PENDING;


                    /*
                    |--------------------------------------------------------------------------
                    | Report Is No Longer Resolved
                    |--------------------------------------------------------------------------
                    */

                    $lockedReport->resolved_at =
                        null;


                    $lockedReport->save();

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | In Review
                |--------------------------------------------------------------------------
                */

                if (
                    $status ===
                    Report::STATUS_IN_REVIEW
                ) {
                    $lockedReport->status =
                        Report::STATUS_IN_REVIEW;


                    $lockedReport->reviewed_by_type =
                        $administrator->getMorphClass();


                    $lockedReport->reviewed_by_id =
                        $administrator->getKey();


                    if (
                        $lockedReport->reviewed_at ===
                        null
                    ) {
                        $lockedReport->reviewed_at =
                            now();
                    }


                    $lockedReport->resolved_at =
                        null;


                    $lockedReport->save();

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Resolved
                |--------------------------------------------------------------------------
                */

                $lockedReport->status =
                    Report::STATUS_RESOLVED;


                $lockedReport->reviewed_by_type =
                    $administrator->getMorphClass();


                $lockedReport->reviewed_by_id =
                    $administrator->getKey();


                if (
                    $lockedReport->reviewed_at ===
                    null
                ) {
                    $lockedReport->reviewed_at =
                        now();
                }


                $lockedReport->resolved_at =
                    now();


                $lockedReport->save();
            }
        );


        return back()->with(
            'success',
            match ($status) {
                Report::STATUS_PENDING =>
                    'Report successfully changed to Pending.',

                Report::STATUS_IN_REVIEW =>
                    'Report successfully changed to In Review.',

                Report::STATUS_RESOLVED =>
                    'Report successfully marked as Resolved.',

                default =>
                    'Report status updated successfully.',
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Feedback
    |--------------------------------------------------------------------------
    |
    | POST
    |
    | /university-admin/reports/{report}/feedback
    |
    */

    public function storeFeedback(
        Request $request,
        Report $report
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */

        $administrator =
            $this->authenticatedAdministrator();


        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        $this->ensureReportBelongsToAdministrator(
            $report,
            $administrator
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'feedback' => [
                        'required',
                        'string',
                        'min:2',
                        'max:3000',
                    ],
                ],
                [
                    'feedback.required' =>
                        'Please write feedback before sending.',

                    'feedback.min' =>
                        'Feedback must contain at least 2 characters.',

                    'feedback.max' =>
                        'Feedback must not exceed 3000 characters.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $report,
                $administrator,
                $validated
            ): void {
                $lockedReport =
                    Report::query()
                        ->whereKey(
                            $report->id
                        )
                        ->where(
                            'university_id',
                            $administrator->university_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Do Not Accidentally Overwrite Existing Feedback
                |--------------------------------------------------------------------------
                */

                if (
                    filled(
                        $lockedReport->feedback
                    )
                ) {
                    abort(
                        422,
                        'This report already has feedback. Please use Edit Feedback instead.'
                    );
                }


                $lockedReport->feedback =
                    trim(
                        $validated[
                            'feedback'
                        ]
                    );


                $lockedReport->reviewed_by_type =
                    $administrator->getMorphClass();


                $lockedReport->reviewed_by_id =
                    $administrator->getKey();


                $lockedReport->reviewed_at =
                    now();


                /*
                |--------------------------------------------------------------------------
                | Sending Feedback Automatically Starts Review
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedReport->status ===
                    Report::STATUS_PENDING
                ) {
                    $lockedReport->status =
                        Report::STATUS_IN_REVIEW;
                }


                $lockedReport->save();
            }
        );


        return back()->with(
            'success',
            'Feedback sent successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Feedback
    |--------------------------------------------------------------------------
    |
    | PATCH
    |
    | /university-admin/reports/{report}/feedback
    |
    */

    public function updateFeedback(
        Request $request,
        Report $report
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */

        $administrator =
            $this->authenticatedAdministrator();


        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        $this->ensureReportBelongsToAdministrator(
            $report,
            $administrator
        );


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'feedback' => [
                        'required',
                        'string',
                        'min:2',
                        'max:3000',
                    ],
                ],
                [
                    'feedback.required' =>
                        'Feedback cannot be empty.',

                    'feedback.min' =>
                        'Feedback must contain at least 2 characters.',

                    'feedback.max' =>
                        'Feedback must not exceed 3000 characters.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $report,
                $administrator,
                $validated
            ): void {
                $lockedReport =
                    Report::query()
                        ->whereKey(
                            $report->id
                        )
                        ->where(
                            'university_id',
                            $administrator->university_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Existing Feedback Required
                |--------------------------------------------------------------------------
                */

                abort_if(
                    blank(
                        $lockedReport->feedback
                    ),
                    404,
                    'Feedback was not found.'
                );


                $lockedReport->feedback =
                    trim(
                        $validated[
                            'feedback'
                        ]
                    );


                /*
                |--------------------------------------------------------------------------
                | Last Editor
                |--------------------------------------------------------------------------
                */

                $lockedReport->reviewed_by_type =
                    $administrator->getMorphClass();


                $lockedReport->reviewed_by_id =
                    $administrator->getKey();


                if (
                    $lockedReport->reviewed_at ===
                    null
                ) {
                    $lockedReport->reviewed_at =
                        now();
                }


                $lockedReport->save();
            }
        );


        return back()->with(
            'success',
            'Feedback updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Feedback
    |--------------------------------------------------------------------------
    |
    | DELETE
    |
    | /university-admin/reports/{report}/feedback
    |
    */

    public function destroyFeedback(
        Report $report
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */

        $administrator =
            $this->authenticatedAdministrator();


        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        $this->ensureReportBelongsToAdministrator(
            $report,
            $administrator
        );


        /*
        |--------------------------------------------------------------------------
        | Delete Feedback
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $report,
                $administrator
            ): void {
                $lockedReport =
                    Report::query()
                        ->whereKey(
                            $report->id
                        )
                        ->where(
                            'university_id',
                            $administrator->university_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $lockedReport->feedback =
                    null;


                /*
                |--------------------------------------------------------------------------
                | Keep Review Information
                |--------------------------------------------------------------------------
                |
                | We do NOT automatically change the status here.
                |
                | Example:
                |
                | A report can remain IN REVIEW even if an incorrect feedback
                | message was deleted.
                |
                */

                $lockedReport->save();
            }
        );


        return back()->with(
            'success',
            'Feedback deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Build Report Payload
    |--------------------------------------------------------------------------
    */

    private function reportPayload(
        Report $report
    ): array {
        $student =
            $report->student;


        $instructor =
            $report->instructor;


        $reviewer =
            $report->reviewedBy;


        return [
            /*
            |--------------------------------------------------------------------------
            | Report
            |--------------------------------------------------------------------------
            */

            'id' =>
                $report->id,

            'component' =>
                $report->component,

            'status' =>
                $report->status,

            'occurrence_date' =>
                optional(
                    $report->occurrence_date
                )->format(
                    'Y-m-d'
                ),

            'subject' =>
                $report->subject,

            'description' =>
                $report->description,

            'feedback' =>
                $report->feedback,

            'created_at' =>
                optional(
                    $report->created_at
                )?->toISOString(),

            'updated_at' =>
                optional(
                    $report->updated_at
                )?->toISOString(),

            'reviewed_at' =>
                optional(
                    $report->reviewed_at
                )?->toISOString(),

            'resolved_at' =>
                optional(
                    $report->resolved_at
                )?->toISOString(),


            /*
            |--------------------------------------------------------------------------
            | Attachment
            |--------------------------------------------------------------------------
            */

            'attachment_url' =>
                $this->publicFileUrl(
                    $report->attachment_path
                ),

            'attachment_original_name' =>
                $report->attachment_original_name,

            'attachment_mime_type' =>
                $report->attachment_mime_type,

            'attachment_size' =>
                $report->attachment_size,


            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            */

            'student' =>
                $student
                    ? [
                        'id' =>
                            $student->id,

                        'full_name' =>
                            $student->full_name
                            ?: $student->name
                            ?: 'Student',

                        'name' =>
                            $student->name,

                        'email' =>
                            $student->email,

                        'profile_photo_url' =>
                            $this->publicFileUrl(
                                $student->profile_photo
                            ),
                    ]
                    : null,


            /*
            |--------------------------------------------------------------------------
            | Instructor
            |--------------------------------------------------------------------------
            */

            'instructor' =>
                $instructor
                    ? [
                        'id' =>
                            $instructor->id,

                        'full_name' =>
                            $instructor->full_name
                            ??
                            $instructor->name
                            ??
                            'Instructor',

                        'name' =>
                            $instructor->name
                            ??
                            $instructor->full_name
                            ??
                            'Instructor',
                    ]
                    : null,


            /*
            |--------------------------------------------------------------------------
            | Reviewer
            |--------------------------------------------------------------------------
            */

            'reviewed_by' =>
                $reviewer
                    ? [
                        'id' =>
                            $reviewer->getKey(),

                        'full_name' =>
                            $reviewer->full_name
                            ??
                            $reviewer->name
                            ??
                            $reviewer->username
                            ??
                            'NSTP Staff',

                        'role' =>
                            $this->reviewerRole(
                                $reviewer
                            ),

                        'role_label' =>
                            $this->reviewerRole(
                                $reviewer
                            ),

                        'profile_photo_url' =>
                            $this->publicFileUrl(
                                $reviewer->profile_photo
                                ??
                                $reviewer->photo
                                ??
                                null
                            ),
                    ]
                    : null,
        ];
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
    | Public File URL
    |--------------------------------------------------------------------------
    */

    private function publicFileUrl(
        ?string $path
    ): ?string {
        if (
            blank(
                $path
            )
        ) {
            return null;
        }


        $path =
            trim(
                $path
            );


        /*
        |--------------------------------------------------------------------------
        | Already Complete URL
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
        | Already Public Storage URL
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $path,
                '/storage/'
            )
        ) {
            return $path;
        }


        /*
        |--------------------------------------------------------------------------
        | Public Disk
        |--------------------------------------------------------------------------
        */

        return Storage::disk(
            'public'
        )->url(
            ltrim(
                $path,
                '/'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticated University Administrator
    |--------------------------------------------------------------------------
    */

    private function authenticatedAdministrator(): UniversityAdministrator
    {
        $administrator =
            Auth::guard(
                'university_admin'
            )->user();


        abort_unless(
            $administrator instanceof
                UniversityAdministrator,
            401
        );


        abort_if(
            empty(
                $administrator->university_id
            ),
            403,
            'Your University Administrator account is not connected to a university.'
        );


        return $administrator;
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Same University
    |--------------------------------------------------------------------------
    */

    private function ensureReportBelongsToAdministrator(
        Report $report,
        UniversityAdministrator $administrator
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Return 404 Instead Of Exposing Another University's Report
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $report->university_id ===
            (int) $administrator->university_id,
            404
        );
    }
}