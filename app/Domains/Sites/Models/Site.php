<?php

namespace App\Domains\Sites\Models;

use App\Domains\Deployments\Models\Deployment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Site extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'build_commands' => 'array',
        'port' => 'integer',
    ];

    public function environmentVariables(): HasMany
    {
        return $this->hasMany(EnvironmentVariable::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function getEnvFileContent(): ?string
    {
        $envPath = $this->deploy_path . '/.env';
        if (file_exists($envPath)) {
            return file_get_contents($envPath);
        }
        return null;
    }

    public function getDatabasePassword(): ?string
    {
        if ($this->database_password) {
            return Crypt::decryptString($this->database_password);
        }
        return null;
    }

    public function getFullDomain(): string
    {
        $settings = \App\Models\Settings::get();
        
        if ($this->domain) {
            return $this->domain;
        }
        
        return $settings->getSiteDomain(
            $this->name,
            $this->domain_strategy ?? 'ip',
            $this->domain
        );
    }
}
