<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GmailOAuthToken extends Model
{
    protected $table = 'gmail_oauth_tokens';

    protected $fillable = [
        'refresh_token',
        'authorized_at',
    ];

    protected function casts(): array
    {
        return [
            'refresh_token' => 'encrypted',
            'authorized_at' => 'datetime',
        ];
    }
}