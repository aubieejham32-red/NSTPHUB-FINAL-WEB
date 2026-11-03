<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Throwable;

class AttendanceSchedule extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | NSTP HUB Timezone
    |--------------------------------------------------------------------------
    */

    public const TIMEZONE = 'Asia/Manila';

    /*
    |--------------------------------------------------------------------------
    | Fillable Attributes
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'university_id',
        'component',
        'attendance_date',

        /*
        |--------------------------------------------------------------------------
        | New Time In Window
        |--------------------------------------------------------------------------
        */

        'start_time_in',
        'end_time_in',
        'time_in_extension_minutes',

        /*
        |--------------------------------------------------------------------------
        | New Time Out Window
        |--------------------------------------------------------------------------
        */

        'start_time_out',
        'end_time_out',
        'time_out_extension_minutes',

        /*
        |--------------------------------------------------------------------------
        | Legacy Columns
        |--------------------------------------------------------------------------
        |
        | Kept temporarily because these already exist in the database.
        |
        */

        'time_in',
        'late_grace_minutes',
        'time_out',

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        'created_by_type',
        'created_by_id',
        'updated_by_type',
        'updated_by_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'attendance_date' =>
            'date',

        'time_in_extension_minutes' =>
            'integer',

        'time_out_extension_minutes' =>
            'integer',

        'late_grace_minutes' =>
            'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Current Philippine Date / Time
    |--------------------------------------------------------------------------
    */

    public static function currentDateTime(): CarbonImmutable
    {
        return CarbonImmutable::now(
            self::TIMEZONE
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Today's Date
    |--------------------------------------------------------------------------
    */

    public static function today(): string
    {
        return self::currentDateTime()
            ->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | Is Schedule Fully Configured?
    |--------------------------------------------------------------------------
    */

    public function isConfigured(): bool
    {
        $requiredTimes = [
            $this->start_time_in,
            $this->end_time_in,
            $this->start_time_out,
            $this->end_time_out,
        ];

        foreach (
            $requiredTimes
            as
            $time
        ) {
            if (
                trim(
                    (string) $time
                ) ===
                ''
            ) {
                return false;
            }
        }

        try {
            $startTimeIn =
                $this->startTimeInAt();

            $endTimeIn =
                $this->endTimeInAt();

            $finalTimeIn =
                $this->finalTimeInCutoffAt();

            $startTimeOut =
                $this->startTimeOutAt();

            $endTimeOut =
                $this->endTimeOutAt();

            $finalTimeOut =
                $this->finalTimeOutCutoffAt();

            return (
                $endTimeIn->greaterThan(
                    $startTimeIn
                )
                &&
                $startTimeOut->greaterThan(
                    $finalTimeIn
                )
                &&
                $endTimeOut->greaterThan(
                    $startTimeOut
                )
                &&
                $finalTimeOut->toDateString() ===
                $this->scheduleDate()
            );
        } catch (
            Throwable
        ) {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Start Time In
    |--------------------------------------------------------------------------
    */

    public function startTimeInAt(): CarbonImmutable
    {
        return $this->dateTimeFromStoredTime(
            $this->start_time_in
        );
    }

    /*
    |--------------------------------------------------------------------------
    | End Time In
    |--------------------------------------------------------------------------
    |
    | Students scanning from Start Time In through End Time In are PRESENT.
    |
    */

    public function endTimeInAt(): CarbonImmutable
    {
        return $this->dateTimeFromStoredTime(
            $this->end_time_in
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Final Time In Cutoff
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | End Time In           = 08:15
    | Added Minutes         = 10
    | Final Cutoff          = 08:25
    |
    | 08:16 through 08:25 = LATE
    | After 08:25          = BLOCKED
    |
    */

    public function finalTimeInCutoffAt(): CarbonImmutable
    {
        return $this
            ->endTimeInAt()
            ->addMinutes(
                max(
                    0,
                    (int) $this
                        ->time_in_extension_minutes
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Start Time Out
    |--------------------------------------------------------------------------
    */

    public function startTimeOutAt(): CarbonImmutable
    {
        return $this->dateTimeFromStoredTime(
            $this->start_time_out
        );
    }

    /*
    |--------------------------------------------------------------------------
    | End Time Out
    |--------------------------------------------------------------------------
    */

    public function endTimeOutAt(): CarbonImmutable
    {
        return $this->dateTimeFromStoredTime(
            $this->end_time_out
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Final Time Out Cutoff
    |--------------------------------------------------------------------------
    */

    public function finalTimeOutCutoffAt(): CarbonImmutable
    {
        return $this
            ->endTimeOutAt()
            ->addMinutes(
                max(
                    0,
                    (int) $this
                        ->time_out_extension_minutes
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibility Methods
    |--------------------------------------------------------------------------
    |
    | This keeps Attendance::remarkForSchedule() compatible.
    |
    | lateCutoffAt() now represents End Time In.
    |
    | <= End Time In = PRESENT
    | >  End Time In = LATE
    |
    */

    public function scheduledTimeInAt(): CarbonImmutable
    {
        return $this->startTimeInAt();
    }

    public function lateCutoffAt(): CarbonImmutable
    {
        return $this->endTimeInAt();
    }

    public function scheduledTimeOutAt(): CarbonImmutable
    {
        return $this->startTimeOutAt();
    }

    /*
    |--------------------------------------------------------------------------
    | HTML Input Values
    |--------------------------------------------------------------------------
    */

    public function startTimeInForInput(): string
    {
        return $this->rawTimeForInput(
            $this->start_time_in
        );
    }

    public function endTimeInForInput(): string
    {
        return $this->rawTimeForInput(
            $this->end_time_in
        );
    }

    public function startTimeOutForInput(): string
    {
        return $this->rawTimeForInput(
            $this->start_time_out
        );
    }

    public function endTimeOutForInput(): string
    {
        return $this->rawTimeForInput(
            $this->end_time_out
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy HTML Input Methods
    |--------------------------------------------------------------------------
    */

    public function timeInForInput(): string
    {
        return $this->startTimeInForInput();
    }

    public function timeOutForInput(): string
    {
        return $this->startTimeOutForInput();
    }

    /*
    |--------------------------------------------------------------------------
    | Frontend Data
    |--------------------------------------------------------------------------
    */

    public function toFrontendArray(): array
    {
        $configured =
            $this->isConfigured();

        return [
            'id' =>
                $this->id,

            'university_id' =>
                (int) $this->university_id,

            'component' =>
                strtoupper(
                    (string) $this->component
                ),

            'attendance_date' =>
                $this
                    ->attendance_date
                    ?->format(
                        'Y-m-d'
                    ),

            /*
            |--------------------------------------------------------------------------
            | Time In Window
            |--------------------------------------------------------------------------
            */

            'start_time_in' =>
                $this->startTimeInForInput(),

            'end_time_in' =>
                $this->endTimeInForInput(),

            'time_in_extension_minutes' =>
                (int) (
                    $this
                        ->time_in_extension_minutes
                    ??
                    0
                ),

            'final_time_in_cutoff' =>
                $configured
                    ? $this
                        ->finalTimeInCutoffAt()
                        ->format(
                            'H:i'
                        )
                    : null,

            'final_time_in_cutoff_label' =>
                $configured
                    ? $this
                        ->finalTimeInCutoffAt()
                        ->format(
                            'g:i A'
                        )
                    : null,

            /*
            |--------------------------------------------------------------------------
            | Time Out Window
            |--------------------------------------------------------------------------
            */

            'start_time_out' =>
                $this->startTimeOutForInput(),

            'end_time_out' =>
                $this->endTimeOutForInput(),

            'time_out_extension_minutes' =>
                (int) (
                    $this
                        ->time_out_extension_minutes
                    ??
                    0
                ),

            'final_time_out_cutoff' =>
                $configured
                    ? $this
                        ->finalTimeOutCutoffAt()
                        ->format(
                            'H:i'
                        )
                    : null,

            'final_time_out_cutoff_label' =>
                $configured
                    ? $this
                        ->finalTimeOutCutoffAt()
                        ->format(
                            'g:i A'
                        )
                    : null,

            'is_configured' =>
                $configured,

            /*
            |--------------------------------------------------------------------------
            | Compatibility Data
            |--------------------------------------------------------------------------
            */

            'time_in' =>
                $this->startTimeInForInput(),

            'late_grace_minutes' =>
                (int) (
                    $this->late_grace_minutes
                    ??
                    0
                ),

            'late_cutoff' =>
                $this->endTimeInForInput(),

            'late_cutoff_label' =>
                $configured
                    ? $this
                        ->endTimeInAt()
                        ->format(
                            'g:i A'
                        )
                    : null,

            'time_out' =>
                $this->startTimeOutForInput(),

            'time_out_label' =>
                $configured
                    ? $this
                        ->startTimeOutAt()
                        ->format(
                            'g:i A'
                        )
                    : null,

            'updated_at' =>
                $this
                    ->updated_at
                    ?->toISOString(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Schedule Date
    |--------------------------------------------------------------------------
    */

    private function scheduleDate(): string
    {
        return $this
            ->attendance_date
            ?->format(
                'Y-m-d'
            )
            ??
            self::today();
    }

    /*
    |--------------------------------------------------------------------------
    | Build Full Date + Time
    |--------------------------------------------------------------------------
    */

    private function dateTimeFromStoredTime(
        mixed $time
    ): CarbonImmutable {
        $timeValue =
            $time instanceof DateTimeInterface
                ? $time->format(
                    'H:i:s'
                )
                : trim(
                    (string) $time
                );

        if (
            $timeValue ===
            ''
        ) {
            throw new InvalidArgumentException(
                'Attendance schedule time is missing.'
            );
        }

        if (
            preg_match(
                '/^\d{2}:\d{2}$/',
                $timeValue
            )
        ) {
            $timeValue .=
                ':00';
        }

        $dateTime =
            CarbonImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $this->scheduleDate()
                .
                ' '
                .
                $timeValue,
                self::TIMEZONE
            );

        if (
            !$dateTime
        ) {
            throw new InvalidArgumentException(
                'Attendance schedule time is invalid.'
            );
        }

        return $dateTime;
    }

    /*
    |--------------------------------------------------------------------------
    | Raw Time For HTML Input
    |--------------------------------------------------------------------------
    */

    private function rawTimeForInput(
        mixed $time
    ): string {
        if (
            $time instanceof DateTimeInterface
        ) {
            return $time->format(
                'H:i'
            );
        }

        $value =
            trim(
                (string) $time
            );

        if (
            $value ===
            ''
        ) {
            return '';
        }

        return substr(
            $value,
            0,
            5
        );
    }
}