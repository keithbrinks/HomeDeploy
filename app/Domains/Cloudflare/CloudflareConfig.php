<?php

declare(strict_types=1);

namespace App\Domains\Cloudflare;

use App\Domains\Sites\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CloudflareConfig extends Model
{
    protected $fillable = [
        'site_id',
        'tunnel_id',
        'tunnel_name',
        'tunnel_token',
        'account_id',
        'hostname',
        'service_url',
        'enabled',
    ];

    protected $hidden = [
        'tunnel_token',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'tunnel_token' => 'encrypted',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
