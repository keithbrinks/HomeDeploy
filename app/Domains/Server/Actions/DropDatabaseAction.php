<?php

declare(strict_types=1);

namespace App\Domains\Server\Actions;

use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Facades\Process;

class DropDatabaseAction
{
    public function execute(string $databaseName, string $username): void
    {
        $rootPassword = config('database.mysql_root_password') ?? env('MYSQL_ROOT_PASSWORD');
        
        try {
            // Drop user
            $dropUser = Process::run("sudo mysql -u root -p'{$rootPassword}' -e \"DROP USER IF EXISTS '{$username}'@'localhost';\"");
            
            if ($dropUser->failed()) {
                throw new \RuntimeException("Failed to drop user: " . $dropUser->errorOutput());
            }

            // Drop database
            $dropDb = Process::run("sudo mysql -u root -p'{$rootPassword}' -e \"DROP DATABASE IF EXISTS {$databaseName};\"");
            
            if ($dropDb->failed()) {
                throw new \RuntimeException("Failed to drop database: " . $dropDb->errorOutput());
            }

            Process::run("sudo mysql -u root -p'{$rootPassword}' -e \"FLUSH PRIVILEGES;\"");
        } catch (ProcessFailedException $e) {
            throw new \RuntimeException("Process failed: " . $e->getMessage());
        }
    }
}
