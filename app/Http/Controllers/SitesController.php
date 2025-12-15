<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Sites\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SitesController extends Controller
{
    public function create(): View
    {
        return view('sites.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'repo_url' => 'required|url',
            'branch' => 'required|string|max:255',
            'deploy_path' => 'required|string|max:255',
        ]);

        Site::create($validated);

        return redirect()->route('dashboard');
    }

    public function show(Site $site): View
    {
        $site->load(['deployments' => function ($query) {
            $query->latest();
        }]);

        return view('sites.show', ['site' => $site]);
    }
}
