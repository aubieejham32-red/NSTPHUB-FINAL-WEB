<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RotcStudentProfile extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | ROTC Profile Status
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT =
        'draft';

    public const STATUS_SUBMITTED =
        'submitted';

    public const STATUS_COMPLETED =
        'completed';


    /*
    |--------------------------------------------------------------------------
    | Mass Assignable
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
        | ROTC Identification
        |--------------------------------------------------------------------------
        */

        'nstp_id_no',
        'ms_level',


        /*
        |--------------------------------------------------------------------------
        | Name
        |--------------------------------------------------------------------------
        */

        'last_name',
        'first_name',
        'middle_name',
        'name_extension',


        /*
        |--------------------------------------------------------------------------
        | Personal Details
        |--------------------------------------------------------------------------
        */

        'gender',
        'blood_type',
        'date_of_birth',
        'place_of_birth',


        /*
        |--------------------------------------------------------------------------
        | Physical Attributes
        |--------------------------------------------------------------------------
        */

        'height_cm',
        'weight_kg',
        'complexion',


        /*
        |--------------------------------------------------------------------------
        | Academic
        |--------------------------------------------------------------------------
        */

        'school_name',
        'course',
        'religion',


        /*
        |--------------------------------------------------------------------------
        | Contact
        |--------------------------------------------------------------------------
        */

        'cellphone_number',
        'contact_email',


        /*
        |--------------------------------------------------------------------------
        | Temporary Address
        |--------------------------------------------------------------------------
        */

        'temporary_address_line',
        'temporary_municipality',
        'temporary_province',


        /*
        |--------------------------------------------------------------------------
        | Permanent Address
        |--------------------------------------------------------------------------
        */

        'permanent_same_as_temporary',

        'permanent_address_line',
        'permanent_municipality',
        'permanent_province',


        /*
        |--------------------------------------------------------------------------
        | Parents
        |--------------------------------------------------------------------------
        */

        'father_name',
        'father_occupation',

        'mother_name',
        'mother_occupation',


        /*
        |--------------------------------------------------------------------------
        | Emergency Contact
        |--------------------------------------------------------------------------
        */

        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_number',
        'emergency_contact_address',


        /*
        |--------------------------------------------------------------------------
        | Advance Course
        |--------------------------------------------------------------------------
        */

        'willing_advance_course',


        /*
        |--------------------------------------------------------------------------
        | ROTC Registration Progress
        |--------------------------------------------------------------------------
        */

        'status',
        'submitted_at',
        'completed_at',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'date_of_birth' =>
                'date',

            'height_cm' =>
                'decimal:2',

            'weight_kg' =>
                'decimal:2',

            'permanent_same_as_temporary' =>
                'boolean',

            'willing_advance_course' =>
                'boolean',

            'submitted_at' =>
                'datetime',

            'completed_at' =>
                'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Student
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | $profile->user
    |
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MS Records
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | $profile->msRecords
    |
    */

    public function msRecords(): HasMany
    {
        return $this->hasMany(
            RotcMsRecord::class,
            'rotc_student_profile_id',
            'id'
        )
            ->orderBy(
                'record_order'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Is Complete?
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return
            $this->status ===
            self::STATUS_COMPLETED
            &&
            $this->completed_at !==
            null;
    }


    /*
    |--------------------------------------------------------------------------
    | Permanent Address Helper
    |--------------------------------------------------------------------------
    |
    | If "same as temporary" was selected, use temporary fields.
    |
    */

    public function getPermanentAddressAttribute(): string
    {
        if (
            $this->permanent_same_as_temporary
        ) {
            return collect([
                $this->temporary_address_line,
                $this->temporary_municipality,
                $this->temporary_province,
            ])
                ->filter()
                ->implode(', ');
        }


        return collect([
            $this->permanent_address_line,
            $this->permanent_municipality,
            $this->permanent_province,
        ])
            ->filter()
            ->implode(', ');
    }


    /*
    |--------------------------------------------------------------------------
    | Temporary Address Helper
    |--------------------------------------------------------------------------
    */

    public function getTemporaryAddressAttribute(): string
    {
        return collect([
            $this->temporary_address_line,
            $this->temporary_municipality,
            $this->temporary_province,
        ])
            ->filter()
            ->implode(', ');
    }
}