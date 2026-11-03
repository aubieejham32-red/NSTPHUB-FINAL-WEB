<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;


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
    | Attendance Remarks
    |--------------------------------------------------------------------------
    */

    public const REMARK_PRESENT = 'PRESENT';

    public const REMARK_LATE = 'LATE';

    public const REMARK_ABSENT = 'ABSENT';

    public const REMARK_EXCUSED = 'EXCUSED';


    /*
    |--------------------------------------------------------------------------
    | Fillable Attributes
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'user_id',
        'component',
        'attendance_date',
        'time_in',
        'time_out',
        'remark',
        'notes',
        'recorded_by_type',
        'recorded_by_id',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'attendance_date' => 'date',
    ];


    /*
    |--------------------------------------------------------------------------
    | Student Relationship
    |--------------------------------------------------------------------------
    */

    public function student(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
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
    | Available Remarks
    |--------------------------------------------------------------------------
    */

    public static function remarks(): array
    {
        return [
            self::REMARK_PRESENT,
            self::REMARK_LATE,
            self::REMARK_ABSENT,
            self::REMARK_EXCUSED,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Determine PRESENT or LATE
    |--------------------------------------------------------------------------
    |
    | The student's actual QR scan time remains automatic.
    |
    | The manually configured attendance schedule determines whether
    | the student is PRESENT or LATE.
    |
    | Example:
    |
    | Time In:      8:00 AM
    | Grace:        15 minutes
    | Late Cutoff:  8:15 AM
    |
    | 8:00 AM = PRESENT
    | 8:15 AM = PRESENT
    | 8:16 AM = LATE
    |
    */

    public static function remarkForSchedule(
        AttendanceSchedule $schedule,
        CarbonInterface $scanAt
    ): string {
        $scanTime =
            CarbonImmutable::instance(
                $scanAt
            )->setTimezone(
                AttendanceSchedule::TIMEZONE
            );


        return $scanTime->greaterThan(
            $schedule->lateCutoffAt()
        )
            ? self::REMARK_LATE
            : self::REMARK_PRESENT;
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Component
    |--------------------------------------------------------------------------
    */

    public function scopeComponent(
        $query,
        string $component
    ) {
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
    | Scope: ROTC
    |--------------------------------------------------------------------------
    */

    public function scopeRotc(
        $query
    ) {
        return $query->where(
            'component',
            self::COMPONENT_ROTC
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: LTS
    |--------------------------------------------------------------------------
    */

    public function scopeLts(
        $query
    ) {
        return $query->where(
            'component',
            self::COMPONENT_LTS
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: CWTS
    |--------------------------------------------------------------------------
    */

    public function scopeCwts(
        $query
    ) {
        return $query->where(
            'component',
            self::COMPONENT_CWTS
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Attendance Date
    |--------------------------------------------------------------------------
    */

    public function scopeForDate(
        $query,
        $date
    ) {
        return $query->whereDate(
            'attendance_date',
            $date
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Present
    |--------------------------------------------------------------------------
    */

    public function scopePresent(
        $query
    ) {
        return $query->where(
            'remark',
            self::REMARK_PRESENT
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Late
    |--------------------------------------------------------------------------
    */

    public function scopeLate(
        $query
    ) {
        return $query->where(
            'remark',
            self::REMARK_LATE
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Absent
    |--------------------------------------------------------------------------
    */

    public function scopeAbsent(
        $query
    ) {
        return $query->where(
            'remark',
            self::REMARK_ABSENT
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: Excused
    |--------------------------------------------------------------------------
    */

    public function scopeExcused(
        $query
    ) {
        return $query->where(
            'remark',
            self::REMARK_EXCUSED
        );
    }
}