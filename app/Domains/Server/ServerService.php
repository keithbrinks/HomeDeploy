<?php

declare(strict_types=1);

namespace App\Domains\Server;

use Illuminate\Database\Eloquent\Model;

class ServerService extends Model
{
    protected $guarded = [];

    protected $casts = [
        'auto_restart' => 'boolean',
    ];
}
