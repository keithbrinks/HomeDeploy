<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = Settings::get();
        
        return view('settings.index', [
            'settings' => $settings,
        ]);
    }
    
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'github_client_id' => ['nullable', 'string', 'max:255'],
            'github_client_secret' => ['nullable', 'string', 'max:255'],
            'github_redirect_uri' => ['nullable', 'string', 'url', 'max:255'],
        ]);
        
        $settings = Settings::get();
        
        // Only update secret if a new one is provided
        if (empty($validated['github_client_secret'])) {
            unset($validated['github_client_secret']);
        }
        
        $settings->update($validated);
        
        return redirect()
            ->route('settings.index')
            ->with('success', 'Settings updated successfully');
    }
    
    public function testGithub(): RedirectResponse
    {
        $settings = Settings::get();
        
        if (!$settings->hasGithubOAuth()) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'GitHub OAuth credentials not configured');
        }
        
        // Test the credentials by attempting to get user info
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept' => 'application/json',
            ])->get('https://api.github.com/user', [
                'client_id' => $settings->github_client_id,
            ]);
            
            if ($response->successful()) {
                return redirect()
                    ->route('settings.index')
                    ->with('success', 'GitHub OAuth credentials are valid');
            }
            
            return redirect()
                ->route('settings.index')
                ->with('error', 'GitHub OAuth test failed: Invalid credentials');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'GitHub OAuth test failed: ' . $e->getMessage());
        }
    }
}
