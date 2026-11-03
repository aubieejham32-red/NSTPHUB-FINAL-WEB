<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class SuperAdmin extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $table = 'super_admins';

    protected $guard_name = 'superadmin';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];
}