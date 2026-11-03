<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class UniversityAdministrator extends Authenticatable
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
    | University Administrator roles must use:
    |
    | guard_name = university_admin
    |
    */

    protected string $guard_name =
        'university_admin';


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
        | Name
        |--------------------------------------------------------------------------
        */

        'first_name',

        'middle_name',

        'last_name',


        /*
        |--------------------------------------------------------------------------
        | Contact Information
        |--------------------------------------------------------------------------
        */

        'email',

        'phone',


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        'username',

        'password',


        /*
        |--------------------------------------------------------------------------
        | Temporary Password
        |--------------------------------------------------------------------------
        |
        | Used for generated University Administrator credentials.
        |
        | temporary_password is encrypted in the database.
        |
        */

        'temporary_password',


        /*
        |--------------------------------------------------------------------------
        | Password Change Status
        |--------------------------------------------------------------------------
        */

        'must_change_password',


        /*
        |--------------------------------------------------------------------------
        | Profile Photo
        |--------------------------------------------------------------------------
        */

        'photo',
    ];


    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    |
    | These values will not be exposed when the model is converted
    | to an array or JSON response.
    |
    */

    protected $hidden = [

        'password',

        'temporary_password',

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
            | Login Password
            |--------------------------------------------------------------------------
            |
            | Automatically hashed when assigned.
            |
            */

            'password' =>
                'hashed',


            /*
            |--------------------------------------------------------------------------
            | Temporary Password
            |--------------------------------------------------------------------------
            |
            | Encrypted so the original generated temporary password can be
            | retrieved for the credential email/PDF until it is changed.
            |
            */

            'temporary_password' =>
                'encrypted',


            /*
            |--------------------------------------------------------------------------
            | Password Change Flag
            |--------------------------------------------------------------------------
            */

            'must_change_password' =>
                'boolean',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University Relationship
    |--------------------------------------------------------------------------
    |
    | A University Administrator belongs to one university.
    |
    | Example:
    |
    | $administrator->university;
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
    | Reviewed Reports Relationship
    |--------------------------------------------------------------------------
    |
    | Reports use:
    |
    | reviewed_by_type
    | reviewed_by_id
    |
    | Therefore this relationship uses:
    |
    | reviewed_by
    |
    | Example:
    |
    | $administrator->reviewedReports;
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
    | Reviewed Excuse Letters Relationship
    |--------------------------------------------------------------------------
    |
    | Excuse letters use:
    |
    | reviewer_type
    | reviewer_id
    |
    | Therefore this relationship uses:
    |
    | reviewer
    |
    | Example:
    |
    | $administrator->reviewedExcuseLetters;
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
    | Pending Excuse Letters Reviewed By Administrator
    |--------------------------------------------------------------------------
    |
    | This returns excuse letters currently marked PENDING where this
    | University Administrator is recorded as the reviewer.
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
    | Approved Excuse Letters Reviewed By Administrator
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
    | Rejected Excuse Letters Reviewed By Administrator
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
    | Full Name Accessor
    |--------------------------------------------------------------------------
    |
    | Allows:
    |
    | $administrator->full_name
    |
    */

    public function getFullNameAttribute(): string
    {
        return trim(
            collect(
                [
                    $this->first_name,

                    $this->middle_name,

                    $this->last_name,
                ]
            )
                ->filter()
                ->implode(
                    ' '
                )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Photo Accessor
    |--------------------------------------------------------------------------
    |
    | Instructor and Coordinator use:
    |
    | profile_photo
    |
    | University Administrator database uses:
    |
    | photo
    |
    | This allows shared layouts to use:
    |
    | $administrator->profile_photo
    |
    */

    public function getProfilePhotoAttribute(): ?string
    {
        return $this->photo;
    }


    /*
    |--------------------------------------------------------------------------
    | University Admin Role Helper
    |--------------------------------------------------------------------------
    */

    public function isUniversityAdmin(): bool
    {
        return $this->hasRole(
            'university-admin'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Can Manage Excuse Letters
    |--------------------------------------------------------------------------
    |
    | The ExcuseLetter model defines University Admin as one of the
    | authorized management roles.
    |
    */

    public function canManageExcuseLetters(): bool
    {
        return ExcuseLetter::roleCanManage(
            ExcuseLetter::ROLE_UNIVERSITY_ADMIN
        );
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
    | Approved Excuse Letter Count
    |--------------------------------------------------------------------------
    */

    public function approvedExcuseLetterCount(): int
    {
        return $this
            ->reviewedExcuseLetters()
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
            ->reviewedExcuseLetters()
            ->where(
                'status',
                ExcuseLetter::STATUS_REJECTED
            )
            ->count();
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
    | Temporary Password Helper
    |--------------------------------------------------------------------------
    */

    public function isUsingTemporaryPassword(): bool
    {
        return (
            $this->must_change_password ===
            true

            &&

            !empty(
                $this->temporary_password
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Password Changed Helper
    |--------------------------------------------------------------------------
    */

    public function hasChangedPassword(): bool
    {
        return (
            $this->must_change_password ===
            false
        );
    }
}