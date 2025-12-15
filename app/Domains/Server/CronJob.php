<?php

declare(strict_types=1);

namespace App\Domains\Server;

use Illuminate\Database\Eloquent\Model;

class CronJob extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'last_run_at' => 'datetime',
    ];
}
