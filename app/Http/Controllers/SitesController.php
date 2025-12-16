<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Identity\Actions\FetchGithubBranchesAction;
use App\Domains\Identity\Actions\FetchGithubRepositoriesAction;
use App\Domains\Sites\Site;
use App\Http\Requests\StoreSiteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SitesController extends Controller
{
    public function create(FetchGithubRepositoriesAction $action): View
    {
        $repositories = [];
        $settings = \App\Models\Settings::get();

        if ($settings->hasGithubToken()) {
            try {
                $repositories = $action->execute($settings->github_token);
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to load GitHub repositories. Please reconnect your account.');
            }
        }

        return view('sites.create', [
            'repositories' => $repositories,
            'hasGithub' => $settings->hasGithubToken(),
            'settings' => $settings,
        ]);
    }

    public function branches(Request $request, FetchGithubBranchesAction $action): JsonResponse
    {
        $validated = $request->validate([
            'owner' => 'required|string',
            'repo' => 'required|string',
        ]);

        $settings = \App\Models\Settings::get();

        if (! $settings->hasGithubToken()) {
            return response()->json(['error' => 'GitHub not connected'], 401);
        }

        try {
            $branches = $action->execute(
                $settings->github_token,
                $validated['owner'],
                $validated['repo']
            );

            return response()->json(['branches' => $branches]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch branches'], 500);
        }
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $settings = \App\Models\Settings::get();
            
            // Set the domain based on the selected strategy
            $data['domain'] = $settings->getSiteDomain(
                $data['name'],
                $data['domain_strategy'],
                $data['domain'] ?? null
            );
            
            $site = Site::create($data);
            
            // If local domain strategy, add to /etc/hosts
            if ($data['domain_strategy'] === 'local') {
                $this->addToHosts($site);
            }

            return redirect()->route('dashboard')->with('success', 'Site created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create site', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create site. Please try again.');
        }
    }

    private function addToHosts(Site $site): void
    {
        $settings = \App\Models\Settings::get();
        $serverIp = $settings->getServerIp();
        
        if (!$serverIp) {
            return;
        }
        
        $entry = "{$serverIp}\t{$site->domain}";
        $hostsPath = '/etc/hosts';
        
        // Check if entry already exists
        $currentHosts = @file_get_contents($hostsPath);
        if ($currentHosts && str_contains($currentHosts, $entry)) {
            return;
        }
        
        // Add entry using sudo
        $tempPath = storage_path('app/temp-hosts-' . $site->id);
        file_put_contents($tempPath, "\n{$entry}\n");
        
        $result = \Illuminate\Support\Facades\Process::run("sudo bash -c 'cat {$tempPath} >> {$hostsPath}'");
        
        @unlink($tempPath);
        
        if ($result->failed()) {
            Log::warning('Failed to add hosts entry', [
                'site' => $site->name,
                'error' => $result->errorOutput(),
            ]);
        }
    }

    public function show(Site $site): View
    {
        $site->load([
            'deployments' => function ($query) {
                $query->latest();
            },
            'environmentVariables'
        ]);

        return view('sites.show', ['site' => $site]);
    }
}
