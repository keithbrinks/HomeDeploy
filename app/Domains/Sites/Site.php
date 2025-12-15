<?php

declare(strict_types=1);

namespace App\Domains\Sites;

use App\Domains\Deployments\Deployment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $guarded = [];

    protected $casts = [
        'build_commands' => 'array',
        'port' => 'integer',
    ];

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function environmentVariables(): HasMany
    {
        return $this->hasMany(EnvironmentVariable::class);
    }
}
