<?php

namespace App\Actions\Cloudflare;

use App\Domains\Cloudflare\CloudflareConfig;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class StopTunnel
{
    public function execute(CloudflareConfig $config): array
    {
        // Find and kill the cloudflared process for this tunnel
        $pidResult = Process::run("pgrep -f 'cloudflared.*{$config->tunnel_id}'");
        
        if ($pidResult->successful() && !empty(trim($pidResult->output()))) {
            $pid = trim($pidResult->output());
            
            $killResult = Process::run("kill {$pid}");
            
            if ($killResult->successful()) {
                Log::info("Cloudflare Tunnel stopped", [
                    'tunnel_id' => $config->tunnel_id,
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Tunnel stopped successfully',
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Tunnel process not found or failed to stop',
        ];
    }
}
