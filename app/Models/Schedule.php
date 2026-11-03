<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Schedule extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Allowed Management Roles
    |--------------------------------------------------------------------------
    |
    | These roles can create, edit and delete schedules.
    |
    */

    public const ROLE_UNIVERSITY_ADMIN = 'university-admin';

    public const ROLE_INSTRUCTOR = 'instructor';

    public const ROLE_COORDINATOR_SCHEDULE = 'coordinator-schedule';


    /*
    |--------------------------------------------------------------------------
    | View-Only Roles
    |--------------------------------------------------------------------------
    |
    | These roles can later access the schedule page,
    | but cannot create, edit or delete.
    |
    */

    public const ROLE_COORDINATOR_ATTENDANCE = 'coordinator-attendance';

    public const ROLE_COORDINATOR_ANNOUNCEMENT = 'coordinator-announcement';

    public const ROLE_STUDENT = 'student';

    public const ROLE_SUPER_ADMIN = 'super-admin';


    /*
    |--------------------------------------------------------------------------
    | NSTP Components
    |--------------------------------------------------------------------------
    */

    public const COMPONENT_ALL = 'ALL';

    public const COMPONENT_CWTS = 'CWTS';

    public const COMPONENT_LTS = 'LTS';

    public const COMPONENT_ROTC = 'ROTC';


    /*
    |--------------------------------------------------------------------------
    | Fillable
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
        | Creator
        |--------------------------------------------------------------------------
        |
        | Creator can be:
        |
        | UniversityAdministrator
        | Instructor
        | Coordinator
        |
        */

        'creator_id',

        'creator_type',

        'creator_role',


        /*
        |--------------------------------------------------------------------------
        | NSTP Component
        |--------------------------------------------------------------------------
        |
        | ALL
        | CWTS
        | LTS
        | ROTC
        |
        */

        'component',


        /*
        |--------------------------------------------------------------------------
        | Schedule Information
        |--------------------------------------------------------------------------
        */

        'title',

        'schedule_date',

        'start_time',

        'end_time',

        'location',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',

            'created_at' => 'datetime',

            'updated_at' => 'datetime',
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
    | Creator Relationship
    |--------------------------------------------------------------------------
    |
    | Possible creators:
    |
    | App\Models\UniversityAdministrator
    | App\Models\Instructor
    | App\Models\Coordinator
    |
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

            self::ROLE_COORDINATOR_SCHEDULE,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Can Role Manage Schedule?
    |--------------------------------------------------------------------------
    */

    public static function roleCanManage(
        ?string $role
    ): bool {
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


    /*
    |--------------------------------------------------------------------------
    | Components Including ALL
    |--------------------------------------------------------------------------
    */

    public static function componentsWithAll(): array
    {
        return [
            self::COMPONENT_ALL,

            ...self::components(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Component
    |--------------------------------------------------------------------------
    */

    public static function isValidComponent(
        ?string $component
    ): bool {
        if (!$component) {
            return false;
        }

        return in_array(
            strtoupper(
                trim(
                    $component
                )
            ),
            self::componentsWithAll(),
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Creator Helpers
    |--------------------------------------------------------------------------
    */

    public function createdByUniversityAdmin(): bool
    {
        return (
            $this->creator_role ===
            self::ROLE_UNIVERSITY_ADMIN
        );
    }


    public function createdByInstructor(): bool
    {
        return (
            $this->creator_role ===
            self::ROLE_INSTRUCTOR
        );
    }


    public function createdByScheduleCoordinator(): bool
    {
        return (
            $this->creator_role ===
            self::ROLE_COORDINATOR_SCHEDULE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Component Helpers
    |--------------------------------------------------------------------------
    */

    public function isForAllComponents(): bool
    {
        return (
            $this->component ===
            self::COMPONENT_ALL
        );
    }


    public function isForCWTS(): bool
    {
        return (
            $this->component ===
            self::COMPONENT_CWTS
        );
    }


    public function isForLTS(): bool
    {
        return (
            $this->component ===
            self::COMPONENT_LTS
        );
    }


    public function isForROTC(): bool
    {
        return (
            $this->component ===
            self::COMPONENT_ROTC
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope - University
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
    | Scope - Component
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | ROTC Instructor / Coordinator / Student
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

    public function scopeForComponent(
        Builder $query,
        string $component
    ): Builder {
        $component = strtoupper(
            trim(
                $component
            )
        );

        return $query->where(
            function (
                Builder $query
            ) use ($component) {

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


    /*
    |--------------------------------------------------------------------------
    | Scope - Specific Component Only
    |--------------------------------------------------------------------------
    */

    public function scopeOnlyComponent(
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
    | Scope - ALL Components
    |--------------------------------------------------------------------------
    */

    public function scopeForAllComponents(
        Builder $query
    ): Builder {
        return $query->where(
            'component',
            self::COMPONENT_ALL
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope - Recent
    |--------------------------------------------------------------------------
    |
    | Newest schedule first.
    |
    | Used by RECENT button.
    |
    */

    public function scopeRecent(
        Builder $query
    ): Builder {
        return $query
            ->orderByDesc(
                'schedule_date'
            )
            ->orderByDesc(
                'start_time'
            )
            ->orderByDesc(
                'created_at'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope - Old
    |--------------------------------------------------------------------------
    |
    | Oldest schedule first.
    |
    | Used by OLD button.
    |
    */

    public function scopeOld(
        Builder $query
    ): Builder {
        return $query
            ->orderBy(
                'schedule_date'
            )
            ->orderBy(
                'start_time'
            )
            ->orderBy(
                'created_at'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope - Upcoming
    |--------------------------------------------------------------------------
    */

    public function scopeUpcoming(
        Builder $query
    ): Builder {
        return $query
            ->whereDate(
                'schedule_date',
                '>=',
                now()->toDateString()
            )
            ->orderBy(
                'schedule_date'
            )
            ->orderBy(
                'start_time'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope - Past
    |--------------------------------------------------------------------------
    */

    public function scopePast(
        Builder $query
    ): Builder {
        return $query
            ->whereDate(
                'schedule_date',
                '<',
                now()->toDateString()
            )
            ->orderByDesc(
                'schedule_date'
            )
            ->orderByDesc(
                'start_time'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope - Specific Date
    |--------------------------------------------------------------------------
    */

    public function scopeOnDate(
        Builder $query,
        string $date
    ): Builder {
        return $query->whereDate(
            'schedule_date',
            $date
        );
    }
}