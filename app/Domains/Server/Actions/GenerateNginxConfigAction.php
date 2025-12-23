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
        // Get domain without http:// prefix for server_name
        $fullDomain = $site->getFullDomain();
        $serverName = str_replace(['http://', 'https://'], '', $fullDomain);
        $siteName = $site->name;
        
        // Detect project type to determine the correct config
        $projectType = $this->detectProjectType($site->deploy_path);
        $config = $this->generateConfigForType($site, $serverName, $projectType);
        

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

    private function detectProjectType(string $deployPath): string
    {
        // Check if path exists
        if (!is_dir($deployPath)) {
            return 'static'; // Default to static if path doesn't exist yet
        }

        // Laravel - has artisan and public directory
        if (file_exists("{$deployPath}/artisan") && is_dir("{$deployPath}/public")) {
            return 'laravel';
        }

        // Check for package.json to detect Node.js projects
        if (file_exists("{$deployPath}/package.json")) {
            $packageJson = json_decode(file_get_contents("{$deployPath}/package.json"), true);
            
            // Next.js
            if (isset($packageJson['dependencies']['next']) || isset($packageJson['devDependencies']['next'])) {
                return 'nextjs';
            }
            
            // Vue/React with dist folder (built static)
            if (is_dir("{$deployPath}/dist")) {
                return 'static-dist';
            }
            
            // Vue/React with build folder
            if (is_dir("{$deployPath}/build")) {
                return 'static-build';
            }
        }

        // Check for index.html in root (simple static site)
        if (file_exists("{$deployPath}/index.html")) {
            return 'static';
        }

        // Check for public/index.html
        if (file_exists("{$deployPath}/public/index.html")) {
            return 'static-public';
        }

        // Default to static HTML
        return 'static';
    }

    private function generateConfigForType(Site $site, string $serverName, string $projectType): string
    {
        return match($projectType) {
            'laravel' => $this->generateLaravelConfig($site, $serverName),
            'nextjs' => $this->generateNextJsConfig($site, $serverName),
            'static-dist' => $this->generateStaticConfig($site, $serverName, 'dist'),
            'static-build' => $this->generateStaticConfig($site, $serverName, 'build'),
            'static-public' => $this->generateStaticConfig($site, $serverName, 'public'),
            default => $this->generateStaticConfig($site, $serverName, '.'),
        };
    }

    private function generateLaravelConfig(Site $site, string $serverName): string
    {
        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$serverName};
    root {$site->deploy_path}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

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
    }

    private function generateStaticConfig(Site $site, string $serverName, string $subdir = '.'): string
    {
        $rootPath = $subdir === '.' ? $site->deploy_path : "{$site->deploy_path}/{$subdir}";
        
        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$serverName};
    root {$rootPath};

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Optional: Support for client-side routing (uncomment if needed for SPAs)
    # location / {
    #     try_files \$uri \$uri/ /index.html;
    # }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX;
    }

    private function generateNextJsConfig(Site $site, string $serverName): string
    {
        $port = $site->port ?? 3000;
        
        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$serverName};

    location / {
        proxy_pass http://127.0.0.1:{$port};
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
NGINX;
    }
}
