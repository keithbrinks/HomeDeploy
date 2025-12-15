<?php

declare(strict_types=1);

namespace App\Domains\Sites;

use App\Domains\Deployments\Deployment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'repo_url',
        'branch',
        'deploy_path',
        'port',
        'github_token',
        'webhook_secret',
        'database_name',
        'database_username',
        'database_password',
        'build_commands',
    ];

    protected $hidden = [
        'github_token',
        'webhook_secret',
        'database_password',
    ];

    protected $casts = [
        'build_commands' => 'array',
        'port' => 'integer',
        'github_token' => 'encrypted',
        'webhook_secret' => 'encrypted',
        'database_password' => 'encrypted',
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
