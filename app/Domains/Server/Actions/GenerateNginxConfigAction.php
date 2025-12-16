<?php

declare(strict_types=1);

namespace App\Domains\Server\Actions;

use App\Domains\Sites\Site;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class GenerateNginxConfigAction
{
    public function execute(Site $site): string
    {
        $serverName = $site->getFullDomain();
        $siteName = $site->name;
        
        $config = <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$serverName};
    root {$site->deploy_path}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX;

        $configPath = "/etc/nginx/sites-available/{$siteName}";
        
        // Write config file using sudo
        $tempPath = storage_path("app/nginx-{$siteName}.conf");
        File::put($tempPath, $config);
        
        $result = Process::run("sudo cp '$tempPath' '$configPath'");
        if ($result->failed()) {
            File::delete($tempPath);
            throw new \RuntimeException("Failed to write Nginx config: " . $result->errorOutput());
        }
        
        File::delete($tempPath);
        
        // Create symlink using sudo
        $enabledPath = "/etc/nginx/sites-enabled/{$siteName}";
        if (! file_exists($enabledPath)) {
            $linkResult = Process::run("sudo ln -sf '$configPath' '$enabledPath'");
            if ($linkResult->failed()) {
                throw new \RuntimeException("Failed to create symlink: " . $linkResult->errorOutput());
            }
        }
        
        // Test and reload Nginx
        $testResult = Process::run("sudo nginx -t");
        if ($testResult->failed()) {
            throw new \RuntimeException("Nginx config test failed: " . $testResult->errorOutput());
        }
        
        $reloadResult = Process::run("sudo systemctl reload nginx");
        if ($reloadResult->failed()) {
            throw new \RuntimeException("Failed to reload Nginx: " . $reloadResult->errorOutput());
        }

        $site->update(['nginx_config_path' => $configPath]);

        return $configPath;
    }
}
