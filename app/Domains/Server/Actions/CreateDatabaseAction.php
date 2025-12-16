<?php

declare(strict_types=1);

namespace App\Domains\Server\Actions;

use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class CreateDatabaseAction
{
    public function execute(string $databaseName): array
    {
        // Sanitize database name
        $sanitized = Str::slug($databaseName, '_');
        $username = Str::limit($sanitized, 16, '');
        $password = Str::random(32);
        
        // Get MySQL root password
        $rootPassword = $this->getMysqlRootPassword();
        
        if (!$rootPassword) {
            throw new \RuntimeException("MySQL root password not found. Check /root/mysql-root-credentials.txt or set MYSQL_ROOT_PASSWORD environment variable.");
        }

        try {
            // Create database
            $createDb = Process::run("sudo mysql -u root -p'{$rootPassword}' -e \"CREATE DATABASE IF NOT EXISTS {$sanitized};\"");
            
            if ($createDb->failed()) {
                throw new \RuntimeException("Failed to create database: " . $createDb->errorOutput());
            }

            // Create user and grant privileges
            $createUser = Process::run("sudo mysql -u root -p'{$rootPassword}' -e \"CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY '{$password}';\"");
            
            if ($createUser->failed()) {
                throw new \RuntimeException("Failed to create user: " . $createUser->errorOutput());
            }

            $grantPrivileges = Process::run("sudo mysql -u root -p'{$rootPassword}' -e \"GRANT ALL PRIVILEGES ON {$sanitized}.* TO '{$username}'@'localhost'; FLUSH PRIVILEGES;\"");
            
            if ($grantPrivileges->failed()) {
                throw new \RuntimeException("Failed to grant privileges: " . $grantPrivileges->errorOutput());
            }

            return [
                'database' => $sanitized,
                'username' => $username,
                'password' => $password,
                'host' => 'localhost',
            ];
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
