<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExcuseLetter extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Allowed Management Roles
    |--------------------------------------------------------------------------
    |
    | These roles can review an excuse letter, leave feedback,
    | approve it, or reject it.
    |
    */

    public const ROLE_UNIVERSITY_ADMIN =
        'university-admin';

    public const ROLE_INSTRUCTOR =
        'instructor';

    public const ROLE_COORDINATOR_ATTENDANCE =
        'coordinator-attendance';


    /*
    |--------------------------------------------------------------------------
    | View / Non-Management Roles
    |--------------------------------------------------------------------------
    */

    public const ROLE_COORDINATOR_ANNOUNCEMENT =
        'coordinator-announcement';

    public const ROLE_COORDINATOR_SCHEDULE =
        'coordinator-schedule';

    public const ROLE_STUDENT =
        'student';

    public const ROLE_SUPER_ADMIN =
        'super-admin';


    /*
    |--------------------------------------------------------------------------
    | NSTP Components
    |--------------------------------------------------------------------------
    */

    public const COMPONENT_CWTS =
        'CWTS';

    public const COMPONENT_LTS =
        'LTS';

    public const COMPONENT_ROTC =
        'ROTC';


    /*
    |--------------------------------------------------------------------------
    | Excuse Letter Statuses
    |--------------------------------------------------------------------------
    |
    | Workflow:
    |
    | PENDING
    |     ↓
    | APPROVED
    |
    | or
    |
    | PENDING
    |     ↓
    | REJECTED
    |
    */

    public const STATUS_PENDING =
        'PENDING';

    public const STATUS_APPROVED =
        'APPROVED';

    public const STATUS_REJECTED =
        'REJECTED';


    /*
    |--------------------------------------------------------------------------
    | Fillable Attributes
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        'university_id',


        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        'user_id',


        /*
        |--------------------------------------------------------------------------
        | Assigned Instructor
        |--------------------------------------------------------------------------
        |
        | This can be nullable.
        |
        | It allows the excuse letter to be associated with the student's
        | assigned instructor when available.
        |
        */

        'instructor_id',


        /*
        |--------------------------------------------------------------------------
        | NSTP Component
        |--------------------------------------------------------------------------
        |
        | CWTS
        | LTS
        | ROTC
        |
        */

        'component',


        /*
        |--------------------------------------------------------------------------
        | Absence Information
        |--------------------------------------------------------------------------
        */

        'absence_date',

        'reason',

        'explanation',


        /*
        |--------------------------------------------------------------------------
        | Supporting Evidence
        |--------------------------------------------------------------------------
        |
        | Optional attachment uploaded by the student.
        |
        */

        'evidence_path',

        'evidence_original_name',

        'evidence_mime_type',

        'evidence_size',


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        |
        | PENDING
        | APPROVED
        | REJECTED
        |
        */

        'status',


        /*
        |--------------------------------------------------------------------------
        | Reviewer Feedback
        |--------------------------------------------------------------------------
        |
        | This is the message the student will see.
        |
        | Example:
        |
        | "Medical certificate verified. Absence excused."
        |
        | or:
        |
        | "No supporting document attached. Please refile..."
        |
        */

        'feedback',


        /*
        |--------------------------------------------------------------------------
        | Reviewer
        |--------------------------------------------------------------------------
        |
        | Reviewer may be:
        |
        | UniversityAdministrator
        | Instructor
        | Coordinator
        |
        | Because Coordinator can have multiple Spatie roles, reviewer_role
        | is also stored separately.
        |
        */

        'reviewer_id',

        'reviewer_type',

        'reviewer_role',


        /*
        |--------------------------------------------------------------------------
        | Reviewed Date
        |--------------------------------------------------------------------------
        */

        'reviewed_at',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'absence_date' =>
                'date',

            'evidence_size' =>
                'integer',

            'reviewed_at' =>
                'datetime',

            'created_at' =>
                'datetime',

            'updated_at' =>
                'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University Relationship
    |--------------------------------------------------------------------------
    */

    public function university(): BelongsTo
    {
        return $this->belongsTo(
            University::class,
            'university_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Student Relationship
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | $excuseLetter->student
    |
    */

    public function student(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Instructor Relationship
    |--------------------------------------------------------------------------
    */

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(
            Instructor::class,
            'instructor_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewer Relationship
    |--------------------------------------------------------------------------
    |
    | Reviewer can be:
    |
    | UniversityAdministrator
    | Instructor
    | Coordinator
    |
    */

    public function reviewer(): MorphTo
    {
        return $this->morphTo(
            'reviewer'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Allowed Management Roles
    |--------------------------------------------------------------------------
    */

    public static function allowedRoles(): array
    {
        return [
            self::ROLE_UNIVERSITY_ADMIN,

            self::ROLE_INSTRUCTOR,

            self::ROLE_COORDINATOR_ATTENDANCE,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Can Role Manage
    |--------------------------------------------------------------------------
    */

    public static function roleCanManage(
        ?string $role
    ): bool {
        if (
            !$role
        ) {
            return false;
        }


        return in_array(
            $role,
            self::allowedRoles(),
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    */

    public static function components(): array
    {
        return [
            self::COMPONENT_CWTS,

            self::COMPONENT_LTS,

            self::COMPONENT_ROTC,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,

            self::STATUS_APPROVED,

            self::STATUS_REJECTED,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewer Role Helpers
    |--------------------------------------------------------------------------
    */

    public function reviewedByUniversityAdmin(): bool
    {
        return (
            $this->reviewer_role ===
            self::ROLE_UNIVERSITY_ADMIN
        );
    }


    public function reviewedByInstructor(): bool
    {
        return (
            $this->reviewer_role ===
            self::ROLE_INSTRUCTOR
        );
    }


    public function reviewedByAttendanceCoordinator(): bool
    {
        return (
            $this->reviewer_role ===
            self::ROLE_COORDINATOR_ATTENDANCE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return (
            $this->status ===
            self::STATUS_PENDING
        );
    }


    public function isApproved(): bool
    {
        return (
            $this->status ===
            self::STATUS_APPROVED
        );
    }


    public function isRejected(): bool
    {
        return (
            $this->status ===
            self::STATUS_REJECTED
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Evidence Helper
    |--------------------------------------------------------------------------
    */

    public function hasEvidence(): bool
    {
        return (
            !empty(
                $this->evidence_path
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Has Feedback
    |--------------------------------------------------------------------------
    */

    public function hasFeedback(): bool
    {
        return (
            !empty(
                trim(
                    (string) $this->feedback
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: University
    |--------------------------------------------------------------------------
    */

    public function scopeForUniversity(
        Builder $query,
        int $universityId
    ): Builder {
        return $query->where(
            'university_id',
            $universityId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Student
    |--------------------------------------------------------------------------
    */

    public function scopeForStudent(
        Builder $query,
        int $studentId
    ): Builder {
        return $query->where(
            'user_id',
            $studentId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Component
    |--------------------------------------------------------------------------
    */

    public function scopeForComponent(
        Builder $query,
        string $component
    ): Builder {
        return $query->where(
            'component',
            strtoupper(
                trim(
                    $component
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Pending
    |--------------------------------------------------------------------------
    */

    public function scopePending(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Approved
    |--------------------------------------------------------------------------
    */

    public function scopeApproved(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_APPROVED
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Rejected
    |--------------------------------------------------------------------------
    */

    public function scopeRejected(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_REJECTED
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Recent
    |--------------------------------------------------------------------------
    */

    public function scopeRecent(
        Builder $query
    ): Builder {
        return $query
            ->orderByDesc(
                'absence_date'
            )
            ->orderByDesc(
                'created_at'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Old
    |--------------------------------------------------------------------------
    */

    public function scopeOld(
        Builder $query
    ): Builder {
        return $query
            ->orderBy(
                'absence_date'
            )
            ->orderBy(
                'created_at'
            );
    }
}