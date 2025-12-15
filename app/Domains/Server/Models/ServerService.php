<?php

namespace App\Domains\Server\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServerService extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'auto_restart' => 'boolean',
    ];
}
