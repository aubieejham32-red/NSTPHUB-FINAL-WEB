<?php

namespace App\Http\Controllers\InstructorCoordinators;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Coordinator;
use App\Models\ExcuseLetter;
use App\Models\Instructor;
use App\Models\University;
use App\Models\UniversityAdministrator;
use App\Support\NstpComponentAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ExcuseLetterController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $access =
            $this->resolveAccess();

        $query =
            ExcuseLetter::query()
                ->with([
                    'student',
                    'instructor',
                    'reviewer',
                ]);

        if (
            $access['role'] !==
            ExcuseLetter::ROLE_SUPER_ADMIN
        ) {
            $query->where(
                'university_id',
                $access['university_id']
            );
        }

        if (
            $access['role'] ===
            ExcuseLetter::ROLE_STUDENT
        ) {
            $query->where(
                'user_id',
                $access['actor']->getKey()
            );
        }

        if (
            in_array(
                $access['role'],
                [
                    ExcuseLetter::ROLE_UNIVERSITY_ADMIN,
                    ExcuseLetter::ROLE_INSTRUCTOR,
                    ExcuseLetter::ROLE_COORDINATOR_ATTENDANCE,
                    ExcuseLetter::ROLE_COORDINATOR_ANNOUNCEMENT,
                    ExcuseLetter::ROLE_COORDINATOR_SCHEDULE,
                ],
                true
            )
        ) {
            $this->ensureValidAccountComponent(
                $access
            );

            $query->where(
                'component',
                $access['component']
            );
        }

        $letters =
            $query
                ->orderByDesc(
                    'created_at'
                )
                ->get()
                ->map(
                    function (
                        ExcuseLetter $letter
                    ): array {
                        return $this->letterPayload(
                            $letter
                        );
                    }
                )
                ->values();

        $sidebarUser =
            $this->buildSidebarUser(
                $access
            );

        $payload = [
            'user' =>
                $sidebarUser,

            'role' =>
                $access['role'],

            'authRole' =>
                $access['role'],

            'canManage' =>
                (
                    $access['can_manage']
                    &&
                    ExcuseLetter::roleCanManage(
                        $access['role']
                    )
                ),

            'activeComponent' =>
                $access['component'],

            'componentOptions' =>
                $access['component_options'],

            'letters' =>
                $letters,
        ];

        if (
            $request->expectsJson()
        ) {
            return response()->json([
                'success' => true,
                ...$payload,
            ]);
        }

        return Inertia::render(
            'InstructorCoordinators/ExcuseLetter',
            $payload
        );
    }


    public function approve(
        Request $request,
        ExcuseLetter $excuseLetter
    ): RedirectResponse|JsonResponse {
        $access =
            $this->resolveAccess();

        $this->ensureCanManage(
            $access
        );

        $this->ensureCanManageExcuseLetter(
            $excuseLetter,
            $access
        );

        /*
        |--------------------------------------------------------------------------
        | Fast Final-Decision Check
        |--------------------------------------------------------------------------
        |
        | This gives an immediate response when the record is already final.
        | The same check is repeated after lockForUpdate() below for proper
        | concurrency protection.
        |
        */

        $this->ensureExcuseLetterIsPending(
            $excuseLetter
        );

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
                        'Please write feedback before approving the excuse letter.',

                    'feedback.min' =>
                        'Feedback must contain at least 2 characters.',

                    'feedback.max' =>
                        'Feedback must not exceed 3000 characters.',
                ]
            );

        DB::transaction(
            function () use (
                $excuseLetter,
                $validated,
                $access
            ): void {
                /*
                |--------------------------------------------------------------------------
                | Lock Current Excuse Letter
                |--------------------------------------------------------------------------
                */

                $letter =
                    ExcuseLetter::query()
                        ->whereKey(
                            $excuseLetter->getKey()
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Recheck Permission On Locked Record
                |--------------------------------------------------------------------------
                */

                $this->ensureCanManageExcuseLetter(
                    $letter,
                    $access
                );

                /*
                |--------------------------------------------------------------------------
                | Final Decision Protection
                |--------------------------------------------------------------------------
                |
                | Only PENDING can reach the approval code.
                |
                | APPROVED -> cannot approve again
                | APPROVED -> cannot reject later
                | REJECTED -> cannot approve later
                | REJECTED -> cannot reject again
                |
                */

                $this->ensureExcuseLetterIsPending(
                    $letter
                );

                /*
                |--------------------------------------------------------------------------
                | Approve
                |--------------------------------------------------------------------------
                */

                $letter->status =
                    ExcuseLetter::STATUS_APPROVED;

                $letter->feedback =
                    trim(
                        $validated[
                            'feedback'
                        ]
                    );

                $letter->reviewer_id =
                    $access[
                        'actor'
                    ]->getKey();

                $letter->reviewer_type =
                    $access[
                        'actor'
                    ]->getMorphClass();

                $letter->reviewer_role =
                    $access[
                        'role'
                    ];

                $letter->reviewed_at =
                    now();

                $letter->save();

                /*
                |--------------------------------------------------------------------------
                | Change Matching Attendance To EXCUSED
                |--------------------------------------------------------------------------
                */

                $this->markAttendanceExcused(
                    $letter,
                    $access
                );
            }
        );

        $message =
            'Excuse letter approved successfully. Attendance has been updated to EXCUSED.';

        if (
            $request->expectsJson()
        ) {
            $excuseLetter
                ->refresh()
                ->loadMissing([
                    'student',
                    'instructor',
                    'reviewer',
                ]);

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    $message,

                'letter' =>
                    $this->letterPayload(
                        $excuseLetter
                    ),
            ]);
        }

        return back()->with(
            'success',
            $message
        );
    }


    public function reject(
        Request $request,
        ExcuseLetter $excuseLetter
    ): RedirectResponse|JsonResponse {
        $access =
            $this->resolveAccess();

        $this->ensureCanManage(
            $access
        );

        $this->ensureCanManageExcuseLetter(
            $excuseLetter,
            $access
        );

        /*
        |--------------------------------------------------------------------------
        | Fast Final-Decision Check
        |--------------------------------------------------------------------------
        */

        $this->ensureExcuseLetterIsPending(
            $excuseLetter
        );

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
                        'Please write feedback before rejecting the excuse letter.',

                    'feedback.min' =>
                        'Feedback must contain at least 2 characters.',

                    'feedback.max' =>
                        'Feedback must not exceed 3000 characters.',
                ]
            );

        DB::transaction(
            function () use (
                $excuseLetter,
                $validated,
                $access
            ): void {
                /*
                |--------------------------------------------------------------------------
                | Lock Current Excuse Letter
                |--------------------------------------------------------------------------
                */

                $letter =
                    ExcuseLetter::query()
                        ->whereKey(
                            $excuseLetter->getKey()
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Recheck Permission
                |--------------------------------------------------------------------------
                */

                $this->ensureCanManageExcuseLetter(
                    $letter,
                    $access
                );

                /*
                |--------------------------------------------------------------------------
                | Final Decision Protection
                |--------------------------------------------------------------------------
                */

                $this->ensureExcuseLetterIsPending(
                    $letter
                );

                /*
                |--------------------------------------------------------------------------
                | Reject
                |--------------------------------------------------------------------------
                */

                $letter->status =
                    ExcuseLetter::STATUS_REJECTED;

                $letter->feedback =
                    trim(
                        $validated[
                            'feedback'
                        ]
                    );

                $letter->reviewer_id =
                    $access[
                        'actor'
                    ]->getKey();

                $letter->reviewer_type =
                    $access[
                        'actor'
                    ]->getMorphClass();

                $letter->reviewer_role =
                    $access[
                        'role'
                    ];

                $letter->reviewed_at =
                    now();

                $letter->save();

                /*
                |--------------------------------------------------------------------------
                | Important
                |--------------------------------------------------------------------------
                |
                | There is intentionally NO logic here to reverse a previous
                | approval.
                |
                | A previously APPROVED request can never reach this section.
                |
                */
            }
        );

        $message =
            'Excuse letter rejected successfully.';

        if (
            $request->expectsJson()
        ) {
            $excuseLetter
                ->refresh()
                ->loadMissing([
                    'student',
                    'instructor',
                    'reviewer',
                ]);

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    $message,

                'letter' =>
                    $this->letterPayload(
                        $excuseLetter
                    ),
            ]);
        }

        return back()->with(
            'success',
            $message
        );
    }


    private function markAttendanceExcused(
        ExcuseLetter $letter,
        array $access
    ): void {
        $component =
            $this->normalizeComponent(
                $letter->component
            );

        if (
            !$component
        ) {
            throw ValidationException::withMessages([
                'component' =>
                    'The excuse letter does not have a valid NSTP component.',
            ]);
        }

        $absenceDate =
            $letter
                ->absence_date
                ?->format(
                    'Y-m-d'
                );

        if (
            !$absenceDate
        ) {
            throw ValidationException::withMessages([
                'absence_date' =>
                    'The excuse letter does not contain a valid absence date.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Matching Attendance
        |--------------------------------------------------------------------------
        |
        | Student
        | Component
        | Absence Date
        |
        */

        $attendance =
            Attendance::query()
                ->where(
                    'user_id',
                    $letter->user_id
                )
                ->where(
                    'component',
                    $component
                )
                ->whereDate(
                    'attendance_date',
                    $absenceDate
                )
                ->lockForUpdate()
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Excuse Letter Marker
        |--------------------------------------------------------------------------
        */

        $marker =
            '[EXCUSE_LETTER:'
            .
            $letter->id
            .
            ']';

        $note =
            $marker
            .
            ' Excuse letter approved. '
            .
            trim(
                (string) $letter->feedback
            );

        /*
        |--------------------------------------------------------------------------
        | Existing Attendance
        |--------------------------------------------------------------------------
        */

        if (
            $attendance
        ) {
            $attendance->remark =
                Attendance::REMARK_EXCUSED;

            $attendance->notes =
                $this->mergeAttendanceNote(
                    $attendance->notes,
                    $marker,
                    $note
                );

            $attendance->recorded_by_type =
                $access[
                    'actor'
                ]->getMorphClass();

            $attendance->recorded_by_id =
                $access[
                    'actor'
                ]->getKey();

            $attendance->save();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | No Attendance Exists Yet
        |--------------------------------------------------------------------------
        */

        Attendance::create([
            'user_id' =>
                $letter->user_id,

            'component' =>
                $component,

            'attendance_date' =>
                $absenceDate,

            'time_in' =>
                null,

            'time_out' =>
                null,

            'remark' =>
                Attendance::REMARK_EXCUSED,

            'notes' =>
                $note,

            'recorded_by_type' =>
                $access[
                    'actor'
                ]->getMorphClass(),

            'recorded_by_id' =>
                $access[
                    'actor'
                ]->getKey(),
        ]);
    }


    private function mergeAttendanceNote(
        ?string $existingNotes,
        string $marker,
        string $newNote
    ): string {
        $lines =
            preg_split(
                '/\r\n|\r|\n/',
                trim(
                    (string) $existingNotes
                )
            )
            ?: [];

        $lines =
            collect(
                $lines
            )
                ->filter(
                    function (
                        string $line
                    ) use (
                        $marker
                    ): bool {
                        $line =
                            trim(
                                $line
                            );

                        return (
                            $line !==
                            ''
                            &&
                            !str_contains(
                                $line,
                                $marker
                            )
                        );
                    }
                )
                ->values()
                ->all();

        $lines[] =
            $newNote;

        return implode(
            PHP_EOL,
            $lines
        );
    }


    private function letterPayload(
        ExcuseLetter $letter
    ): array {
        $student =
            $letter->student;

        $instructor =
            $letter->instructor;

        $reviewer =
            $letter->reviewer;

        $studentName =
            $student
                ? (
                    $student->full_name
                    ??
                    $student->name
                    ??
                    'Student'
                )
                : 'Student';

        $studentId =
            $student
                ? (
                    $student->username
                    ??
                    $student->email
                    ??
                    (
                        'Student #'
                        .
                        $student->id
                    )
                )
                : '—';

        return [
            /*
            |--------------------------------------------------------------------------
            | Excuse Letter
            |--------------------------------------------------------------------------
            */

            'id' =>
                $letter->id,

            'status' =>
                $letter->status,

            /*
            |--------------------------------------------------------------------------
            | Mobile / Frontend Final Decision Flag
            |--------------------------------------------------------------------------
            */

            'decision_locked' =>
                in_array(
                    strtoupper(
                        trim(
                            (string) $letter->status
                        )
                    ),
                    [
                        ExcuseLetter::STATUS_APPROVED,
                        ExcuseLetter::STATUS_REJECTED,
                    ],
                    true
                ),

            'component' =>
                $letter->component,

            'absence_date' =>
                $letter
                    ->absence_date
                    ?->format(
                        'Y-m-d'
                    ),

            'filed_date' =>
                $letter
                    ->created_at
                    ?->format(
                        'Y-m-d'
                    ),

            'reason' =>
                $letter->reason,

            'explanation' =>
                $letter->explanation,

            'feedback' =>
                $letter->feedback,

            /*
            |--------------------------------------------------------------------------
            | Evidence
            |--------------------------------------------------------------------------
            */

            'evidence_url' =>
                $this->publicFileUrl(
                    $letter->evidence_path
                ),

            'evidence_type' =>
                $this->evidenceType(
                    $letter->evidence_mime_type
                ),

            'evidence_mime_type' =>
                $letter->evidence_mime_type,

            'evidence_original_name' =>
                $letter->evidence_original_name,

            'evidence_size' =>
                $letter->evidence_size,

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

                        'name' =>
                            $studentName,

                        'student_id' =>
                            $studentId,

                        'email' =>
                            $student->email,

                        'profile_photo' =>
                            $student->profile_photo
                            ??
                            null,

                        'profile_photo_url' =>
                            $this->publicFileUrl(
                                $student->profile_photo
                                ??
                                null
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

                        'name' =>
                            $instructor->full_name
                            ??
                            $instructor->username
                            ??
                            'Instructor',
                    ]
                    : null,

            /*
            |--------------------------------------------------------------------------
            | Reviewer
            |--------------------------------------------------------------------------
            */

            'reviewer' =>
                $reviewer
                    ? [
                        'id' =>
                            $reviewer->getKey(),

                        'name' =>
                            $this->actorName(
                                $reviewer
                            ),

                        'role' =>
                            $letter->reviewer_role,

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

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'reviewed_at' =>
                $letter
                    ->reviewed_at
                    ?->toISOString(),

            'created_at' =>
                $letter
                    ->created_at
                    ?->toISOString(),

            'updated_at' =>
                $letter
                    ->updated_at
                    ?->toISOString(),
        ];
    }


    private function resolveAccess(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Sanctum / Mobile Instructor
        |--------------------------------------------------------------------------
        */

        $apiActor =
            request()->user();

        if (
            $apiActor instanceof Instructor
            &&
            $apiActor->isInstructor()
        ) {
            $universityId =
                $this->resolveUniversityId(
                    $apiActor
                );

            $componentOptions =
                $apiActor->componentCodes();

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
                    $apiActor,

                'guard' =>
                    'sanctum',

                'role' =>
                    ExcuseLetter::ROLE_INSTRUCTOR,

                'university_id' =>
                    $universityId,

                'component' =>
                    $component,

                'component_options' =>
                    $componentOptions,

                'can_manage' =>
                    true,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sanctum / Mobile Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            $apiActor instanceof Coordinator
        ) {
            $universityId =
                $this->resolveUniversityId(
                    $apiActor
                );

            $component =
                $this->normalizeComponent(
                    $apiActor->component
                    ??
                    null
                );

            if (
                $this->coordinatorHasRole(
                    $apiActor,
                    ExcuseLetter::ROLE_COORDINATOR_ATTENDANCE
                )
            ) {
                return [
                    'actor' =>
                        $apiActor,

                    'guard' =>
                        'sanctum',

                    'role' =>
                        ExcuseLetter::ROLE_COORDINATOR_ATTENDANCE,

                    'university_id' =>
                        $universityId,

                    'component' =>
                        $component,

                    'component_options' =>
                        $component
                            ? [$component]
                            : [],

                    'can_manage' =>
                        true,
                ];
            }

            if (
                $this->coordinatorHasRole(
                    $apiActor,
                    ExcuseLetter::ROLE_COORDINATOR_ANNOUNCEMENT
                )
            ) {
                return [
                    'actor' =>
                        $apiActor,

                    'guard' =>
                        'sanctum',

                    'role' =>
                        ExcuseLetter::ROLE_COORDINATOR_ANNOUNCEMENT,

                    'university_id' =>
                        $universityId,

                    'component' =>
                        $component,

                    'component_options' =>
                        $component
                            ? [$component]
                            : [],

                    'can_manage' =>
                        false,
                ];
            }

            if (
                $this->coordinatorHasRole(
                    $apiActor,
                    ExcuseLetter::ROLE_COORDINATOR_SCHEDULE
                )
            ) {
                return [
                    'actor' =>
                        $apiActor,

                    'guard' =>
                        'sanctum',

                    'role' =>
                        ExcuseLetter::ROLE_COORDINATOR_SCHEDULE,

                    'university_id' =>
                        $universityId,

                    'component' =>
                        $component,

                    'component_options' =>
                        $component
                            ? [$component]
                            : [],

                    'can_manage' =>
                        false,
                ];
            }

            abort(
                403,
                'Your coordinator account does not have a recognized role.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sanctum / Mobile University Administrator
        |--------------------------------------------------------------------------
        */

        if (
            $apiActor instanceof UniversityAdministrator
        ) {
            $universityId =
                $this->resolveUniversityId(
                    $apiActor
                );

            $componentOptions =
                $this->universityComponentOptions(
                    $universityId
                );

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
                    $apiActor,

                'guard' =>
                    'sanctum',

                'role' =>
                    ExcuseLetter::ROLE_UNIVERSITY_ADMIN,

                'university_id' =>
                    $universityId,

                'component' =>
                    $component,

                'component_options' =>
                    $componentOptions,

                'can_manage' =>
                    true,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Web Instructor
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

            $universityId =
                $this->resolveUniversityId(
                    $actor
                );

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
                    ExcuseLetter::ROLE_INSTRUCTOR,

                'university_id' =>
                    $universityId,

                'component' =>
                    $component,

                'component_options' =>
                    $componentOptions,

                'can_manage' =>
                    true,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Web Coordinator
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

            $universityId =
                $this->resolveUniversityId(
                    $actor
                );

            $component =
                $this->normalizeComponent(
                    $actor->component
                    ??
                    null
                );

            if (
                $this->coordinatorHasRole(
                    $actor,
                    ExcuseLetter::ROLE_COORDINATOR_ATTENDANCE
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        ExcuseLetter::ROLE_COORDINATOR_ATTENDANCE,

                    'university_id' =>
                        $universityId,

                    'component' =>
                        $component,

                    'component_options' =>
                        $component
                            ? [$component]
                            : [],

                    'can_manage' =>
                        true,
                ];
            }

            if (
                $this->coordinatorHasRole(
                    $actor,
                    ExcuseLetter::ROLE_COORDINATOR_ANNOUNCEMENT
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        ExcuseLetter::ROLE_COORDINATOR_ANNOUNCEMENT,

                    'university_id' =>
                        $universityId,

                    'component' =>
                        $component,

                    'component_options' =>
                        $component
                            ? [$component]
                            : [],

                    'can_manage' =>
                        false,
                ];
            }

            if (
                $this->coordinatorHasRole(
                    $actor,
                    ExcuseLetter::ROLE_COORDINATOR_SCHEDULE
                )
            ) {
                return [
                    'actor' =>
                        $actor,

                    'guard' =>
                        'coordinator',

                    'role' =>
                        ExcuseLetter::ROLE_COORDINATOR_SCHEDULE,

                    'university_id' =>
                        $universityId,

                    'component' =>
                        $component,

                    'component_options' =>
                        $component
                            ? [$component]
                            : [],

                    'can_manage' =>
                        false,
                ];
            }

            abort(
                403,
                'Your coordinator account does not have a recognized role.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Web University Administrator
        |--------------------------------------------------------------------------
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

            $universityId =
                $this->resolveUniversityId(
                    $actor
                );

            $componentOptions =
                $this->universityComponentOptions(
                    $universityId
                );

            return [
                'actor' =>
                    $actor,

                'guard' =>
                    'university_admin',

                'role' =>
                    ExcuseLetter::ROLE_UNIVERSITY_ADMIN,

                'university_id' =>
                    $universityId,

                'component' =>
                    NstpComponentAccess::selected(
                        request(),
                        [
                            'components' =>
                                $componentOptions,
                        ]
                    ),

                'component_options' =>
                    $componentOptions,

                'can_manage' =>
                    true,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'web'
            )->check()
        ) {
            $actor =
                Auth::guard(
                    'web'
                )->user();

            $universityId =
                $this->resolveUniversityId(
                    $actor
                );

            $component =
                $this->normalizeComponent(
                    $actor->component
                    ??
                    null
                );

            return [
                'actor' =>
                    $actor,

                'guard' =>
                    'web',

                'role' =>
                    ExcuseLetter::ROLE_STUDENT,

                'university_id' =>
                    $universityId,

                'component' =>
                    $component,

                'component_options' =>
                    $component
                        ? [$component]
                        : [],

                'can_manage' =>
                    false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Super Administrator
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'superadmin'
            )->check()
        ) {
            $actor =
                Auth::guard(
                    'superadmin'
                )->user();

            return [
                'actor' =>
                    $actor,

                'guard' =>
                    'superadmin',

                'role' =>
                    ExcuseLetter::ROLE_SUPER_ADMIN,

                'university_id' =>
                    null,

                'component' =>
                    null,

                'component_options' =>
                    ExcuseLetter::components(),

                'can_manage' =>
                    false,
            ];
        }

        abort(
            401,
            'You must be logged in to view excuse letters.'
        );
    }


    private function ensureCanManage(
        array $access
    ): void {
        if (
            !$access[
                'can_manage'
            ]
        ) {
            abort(
                403,
                'You only have permission to view excuse letters.'
            );
        }

        if (
            !ExcuseLetter::roleCanManage(
                $access[
                    'role'
                ]
            )
        ) {
            abort(
                403,
                'You do not have permission to approve or reject excuse letters.'
            );
        }
    }


    private function canManageExcuseLetter(
        ExcuseLetter $letter,
        array $access
    ): bool {
        if (
            !$access[
                'can_manage'
            ]
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | University Administrator
        |--------------------------------------------------------------------------
        */

        if (
            $access['role'] ===
            ExcuseLetter::ROLE_UNIVERSITY_ADMIN
        ) {
            return (
                (int)
                $letter->university_id
                ===
                (int)
                $access['university_id']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Instructor / Attendance Coordinator
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $access['role'],
                [
                    ExcuseLetter::ROLE_INSTRUCTOR,
                    ExcuseLetter::ROLE_COORDINATOR_ATTENDANCE,
                ],
                true
            )
        ) {
            return (
                (int)
                $letter->university_id
                ===
                (int)
                $access['university_id']
            )
            &&
            (
                $this->normalizeComponent(
                    $letter->component
                )
                ===
                $access['component']
            );
        }

        return false;
    }


    private function ensureCanManageExcuseLetter(
        ExcuseLetter $letter,
        array $access
    ): void {
        if (
            !$this->canManageExcuseLetter(
                $letter,
                $access
            )
        ) {
            abort(
                403,
                'You cannot approve or reject this excuse letter.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Final Decision Protection
    |--------------------------------------------------------------------------
    |
    | Only PENDING excuse letters are reviewable.
    |
    | APPROVED and REJECTED are permanent final states.
    |
    */

    private function ensureExcuseLetterIsPending(
        ExcuseLetter $letter
    ): void {
        $status =
            strtoupper(
                trim(
                    (string) $letter->status
                )
            );

        if (
            $status ===
            ExcuseLetter::STATUS_APPROVED
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'This excuse letter has already been approved. The decision is final and can no longer be changed.',
            ]);
        }

        if (
            $status ===
            ExcuseLetter::STATUS_REJECTED
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'This excuse letter has already been rejected. The decision is final and can no longer be changed.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Defensive Protection
        |--------------------------------------------------------------------------
        |
        | The normal undecided state is PENDING. Any unexpected database state
        | is blocked instead of being silently overwritten.
        |
        */

        if (
            $status !==
            'PENDING'
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'This excuse letter is not pending and cannot be reviewed.',
            ]);
        }
    }


    private function buildSidebarUser(
        array $access
    ): array {
        $actor =
            $access[
                'actor'
            ];

        return [
            'id' =>
                $actor->getKey(),

            'university_id' =>
                $access[
                    'university_id'
                ],

            'full_name' =>
                $this->actorName(
                    $actor
                ),

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
                $access[
                    'component'
                ],

            'role' =>
                $access[
                    'role'
                ],
        ];
    }


    private function actorName(
        object $actor
    ): string {
        $name =
            $actor->full_name
            ??
            $actor->name
            ??
            null;

        if (
            $name
        ) {
            return trim(
                (string) $name
            );
        }

        $parts = [
            $actor->first_name
            ??
            null,

            $actor->middle_name
            ??
            null,

            $actor->last_name
            ??
            null,
        ];

        $name =
            trim(
                implode(
                    ' ',
                    array_filter(
                        $parts
                    )
                )
            );

        if (
            $name !==
            ''
        ) {
            return $name;
        }

        return (
            $actor->username
            ??
            'NSTP USER'
        );
    }


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


    private function resolveUniversityId(
        object $actor
    ): int {
        $universityId =
            $actor->university_id
            ??
            null;

        if (
            !$universityId
        ) {
            abort(
                403,
                'Your account is not assigned to a university.'
            );
        }

        return (int)
            $universityId;
    }


    private function universityComponentOptions(
        int $universityId
    ): array {
        $university =
            University::find(
                $universityId
            );

        if (
            !$university
        ) {
            abort(
                403,
                'University account not found.'
            );
        }

        $components =
            $university->components
            ??
            [];

        if (
            !is_array(
                $components
            )
        ) {
            $components =
                [];
        }

        $normalized =
            [];

        foreach (
            $components as $component
        ) {
            $component =
                $this->normalizeComponent(
                    $component
                );

            if (
                $component
                &&
                in_array(
                    $component,
                    ExcuseLetter::components(),
                    true
                )
            ) {
                $normalized[] =
                    $component;
            }
        }

        if (
            empty(
                $normalized
            )
        ) {
            $normalized =
                ExcuseLetter::components();
        }

        return array_values(
            array_unique(
                $normalized
            )
        );
    }


    private function normalizeComponent(
        mixed $component
    ): ?string {
        if (
            $component ===
            null
            ||
            $component ===
            ''
        ) {
            return null;
        }

        $component =
            strtoupper(
                trim(
                    (string) $component
                )
            );

        if (
            !in_array(
                $component,
                ExcuseLetter::components(),
                true
            )
        ) {
            return null;
        }

        return $component;
    }


    private function ensureValidAccountComponent(
        array $access
    ): void {
        if (
            !$access[
                'component'
            ]
            ||
            !in_array(
                $access[
                    'component'
                ],
                ExcuseLetter::components(),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'component' =>
                    'Your account does not have a valid CWTS, LTS, or ROTC component.',
            ]);
        }
    }


    private function publicFileUrl(
        ?string $path
    ): ?string {
        if (
            !$path
        ) {
            return null;
        }

        $path =
            trim(
                $path
            );

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

        if (
            str_starts_with(
                $path,
                '/storage/'
            )
        ) {
            return $path;
        }

        if (
            str_starts_with(
                $path,
                'storage/'
            )
        ) {
            return '/'
                .
                $path;
        }

        if (
            str_starts_with(
                $path,
                '/'
            )
        ) {
            return $path;
        }

        return Storage::disk(
            'public'
        )->url(
            ltrim(
                $path,
                '/'
            )
        );
    }


    private function evidenceType(
        ?string $mimeType
    ): string {
        $mimeType =
            strtolower(
                trim(
                    (string) $mimeType
                )
            );

        if (
            str_starts_with(
                $mimeType,
                'image/'
            )
        ) {
            return 'image';
        }

        if (
            $mimeType !==
            ''
        ) {
            return 'document';
        }

        return '';
    }
}