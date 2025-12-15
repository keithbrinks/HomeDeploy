<?php

namespace App\Actions\System;

use Illuminate\Support\Facades\Process;

class CheckForUpdates
{
    public function execute(): array
    {
        $basePath = base_path();
        
        // Fetch latest changes from remote
        $fetch = Process::path($basePath)->run('git fetch origin');
        
        if (!$fetch->successful()) {
            return [
                'hasUpdates' => false,
                'error' => 'Failed to fetch updates from remote',
            ];
        }
        
        // Get current commit hash
        $currentCommit = trim(Process::path($basePath)->run('git rev-parse HEAD')->output());
        
        // Get remote commit hash
        $remoteCommit = trim(Process::path($basePath)->run('git rev-parse origin/main')->output());
        
        $hasUpdates = $currentCommit !== $remoteCommit;
        
        $commits = [];
        if ($hasUpdates) {
            // Get list of commits between current and remote
            $log = Process::path($basePath)->run('git log --oneline HEAD..origin/main')->output();
            $commits = array_filter(explode("\n", trim($log)));
        }
        
        return [
            'hasUpdates' => $hasUpdates,
            'currentCommit' => substr($currentCommit, 0, 7),
            'remoteCommit' => substr($remoteCommit, 0, 7),
            'commits' => $commits,
        ];
    }
}
