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
        'base_domain',
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
        return !empty($this->cloudflare_tunnel_token) 
            && !empty($this->cloudflare_tunnel_id)
            && !empty($this->base_domain);
    }
    
    public function getTunnelHostname(): ?string
    {
        return $this->base_domain;
    }
    
    public function getDnsRecordName(): string
    {
        if (!$this->base_domain) {
            return '@';
        }
        
        // If base_domain is just a domain (example.com), use @
        // If it's a subdomain (app.example.com), extract the subdomain part
        $parts = explode('.', $this->base_domain);
        if (count($parts) <= 2) {
            return '@';  // Root domain
        }
        
        // Return the subdomain part (e.g., 'app' from 'app.example.com')
        return $parts[0];
    }
    
    public function getTunnelServiceStatus(): array
    {
        if (!$this->cloudflare_tunnel_id) {
            return ['status' => 'not_configured', 'message' => 'Tunnel not configured'];
        }
        
        $result = \Illuminate\Support\Facades\Process::run('sudo systemctl is-active cloudflared-tunnel 2>/dev/null');
        $isActive = trim($result->output()) === 'active';
        
        if (!$isActive) {
            // Get more details
            $statusResult = \Illuminate\Support\Facades\Process::run('sudo systemctl status cloudflared-tunnel 2>/dev/null');
            return [
                'status' => 'stopped',
                'message' => 'Service is not running',
                'details' => $statusResult->output()
            ];
        }
        
        return ['status' => 'running', 'message' => 'Service is active'];
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
            'subdomain' => $this->base_domain ? "{$siteName}.{$this->base_domain}" : "{$siteName}.example.com",
            'custom' => $customDomain ?? $siteName,
            default => $this->base_domain ? "{$siteName}.{$this->base_domain}" : "{$siteName}.example.com",
        };
    }
}
