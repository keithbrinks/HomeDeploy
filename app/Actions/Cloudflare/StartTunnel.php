<?php

namespace App\Actions\Cloudflare;

use App\Domains\Cloudflare\CloudflareConfig;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class StartTunnel
{
    public function execute(CloudflareConfig $config): array
    {
        $configPath = "/etc/cloudflared/config-{$config->tunnel_id}.yml";
        
        if (!file_exists($configPath)) {
            return [
                'success' => false,
                'message' => 'Tunnel configuration file not found',
            ];
        }
        
        // Start cloudflared tunnel as a background service
        $command = "cloudflared tunnel --config {$configPath} run {$config->tunnel_id}";
        
        $result = Process::start($command);
        
        if ($result->running()) {
            Log::info("Cloudflare Tunnel started", [
                'tunnel_id' => $config->tunnel_id,
                'hostname' => $config->hostname,
            ]);
            
            return [
                'success' => true,
                'message' => 'Tunnel started successfully',
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to start tunnel',
        ];
    }
}
