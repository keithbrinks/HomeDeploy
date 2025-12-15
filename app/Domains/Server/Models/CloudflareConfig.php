<?php

namespace App\Domains\Server\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloudflareConfig extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tunnel_token' => 'encrypted',
        'routes' => 'array',
    ];
}
