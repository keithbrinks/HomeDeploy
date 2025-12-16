<?php

declare(strict_types=1);

namespace App\Domains\Sites;

use App\Domains\Deployments\Deployment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Site extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'domain_strategy',
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
    
    public function cloudflareConfig(): HasOne
    {
        return $this->hasOne(\App\Domains\Cloudflare\CloudflareConfig::class);
    }

    public function getEnvFileContent(): ?string
    {
        $envPath = $this->deploy_path . '/.env';
        
        if (!file_exists($envPath)) {
            return null;
        }
        
        return file_get_contents($envPath);
    }

    public function getFullDomain(): string
    {
        // If domain is already set, use it
        if ($this->domain) {
            return 'http://' . $this->domain;
        }
        
        // Otherwise generate from strategy
        $settings = \App\Models\Settings::get();
        return 'http://' . $settings->getSiteDomain(
            $this->name,
            $this->domain_strategy ?? 'ip',
            null
        );
    }

    public function getDatabasePassword(): ?string
    {
        if ($this->database_password) {
            return \Illuminate\Support\Facades\Crypt::decryptString($this->database_password);
        }
        return null;
    }
}

