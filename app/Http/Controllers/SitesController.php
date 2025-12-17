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
            
            // Validate base_domain is set
            if (!$settings->base_domain) {
                return back()
                    ->withInput()
                    ->with('error', 'Please configure a base domain in Settings first.');
            }
            
            // Build full domain from subdomain prefix + base domain
            $fullDomain = $data['domain'] . '.' . $settings->base_domain;
            $data['domain'] = $fullDomain;
            $data['domain_strategy'] = 'subdomain';
            
            $site = Site::create($data);

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

    public function updateDomain(Request $request, Site $site): RedirectResponse
    {
        $validated = $request->validate([
            'domain_strategy' => 'required|in:subdomain,custom',
            'custom_domain' => 'nullable|string',
        ]);

        $settings = \App\Models\Settings::get();
        
        // Calculate new domain based on strategy
        $newDomain = $settings->getSiteDomain(
            $site->name,
            $validated['domain_strategy'],
            $validated['custom_domain'] ?? null
        );

        // Update site
        $site->update([
            'domain_strategy' => $validated['domain_strategy'],
            'domain' => $newDomain,
        ]);

        // Regenerate Nginx config with new domain
        $generateNginxAction = app(\App\Domains\Server\Actions\GenerateNginxConfigAction::class);
        $generateNginxAction->execute($site);

        return redirect()->route('sites.show', $site)->with('success', 'Domain configuration updated! Nginx config regenerated.');
    }
}
