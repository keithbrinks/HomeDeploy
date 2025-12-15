<?php

declare(strict_types=1);

namespace App\Domains\Server\Actions;

use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Facades\Process;

class DropDatabaseAction
{
    public function execute(string $databaseName, string $username): void
    {
        try {
            // Drop user
            $dropUser = Process::run("sudo mysql -e \"DROP USER IF EXISTS '{$username}'@'localhost';\"");
            
            if ($dropUser->failed()) {
                throw new \RuntimeException("Failed to drop user: " . $dropUser->errorOutput());
            }

            // Drop database
            $dropDb = Process::run("sudo mysql -e \"DROP DATABASE IF EXISTS {$databaseName};\"");
            
            if ($dropDb->failed()) {
                throw new \RuntimeException("Failed to drop database: " . $dropDb->errorOutput());
            }

            Process::run("sudo mysql -e \"FLUSH PRIVILEGES;\"");
        } catch (ProcessFailedException $e) {
            throw new \RuntimeException("Process failed: " . $e->getMessage());
        }
    }
}
