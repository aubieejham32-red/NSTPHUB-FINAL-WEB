<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Coordinator extends Authenticatable
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
    | Coordinator roles must use:
    |
    | guard_name = coordinator
    |
    */

    protected string $guard_name =
        'coordinator';


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
        | Coordinator Information
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
        | NSTP Information
        |--------------------------------------------------------------------------
        */

        'component',

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
    | One Coordinator belongs to one university.
    |
    | Example:
    |
    | $coordinator->university;
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
    | This allows a Coordinator to be recorded as the reviewer of a report.
    |
    | Example:
    |
    | $coordinator->reviewedReports;
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
    | Example:
    |
    | $coordinator->reviewedExcuseLetters;
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
    | Pending Reviewed Excuse Letters
    |--------------------------------------------------------------------------
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
    | Attendance Coordinator Role Helper
    |--------------------------------------------------------------------------
    */

    public function isAttendanceCoordinator(): bool
    {
        return $this->hasRole(
            'coordinator-attendance'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Announcement Coordinator Role Helper
    |--------------------------------------------------------------------------
    */

    public function isAnnouncementCoordinator(): bool
    {
        return $this->hasRole(
            'coordinator-announcement'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Schedule Coordinator Role Helper
    |--------------------------------------------------------------------------
    */

    public function isScheduleCoordinator(): bool
    {
        return $this->hasRole(
            'coordinator-schedule'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Coordinator Role
    |--------------------------------------------------------------------------
    |
    | Returns the coordinator's NSTP management role.
    |
    | Priority:
    |
    | coordinator-attendance
    | coordinator-announcement
    | coordinator-schedule
    |
    */

    public function coordinatorRole(): ?string
    {
        if (
            $this->isAttendanceCoordinator()
        ) {
            return ExcuseLetter::ROLE_COORDINATOR_ATTENDANCE;
        }


        if (
            $this->isAnnouncementCoordinator()
        ) {
            return ExcuseLetter::ROLE_COORDINATOR_ANNOUNCEMENT;
        }


        if (
            $this->isScheduleCoordinator()
        ) {
            return ExcuseLetter::ROLE_COORDINATOR_SCHEDULE;
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Can Manage Excuse Letters
    |--------------------------------------------------------------------------
    |
    | Only:
    |
    | coordinator-attendance
    |
    | can approve, reject, and give feedback to excuse letters.
    |
    | Announcement and Schedule Coordinators cannot manage excuse letters.
    |
    */

    public function canManageExcuseLetters(): bool
    {
        return ExcuseLetter::roleCanManage(
            $this->coordinatorRole()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Can Manage Attendance
    |--------------------------------------------------------------------------
    */

    public function canManageAttendance(): bool
    {
        return $this->isAttendanceCoordinator();
    }


    /*
    |--------------------------------------------------------------------------
    | Can Manage Announcements
    |--------------------------------------------------------------------------
    */

    public function canManageAnnouncements(): bool
    {
        return $this->isAnnouncementCoordinator();
    }


    /*
    |--------------------------------------------------------------------------
    | Can Manage Schedules
    |--------------------------------------------------------------------------
    */

    public function canManageSchedules(): bool
    {
        return $this->isScheduleCoordinator();
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
    | Has Approved Excuse Letters
    |--------------------------------------------------------------------------
    */

    public function hasApprovedExcuseLetters(): bool
    {
        return $this
            ->reviewedExcuseLetters()
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
            ->reviewedExcuseLetters()
            ->where(
                'status',
                ExcuseLetter::STATUS_REJECTED
            )
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
    | Latest Reviewed Report
    |--------------------------------------------------------------------------
    */

    public function latestReviewedReport(): ?Report
    {
        return $this
            ->reviewedReports()
            ->latest(
                'reviewed_at'
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Active Coordinator Helper
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return (
            strtoupper(
                trim(
                    (string) (
                        $this->status
                        ??
                        ''
                    )
                )
            ) ===
            'ACTIVE'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Component Helper
    |--------------------------------------------------------------------------
    |
    | Returns:
    |
    | CWTS
    | LTS
    | ROTC
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
    | CWTS Coordinator Helper
    |--------------------------------------------------------------------------
    */

    public function isCwtsCoordinator(): bool
    {
        return (
            $this->nstp_component ===
            ExcuseLetter::COMPONENT_CWTS
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LTS Coordinator Helper
    |--------------------------------------------------------------------------
    */

    public function isLtsCoordinator(): bool
    {
        return (
            $this->nstp_component ===
            ExcuseLetter::COMPONENT_LTS
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Coordinator Helper
    |--------------------------------------------------------------------------
    */

    public function isRotcCoordinator(): bool
    {
        return (
            $this->nstp_component ===
            ExcuseLetter::COMPONENT_ROTC
        );
    }
}