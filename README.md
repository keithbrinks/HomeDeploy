# HomeDeploy

A lightweight, self-hosted deployment dashboard for single-server homelabs. Installable via curl, built with Laravel, focused on GitHub integration and Cloudflare tunnel management.

## Vision

Create a dead-simple alternative to Laravel Forge/VitoDeploy that assumes it's running on the **same server** it's managing. No SSH to remote servers, no multi-cloud provider abstractions—just a clean control panel for deploying and managing PHP apps on your homelab server.

## Key Features

- **One-line Install**: Set up a fresh server with a single command.
- **Server Management**: Install/switch PHP versions, manage Nginx/MySQL/Redis services.
- **GitHub Integration**: Connect via OAuth to browse repos and auto-deploy on push.
- **Cloudflare Tunnels**: Built-in management for exposing sites securely.
- **Self-Updating**: The dashboard keeps itself up to date.

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Alpine.js + Blade + Tailwind CSS
- **Database**: SQLite (for the manager itself)
- **Queue**: Database driver

## Installation

**Requirements**: Ubuntu 24.04 LTS (Recommended)

Run the following command on your fresh server as root:

```bash
curl -sSL https://raw.githubusercontent.com/keithbrinks/HomeDeploy/main/install.sh | bash
```

This script will:
1. Install PHP 8.2+, Composer, Git, Nginx, MySQL, Redis.
2. Clone the HomeDeploy repository.
3. Set up the application and systemd services.
4. Output your admin credentials.

## Configuration

### GitHub OAuth
To enable repository browsing and deployments, you need to create a GitHub OAuth App:
1. Go to GitHub Developer Settings > OAuth Apps.
2. Create a new app.
   - **Homepage URL**: `https://manager.yourdomain.com` (or `http://localhost:8080` if tunneling)
   - **Callback URL**: `https://manager.yourdomain.com/auth/github/callback`
3. Add the Client ID and Secret to your `.env` file.

## License

HomeDeploy is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
