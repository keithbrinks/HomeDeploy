<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Sites\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BuildCommandsController extends Controller
{
    public function edit(Site $site): View
    {
        return view('sites.build-commands', [
            'site' => $site,
            'commonPresets' => $this->getCommonPresets(),
        ]);
    }

    public function update(Site $site, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'commands' => 'required|array|min:1',
            'commands.*' => 'required|string|max:500',
        ]);

        try {
            $site->update([
                'build_commands' => array_values(array_filter($validated['commands'])),
            ]);

            Log::info('Build commands updated', [
                'site_id' => $site->id,
                'commands_count' => count($validated['commands']),
            ]);

            return redirect()
                ->route('sites.show', $site)
                ->with('success', 'Build commands updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update build commands', [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update build commands. Please try again.');
        }
    }

    private function getCommonPresets(): array
    {
        return [
            'Laravel' => [
                'composer install --no-dev --optimize-autoloader',
                'php artisan config:cache',
                'php artisan route:cache',
                'php artisan view:cache',
            ],
            'Laravel + Vite' => [
                'composer install --no-dev --optimize-autoloader',
                'npm install',
                'npm run build',
                'php artisan config:cache',
                'php artisan route:cache',
                'php artisan view:cache',
            ],
            'Node.js / React' => [
                'npm install',
                'npm run build',
            ],
            'Next.js' => [
                'npm install',
                'npm run build',
            ],
            'Static Site' => [
                'npm install',
                'npm run build',
            ],
        ];
    }
}
