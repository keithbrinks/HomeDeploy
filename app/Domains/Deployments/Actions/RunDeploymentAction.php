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

            // Ensure directory exists
            if (! is_dir($path)) {
                $this->log($deployment, "Creating directory: $path");
                
                // Create directory with sudo to handle permissions
                $mkdirResult = Process::run("sudo mkdir -p '$path'");
                if ($mkdirResult->failed()) {
                    throw new \RuntimeException("Failed to create directory: " . $mkdirResult->errorOutput());
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
                // Pull with GitHub token
                $this->updateGitRemote($deployment, $path, $site->repo_url);
                $this->runCommand($deployment, "git pull origin {$site->branch}", $path);
            }

            // Run build commands
            if ($site->build_commands) {
                foreach ($site->build_commands as $command) {
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

        $result = Process::path($path)
            ->timeout(600)
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
}
