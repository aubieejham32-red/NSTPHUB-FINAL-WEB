<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Report Statuses
    |--------------------------------------------------------------------------
    |
    | Student report workflow:
    |
    | PENDING
    |      ↓
    | IN REVIEW
    |      ↓
    | RESOLVED
    |
    */

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_IN_REVIEW = 'IN REVIEW';

    public const STATUS_RESOLVED = 'RESOLVED';


    /*
    |--------------------------------------------------------------------------
    | NSTP Components
    |--------------------------------------------------------------------------
    */

    public const COMPONENT_ROTC = 'ROTC';

    public const COMPONENT_LTS = 'LTS';

    public const COMPONENT_CWTS = 'CWTS';


    /*
    |--------------------------------------------------------------------------
    | Fillable Attributes
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        'user_id',


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        'university_id',


        /*
        |--------------------------------------------------------------------------
        | Instructor
        |--------------------------------------------------------------------------
        |
        | The student selects the instructor related to the concern.
        |
        */

        'instructor_id',


        /*
        |--------------------------------------------------------------------------
        | NSTP Component
        |--------------------------------------------------------------------------
        |
        | ROTC
        | LTS
        | CWTS
        |
        */

        'component',


        /*
        |--------------------------------------------------------------------------
        | Date Of Occurrence
        |--------------------------------------------------------------------------
        */

        'occurrence_date',


        /*
        |--------------------------------------------------------------------------
        | Concern
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Attendance Concern
        |
        */

        'subject',

        'description',


        /*
        |--------------------------------------------------------------------------
        | Optional Proof / Attachment
        |--------------------------------------------------------------------------
        */

        'attachment_path',

        'attachment_original_name',

        'attachment_mime_type',

        'attachment_size',


        /*
        |--------------------------------------------------------------------------
        | Report Status
        |--------------------------------------------------------------------------
        */

        'status',


        /*
        |--------------------------------------------------------------------------
        | NSTP Feedback
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | "Thank you for submitting your concern. Your report has been
        | reviewed and forwarded to your instructor..."
        |
        */

        'feedback',


        /*
        |--------------------------------------------------------------------------
        | Reviewer
        |--------------------------------------------------------------------------
        |
        | Polymorphic reviewer.
        |
        | This lets a report be reviewed by different NSTP account types
        | without forcing the reviewer to be only one model.
        |
        | Example:
        |
        | UniversityAdministrator
        | Instructor
        | Coordinator
        |
        */

        'reviewed_by_type',

        'reviewed_by_id',


        /*
        |--------------------------------------------------------------------------
        | Review Dates
        |--------------------------------------------------------------------------
        */

        'reviewed_at',

        'resolved_at',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'occurrence_date' =>
                'date',

            'attachment_size' =>
                'integer',

            'reviewed_at' =>
                'datetime',

            'resolved_at' =>
                'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Student Relationship
    |--------------------------------------------------------------------------
    |
    | $report->student
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
    | University Relationship
    |--------------------------------------------------------------------------
    |
    | $report->university
    |
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
    | Instructor Relationship
    |--------------------------------------------------------------------------
    |
    | $report->instructor
    |
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
    | The reviewer can be:
    |
    | UniversityAdministrator
    | Instructor
    | Coordinator
    |
    | Example:
    |
    | $report->reviewedBy
    |
    */

    public function reviewedBy(): MorphTo
    {
        return $this->morphTo(
            'reviewed_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Available Statuses
    |--------------------------------------------------------------------------
    */

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_REVIEW,
            self::STATUS_RESOLVED,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Available Components
    |--------------------------------------------------------------------------
    */

    public static function components(): array
    {
        return [
            self::COMPONENT_ROTC,
            self::COMPONENT_LTS,
            self::COMPONENT_CWTS,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Pending
    |--------------------------------------------------------------------------
    |
    | Report::pending()->get();
    |
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
    | Scope: In Review
    |--------------------------------------------------------------------------
    */

    public function scopeInReview(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_IN_REVIEW
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Resolved
    |--------------------------------------------------------------------------
    */

    public function scopeResolved(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_RESOLVED
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
    | Scope: Component
    |--------------------------------------------------------------------------
    */

    public function scopeComponent(
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
    | Pending Helper
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return (
            $this->status ===
            self::STATUS_PENDING
        );
    }


    /*
    |--------------------------------------------------------------------------
    | In Review Helper
    |--------------------------------------------------------------------------
    */

    public function isInReview(): bool
    {
        return (
            $this->status ===
            self::STATUS_IN_REVIEW
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolved Helper
    |--------------------------------------------------------------------------
    */

    public function isResolved(): bool
    {
        return (
            $this->status ===
            self::STATUS_RESOLVED
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark As In Review
    |--------------------------------------------------------------------------
    */

    public function markAsInReview(
        Model $reviewer,
        ?string $feedback = null
    ): void {
        $this->status =
            self::STATUS_IN_REVIEW;


        $this->reviewed_by_type =
            $reviewer->getMorphClass();


        $this->reviewed_by_id =
            $reviewer->getKey();


        $this->reviewed_at =
            now();


        if (
            $feedback !==
            null
        ) {
            $this->feedback =
                trim(
                    $feedback
                );
        }


        $this->save();
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Report
    |--------------------------------------------------------------------------
    */

    public function markAsResolved(
        Model $reviewer,
        ?string $feedback = null
    ): void {
        $this->status =
            self::STATUS_RESOLVED;


        $this->reviewed_by_type =
            $reviewer->getMorphClass();


        $this->reviewed_by_id =
            $reviewer->getKey();


        if (
            $this->reviewed_at ===
            null
        ) {
            $this->reviewed_at =
                now();
        }


        $this->resolved_at =
            now();


        if (
            $feedback !==
            null
        ) {
            $this->feedback =
                trim(
                    $feedback
                );
        }


        $this->save();
    }


    /*
    |--------------------------------------------------------------------------
    | Attachment Helper
    |--------------------------------------------------------------------------
    */

    public function hasAttachment(): bool
    {
        return (
            !empty(
                $this->attachment_path
            )
        );
    }
}