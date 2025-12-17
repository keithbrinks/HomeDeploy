<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class CloudflareTunnelController extends Controller
{
    public function start(): RedirectResponse
    {
        $settings = Settings::get();
        
        if (!$settings->cloudflare_tunnel_token) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Please configure Cloudflare Tunnel token first');
        }
        
        if (!$settings->cloudflare_tunnel_id) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Please configure Tunnel ID first');
        }
        
        if (!$settings->base_domain) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Please configure Base Domain first. This will be the domain where HomeDeploy is accessible.');
        }

        try {
            // Generate config file
            $this->generateConfig($settings);
            
            // Create systemd service if it doesn't exist
            $this->createSystemdService();
            
            // Start the service
            $result = Process::run('sudo systemctl start cloudflared-tunnel');
            if ($result->failed()) {
                throw new \RuntimeException('Failed to start tunnel: ' . $result->errorOutput());
            }
            
            // Enable on boot
            Process::run('sudo systemctl enable cloudflared-tunnel');
            
            // Verify service is actually running
            sleep(2); // Give it a moment to start
            $statusResult = Process::run('sudo systemctl is-active cloudflared-tunnel');
            $isRunning = trim($statusResult->output()) === 'active';
            
            // Update settings
            $settings->update(['cloudflare_tunnel_enabled' => $isRunning]);
            
            if (!$isRunning) {
                // Get detailed status for error message
                $statusDetails = Process::run('sudo systemctl status cloudflared-tunnel');
                throw new \RuntimeException('Tunnel service failed to start. Check logs with: sudo journalctl -u cloudflared-tunnel -n 50');
            }
            
            return redirect()
                ->route('settings.index')
                ->with('success', 'Cloudflare Tunnel started successfully! Configure DNS: CNAME ' . $settings->getTunnelHostname() . ' to ' . $settings->cloudflare_tunnel_id . '.cfargotunnel.com');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Failed to start tunnel: ' . $e->getMessage());
        }
    }
    
    public function stop(): RedirectResponse
    {
        try {
            $result = Process::run('sudo systemctl stop cloudflared-tunnel');
            if ($result->failed()) {
                throw new \RuntimeException('Failed to stop tunnel: ' . $result->errorOutput());
            }
            
            // Disable on boot
            Process::run('sudo systemctl disable cloudflared-tunnel');
            
            // Update settings
            $settings = Settings::get();
            $settings->update(['cloudflare_tunnel_enabled' => false]);
            
            return redirect()
                ->route('settings.index')
                ->with('success', 'Cloudflare Tunnel stopped successfully');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Failed to stop tunnel: ' . $e->getMessage());
        }
    }
    
    private function generateConfig(Settings $settings): void
    {
        if (!$settings->cloudflare_tunnel_id) {
            throw new \RuntimeException('Tunnel ID is required');
        }

        // Save credentials file to /root/.cloudflared/
        $credentialsDir = '/root/.cloudflared';
        $credentialsFile = $credentialsDir . '/' . $settings->cloudflare_tunnel_id . '.json';
        $tempCredentials = storage_path('app/tunnel-credentials.json');
        
        File::put($tempCredentials, $settings->cloudflare_tunnel_token);
        
        // Create directory and copy credentials
        Process::run("sudo mkdir -p $credentialsDir");
        $result = Process::run("sudo cp '$tempCredentials' '$credentialsFile'");
        File::delete($tempCredentials);
        
        if ($result->failed()) {
            throw new \RuntimeException('Failed to save credentials: ' . $result->errorOutput());
        }

        // Generate config.yml
        $hostname = $settings->getTunnelHostname();
        if (!$hostname) {
            throw new \RuntimeException('Base domain is required. Please configure it in settings.');
        }
        
        $configContent = <<<YAML
tunnel: {$settings->cloudflare_tunnel_id}
credentials-file: {$credentialsFile}

ingress:
  - hostname: {$hostname}
    service: http://localhost:8080
  - service: http_status:404
YAML;
        
        $configPath = '/etc/cloudflared/config.yml';
        $tempPath = storage_path('app/cloudflared-config.yml');
        
        File::put($tempPath, $configContent);
        
        // Create directory if it doesn't exist
        Process::run('sudo mkdir -p /etc/cloudflared');
        
        // Copy config
        $result = Process::run("sudo cp '$tempPath' '$configPath'");
        if ($result->failed()) {
            File::delete($tempPath);
            throw new \RuntimeException('Failed to write config: ' . $result->errorOutput());
        }
        
        File::delete($tempPath);
    }
    
    private function createSystemdService(): void
    {
        $serviceContent = <<<'SERVICE'
[Unit]
Description=Cloudflare Tunnel
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/local/bin/cloudflared tunnel --config /etc/cloudflared/config.yml run
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
SERVICE;

        $servicePath = '/etc/systemd/system/cloudflared-tunnel.service';
        $tempPath = storage_path('app/cloudflared-tunnel.service');
        
        File::put($tempPath, $serviceContent);
        
        $result = Process::run("sudo cp '$tempPath' '$servicePath'");
        if ($result->failed()) {
            File::delete($tempPath);
            throw new \RuntimeException('Failed to create service: ' . $result->errorOutput());
        }
        
        File::delete($tempPath);
        
        // Reload systemd
        Process::run('sudo systemctl daemon-reload');
    }
}
