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
            'homedeploy_domain' => ['nullable', 'string', 'max:255'],
            'server_ip' => ['nullable', 'string', 'ip'],
            'sites_base_domain' => ['nullable', 'string', 'max:255'],
            'sites_local_suffix' => ['nullable', 'string', 'max:50'],
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

    public function regenerateNginx(): RedirectResponse
    {
        $settings = Settings::get();
        
        if (!$settings->homedeploy_domain) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Please set HomeDeploy domain first');
        }

        try {
            $config = <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$settings->homedeploy_domain};

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_redirect off;
    }
}
NGINX;

            $configPath = "/etc/nginx/sites-available/homedeploy";
            $tempPath = storage_path("app/nginx-homedeploy.conf");
            \Illuminate\Support\Facades\File::put($tempPath, $config);
            
            $result = \Illuminate\Support\Facades\Process::run("sudo cp '$tempPath' '$configPath'");
            if ($result->failed()) {
                \Illuminate\Support\Facades\File::delete($tempPath);
                throw new \RuntimeException("Failed to write Nginx config: " . $result->errorOutput());
            }
            
            \Illuminate\Support\Facades\File::delete($tempPath);
            
            // Test and reload Nginx
            $testResult = \Illuminate\Support\Facades\Process::run("sudo nginx -t");
            if ($testResult->failed()) {
                throw new \RuntimeException("Nginx config test failed: " . $testResult->errorOutput());
            }
            
            $reloadResult = \Illuminate\Support\Facades\Process::run("sudo systemctl reload nginx");
            if ($reloadResult->failed()) {
                throw new \RuntimeException("Failed to reload Nginx: " . $reloadResult->errorOutput());
            }

            return redirect()
                ->route('settings.index')
                ->with('success', 'HomeDeploy Nginx configuration updated successfully');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Failed to update Nginx config: ' . $e->getMessage());
        }
    }
}
