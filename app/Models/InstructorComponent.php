<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorComponent extends Model
{
    protected $fillable = [
        'instructor_id',
        'component',
    ];


    public function instructor(): BelongsTo
    {
        return $this->belongsTo(
            Instructor::class
        );
    }
}