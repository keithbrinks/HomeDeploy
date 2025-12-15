<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Sites\EnvironmentVariable;
use App\Domains\Sites\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}
