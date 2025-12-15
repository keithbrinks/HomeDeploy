<?php

declare(strict_types=1);

namespace App\Domains\Server;

use Illuminate\Database\Eloquent\Model;

class CloudflareConfig extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tunnel_token' => 'encrypted',
        'routes' => 'array',
    ];
}
