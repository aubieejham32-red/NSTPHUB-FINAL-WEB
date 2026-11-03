<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RotcMsRecord extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Mass Assignable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'rotc_student_profile_id',
        'record_order',

        'ms_level',
        'semester',
        'school_year',
        'grade',
        'remarks',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'record_order' =>
                'integer',

            'grade' =>
                'decimal:2',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ROTC Student Profile
    |--------------------------------------------------------------------------
    */

    public function rotcStudentProfile(): BelongsTo
    {
        return $this->belongsTo(
            RotcStudentProfile::class,
            'rotc_student_profile_id',
            'id'
        );
    }
}