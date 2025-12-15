<?php

declare(strict_types=1);

namespace App\Domains\Server\Actions;

use App\Domains\Sites\Site;
use Illuminate\Support\Facades\File;

class GenerateNginxConfigAction
{
    public function execute(Site $site): string
    {
        $serverName = str_replace(['http://', 'https://'], '', $site->repo_url);
        $serverName = parse_url('http://' . $serverName, PHP_URL_HOST) ?? 'localhost';
        $siteName = $site->name;
        
        $config = <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$siteName}.local;
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
        
        // Write config file
        File::put($configPath, $config);
        
        // Create symlink
        $enabledPath = "/etc/nginx/sites-enabled/{$siteName}";
        if (! file_exists($enabledPath)) {
            symlink($configPath, $enabledPath);
        }

        $site->update(['nginx_config_path' => $configPath]);

        return $configPath;
    }
}
