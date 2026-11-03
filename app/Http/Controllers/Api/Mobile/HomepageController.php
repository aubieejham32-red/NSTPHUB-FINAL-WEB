<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ExcuseLetter;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomepageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Mobile Homepage
    |--------------------------------------------------------------------------
    |
    | GET /api/mobile/homepage
    |
    */

    public function show(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Authenticated Student
        |--------------------------------------------------------------------------
        */

        $authenticated = $request->user();

        if (!($authenticated instanceof User)) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This account is not authorized to use the NSTP HUB mobile application.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Reload Student + University
        |--------------------------------------------------------------------------
        */

        $student = User::query()
            ->with('university')
            ->find($authenticated->id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student account could not be found.',
            ], 404);
        }

        if (!$student->isStudent()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Only student accounts may access the mobile homepage.',
            ], 403);
        }

        if (!$student->university) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Your student account is not connected to a university.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Student NSTP Component
        |--------------------------------------------------------------------------
        */

        $component = strtoupper(
            trim((string) $student->component)
        );

        if (
            !in_array(
                $component,
                [
                    'CWTS',
                    'LTS',
                    'ROTC',
                ],
                true
            )
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Your NSTP component has not been configured correctly.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Upcoming Schedules
        |--------------------------------------------------------------------------
        |
        | The student may see:
        |
        | - ALL component schedules
        | - schedules for their own component
        |
        */

        $schedules = Schedule::query()
            ->forUniversity(
                $student->university_id
            )
            ->forComponent(
                $component
            )
            ->upcoming()
            ->limit(3)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Announcements
        |--------------------------------------------------------------------------
        */

        $announcementQuery = Announcement::query()
            ->where(
                'university_id',
                $student->university_id
            )
            ->forComponent(
                $component
            )
            ->forStudent(
                $student->email
            );


        /*
        |--------------------------------------------------------------------------
        | New Announcement Count
        |--------------------------------------------------------------------------
        |
        | Since there is currently no announcement read/unread table,
        | announcements from the last seven days are considered new.
        |
        */

        $newAnnouncementCount = (clone $announcementQuery)
            ->whereDate(
                'announcement_date',
                '>=',
                now()
                    ->subDays(7)
                    ->toDateString()
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Latest Three Announcements
        |--------------------------------------------------------------------------
        */

        $announcements = $announcementQuery
            ->with('creator')
            ->recent()
            ->limit(3)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Student Record Summary
        |--------------------------------------------------------------------------
        |
        | One homepage API request now returns the real database summary for:
        |
        | - Excuse Letters
        | - Reports
        | - Attendance
        |
        */

        $summary = $this->studentRecordSummary(
            $student
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,


            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            */

            'student' => [
                'id' =>
                    $student->id,

                'student_id_number' =>
                    $student->student_id_number,

                'name' =>
                    $student->name,

                'full_name' =>
                    $student->full_name,

                'email' =>
                    $student->email,

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

                'subject' =>
                    $student->subject,

                'component' =>
                    $component,

                'term' =>
                    $student->term,

                'gender' =>
                    $student->gender,

                'contact_number' =>
                    $student->contact_number,

                'profile_photo' =>
                    $student->profile_photo,

                'profile_photo_url' =>
                    $this->fileUrl(
                        request: $request,
                        path: $student->profile_photo
                    ),
            ],


            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            */

            'university' => [
                'id' =>
                    $student
                        ->university
                        ->id,

                'name' =>
                    $student
                        ->university
                        ->name,

                'acronym' =>
                    $student
                        ->university
                        ->acronym,

                'academic_year' =>
                    $student
                        ->university
                        ->academic_year,

                'semester' =>
                    $student
                        ->university
                        ->semester,

                'logo' =>
                    $student
                        ->university
                        ->logo,

                /*
                |--------------------------------------------------------------------------
                | University Logo URL
                |--------------------------------------------------------------------------
                |
                | Use the same host/IP address that the physical phone used.
                |
                */

                'logo_url' =>
                    $request
                        ->getSchemeAndHttpHost()
                    .
                    '/api/mobile/universities/'
                    .
                    $student
                        ->university
                        ->id
                    .
                    '/logo',
            ],


            /*
            |--------------------------------------------------------------------------
            | Student Record Summary
            |--------------------------------------------------------------------------
            */

            'summary' =>
                $summary,


            /*
            |--------------------------------------------------------------------------
            | Schedules
            |--------------------------------------------------------------------------
            */

            'schedules' =>
                $schedules
                    ->map(
                        function (
                            Schedule $schedule
                        ): array {
                            return [
                                'id' =>
                                    $schedule->id,

                                'component' =>
                                    $schedule->component,

                                'title' =>
                                    $schedule->title,

                                'schedule_date' =>
                                    $schedule
                                        ->schedule_date
                                        ?->format(
                                            'Y-m-d'
                                        ),

                                'date_label' =>
                                    $schedule
                                        ->schedule_date
                                        ?->format(
                                            'F d, Y'
                                        ),

                                'day_label' =>
                                    $schedule
                                        ->schedule_date
                                        ?->format(
                                            'l'
                                        ),

                                'start_time' =>
                                    $schedule->start_time,

                                'end_time' =>
                                    $schedule->end_time,

                                'time_label' =>
                                    $this->scheduleTimeLabel(
                                        $schedule->start_time,
                                        $schedule->end_time
                                    ),

                                'location' =>
                                    $schedule->location,
                            ];
                        }
                    )
                    ->values(),


            /*
            |--------------------------------------------------------------------------
            | Announcements
            |--------------------------------------------------------------------------
            */

            'announcements' =>
                $announcements
                    ->map(
                        function (
                            Announcement $announcement
                        ): array {
                            return [
                                'id' =>
                                    $announcement->id,

                                'component' =>
                                    $announcement->component,

                                'title' =>
                                    $announcement->title,

                                'description' =>
                                    $announcement->description,

                                'announcement_date' =>
                                    $announcement
                                        ->announcement_date
                                        ?->format(
                                            'Y-m-d'
                                        ),

                                'date_label' =>
                                    $announcement
                                        ->announcement_date
                                        ?->format(
                                            'F d'
                                        ),

                                'recipient' =>
                                    $announcement->recipient,

                                'author' =>
                                    $this->creatorLabel(
                                        $announcement
                                    ),
                            ];
                        }
                    )
                    ->values(),


            /*
            |--------------------------------------------------------------------------
            | New Announcement Count
            |--------------------------------------------------------------------------
            */

            'new_announcement_count' =>
                $newAnnouncementCount,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Student Record Summary
    |--------------------------------------------------------------------------
    |
    | Returned structure:
    |
    | summary.excuse_letters
    | summary.reports
    | summary.attendance
    |
    */

    private function studentRecordSummary(
        User $student
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Excuse Letter Summary
        |--------------------------------------------------------------------------
        */

        $excuseLetterStats =
            $student
                ->excuseLetters()
                ->selectRaw(
                    'COUNT(*) AS total'
                )
                ->selectRaw(
                    'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS pending',
                    [
                        ExcuseLetter::STATUS_PENDING,
                    ]
                )
                ->selectRaw(
                    'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS approved',
                    [
                        ExcuseLetter::STATUS_APPROVED,
                    ]
                )
                ->selectRaw(
                    'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS rejected',
                    [
                        ExcuseLetter::STATUS_REJECTED,
                    ]
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Report Summary
        |--------------------------------------------------------------------------
        */

        $reportStats =
            $student
                ->reports()
                ->selectRaw(
                    'COUNT(*) AS total'
                )
                ->selectRaw(
                    'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS pending',
                    [
                        Report::STATUS_PENDING,
                    ]
                )
                ->selectRaw(
                    'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS in_review',
                    [
                        Report::STATUS_IN_REVIEW,
                    ]
                )
                ->selectRaw(
                    'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS resolved',
                    [
                        Report::STATUS_RESOLVED,
                    ]
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Attendance Summary
        |--------------------------------------------------------------------------
        |
        | The existing Attendance screen recognizes:
        |
        | PRESENT
        | LATE
        | EXCUSED / EXCUSE
        | ABSENT
        |
        */

        $attendanceStats =
            $student
                ->attendances()
                ->selectRaw(
                    'COUNT(*) AS total'
                )
                ->selectRaw(
                    "
                    SUM(
                        CASE
                            WHEN UPPER(
                                TRIM(
                                    COALESCE(
                                        remark,
                                        ''
                                    )
                                )
                            ) = 'PRESENT'
                            THEN 1
                            ELSE 0
                        END
                    ) AS present
                    "
                )
                ->selectRaw(
                    "
                    SUM(
                        CASE
                            WHEN UPPER(
                                TRIM(
                                    COALESCE(
                                        remark,
                                        ''
                                    )
                                )
                            ) = 'LATE'
                            THEN 1
                            ELSE 0
                        END
                    ) AS late
                    "
                )
                ->selectRaw(
                    "
                    SUM(
                        CASE
                            WHEN UPPER(
                                TRIM(
                                    COALESCE(
                                        remark,
                                        ''
                                    )
                                )
                            ) IN (
                                'EXCUSED',
                                'EXCUSE'
                            )
                            THEN 1
                            ELSE 0
                        END
                    ) AS excused
                    "
                )
                ->selectRaw(
                    "
                    SUM(
                        CASE
                            WHEN UPPER(
                                TRIM(
                                    COALESCE(
                                        remark,
                                        ''
                                    )
                                )
                            ) = 'ABSENT'
                            THEN 1
                            ELSE 0
                        END
                    ) AS absent
                    "
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Summary Response
        |--------------------------------------------------------------------------
        */

        return [
            'excuse_letters' => [
                'total' =>
                    (int) (
                        $excuseLetterStats
                            ?->total
                        ??
                        0
                    ),

                'pending' =>
                    (int) (
                        $excuseLetterStats
                            ?->pending
                        ??
                        0
                    ),

                'approved' =>
                    (int) (
                        $excuseLetterStats
                            ?->approved
                        ??
                        0
                    ),

                'rejected' =>
                    (int) (
                        $excuseLetterStats
                            ?->rejected
                        ??
                        0
                    ),
            ],


            'reports' => [
                'total' =>
                    (int) (
                        $reportStats
                            ?->total
                        ??
                        0
                    ),

                'pending' =>
                    (int) (
                        $reportStats
                            ?->pending
                        ??
                        0
                    ),

                'in_review' =>
                    (int) (
                        $reportStats
                            ?->in_review
                        ??
                        0
                    ),

                'resolved' =>
                    (int) (
                        $reportStats
                            ?->resolved
                        ??
                        0
                    ),
            ],


            'attendance' => [
                'total' =>
                    (int) (
                        $attendanceStats
                            ?->total
                        ??
                        0
                    ),

                'present' =>
                    (int) (
                        $attendanceStats
                            ?->present
                        ??
                        0
                    ),

                'late' =>
                    (int) (
                        $attendanceStats
                            ?->late
                        ??
                        0
                    ),

                'excused' =>
                    (int) (
                        $attendanceStats
                            ?->excused
                        ??
                        0
                    ),

                'absent' =>
                    (int) (
                        $attendanceStats
                            ?->absent
                        ??
                        0
                    ),
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Schedule Time Label
    |--------------------------------------------------------------------------
    */

    private function scheduleTimeLabel(
        ?string $startTime,
        ?string $endTime
    ): string {
        $start =
            $this->formatTime(
                $startTime
            );

        $end =
            $this->formatTime(
                $endTime
            );

        if (
            $start !== ''
            &&
            $end !== ''
        ) {
            return
                $start
                .
                ' - '
                .
                $end;
        }

        return
            $start
            ?:
            $end
            ?:
            '';
    }


    /*
    |--------------------------------------------------------------------------
    | Format Time
    |--------------------------------------------------------------------------
    */

    private function formatTime(
        ?string $time
    ): string {
        if (
            blank(
                $time
            )
        ) {
            return '';
        }

        foreach (
            [
                'H:i:s',
                'H:i',
            ]
            as $format
        ) {
            try {
                return Carbon::createFromFormat(
                    $format,
                    $time
                )
                    ->format(
                        'g:i A'
                    );

            } catch (
                \Throwable
            ) {
                /*
                |--------------------------------------------------------------------------
                | Try Next Format
                |--------------------------------------------------------------------------
                */
            }
        }

        return $time;
    }


    /*
    |--------------------------------------------------------------------------
    | Announcement Creator
    |--------------------------------------------------------------------------
    */

    private function creatorLabel(
        Announcement $announcement
    ): string {
        $creatorName =
            trim(
                (string) (
                    $announcement
                        ->creator
                        ?->name
                    ??
                    ''
                )
            );

        if (
            $creatorName !== ''
        ) {
            return $creatorName;
        }

        return match (
            $announcement->creator_role
        ) {
            Announcement::ROLE_UNIVERSITY_ADMIN =>
                'University Admin',

            Announcement::ROLE_INSTRUCTOR =>
                'Instructor',

            Announcement::ROLE_COORDINATOR_ANNOUNCEMENT =>
                'Announcement Coordinator',

            default =>
                'NSTP Office',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Public File URL
    |--------------------------------------------------------------------------
    */

    private function fileUrl(
        Request $request,
        ?string $path
    ): ?string {
        if (
            blank(
                $path
            )
        ) {
            return null;
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
            return $path;
        }

        $relativeUrl =
            Storage::disk(
                'public'
            )
                ->url(
                    $path
                );

        return
            $request
                ->getSchemeAndHttpHost()
            .
            $relativeUrl;
    }
}