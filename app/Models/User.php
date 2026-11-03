<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;


    /*
    |--------------------------------------------------------------------------
    | Spatie Permission Guard
    |--------------------------------------------------------------------------
    |
    | Student permission metadata uses the web guard.
    |
    */

    protected string $guard_name = 'web';


    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Attributes
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Account Information
        |--------------------------------------------------------------------------
        */

        'name',

        'username',

        'email',

        'password',


        /*
        |--------------------------------------------------------------------------
        | University
        |--------------------------------------------------------------------------
        */

        'university_id',


        /*
        |--------------------------------------------------------------------------
        | NSTP Registration Details
        |--------------------------------------------------------------------------
        |
        | component:
        |
        | ROTC
        | LTS
        | CWTS
        |
        | nstp_status:
        |
        | ACTIVE
        | WARNING FOR DROPOUT
        | DROPOUT
        |
        */

        'subject',

        'component',

        'term',

        'nstp_status',


        /*
        |--------------------------------------------------------------------------
        | Student Name
        |--------------------------------------------------------------------------
        */

        'surname',

        'first_name',

        'middle_name',


        /*
        |--------------------------------------------------------------------------
        | Academic Information
        |--------------------------------------------------------------------------
        */

        'course',

        'year_level',

        'section',


        /*
        |--------------------------------------------------------------------------
        | Personal Information
        |--------------------------------------------------------------------------
        */

        'gender',

        'birth_date',

        'contact_number',


        /*
        |--------------------------------------------------------------------------
        | Address
        |--------------------------------------------------------------------------
        */

        'city_address',

        'municipality',

        'province',


        /*
        |--------------------------------------------------------------------------
        | Parent / Guardian
        |--------------------------------------------------------------------------
        */

        'guardian_name',

        'guardian_address',

        'guardian_contact_number',


        /*
        |--------------------------------------------------------------------------
        | Student Files
        |--------------------------------------------------------------------------
        */

        'profile_photo',

        'signature_path',
    ];


    /*
    |--------------------------------------------------------------------------
    | Hidden Attributes
    |--------------------------------------------------------------------------
    */

    protected $hidden = [

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        'password',

        'two_factor_secret',

        'two_factor_recovery_codes',

        'remember_token',


        /*
        |--------------------------------------------------------------------------
        | QR Token
        |--------------------------------------------------------------------------
        |
        | QR tokens must not be exposed through normal User JSON responses.
        |
        */

        'qr_token',
    ];


    /*
    |--------------------------------------------------------------------------
    | Attribute Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Email Verification
            |--------------------------------------------------------------------------
            */

            'email_verified_at' =>
                'datetime',


            /*
            |--------------------------------------------------------------------------
            | Birth Date
            |--------------------------------------------------------------------------
            */

            'birth_date' =>
                'date',


            /*
            |--------------------------------------------------------------------------
            | Password
            |--------------------------------------------------------------------------
            */

            'password' =>
                'hashed',


            /*
            |--------------------------------------------------------------------------
            | Two-Factor Authentication
            |--------------------------------------------------------------------------
            */

            'two_factor_confirmed_at' =>
                'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University Relationship
    |--------------------------------------------------------------------------
    |
    | A student belongs to one university.
    |
    | Example:
    |
    | $student->university;
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
    | Attendance Relationship
    |--------------------------------------------------------------------------
    |
    | One NSTP student can have many attendance records.
    |
    | Example:
    |
    | $student->attendances;
    |
    | $student
    |     ->attendances()
    |     ->where('component', 'ROTC')
    |     ->get();
    |
    */

    public function attendances(): HasMany
    {
        return $this->hasMany(
            Attendance::class,
            'user_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Report Relationship
    |--------------------------------------------------------------------------
    |
    | One NSTP student can submit many reports.
    |
    | Example:
    |
    | $student->reports;
    |
    */

    public function reports(): HasMany
    {
        return $this->hasMany(
            Report::class,
            'user_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Pending Reports Relationship
    |--------------------------------------------------------------------------
    */

    public function pendingReports(): HasMany
    {
        return $this->hasMany(
            Report::class,
            'user_id',
            'id'
        )
            ->where(
                'status',
                Report::STATUS_PENDING
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reports In Review Relationship
    |--------------------------------------------------------------------------
    */

    public function reportsInReview(): HasMany
    {
        return $this->hasMany(
            Report::class,
            'user_id',
            'id'
        )
            ->where(
                'status',
                Report::STATUS_IN_REVIEW
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolved Reports Relationship
    |--------------------------------------------------------------------------
    */

    public function resolvedReports(): HasMany
    {
        return $this->hasMany(
            Report::class,
            'user_id',
            'id'
        )
            ->where(
                'status',
                Report::STATUS_RESOLVED
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Excuse Letter Relationship
    |--------------------------------------------------------------------------
    |
    | One NSTP student can submit many excuse letters.
    |
    | Example:
    |
    | $student->excuseLetters;
    |
    */

    public function excuseLetters(): HasMany
    {
        return $this->hasMany(
            ExcuseLetter::class,
            'user_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Pending Excuse Letters
    |--------------------------------------------------------------------------
    |
    | Excuse letters waiting for review.
    |
    | Example:
    |
    | $student->pendingExcuseLetters;
    |
    */

    public function pendingExcuseLetters(): HasMany
    {
        return $this->hasMany(
            ExcuseLetter::class,
            'user_id',
            'id'
        )
            ->where(
                'status',
                ExcuseLetter::STATUS_PENDING
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approved Excuse Letters
    |--------------------------------------------------------------------------
    |
    | Excuse letters approved by an authorized NSTP reviewer.
    |
    */

    public function approvedExcuseLetters(): HasMany
    {
        return $this->hasMany(
            ExcuseLetter::class,
            'user_id',
            'id'
        )
            ->where(
                'status',
                ExcuseLetter::STATUS_APPROVED
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Rejected Excuse Letters
    |--------------------------------------------------------------------------
    |
    | Excuse letters rejected by an authorized NSTP reviewer.
    |
    */

    public function rejectedExcuseLetters(): HasMany
    {
        return $this->hasMany(
            ExcuseLetter::class,
            'user_id',
            'id'
        )
            ->where(
                'status',
                ExcuseLetter::STATUS_REJECTED
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Full Name Helper
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        /*
        |--------------------------------------------------------------------------
        | Name Parts
        |--------------------------------------------------------------------------
        */

        $surname =
            trim(
                (string) (
                    $this->surname
                    ??
                    ''
                )
            );


        $firstName =
            trim(
                (string) (
                    $this->first_name
                    ??
                    ''
                )
            );


        $middleName =
            trim(
                (string) (
                    $this->middle_name
                    ??
                    ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Given Names
        |--------------------------------------------------------------------------
        */

        $givenNames =
            collect(
                [
                    $firstName,

                    $middleName,
                ]
            )
                ->filter()
                ->implode(
                    ' '
                );


        /*
        |--------------------------------------------------------------------------
        | Surname, First Name Middle Name
        |--------------------------------------------------------------------------
        */

        if (
            $surname !==
            ''
            &&
            $givenNames !==
            ''
        ) {
            return trim(
                $surname
                .
                ', '
                .
                $givenNames
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Only Given Name
        |--------------------------------------------------------------------------
        */

        if (
            $givenNames !==
            ''
        ) {
            return $givenNames;
        }


        /*
        |--------------------------------------------------------------------------
        | Only Surname
        |--------------------------------------------------------------------------
        */

        if (
            $surname !==
            ''
        ) {
            return $surname;
        }


        /*
        |--------------------------------------------------------------------------
        | Account Name Fallback
        |--------------------------------------------------------------------------
        */

        return trim(
            (string) (
                $this->name
                ??
                ''
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Full Address Helper
    |--------------------------------------------------------------------------
    */

    public function getFullAddressAttribute(): string
    {
        return collect(
            [
                $this->city_address,

                $this->municipality,

                $this->province,
            ]
        )
            ->filter()
            ->implode(
                ', '
            );
    }


    /*
    |--------------------------------------------------------------------------
    | NSTP Component Helper
    |--------------------------------------------------------------------------
    |
    | Returns the student's component in a consistent uppercase format.
    |
    */

    public function getNstpComponentAttribute(): string
    {
        return strtoupper(
            trim(
                (string) (
                    $this->component
                    ??
                    ''
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NSTP Status Helper
    |--------------------------------------------------------------------------
    |
    | Default status for enrolled students is ACTIVE.
    |
    */

    public function getNstpStatusLabelAttribute(): string
    {
        $status =
            strtoupper(
                trim(
                    (string) (
                        $this->nstp_status
                        ??
                        'ACTIVE'
                    )
                )
            );


        return match ($status) {

            'WARNING',
            'AT RISK',
            'AT RISK FOR DROPOUT',
            'WARNING FOR DROPOUT' =>
                'WARNING FOR DROPOUT',

            'DROPPED',
            'DROP OUT',
            'DROPOUT' =>
                'DROPOUT',

            default =>
                'ACTIVE',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Active Student Helper
    |--------------------------------------------------------------------------
    */

    public function isActiveNstpStudent(): bool
    {
        return (
            $this->nstp_status_label ===
            'ACTIVE'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Warning For Dropout Helper
    |--------------------------------------------------------------------------
    */

    public function isWarningForDropout(): bool
    {
        return (
            $this->nstp_status_label ===
            'WARNING FOR DROPOUT'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Dropout Helper
    |--------------------------------------------------------------------------
    */

    public function isDropout(): bool
    {
        return (
            $this->nstp_status_label ===
            'DROPOUT'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Student Helper
    |--------------------------------------------------------------------------
    */

    public function isRotcStudent(): bool
    {
        return (
            $this->nstp_component ===
            'ROTC'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LTS Student Helper
    |--------------------------------------------------------------------------
    */

    public function isLtsStudent(): bool
    {
        return (
            $this->nstp_component ===
            'LTS'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CWTS Student Helper
    |--------------------------------------------------------------------------
    */

    public function isCwtsStudent(): bool
    {
        return (
            $this->nstp_component ===
            'CWTS'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Has Reports Helper
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
    | Has Pending Reports Helper
    |--------------------------------------------------------------------------
    */

    public function hasPendingReports(): bool
    {
        return $this
            ->reports()
            ->where(
                'status',
                Report::STATUS_PENDING
            )
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Has Reports In Review Helper
    |--------------------------------------------------------------------------
    */

    public function hasReportsInReview(): bool
    {
        return $this
            ->reports()
            ->where(
                'status',
                Report::STATUS_IN_REVIEW
            )
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Latest Report Helper
    |--------------------------------------------------------------------------
    */

    public function latestReport(): ?Report
    {
        return $this
            ->reports()
            ->latest(
                'created_at'
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Has Excuse Letters Helper
    |--------------------------------------------------------------------------
    |
    | Returns true when the student has submitted at least one excuse letter.
    |
    */

    public function hasExcuseLetters(): bool
    {
        return $this
            ->excuseLetters()
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Has Pending Excuse Letters Helper
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
    | Has Approved Excuse Letters Helper
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
    | Has Rejected Excuse Letters Helper
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
    | Latest Excuse Letter Helper
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | $student->latestExcuseLetter();
    |
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
    | Latest Pending Excuse Letter Helper
    |--------------------------------------------------------------------------
    */

    public function latestPendingExcuseLetter(): ?ExcuseLetter
    {
        return $this
            ->excuseLetters()
            ->where(
                'status',
                ExcuseLetter::STATUS_PENDING
            )
            ->latest(
                'created_at'
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Excuse Letter Count Helper
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
    | Pending Excuse Letter Count Helper
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
    | Approved Excuse Letter Count Helper
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
    | Rejected Excuse Letter Count Helper
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
    | Student Identity Helper
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Student identity must not depend entirely on the Spatie model_has_roles
    | pivot table.
    |
    | In the NSTP HUB mobile flow:
    |
    | AccessCodeScreen
    |      ↓
    | University verified
    |      ↓
    | MobileAuthController
    |      ↓
    | users.university_id assigned
    |      ↓
    | Sanctum token created
    |
    | Therefore a valid authenticated mobile User that is connected to a
    | university is an NSTP student account.
    |
    | The Spatie "student" role is still maintained separately for permissions.
    |
    */

    public function isStudent(): bool
    {
        return (
            $this->exists
            &&
            $this->university_id !==
            null
        );
    }
}