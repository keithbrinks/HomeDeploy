<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'github_client_id',
        'github_client_secret',
        'github_redirect_uri',
        'github_token',
        'github_user',
        'server_ip',
        'default_domain',
        'local_domain_suffix',
        'homedeploy_domain',
        'cloudflare_tunnel_token',
        'cloudflare_tunnel_id',
        'cloudflare_tunnel_enabled',
    ];

    protected $hidden = [
        'github_client_secret',
        'github_token',
        'cloudflare_tunnel_token',
    ];

    protected $casts = [
        'github_client_secret' => 'encrypted',
        'github_token' => 'encrypted',
        'cloudflare_tunnel_token' => 'encrypted',
        'cloudflare_tunnel_enabled' => 'boolean',
    ];

    public static function get(): self
    {
        return static::firstOrCreate([
            'id' => 1,
        ]);
    }

    public function hasGithubOAuth(): bool
    {
        return !empty($this->github_client_id) && !empty($this->github_client_secret);
    }

    public function hasGithubToken(): bool
    {
        return !empty($this->github_token);
    }

    public function isGithubConnected(): bool
    {
        return $this->hasGithubOAuth() && $this->hasGithubToken();
    }

    public function hasCloudflare(): bool
    {
        return !empty($this->cloudflare_tunnel_token);
    }

    public function getServerIp(): ?string
    {
        if ($this->server_ip) {
            return $this->server_ip;
        }

        // Auto-detect server IP
        $ip = @file_get_contents('https://api.ipify.org');
        return $ip ?: null;
    }

    public function getSiteDomain(string $siteName, string $domainStrategy, ?string $customDomain = null): string
    {
        return match ($domainStrategy) {
            'ip' => $this->getServerIp() ?? 'localhost',
            'subdomain' => "{$siteName}.{$this->default_domain}",
            'local' => "{$siteName}{$this->local_domain_suffix}",
            'custom' => $customDomain ?? $siteName,
            default => $siteName,
        };
    }
}
