<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ExcuseLetter;
use App\Models\Instructor;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Throwable;

class MobileExcuseLetterController extends Controller
{
    private const TIMEZONE = 'Asia/Manila';

    private const EVIDENCE_DISK = 'public';

    private const MAX_EVIDENCE_KILOBYTES = 10240;

    private const EVIDENCE_URL_LIFETIME_HOURS = 6;


    /*
    |--------------------------------------------------------------------------
    | List Student Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $letters =
            ExcuseLetter::query()
                ->where(
                    'user_id',
                    $student->id
                )
                ->where(
                    'university_id',
                    $student->university_id
                )
                ->with([
                    'instructor',
                    'reviewer',
                ])
                ->orderByDesc(
                    'created_at'
                )
                ->orderByDesc(
                    'id'
                )
                ->get()
                ->map(
                    fn (
                        ExcuseLetter $letter
                    ): array =>
                        $this->letterPayload(
                            $request,
                            $student,
                            $letter
                        )
                )
                ->values();


        $allowedComponents =
            $this->allowedComponents(
                $student
            );


        $instructors =
            $this->availableInstructors(
                $student,
                $allowedComponents
            );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Excuse letters retrieved successfully.',

            'student' =>
                $this->studentPayload(
                    $request,
                    $student
                ),

            'university' =>
                $this->universityPayload(
                    $request,
                    $student
                ),

            'allowed_components' =>
                $allowedComponents,

            'instructors' =>
                $instructors,

            'summary' => [
                'total' =>
                    $letters->count(),

                'pending' =>
                    $letters
                        ->where(
                            'status',
                            ExcuseLetter::STATUS_PENDING
                        )
                        ->count(),

                'approved' =>
                    $letters
                        ->where(
                            'status',
                            ExcuseLetter::STATUS_APPROVED
                        )
                        ->count(),

                'rejected' =>
                    $letters
                        ->where(
                            'status',
                            ExcuseLetter::STATUS_REJECTED
                        )
                        ->count(),
            ],

            'letters' =>
                $letters,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Store Excuse Letter
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $validated =
            $this->validateExcuseLetter(
                $request,
                $student
            );


        $component =
            $this->validateStudentComponent(
                $student,
                $validated[
                    'component'
                ]
            );


        $instructor =
            $this->resolveInstructor(
                $student,
                $component,
                $validated[
                    'instructor_id'
                ]
                    ?? null
            );


        $this->validateAbsenceDate(
            $validated[
                'absence_date'
            ]
        );


        $this->ensureNoDuplicateLetter(
            $student,
            $component,
            $validated[
                'absence_date'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Optional Supporting Evidence
        |--------------------------------------------------------------------------
        |
        | Supporting evidence remains optional in the API.
        |
        | If evidence is supplied, it will be validated and stored.
        |
        */

        $evidence =
            $request->hasFile(
                'evidence'
            )
                ? $request->file(
                    'evidence'
                )
                : null;


        $evidencePath =
            null;


        try {

            if (
                $evidence
            ) {
                $evidencePath =
                    $this->storeEvidence(
                        $student,
                        $evidence
                    );
            }


            $letter =
                DB::transaction(
                    function () use (
                        $student,
                        $validated,
                        $component,
                        $instructor,
                        $evidence,
                        $evidencePath
                    ): ExcuseLetter {

                        return ExcuseLetter::create([
                            'university_id' =>
                                $student->university_id,

                            'user_id' =>
                                $student->id,

                            'instructor_id' =>
                                $instructor?->id,

                            'component' =>
                                $component,

                            'absence_date' =>
                                $validated[
                                    'absence_date'
                                ],

                            'reason' =>
                                trim(
                                    $validated[
                                        'reason'
                                    ]
                                ),

                            'explanation' =>
                                trim(
                                    $validated[
                                        'explanation'
                                    ]
                                ),

                            'evidence_path' =>
                                $evidencePath,

                            'evidence_original_name' =>
                                $evidence
                                    ?->getClientOriginalName(),

                            'evidence_mime_type' =>
                                $evidence
                                    ?->getMimeType(),

                            'evidence_size' =>
                                $evidence
                                    ?->getSize(),

                            'status' =>
                                ExcuseLetter::STATUS_PENDING,

                            'feedback' =>
                                null,

                            'reviewer_id' =>
                                null,

                            'reviewer_type' =>
                                null,

                            'reviewer_role' =>
                                null,

                            'reviewed_at' =>
                                null,
                        ]);
                    }
                );


            $letter->load([
                'instructor',
                'reviewer',
            ]);


            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Excuse letter submitted successfully.',

                'letter' =>
                    $this->letterPayload(
                        $request,
                        $student,
                        $letter
                    ),
            ], 201);

        } catch (
            Throwable $throwable
        ) {

            if (
                $evidencePath
            ) {
                Storage::disk(
                    self::EVIDENCE_DISK
                )->delete(
                    $evidencePath
                );
            }


            throw $throwable;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Pending Excuse Letter
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        ExcuseLetter $excuseLetter
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $this->ensureOwnedByStudent(
            $student,
            $excuseLetter
        );


        $this->ensurePending(
            $excuseLetter
        );


        $validated =
            $this->validateExcuseLetter(
                $request,
                $student,
                $excuseLetter
            );


        $component =
            $this->validateStudentComponent(
                $student,
                $validated[
                    'component'
                ]
            );


        $instructor =
            $this->resolveInstructor(
                $student,
                $component,
                $validated[
                    'instructor_id'
                ]
                    ?? null
            );


        $this->validateAbsenceDate(
            $validated[
                'absence_date'
            ]
        );


        $this->ensureNoDuplicateLetter(
            $student,
            $component,
            $validated[
                'absence_date'
            ],
            $excuseLetter
        );


        $removeEvidence =
            (bool) (
                $validated[
                    'remove_evidence'
                ]
                ?? false
            );


        $hasNewEvidence =
            $request->hasFile(
                'evidence'
            );


        $newEvidencePath =
            null;


        $oldEvidencePath =
            $excuseLetter
                ->evidence_path;


        try {

            if (
                $hasNewEvidence
            ) {
                $newEvidencePath =
                    $this->storeEvidence(
                        $student,
                        $request->file(
                            'evidence'
                        )
                    );
            }


            $letter =
                DB::transaction(
                    function () use (
                        $excuseLetter,
                        $student,
                        $validated,
                        $component,
                        $instructor,
                        $request,
                        $hasNewEvidence,
                        $newEvidencePath,
                        $removeEvidence
                    ): ExcuseLetter {

                        $letter =
                            ExcuseLetter::query()
                                ->whereKey(
                                    $excuseLetter->id
                                )
                                ->lockForUpdate()
                                ->firstOrFail();


                        $this->ensureOwnedByStudent(
                            $student,
                            $letter
                        );


                        $this->ensurePending(
                            $letter
                        );


                        $letter->component =
                            $component;


                        $letter->instructor_id =
                            $instructor?->id;


                        $letter->absence_date =
                            $validated[
                                'absence_date'
                            ];


                        $letter->reason =
                            trim(
                                $validated[
                                    'reason'
                                ]
                            );


                        $letter->explanation =
                            trim(
                                $validated[
                                    'explanation'
                                ]
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Replace Evidence
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $hasNewEvidence
                        ) {
                            $evidence =
                                $request->file(
                                    'evidence'
                                );


                            $letter->evidence_path =
                                $newEvidencePath;


                            $letter->evidence_original_name =
                                $evidence
                                    ->getClientOriginalName();


                            $letter->evidence_mime_type =
                                $evidence
                                    ->getMimeType();


                            $letter->evidence_size =
                                $evidence
                                    ->getSize();

                        } elseif (
                            $removeEvidence
                        ) {

                            /*
                            |--------------------------------------------------------------------------
                            | Remove Existing Evidence
                            |--------------------------------------------------------------------------
                            */

                            $letter->evidence_path =
                                null;


                            $letter->evidence_original_name =
                                null;


                            $letter->evidence_mime_type =
                                null;


                            $letter->evidence_size =
                                null;
                        }


                        $letter->save();


                        return $letter;
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | Delete Old Physical Evidence
            |--------------------------------------------------------------------------
            */

            $shouldDeleteOldEvidence =
                $oldEvidencePath
                &&
                (
                    (
                        $newEvidencePath
                        &&
                        $oldEvidencePath !==
                            $newEvidencePath
                    )
                    ||
                    (
                        $removeEvidence
                        &&
                        !$hasNewEvidence
                    )
                );


            if (
                $shouldDeleteOldEvidence
            ) {
                Storage::disk(
                    self::EVIDENCE_DISK
                )->delete(
                    $oldEvidencePath
                );
            }


            $letter->load([
                'instructor',
                'reviewer',
            ]);


            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Excuse letter updated successfully.',

                'letter' =>
                    $this->letterPayload(
                        $request,
                        $student,
                        $letter
                    ),
            ]);

        } catch (
            Throwable $throwable
        ) {

            if (
                $newEvidencePath
            ) {
                Storage::disk(
                    self::EVIDENCE_DISK
                )->delete(
                    $newEvidencePath
                );
            }


            throw $throwable;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Pending Excuse Letter
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        ExcuseLetter $excuseLetter
    ): JsonResponse {
        $student =
            $this->authenticatedStudent(
                $request
            );


        $this->ensureOwnedByStudent(
            $student,
            $excuseLetter
        );


        $this->ensurePending(
            $excuseLetter
        );


        $evidencePath =
            $excuseLetter
                ->evidence_path;


        DB::transaction(
            function () use (
                $excuseLetter,
                $student
            ): void {

                $letter =
                    ExcuseLetter::query()
                        ->whereKey(
                            $excuseLetter->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $this->ensureOwnedByStudent(
                    $student,
                    $letter
                );


                $this->ensurePending(
                    $letter
                );


                $letter->delete();
            }
        );


        if (
            $evidencePath
        ) {
            Storage::disk(
                self::EVIDENCE_DISK
            )->delete(
                $evidencePath
            );
        }


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Excuse letter deleted successfully.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Display Supporting Evidence
    |--------------------------------------------------------------------------
    |
    | This endpoint is used by:
    |
    | 1. Normal evidence preview
    | 2. Full-screen enlarged preview
    | 3. Open original evidence
    |
    | Laravel serves the real file directly from storage/app/public.
    |
    | It does NOT depend on:
    |
    | public/storage
    |
    | The URL is protected by:
    |
    | - expiry timestamp
    | - HMAC signature
    |
    */

    public function evidence(
        Request $request,
        ExcuseLetter $excuseLetter
    ): BinaryFileResponse {
        $expires =
            (int) $request->query(
                'expires'
            );


        $signature =
            trim(
                (string) $request->query(
                    'signature'
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Verify Expiration
        |--------------------------------------------------------------------------
        */

        if (
            !$expires
            ||
            $expires <
                CarbonImmutable::now(
                    self::TIMEZONE
                )->timestamp
        ) {
            abort(
                403,
                'This evidence link has expired.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Verify Signature
        |--------------------------------------------------------------------------
        */

        $expectedSignature =
            $this->evidenceSignature(
                $excuseLetter->id,
                $expires
            );


        if (
            $signature === ''
            ||
            !hash_equals(
                $expectedSignature,
                $signature
            )
        ) {
            abort(
                403,
                'Invalid evidence link.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve Evidence Path
        |--------------------------------------------------------------------------
        */

        $path =
            trim(
                (string)
                $excuseLetter
                    ->evidence_path
            );


        if (
            $path === ''
        ) {
            abort(
                404,
                'Evidence file not found.'
            );
        }


        $disk =
            Storage::disk(
                self::EVIDENCE_DISK
            );


        if (
            !$disk->exists(
                $path
            )
        ) {
            abort(
                404,
                'Evidence file not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve MIME Type
        |--------------------------------------------------------------------------
        */

        $mimeType =
            trim(
                (string)
                $excuseLetter
                    ->evidence_mime_type
            );


        if (
            $mimeType === ''
        ) {
            try {

                $mimeType =
                    (string)
                    $disk->mimeType(
                        $path
                    );

            } catch (
                Throwable
            ) {

                $mimeType =
                    'application/octet-stream';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve File Name
        |--------------------------------------------------------------------------
        */

        $absolutePath =
            $disk->path(
                $path
            );


        $fileName =
            $this->evidenceFileName(
                $excuseLetter,
                $path
            );


        /*
        |--------------------------------------------------------------------------
        | Mobile-Friendly Headers
        |--------------------------------------------------------------------------
        |
        | Content-Disposition INLINE is important so images render inside the
        | React Native Image component and inside the full-screen modal.
        |
        */

        $headers = [
            'Content-Type' =>
                $mimeType
                ?: 'application/octet-stream',

            'Content-Disposition' =>
                HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_INLINE,
                    $fileName
                ),

            'Cache-Control' =>
                'private, max-age=900',

            'X-Content-Type-Options' =>
                'nosniff',

            'Accept-Ranges' =>
                'bytes',
        ];


        /*
        |--------------------------------------------------------------------------
        | Content Length
        |--------------------------------------------------------------------------
        */

        try {

            $size =
                $disk->size(
                    $path
                );


            if (
                is_int(
                    $size
                )
                &&
                $size > 0
            ) {
                $headers[
                    'Content-Length'
                ] =
                    (string)
                    $size;
            }

        } catch (
            Throwable
        ) {
            /*
            |--------------------------------------------------------------------------
            | BinaryFileResponse can determine the size automatically.
            |--------------------------------------------------------------------------
            */
        }


        return response()->file(
            $absolutePath,
            $headers
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateExcuseLetter(
        Request $request,
        User $student,
        ?ExcuseLetter $letter = null
    ): array {
        return $request->validate([
            'component' => [
                'required',
                'string',

                Rule::in(
                    $this->allowedComponents(
                        $student
                    )
                ),
            ],


            'instructor_id' => [
                'nullable',
                'integer',
            ],


            'absence_date' => [
                'required',
                'date_format:Y-m-d',
            ],


            'reason' => [
                'required',
                'string',
                'min:2',
                'max:500',
            ],


            'explanation' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],


            'remove_evidence' => [
                'sometimes',
                'boolean',
            ],


            'evidence' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:'
                .
                self::MAX_EVIDENCE_KILOBYTES,
            ],
        ], [
            'component.required' =>
                'Please select your NSTP component.',

            'component.in' =>
                'The selected NSTP component is not available for your account.',

            'absence_date.required' =>
                'Please enter the date of absence.',

            'absence_date.date_format' =>
                'The date of absence must use YYYY-MM-DD format.',

            'reason.required' =>
                'Please enter your reason for absence.',

            'explanation.required' =>
                'Please explain why you were absent.',

            'evidence.mimes' =>
                'Evidence must be a JPG, PNG, WEBP, or PDF file.',

            'evidence.max' =>
                'Evidence must not be larger than 10 MB.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticated Student
    |--------------------------------------------------------------------------
    */

    private function authenticatedStudent(
        Request $request
    ): User {
        $student =
            $request->user();


        if (
            !$student instanceof User
            ||
            !$student->exists
            ||
            !$student->university_id
        ) {
            throw ValidationException::withMessages([
                'student' =>
                    'Your NSTP student account is not connected to a university.',
            ]);
        }


        $student->loadMissing(
            'university'
        );


        return $student;
    }


    /*
    |--------------------------------------------------------------------------
    | Allowed NSTP Components
    |--------------------------------------------------------------------------
    */

    private function allowedComponents(
        User $student
    ): array {
        $studentComponent =
            $this->normalizeComponent(
                $student->component
                ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | Student Already Has Assigned Component
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $studentComponent,
                ExcuseLetter::components(),
                true
            )
        ) {
            return [
                $studentComponent,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Otherwise Use University Components
        |--------------------------------------------------------------------------
        */

        $universityComponents =
            collect(
                $student
                    ->university
                    ?->components
                ?? []
            )
                ->map(
                    fn (
                        $component
                    ): string =>
                        $this->normalizeComponent(
                            $component
                        )
                )
                ->filter(
                    fn (
                        string $component
                    ): bool =>
                        in_array(
                            $component,
                            ExcuseLetter::components(),
                            true
                        )
                )
                ->unique()
                ->values()
                ->all();


        return count(
            $universityComponents
        ) > 0
            ? $universityComponents
            : ExcuseLetter::components();
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Student Component
    |--------------------------------------------------------------------------
    */

    private function validateStudentComponent(
        User $student,
        string $component
    ): string {
        $component =
            $this->normalizeComponent(
                $component
            );


        if (
            !in_array(
                $component,
                $this->allowedComponents(
                    $student
                ),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'component' =>
                    'You cannot submit an excuse letter for that NSTP component.',
            ]);
        }


        return $component;
    }


    /*
    |--------------------------------------------------------------------------
    | Available Instructors
    |--------------------------------------------------------------------------
    */

    private function availableInstructors(
        User $student,
        array $components
    ): Collection {
        return Instructor::query()
            ->where(
                'university_id',
                $student->university_id
            )
            ->whereIn(
                'component',
                $components
            )
            ->where(
                function (
                    Builder $query
                ): void {

                    $query
                        ->whereNull(
                            'status'
                        )
                        ->orWhereRaw(
                            'UPPER(TRIM(status)) = ?',
                            [
                                'ACTIVE',
                            ]
                        );
                }
            )
            ->orderBy(
                'component'
            )
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
                            trim(
                                (string) (
                                    $instructor
                                        ->full_name
                                    ?? ''
                                )
                            ),

                        'email' =>
                            $instructor->email,

                        'component' =>
                            $this->normalizeComponent(
                                $instructor->component
                                ?? ''
                            ),
                    ];
                }
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Instructor
    |--------------------------------------------------------------------------
    */

    private function resolveInstructor(
        User $student,
        string $component,
        ?int $instructorId
    ): ?Instructor {
        if (
            !$instructorId
        ) {
            return null;
        }


        $instructor =
            Instructor::query()
                ->whereKey(
                    $instructorId
                )
                ->where(
                    'university_id',
                    $student->university_id
                )
                ->where(
                    'component',
                    $component
                )
                ->first();


        if (
            !$instructor
        ) {
            throw ValidationException::withMessages([
                'instructor_id' =>
                    'The selected instructor does not belong to your university and NSTP component.',
            ]);
        }


        return $instructor;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Absence Date
    |--------------------------------------------------------------------------
    */

    private function validateAbsenceDate(
        string $absenceDate
    ): void {
        $date =
            CarbonImmutable::createFromFormat(
                'Y-m-d',
                $absenceDate,
                self::TIMEZONE
            )
                ->startOfDay();


        $today =
            CarbonImmutable::now(
                self::TIMEZONE
            )
                ->startOfDay();


        if (
            $date->greaterThan(
                $today
            )
        ) {
            throw ValidationException::withMessages([
                'absence_date' =>
                    'The absence date cannot be in the future.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Protection
    |--------------------------------------------------------------------------
    */

    private function ensureNoDuplicateLetter(
        User $student,
        string $component,
        string $absenceDate,
        ?ExcuseLetter $ignoreLetter = null
    ): void {
        $query =
            ExcuseLetter::query()
                ->where(
                    'user_id',
                    $student->id
                )
                ->where(
                    'university_id',
                    $student->university_id
                )
                ->where(
                    'component',
                    $component
                )
                ->whereDate(
                    'absence_date',
                    $absenceDate
                )
                ->whereIn(
                    'status',
                    [
                        ExcuseLetter::STATUS_PENDING,
                        ExcuseLetter::STATUS_APPROVED,
                    ]
                );


        if (
            $ignoreLetter
        ) {
            $query->where(
                'id',
                '!=',
                $ignoreLetter->id
            );
        }


        if (
            $query->exists()
        ) {
            throw ValidationException::withMessages([
                'absence_date' =>
                    'You already have a pending or approved excuse letter for this date and component.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership Protection
    |--------------------------------------------------------------------------
    */

    private function ensureOwnedByStudent(
        User $student,
        ExcuseLetter $letter
    ): void {
        if (
            (int) $letter->user_id !==
                (int) $student->id
            ||
            (int) $letter->university_id !==
                (int) $student->university_id
        ) {
            abort(
                404
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Pending Protection
    |--------------------------------------------------------------------------
    */

    private function ensurePending(
        ExcuseLetter $letter
    ): void {
        if (
            $letter->status !==
                ExcuseLetter::STATUS_PENDING
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'Only pending excuse letters can be edited or deleted.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Store Supporting Evidence
    |--------------------------------------------------------------------------
    */

    private function storeEvidence(
        User $student,
        $evidence
    ): string {
        return $evidence->store(
            'excuse-letters'
            .
            '/'
            .
            $student->university_id
            .
            '/'
            .
            $student->id,

            self::EVIDENCE_DISK
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Letter Payload
    |--------------------------------------------------------------------------
    */

    private function letterPayload(
        Request $request,
        User $student,
        ExcuseLetter $letter
    ): array {
        $status =
            strtoupper(
                trim(
                    (string)
                    $letter->status
                )
            );


        $reviewerName =
            null;


        if (
            $letter->reviewer
        ) {
            $reviewerName =
                trim(
                    (string) (
                        $letter
                            ->reviewer
                            ->full_name

                        ??

                        $letter
                            ->reviewer
                            ->name

                        ??

                        ''
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Secure Evidence URL
        |--------------------------------------------------------------------------
        */

        $evidenceUrl =
            $this->evidenceUrl(
                $request,
                $letter
            );


        $isImageEvidence =
            $this->isImageEvidence(
                $letter
            );


        return [
            'id' =>
                $letter->id,


            'section' =>
                $status ===
                    ExcuseLetter::STATUS_PENDING
                    ? 'recent'
                    : 'history',


            'user_id' =>
                $letter->user_id,


            'university_id' =>
                $letter->university_id,


            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            */

            'name' =>
                $student->full_name,


            'studentId' =>
                $this->studentIdNumber(
                    $student
                ),


            'student_id_number' =>
                $this->studentIdNumber(
                    $student
                ),


            'profile_photo_url' =>
                $this->publicFileUrl(
                    $request,
                    $student->profile_photo
                ),


            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'date' =>
                $letter
                    ->created_at
                    ?->timezone(
                        self::TIMEZONE
                    )
                    ?->format(
                        'F d, Y'
                    ),


            'created_at' =>
                $letter
                    ->created_at
                    ?->timezone(
                        self::TIMEZONE
                    )
                    ?->toIso8601String(),


            'absenceDate' =>
                $letter
                    ->absence_date
                    ?->format(
                        'Y-m-d'
                    ),


            'absence_date' =>
                $letter
                    ->absence_date
                    ?->format(
                        'Y-m-d'
                    ),


            /*
            |--------------------------------------------------------------------------
            | Excuse Letter
            |--------------------------------------------------------------------------
            */

            'component' =>
                $this->normalizeComponent(
                    $letter->component
                ),


            'instructor_id' =>
                $letter->instructor_id,


            'instructor_name' =>
                $letter
                    ->instructor
                    ?->full_name,


            'reason' =>
                $letter->reason,


            'explanation' =>
                $letter->explanation,


            'status' =>
                $status,


            'feedback' =>
                $letter->feedback,


            /*
            |--------------------------------------------------------------------------
            | Supporting Evidence
            |--------------------------------------------------------------------------
            |
            | Old keys are kept.
            |
            | New keys are added for the enlarged mobile preview.
            |
            */

            'attachmentName' =>
                $letter
                    ->evidence_original_name,


            'evidence_original_name' =>
                $letter
                    ->evidence_original_name,


            'evidence_mime_type' =>
                $letter
                    ->evidence_mime_type,


            'evidence_size' =>
                $letter
                    ->evidence_size,


            /*
            |--------------------------------------------------------------------------
            | New Evidence Helpers
            |--------------------------------------------------------------------------
            */

            'has_evidence' =>
                $evidenceUrl !== null,


            'evidence_is_image' =>
                $isImageEvidence,


            'evidence_preview_url' =>
                $evidenceUrl,


            'evidence_open_url' =>
                $evidenceUrl,


            /*
            |--------------------------------------------------------------------------
            | Existing Evidence Keys
            |--------------------------------------------------------------------------
            |
            | DO NOT REMOVE.
            |
            | Your current React Native screen may already use these.
            |
            */

            'evidenceUri' =>
                $evidenceUrl,


            'evidence_url' =>
                $evidenceUrl,


            /*
            |--------------------------------------------------------------------------
            | Review
            |--------------------------------------------------------------------------
            */

            'reviewer_name' =>
                $reviewerName,


            'reviewer_role' =>
                $letter
                    ->reviewer_role,


            'reviewed_at' =>
                $letter
                    ->reviewed_at
                    ?->timezone(
                        self::TIMEZONE
                    )
                    ?->toIso8601String(),


            /*
            |--------------------------------------------------------------------------
            | Permissions
            |--------------------------------------------------------------------------
            */

            'can_edit' =>
                $status ===
                    ExcuseLetter::STATUS_PENDING,


            'can_delete' =>
                $status ===
                    ExcuseLetter::STATUS_PENDING,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Student Payload
    |--------------------------------------------------------------------------
    */

    private function studentPayload(
        Request $request,
        User $student
    ): array {
        return [
            'id' =>
                $student->id,


            'student_id_number' =>
                $this->studentIdNumber(
                    $student
                ),


            'full_name' =>
                $student->full_name,


            'email' =>
                $student->email,


            'component' =>
                $this->normalizeComponent(
                    $student->component
                    ?? ''
                ),


            'status' =>
                $student
                    ->nstp_status_label,


            'profile_photo_url' =>
                $this->publicFileUrl(
                    $request,
                    $student->profile_photo
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University Payload
    |--------------------------------------------------------------------------
    */

    private function universityPayload(
        Request $request,
        User $student
    ): ?array {
        $university =
            $student->university;


        if (
            !$university
        ) {
            return null;
        }


        return [
            'id' =>
                $university->id,


            'name' =>
                $university->name,


            'acronym' =>
                $university->acronym,


            'academic_year' =>
                $university
                    ->academic_year,


            'semester' =>
                $university
                    ->semester,


            'components' =>
                $university
                    ->components,


            'status' =>
                $university
                    ->status,


            'logo_url' =>
                $this->publicFileUrl(
                    $request,
                    $university->logo
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Student ID Number
    |--------------------------------------------------------------------------
    */

    private function studentIdNumber(
        User $student
    ): string {
        foreach (
            [
                'student_id_number',
                'nstp_id_no',
                'student_id',
            ]
            as $field
        ) {
            $value =
                trim(
                    (string) (
                        $student->{$field}
                        ?? ''
                    )
                );


            if (
                $value !== ''
            ) {
                return $value;
            }
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
    | Public File URL
    |--------------------------------------------------------------------------
    |
    | Used for normal public files such as:
    |
    | - profile photo
    | - university logo
    |
    | Excuse-letter evidence uses the secure signed URL instead.
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
            $path === ''
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


        $storageUrl =
            Storage::disk(
                'public'
            )->url(
                $path
            );


        return rtrim(
            $request
                ->getSchemeAndHttpHost(),
            '/'
        )
        .
        '/'
        .
        ltrim(
            $storageUrl,
            '/'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Evidence File Name
    |--------------------------------------------------------------------------
    |
    | Keeps the real uploaded file name when opening the evidence.
    |
    */

    private function evidenceFileName(
        ExcuseLetter $letter,
        string $path
    ): string {
        $originalName =
            trim(
                (string)
                $letter
                    ->evidence_original_name
            );


        if (
            $originalName !== ''
        ) {
            return basename(
                str_replace(
                    '\\',
                    '/',
                    $originalName
                )
            );
        }


        $fallback =
            basename(
                str_replace(
                    '\\',
                    '/',
                    $path
                )
            );


        return $fallback !== ''
            ? $fallback
            : 'supporting-evidence';
    }


    /*
    |--------------------------------------------------------------------------
    | Detect Image Evidence
    |--------------------------------------------------------------------------
    |
    | React Native can use this field to decide whether to show:
    |
    | - full-screen image preview
    | - document/PDF opener
    |
    */

    private function isImageEvidence(
        ExcuseLetter $letter
    ): bool {
        $mimeType =
            strtolower(
                trim(
                    (string)
                    $letter
                        ->evidence_mime_type
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Check MIME Type
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $mimeType,
                'image/'
            )
        ) {
            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback to Extension
        |--------------------------------------------------------------------------
        */

        $fileName =
            strtolower(
                trim(
                    (string)
                    $letter
                        ->evidence_original_name
                )
            );


        if (
            $fileName === ''
        ) {
            return false;
        }


        $extension =
            strtolower(
                pathinfo(
                    $fileName,
                    PATHINFO_EXTENSION
                )
            );


        return in_array(
            $extension,
            [
                'jpg',
                'jpeg',
                'png',
                'webp',
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Secure Evidence URL
    |--------------------------------------------------------------------------
    |
    | Creates:
    |
    | /api/mobile/excuse-letters/{id}/evidence
    | ?expires=...
    | &signature=...
    |
    */

    private function evidenceUrl(
        Request $request,
        ExcuseLetter $letter
    ): ?string {
        if (
            !$letter->evidence_path
        ) {
            return null;
        }


        $expires =
            CarbonImmutable::now(
                self::TIMEZONE
            )
                ->addHours(
                    self::EVIDENCE_URL_LIFETIME_HOURS
                )
                ->timestamp;


        $signature =
            $this->evidenceSignature(
                $letter->id,
                $expires
            );


        return rtrim(
            $request
                ->getSchemeAndHttpHost(),
            '/'
        )
        .
        '/api/mobile/excuse-letters/'
        .
        $letter->id
        .
        '/evidence?expires='
        .
        $expires
        .
        '&signature='
        .
        $signature;
    }


    /*
    |--------------------------------------------------------------------------
    | Evidence Signature
    |--------------------------------------------------------------------------
    */

    private function evidenceSignature(
        int $letterId,
        int $expires
    ): string {
        return hash_hmac(
            'sha256',

            $letterId
            .
            '|'
            .
            $expires,

            (string)
            config(
                'app.key'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Component
    |--------------------------------------------------------------------------
    */

    private function normalizeComponent(
        ?string $component
    ): string {
        return strtoupper(
            trim(
                (string)
                $component
            )
        );
    }
}