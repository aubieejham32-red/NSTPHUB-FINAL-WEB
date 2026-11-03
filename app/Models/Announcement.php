<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Announcement extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Allowed Management Roles
    |--------------------------------------------------------------------------
    */

    public const ROLE_UNIVERSITY_ADMIN = 'university-admin';

    public const ROLE_INSTRUCTOR = 'instructor';

    public const ROLE_COORDINATOR_ANNOUNCEMENT = 'coordinator-announcement';


    /*
    |--------------------------------------------------------------------------
    | View-Only Roles
    |--------------------------------------------------------------------------
    */

    public const ROLE_COORDINATOR_ATTENDANCE = 'coordinator-attendance';

    public const ROLE_COORDINATOR_SCHEDULE = 'coordinator-schedule';

    public const ROLE_STUDENT = 'student';

    public const ROLE_SUPER_ADMIN = 'super-admin';


    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    */

    public const COMPONENT_ALL = 'ALL';

    public const COMPONENT_CWTS = 'CWTS';

    public const COMPONENT_LTS = 'LTS';

    public const COMPONENT_ROTC = 'ROTC';


    /*
    |--------------------------------------------------------------------------
    | Recipient Types
    |--------------------------------------------------------------------------
    */

    public const RECIPIENT_ALL_STUDENTS = 'all_students';

    public const RECIPIENT_SPECIFIC_STUDENT = 'specific_student';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'university_id',

        'creator_id',
        'creator_type',
        'creator_role',

        'component',

        'title',
        'announcement_date',
        'description',

        'recipient',
        'student_email',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'announcement_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function university(): BelongsTo
    {
        return $this->belongsTo(
            University::class,
            'university_id'
        );
    }


    /**
     * Creator can be:
     *
     * UniversityAdministrator
     * Instructor
     * Coordinator
     */
    public function creator(): MorphTo
    {
        return $this->morphTo();
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
            self::ROLE_COORDINATOR_ANNOUNCEMENT,
        ];
    }


    public static function roleCanManage(?string $role): bool
    {
        if (!$role) {
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


    public static function componentsWithAll(): array
    {
        return [
            self::COMPONENT_ALL,
            ...self::components(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function createdByUniversityAdmin(): bool
    {
        return $this->creator_role === self::ROLE_UNIVERSITY_ADMIN;
    }


    public function createdByInstructor(): bool
    {
        return $this->creator_role === self::ROLE_INSTRUCTOR;
    }


    public function createdByAnnouncementCoordinator(): bool
    {
        return $this->creator_role === self::ROLE_COORDINATOR_ANNOUNCEMENT;
    }


    /*
    |--------------------------------------------------------------------------
    | Recipient Helpers
    |--------------------------------------------------------------------------
    */

    public function isForAllStudents(): bool
    {
        return $this->recipient === self::RECIPIENT_ALL_STUDENTS;
    }


    public function isForSpecificStudent(): bool
    {
        return $this->recipient === self::RECIPIENT_SPECIFIC_STUDENT;
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForAllStudents(Builder $query): Builder
    {
        return $query->where(
            'recipient',
            self::RECIPIENT_ALL_STUDENTS
        );
    }


    public function scopeForStudent(
        Builder $query,
        string $email
    ): Builder {
        return $query->where(
            function (Builder $query) use ($email) {
                $query
                    ->where(
                        'recipient',
                        self::RECIPIENT_ALL_STUDENTS
                    )
                    ->orWhere(
                        function (Builder $query) use ($email) {
                            $query
                                ->where(
                                    'recipient',
                                    self::RECIPIENT_SPECIFIC_STUDENT
                                )
                                ->where(
                                    'student_email',
                                    $email
                                );
                        }
                    );
            }
        );
    }


    public function scopeForComponent(
        Builder $query,
        string $component
    ): Builder {
        $component = strtoupper($component);

        return $query->where(
            function (Builder $query) use ($component) {
                $query
                    ->where(
                        'component',
                        self::COMPONENT_ALL
                    )
                    ->orWhere(
                        'component',
                        $component
                    );
            }
        );
    }


    public function scopeRecent(Builder $query): Builder
    {
        return $query
            ->orderByDesc('announcement_date')
            ->orderByDesc('created_at');
    }


    public function scopeOld(Builder $query): Builder
    {
        return $query
            ->orderBy('announcement_date')
            ->orderBy('created_at');
    }
}