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

        try {
            // Create database
            $createDb = Process::run("sudo mysql -e \"CREATE DATABASE IF NOT EXISTS {$sanitized};\"");
            
            if ($createDb->failed()) {
                throw new \RuntimeException("Failed to create database: " . $createDb->errorOutput());
            }

            // Create user and grant privileges
            $createUser = Process::run("sudo mysql -e \"CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY '{$password}';\"");
            
            if ($createUser->failed()) {
                throw new \RuntimeException("Failed to create user: " . $createUser->errorOutput());
            }

            $grantPrivileges = Process::run("sudo mysql -e \"GRANT ALL PRIVILEGES ON {$sanitized}.* TO '{$username}'@'localhost'; FLUSH PRIVILEGES;\"");
            
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
}
