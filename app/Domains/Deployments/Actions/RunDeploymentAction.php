<?php

declare(strict_types=1);

namespace App\Domains\Deployments\Actions;

use App\Domains\Deployments\Deployment;
use App\Models\Settings;
use Illuminate\Support\Facades\Process;
use Throwable;

class RunDeploymentAction
{
    public function execute(Deployment $deployment): void
    {
        $deployment->update([
            'status' => 'running',
            'started_at' => now(),
            'output' => "Starting deployment...\n",
        ]);

        try {
            $site = $deployment->site;
            $path = $site->deploy_path;
            $gitDir = $path . '/.git';

            // Check if it's a git repository
            if (! is_dir($gitDir)) {
                // First-time deployment - need to clone
                $this->log($deployment, "First deployment - cloning repository...");
                
                // Ensure directory exists
                if (! is_dir($path)) {
                    $this->log($deployment, "Creating directory: $path");
                    
                    $mkdirResult = Process::run("sudo mkdir -p '$path'");
                    if ($mkdirResult->failed()) {
                        throw new \RuntimeException("Failed to create directory: " . $mkdirResult->errorOutput());
                    }
                } else {
                    // Directory exists but is not a git repo - clear it first
                    $this->log($deployment, "Directory exists but is not a git repository. Clearing...");
                    $clearResult = Process::run("sudo rm -rf '$path'/*");
                    if ($clearResult->failed()) {
                        throw new \RuntimeException("Failed to clear directory: " . $clearResult->errorOutput());
                    }
                }
                
                // Set ownership to www-data
                $chownResult = Process::run("sudo chown -R www-data:www-data '$path'");
                if ($chownResult->failed()) {
                    throw new \RuntimeException("Failed to set ownership: " . $chownResult->errorOutput());
                }
                
                // Clone with GitHub token for private repos
                $cloneUrl = $this->getAuthenticatedRepoUrl($site->repo_url);
                $this->runCommand($deployment, "git clone -b {$site->branch} {$cloneUrl} .", $path);
            } else {
                // Subsequent deployment - pull updates
                $this->log($deployment, "Updating existing repository...");
                $this->updateGitRemote($deployment, $path, $site->repo_url);
                $this->runCommand($deployment, "git pull origin {$site->branch}", $path);
            }

            // Run build commands
            if ($site->build_commands) {
                foreach ($site->build_commands as $command) {
                    // Skip migrations if it's a migrate command and no database is configured
                    if ($this->isMigrateCommand($command) && !$this->hasDatabaseConfigured($site, $path)) {
                        $this->log($deployment, "Skipping migration: No database configured for this site.");
                        $this->log($deployment, "Create a database and sync credentials to .env before running migrations.");
                        continue;
                    }
                    
                    // Clear config cache before migrations to ensure .env changes are picked up
                    if ($this->isMigrateCommand($command)) {
                        $this->log($deployment, "Clearing config cache before migration...");
                        $this->runCommand($deployment, "php artisan config:clear", $path);
                    }
                    
                    $this->runCommand($deployment, $command, $path);
                }
            }

            $deployment->update([
                'status' => 'success',
                'completed_at' => now(),
            ]);
            $this->log($deployment, "Deployment successful!");

        } catch (Throwable $e) {
            $this->log($deployment, "Error: " . $e->getMessage());
            $deployment->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
        }
    }

    private function runCommand(Deployment $deployment, string $command, string $path): void
    {
        $this->log($deployment, "> $command");

        // Set npm cache to a writable location
        $npmCachePath = storage_path('npm-cache');
        if (!is_dir($npmCachePath)) {
            mkdir($npmCachePath, 0755, true);
        }

        $result = Process::path($path)
            ->timeout(600)
            ->env([
                'npm_config_cache' => $npmCachePath,
                'npm_config_prefix' => $npmCachePath,
                'HOME' => $path,
                'NPM_CONFIG_CACHE' => $npmCachePath,
            ])
            ->run($command, function (string $type, string $output) use ($deployment) {
                $this->log($deployment, $output);
            });

        if ($result->failed()) {
            throw new \RuntimeException("Command failed: $command");
        }
    }

    private function log(Deployment $deployment, string $message): void
    {
        // Append to output. In production, might want to optimize this (e.g. separate log table or file)
        // For MVP, appending to longText is fine.
        $deployment->output .= $message . "\n";
        $deployment->saveQuietly(); // Avoid triggering events if possible, or just save()
    }

    private function getAuthenticatedRepoUrl(string $repoUrl): string
    {
        $settings = Settings::get();
        
        if (!$settings->hasGithubToken()) {
            return $repoUrl; // Return original URL if no token
        }

        // Convert HTTPS URL to authenticated format: https://TOKEN@github.com/user/repo.git
        $token = $settings->github_token;
        
        if (preg_match('#https://github\.com/(.+)#', $repoUrl, $matches)) {
            return "https://{$token}@github.com/{$matches[1]}";
        }
        
        return $repoUrl;
    }

    private function updateGitRemote(Deployment $deployment, string $path, string $repoUrl): void
    {
        $authenticatedUrl = $this->getAuthenticatedRepoUrl($repoUrl);
        
        // Update the origin remote to use authenticated URL
        Process::path($path)->run("git remote set-url origin {$authenticatedUrl}");
    }

    private function isMigrateCommand(string $command): bool
    {
        return str_contains($command, 'artisan migrate');
    }

    private function hasDatabaseConfigured(object $site, string $path): bool
    {
        // Check if site has a database configured in HomeDeploy
        if (!$site->database_name) {
            return false;
        }

        // Check if .env file has MySQL database configured
        $envPath = $path . '/.env';
        if (!file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);
        
        // Must have DB_CONNECTION=mysql AND matching database name
        $hasMysql = preg_match('/^DB_CONNECTION=mysql/m', $envContent);
        $hasDatabase = str_contains($envContent, 'DB_DATABASE=' . $site->database_name);
        
        return $hasMysql && $hasDatabase;
    }
}