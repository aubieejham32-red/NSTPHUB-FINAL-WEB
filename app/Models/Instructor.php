<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Instructor extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;


    /*
    |--------------------------------------------------------------------------
    | Spatie Guard
    |--------------------------------------------------------------------------
    |
    | Instructor roles must use:
    |
    | guard_name = instructor
    |
    */

    protected string $guard_name =
        'instructor';


    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Fields
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
        | Instructor Information
        |--------------------------------------------------------------------------
        */

        'full_name',

        'username',

        'email',

        'phone_number',

        'profile_photo',


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        'password',


        /*
        |--------------------------------------------------------------------------
        | NSTP
        |--------------------------------------------------------------------------
        */

        'component',

        'access_code',

        'status',
    ];


    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    */

    protected $hidden = [

        'password',

        'remember_token',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Password
            |--------------------------------------------------------------------------
            */

            'password' =>
                'hashed',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University Relationship
    |--------------------------------------------------------------------------
    |
    | One instructor belongs to one university.
    |
    | Example:
    |
    | $instructor->university;
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
    | Managed NSTP Components
    |--------------------------------------------------------------------------
    |
    | An instructor may manage one or more NSTP components.
    | The legacy `component` column remains the default / primary component
    | for backward compatibility with existing code.
    |
    */

    public function managedComponents(): HasMany
    {
        return $this->hasMany(
            InstructorComponent::class,
            'instructor_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Managed Component Codes
    |--------------------------------------------------------------------------
    |
    | Returns a normalized array such as:
    |
    | ['LTS', 'CWTS']
    |
    | If there are no rows yet in instructor_components, this falls back to
    | the existing `component` column so current instructors keep working.
    |
    */

    public function componentCodes(): array
    {
        $components = $this
            ->managedComponents()
            ->pluck('component')
            ->map(
                fn ($component) => strtoupper(
                    trim((string) $component)
                )
            )
            ->filter(
                fn ($component) => in_array(
                    $component,
                    ['LTS', 'CWTS', 'ROTC'],
                    true
                )
            )
            ->unique()
            ->values()
            ->all();

        if (
            empty($components)
            && filled($this->component)
        ) {
            $defaultComponent = strtoupper(
                trim((string) $this->component)
            );

            if (
                in_array(
                    $defaultComponent,
                    ['LTS', 'CWTS', 'ROTC'],
                    true
                )
            ) {
                $components[] = $defaultComponent;
            }
        }

        return $components;
    }


    /*
    |--------------------------------------------------------------------------
    | Manages Component
    |--------------------------------------------------------------------------
    */

    public function managesComponent(string $component): bool
    {
        $component = strtoupper(
            trim($component)
        );

        return in_array(
            $component,
            $this->componentCodes(),
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Primary / Default Component
    |--------------------------------------------------------------------------
    |
    | Existing parts of the project still expect `$instructor->component`.
    | This helper makes the default choice explicit for new code.
    |
    */

    public function primaryComponent(): string
    {
        $legacy = strtoupper(
            trim((string) ($this->component ?? ''))
        );

        if (
            in_array(
                $legacy,
                ['LTS', 'CWTS', 'ROTC'],
                true
            )
        ) {
            return $legacy;
        }

        return $this->componentCodes()[0] ?? '';
    }


    /*
    |--------------------------------------------------------------------------
    | Assigned Reports
    |--------------------------------------------------------------------------
    |
    | Reports where this instructor is selected as the student's instructor.
    |
    | Database:
    |
    | reports.instructor_id
    |
    | Example:
    |
    | $instructor->reports;
    |
    */

    public function reports(): HasMany
    {
        return $this->hasMany(
            Report::class,
            'instructor_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewed Reports
    |--------------------------------------------------------------------------
    |
    | Reports use the polymorphic columns:
    |
    | reviewed_by_type
    | reviewed_by_id
    |
    | This relationship returns reports actually reviewed by this instructor.
    |
    | Example:
    |
    | $instructor->reviewedReports;
    |
    */

    public function reviewedReports(): MorphMany
    {
        return $this->morphMany(
            Report::class,
            'reviewed_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Assigned Excuse Letters
    |--------------------------------------------------------------------------
    |
    | Excuse letters where this instructor is assigned to the student/request.
    |
    | Database:
    |
    | excuse_letters.instructor_id
    |
    | Example:
    |
    | $instructor->excuseLetters;
    |
    */

    public function excuseLetters(): HasMany
    {
        return $this->hasMany(
            ExcuseLetter::class,
            'instructor_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewed Excuse Letters
    |--------------------------------------------------------------------------
    |
    | Excuse letters use:
    |
    | reviewer_type
    | reviewer_id
    |
    | Example:
    |
    | $instructor->reviewedExcuseLetters;
    |
    */

    public function reviewedExcuseLetters(): MorphMany
    {
        return $this->morphMany(
            ExcuseLetter::class,
            'reviewer'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Pending Assigned Excuse Letters
    |--------------------------------------------------------------------------
    |
    | Excuse letters assigned to this instructor that are waiting for review.
    |
    */

    public function pendingExcuseLetters(): HasMany
    {
        return $this->hasMany(
            ExcuseLetter::class,
            'instructor_id',
            'id'
        )
            ->where(
                'status',
                ExcuseLetter::STATUS_PENDING
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approved Assigned Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function approvedExcuseLetters(): HasMany
    {
        return $this->hasMany(
            ExcuseLetter::class,
            'instructor_id',
            'id'
        )
            ->where(
                'status',
                ExcuseLetter::STATUS_APPROVED
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Rejected Assigned Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function rejectedExcuseLetters(): HasMany
    {
        return $this->hasMany(
            ExcuseLetter::class,
            'instructor_id',
            'id'
        )
            ->where(
                'status',
                ExcuseLetter::STATUS_REJECTED
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Pending Reviewed Excuse Letters
    |--------------------------------------------------------------------------
    |
    | These are excuse letters where this instructor is already recorded
    | as the polymorphic reviewer.
    |
    */

    public function pendingReviewedExcuseLetters(): MorphMany
    {
        return $this
            ->morphMany(
                ExcuseLetter::class,
                'reviewer'
            )
            ->where(
                'status',
                ExcuseLetter::STATUS_PENDING
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approved Reviewed Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function approvedReviewedExcuseLetters(): MorphMany
    {
        return $this
            ->morphMany(
                ExcuseLetter::class,
                'reviewer'
            )
            ->where(
                'status',
                ExcuseLetter::STATUS_APPROVED
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Rejected Reviewed Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function rejectedReviewedExcuseLetters(): MorphMany
    {
        return $this
            ->morphMany(
                ExcuseLetter::class,
                'reviewer'
            )
            ->where(
                'status',
                ExcuseLetter::STATUS_REJECTED
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Instructor Role Helper
    |--------------------------------------------------------------------------
    */

    public function isInstructor(): bool
    {
        return $this->hasRole(
            'instructor'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Can Manage Excuse Letters
    |--------------------------------------------------------------------------
    |
    | Instructor is included in ExcuseLetter::allowedRoles().
    |
    */

    public function canManageExcuseLetters(): bool
    {
        return ExcuseLetter::roleCanManage(
            ExcuseLetter::ROLE_INSTRUCTOR
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Has Assigned Reports
    |--------------------------------------------------------------------------
    */

    public function hasReports(): bool
    {
        return $this
            ->reports()
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Has Reviewed Reports
    |--------------------------------------------------------------------------
    */

    public function hasReviewedReports(): bool
    {
        return $this
            ->reviewedReports()
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Assigned Report Count
    |--------------------------------------------------------------------------
    */

    public function reportCount(): int
    {
        return $this
            ->reports()
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewed Report Count
    |--------------------------------------------------------------------------
    */

    public function reviewedReportCount(): int
    {
        return $this
            ->reviewedReports()
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Has Assigned Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function hasExcuseLetters(): bool
    {
        return $this
            ->excuseLetters()
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Has Pending Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function hasPendingExcuseLetters(): bool
    {
        return $this
            ->excuseLetters()
            ->where(
                'status',
                ExcuseLetter::STATUS_PENDING
            )
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Has Approved Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function hasApprovedExcuseLetters(): bool
    {
        return $this
            ->excuseLetters()
            ->where(
                'status',
                ExcuseLetter::STATUS_APPROVED
            )
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Has Rejected Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function hasRejectedExcuseLetters(): bool
    {
        return $this
            ->excuseLetters()
            ->where(
                'status',
                ExcuseLetter::STATUS_REJECTED
            )
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Has Reviewed Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function hasReviewedExcuseLetters(): bool
    {
        return $this
            ->reviewedExcuseLetters()
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Excuse Letter Count
    |--------------------------------------------------------------------------
    */

    public function excuseLetterCount(): int
    {
        return $this
            ->excuseLetters()
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Pending Excuse Letter Count
    |--------------------------------------------------------------------------
    */

    public function pendingExcuseLetterCount(): int
    {
        return $this
            ->excuseLetters()
            ->where(
                'status',
                ExcuseLetter::STATUS_PENDING
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Approved Excuse Letter Count
    |--------------------------------------------------------------------------
    */

    public function approvedExcuseLetterCount(): int
    {
        return $this
            ->excuseLetters()
            ->where(
                'status',
                ExcuseLetter::STATUS_APPROVED
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Rejected Excuse Letter Count
    |--------------------------------------------------------------------------
    */

    public function rejectedExcuseLetterCount(): int
    {
        return $this
            ->excuseLetters()
            ->where(
                'status',
                ExcuseLetter::STATUS_REJECTED
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewed Excuse Letter Count
    |--------------------------------------------------------------------------
    */

    public function reviewedExcuseLetterCount(): int
    {
        return $this
            ->reviewedExcuseLetters()
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Latest Assigned Excuse Letter
    |--------------------------------------------------------------------------
    */

    public function latestExcuseLetter(): ?ExcuseLetter
    {
        return $this
            ->excuseLetters()
            ->latest(
                'created_at'
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Latest Reviewed Excuse Letter
    |--------------------------------------------------------------------------
    */

    public function latestReviewedExcuseLetter(): ?ExcuseLetter
    {
        return $this
            ->reviewedExcuseLetters()
            ->latest(
                'reviewed_at'
            )
            ->first();
    }
}