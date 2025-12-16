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
            'server_ip' => ['nullable', 'string', 'ip'],
            'base_domain' => ['nullable', 'string', 'max:255'],
            'cloudflare_tunnel_id' => ['nullable', 'string', 'max:255'],
            'cloudflare_tunnel_token' => ['nullable', 'string'],
        ]);
        
        $settings = Settings::get();
        
        // Only update secrets if new ones are provided
        if (empty($validated['github_client_secret'])) {
            unset($validated['github_client_secret']);
        }
        if (empty($validated['cloudflare_tunnel_token'])) {
            unset($validated['cloudflare_tunnel_token']);
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
