<?php

declare(strict_types=1);

namespace App\Domains\Server\Actions;

use Illuminate\Support\Facades\Process;

class RestartNginxAction
{
    public function execute(): bool
    {
        $result = Process::run('sudo systemctl reload nginx');

        return $result->successful();
    }
}
