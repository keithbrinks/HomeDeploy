<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Sites\EnvironmentVariable;
use App\Domains\Sites\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EnvironmentVariablesController extends Controller
{
    public function store(Site $site, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ]);

        $site->environmentVariables()->create($validated);

        return back()->with('success', 'Environment variable added successfully!');
    }

    public function destroy(Site $site, EnvironmentVariable $environmentVariable): RedirectResponse
    {
        if ($environmentVariable->site_id !== $site->id) {
            abort(403);
        }

        $environmentVariable->delete();

        return back()->with('success', 'Environment variable deleted successfully!');
    }

    public function updateEnvFile(Site $site, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $envPath = $site->deploy_path . '/.env';
        
        try {
            // Write to temp file first
            $tempPath = storage_path("app/env-{$site->id}.tmp");
            File::put($tempPath, $validated['content']);
            
            // Copy to site directory with sudo
            $result = \Illuminate\Support\Facades\Process::run("sudo cp '$tempPath' '$envPath'");
            
            if ($result->failed()) {
                File::delete($tempPath);
                throw new \RuntimeException("Failed to update .env file: " . $result->errorOutput());
            }
            
            // Set proper permissions
            \Illuminate\Support\Facades\Process::run("sudo chown www-data:www-data '$envPath'");
            \Illuminate\Support\Facades\Process::run("sudo chmod 644 '$envPath'");
            
            File::delete($tempPath);
            
            return back()->with('success', '.env file updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update .env file: ' . $e->getMessage());
        }
    }
}
