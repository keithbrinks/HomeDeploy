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
            // Use sudo mysql (works with auth_socket) - no password needed
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

    private function getMysqlRootPassword(): ?string
    {
        // Try environment variable first
        $password = env('MYSQL_ROOT_PASSWORD');
        if ($password) {
            return $password;
        }

        // Try reading from credentials file using sudo
        $credFile = '/root/mysql-root-credentials.txt';
        $result = Process::run("sudo cat {$credFile} 2>/dev/null");
        
        if ($result->successful()) {
            $contents = $result->output();
            if (preg_match('/Password:\s*(.+)/', $contents, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }
}
