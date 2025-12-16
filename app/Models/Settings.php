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
    ];

    protected $hidden = [
        'github_client_secret',
        'github_token',
    ];

    protected $casts = [
        'github_client_secret' => 'encrypted',
        'github_token' => 'encrypted',
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
}
