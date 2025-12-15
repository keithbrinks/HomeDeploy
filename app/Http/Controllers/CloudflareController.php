<?php

namespace App\Http\Controllers;

use App\Actions\Cloudflare\GenerateTunnelConfig;
use App\Actions\Cloudflare\StartTunnel;
use App\Actions\Cloudflare\StopTunnel;
use App\Domains\Cloudflare\CloudflareConfig;
use App\Domains\Sites\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CloudflareController extends Controller
{
    public function edit(Site $site): View
    {
        $config = CloudflareConfig::where('site_id', $site->id)->first();
        
        return view('cloudflare.edit', [
            'site' => $site,
            'config' => $config,
        ]);
    }
    
    public function store(Request $request, Site $site): RedirectResponse
    {
        $validated = $request->validate([
            'tunnel_id' => ['required', 'string'],
            'tunnel_name' => ['required', 'string'],
            'tunnel_token' => ['required', 'string'],
            'account_id' => ['required', 'string'],
            'hostname' => ['required', 'string', 'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*$/i'],
            'service_url' => ['nullable', 'string', 'url'],
        ]);
        
        $config = CloudflareConfig::updateOrCreate(
            ['site_id' => $site->id],
            [
                ...$validated,
                'service_url' => $validated['service_url'] ?? "http://localhost:{$site->port}",
            ]
        );
        
        return redirect()
            ->route('cloudflare.edit', $site)
            ->with('success', 'Cloudflare tunnel configuration saved');
    }
    
    public function start(Site $site, GenerateTunnelConfig $generateConfig, StartTunnel $startTunnel): RedirectResponse
    {
        $config = CloudflareConfig::where('site_id', $site->id)->firstOrFail();
        
        // Generate configuration files
        $generateConfig->execute($config);
        
        // Start the tunnel
        $result = $startTunnel->execute($config);
        
        if ($result['success']) {
            $config->update(['enabled' => true]);
            
            return redirect()
                ->route('cloudflare.edit', $site)
                ->with('success', $result['message']);
        }
        
        return redirect()
            ->route('cloudflare.edit', $site)
            ->with('error', $result['message']);
    }
    
    public function stop(Site $site, StopTunnel $stopTunnel): RedirectResponse
    {
        $config = CloudflareConfig::where('site_id', $site->id)->firstOrFail();
        
        $result = $stopTunnel->execute($config);
        
        if ($result['success']) {
            $config->update(['enabled' => false]);
            
            return redirect()
                ->route('cloudflare.edit', $site)
                ->with('success', $result['message']);
        }
        
        return redirect()
            ->route('cloudflare.edit', $site)
            ->with('error', $result['message']);
    }
    
    public function destroy(Site $site): RedirectResponse
    {
        $config = CloudflareConfig::where('site_id', $site->id)->firstOrFail();
        
        // Stop tunnel if it's running
        if ($config->enabled) {
            app(StopTunnel::class)->execute($config);
        }
        
        // Clean up config files
        $configPath = "/etc/cloudflared/config-{$config->tunnel_id}.yml";
        $credentialsPath = "/etc/cloudflared/{$config->tunnel_id}.json";
        
        if (file_exists($configPath)) {
            unlink($configPath);
        }
        
        if (file_exists($credentialsPath)) {
            unlink($credentialsPath);
        }
        
        $config->delete();
        
        return redirect()
            ->route('cloudflare.edit', $site)
            ->with('success', 'Cloudflare tunnel configuration deleted');
    }
}
