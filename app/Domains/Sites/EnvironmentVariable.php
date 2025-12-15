<?php

declare(strict_types=1);

namespace App\Domains\Sites;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvironmentVariable extends Model
{
    protected $fillable = [
        'site_id',
        'key',
        'value',
    ];

    protected $hidden = [
        'value',
    ];

    protected $casts = [
        'value' => 'encrypted',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
