<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MobileAnnouncementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Student Announcements
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/announcements
    |
    */

    public function index(
        Request $request
    ): JsonResponse {
        try {

            /*
            |--------------------------------------------------------------------------
            | Authenticated Student
            |--------------------------------------------------------------------------
            */

            $student =
                $request->user();


            if (
                !(
                    $student instanceof User
                )
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Authenticated student account could not be found.',
                ], 401);
            }


            /*
            |--------------------------------------------------------------------------
            | Student Authorization
            |--------------------------------------------------------------------------
            */

            if (
                !$student->isStudent()
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'This account is not authorized as an NSTP student.',
                ], 403);
            }


            /*
            |--------------------------------------------------------------------------
            | University Required
            |--------------------------------------------------------------------------
            */

            if (
                !$student->university_id
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Your student account is not connected to a university.',
                ], 409);
            }


            /*
            |--------------------------------------------------------------------------
            | Student Email
            |--------------------------------------------------------------------------
            */

            $studentEmail =
                strtolower(
                    trim(
                        (string)
                        $student->email
                    )
                );


            if (
                $studentEmail ===
                ''
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Your student account does not have a valid email address.',
                ], 409);
            }


            /*
            |--------------------------------------------------------------------------
            | Student Component
            |--------------------------------------------------------------------------
            */

            $component =
                strtoupper(
                    trim(
                        (string)
                        (
                            $student->component
                            ??
                            ''
                        )
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Base Query
            |--------------------------------------------------------------------------
            |
            | Security:
            |
            | Student can only access announcements belonging to the university
            | connected to users.university_id.
            |
            */

            $query =
                Announcement::query()
                    ->where(
                        'university_id',
                        $student->university_id
                    );


            /*
            |--------------------------------------------------------------------------
            | Recipient Filtering
            |--------------------------------------------------------------------------
            |
            | Student receives:
            |
            | recipient = all_students
            |
            | OR
            |
            | recipient = specific_student
            | AND student_email = authenticated student's email
            |
            */

            $query->forStudent(
                $studentEmail
            );


            /*
            |--------------------------------------------------------------------------
            | Component Filtering
            |--------------------------------------------------------------------------
            |
            | Example ROTC student:
            |
            | Can see:
            |
            | ALL
            | ROTC
            |
            | Cannot see:
            |
            | CWTS
            | LTS
            |
            */

            if (
                in_array(
                    $component,
                    Announcement::components(),
                    true
                )
            ) {
                $query->forComponent(
                    $component
                );

            } else {

                /*
                |--------------------------------------------------------------------------
                | Invalid / Missing Component
                |--------------------------------------------------------------------------
                |
                | Only general announcements are returned.
                |
                */

                $query->where(
                    'component',
                    Announcement::COMPONENT_ALL
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Latest Announcements First
            |--------------------------------------------------------------------------
            */

            $announcements =
                $query
                    ->recent()
                    ->get();


            /*
            |--------------------------------------------------------------------------
            | Format Mobile Response
            |--------------------------------------------------------------------------
            */

            $formatted =
                $announcements
                    ->map(
                        function (
                            Announcement $announcement
                        ): array {
                            $announcementDate =
                                $announcement
                                    ->announcement_date;


                            return [
                                'id' =>
                                    $announcement->id,

                                'component' =>
                                    strtoupper(
                                        trim(
                                            (string)
                                            $announcement->component
                                        )
                                    ),

                                'title' =>
                                    $announcement->title,

                                'description' =>
                                    $announcement->description,

                                /*
                                |--------------------------------------------------------------------------
                                | Raw Date
                                |--------------------------------------------------------------------------
                                */

                                'announcement_date' =>
                                    $announcementDate
                                        ? $announcementDate
                                            ->format(
                                                'Y-m-d'
                                            )
                                        : null,

                                /*
                                |--------------------------------------------------------------------------
                                | Display Date
                                |--------------------------------------------------------------------------
                                */

                                'date_label' =>
                                    $announcementDate
                                        ? $announcementDate
                                            ->format(
                                                'F d, Y'
                                            )
                                        : null,

                                'day_label' =>
                                    $announcementDate
                                        ? $announcementDate
                                            ->format(
                                                'l'
                                            )
                                        : null,

                                /*
                                |--------------------------------------------------------------------------
                                | Creator
                                |--------------------------------------------------------------------------
                                */

                                'creator_role' =>
                                    $announcement->creator_role,

                                'author_label' =>
                                    $this->authorLabel(
                                        $announcement
                                            ->creator_role
                                    ),

                                /*
                                |--------------------------------------------------------------------------
                                | Recipient
                                |--------------------------------------------------------------------------
                                |
                                | We intentionally do NOT expose student_email.
                                |
                                */

                                'recipient_type' =>
                                    $announcement->recipient,

                                'is_personal' =>
                                    $announcement
                                        ->isForSpecificStudent(),

                                /*
                                |--------------------------------------------------------------------------
                                | Date Status
                                |--------------------------------------------------------------------------
                                */

                                'is_today' =>
                                    $announcementDate
                                        ? $announcementDate
                                            ->isToday()
                                        : false,
                            ];
                        }
                    )
                    ->values();


            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Student announcements loaded successfully.',

                'component' =>
                    $component !== ''
                        ? $component
                        : null,

                'total' =>
                    $formatted->count(),

                'announcements' =>
                    $formatted,
            ]);

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Laravel Log
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Mobile announcements API failed.',
                [
                    'message' =>
                        $exception->getMessage(),

                    'file' =>
                        $exception->getFile(),

                    'line' =>
                        $exception->getLine(),

                    'user_id' =>
                        optional(
                            $request->user()
                        )->id,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Always Return JSON
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    config(
                        'app.debug'
                    )
                        ? 'Announcement error: '
                            .
                            $exception->getMessage()
                        : 'Unable to load your NSTP announcements.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Author Label
    |--------------------------------------------------------------------------
    */

    private function authorLabel(
        ?string $role
    ): string {
        return match (
            trim(
                (string)
                $role
            )
        ) {
            Announcement::ROLE_UNIVERSITY_ADMIN =>
                'University Administrator',

            Announcement::ROLE_INSTRUCTOR =>
                'NSTP Instructor',

            Announcement::ROLE_COORDINATOR_ANNOUNCEMENT =>
                'Announcement Coordinator',

            default =>
                'NSTP Administration',
        };
    }
}