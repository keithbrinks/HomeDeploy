<?php

namespace App\Actions\Cloudflare;

use App\Domains\Cloudflare\CloudflareConfig;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class GenerateTunnelConfig
{
    public function execute(CloudflareConfig $config): string
    {
        $configContent = <<<YAML
tunnel: {$config->tunnel_id}
credentials-file: /etc/cloudflared/{$config->tunnel_id}.json

ingress:
  - hostname: {$config->hostname}
    service: {$config->service_url}
  - service: http_status:404
YAML;

        // Store config file
        $configPath = "/etc/cloudflared/config-{$config->tunnel_id}.yml";
        file_put_contents($configPath, $configContent);
        
        // Store credentials file
        $credentialsContent = json_encode([
            'AccountTag' => $config->account_id,
            'TunnelSecret' => base64_encode($config->tunnel_token),
            'TunnelID' => $config->tunnel_id,
        ]);
        
        $credentialsPath = "/etc/cloudflared/{$config->tunnel_id}.json";
        file_put_contents($credentialsPath, $credentialsContent);
        chmod($credentialsPath, 0600);
        
        return $configPath;
    }
}
