<?php

namespace App\Actions\System;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class PerformUpdate
{
    public function execute(): array
    {
        $basePath = base_path();
        $backupPath = storage_path('backups/pre-update-' . date('Y-m-d-His'));
        
        try {
            // Create backup directory
            if (!is_dir(storage_path('backups'))) {
                mkdir(storage_path('backups'), 0755, true);
            }
            
            // Get current commit for rollback
            $currentCommit = trim(Process::path($basePath)->run('git rev-parse HEAD')->output());
            
            // Save backup info
            file_put_contents(
                storage_path('backups/update-info.json'),
                json_encode([
                    'timestamp' => date('Y-m-d H:i:s'),
                    'commit' => $currentCommit,
                    'backup_path' => $backupPath,
                ])
            );
            
            // Stash any local changes
            $stash = Process::path($basePath)->run('git stash');
            
            // Pull latest changes
            $pull = Process::path($basePath)->run('git pull origin main');
            
            if (!$pull->successful()) {
                Log::error('Update failed during git pull', ['output' => $pull->errorOutput()]);
                
                // Restore stashed changes
                Process::path($basePath)->run('git stash pop');
                
                return [
                    'success' => false,
                    'message' => 'Failed to pull updates: ' . $pull->errorOutput(),
                ];
            }
            
            // Run composer install
            $composer = Process::path($basePath)
                ->timeout(300)
                ->env(['HOME' => $basePath, 'COMPOSER_HOME' => storage_path('composer')])
                ->run('composer install --no-dev --optimize-autoloader');
            
            if (!$composer->successful()) {
                Log::error('Update failed during composer install', ['output' => $composer->errorOutput()]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to install dependencies: ' . $composer->errorOutput(),
                ];
            }
            
            // Run migrations
            $migrate = Process::path($basePath)
                ->timeout(120)
                ->env(['HOME' => $basePath])
                ->run('php artisan migrate --force');
            
            if (!$migrate->successful()) {
                Log::error('Update failed during migrations', ['output' => $migrate->errorOutput()]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to run migrations: ' . $migrate->errorOutput(),
                ];
            }
            
            // Clear and optimize cache
            Process::path($basePath)->env(['HOME' => $basePath])->run('php artisan optimize:clear');
            Process::path($basePath)->env(['HOME' => $basePath])->run('php artisan optimize');
            
            // Restart services to pick up new code
            Process::run('sudo systemctl restart php8.2-fpm');
            Process::run('sudo systemctl restart homedeploy-queue');
            
            $newCommit = trim(Process::path($basePath)->run('git rev-parse HEAD')->output());
            
            Log::info('System updated successfully', [
                'from' => $currentCommit,
                'to' => $newCommit,
            ]);
            
            return [
                'success' => true,
                'message' => 'System updated successfully',
                'from_commit' => substr($currentCommit, 0, 7),
                'to_commit' => substr($newCommit, 0, 7),
            ];
            
        } catch (\Exception $e) {
            Log::error('Update failed with exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ];
        }
    }
}
