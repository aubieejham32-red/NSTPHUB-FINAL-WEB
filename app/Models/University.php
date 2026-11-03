<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'acronym',
        'type',
        'campus_type',
        'email',
        'contact_number',
        'website',
        'logo',

        'region',
        'province',
        'city',
        'barangay',
        'zip_code',
        'complete_address',

        'academic_year',
        'semester',
        'components',
        'max_students',

        'access_code',
        'status',
    ];

    protected $casts = [
        'components' => 'array',
    ];

    /**
     * Always expose a browser-ready logo URL together with the stored path.
     *
     * Database value example:
     *     universities/logos/abc123.png
     *
     * JSON value returned as logo_url:
     *     /storage/universities/logos/abc123.png
     */
    protected $appends = [
        'logo_url',
    ];

    /**
     * Build a safe public URL for the university logo.
     */
    public function getLogoUrlAttribute(): ?string
    {
        $logo = trim((string) ($this->attributes['logo'] ?? ''));

        if ($logo === '') {
            return null;
        }

        if (
            Str::startsWith($logo, 'http://') ||
            Str::startsWith($logo, 'https://') ||
            Str::startsWith($logo, 'data:') ||
            Str::startsWith($logo, 'blob:')
        ) {
            return $logo;
        }

        if (
            Str::startsWith($logo, '/storage/') ||
            Str::startsWith($logo, '/images/') ||
            Str::startsWith($logo, '/uploads/')
        ) {
            return $logo;
        }

        if (Str::startsWith($logo, 'storage/')) {
            return '/'.ltrim($logo, '/');
        }

        if (Str::startsWith($logo, 'public/')) {
            return '/'.ltrim(Str::after($logo, 'public/'), '/');
        }

        return '/storage/'.ltrim($logo, '/');
    }

    /**
     * One University has one Administrator.
     */
    public function administrator()
    {
        return $this->hasOne(
            UniversityAdministrator::class,
            'university_id',
            'id'
        );
    }

    /**
     * One University can have many Instructors.
     */
    public function instructors()
    {
        return $this->hasMany(
            Instructor::class,
            'university_id',
            'id'
        );
    }
}
