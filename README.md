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
curl -sSL https://raw.githubusercontent.com/keithbrinks/HomeDeploy/main/install.sh | sudo bash
```

This script will:
1. Install PHP 8.2+, Composer, Git, Nginx, MySQL, Redis.
2. Clone the HomeDeploy repository.
3. Set up the application and systemd services.
4. Output your admin credentials.

## Configuration

After installation, access the web interface and configure GitHub OAuth:

1. Navigate to **Settings** in the top menu
2. Follow the GitHub OAuth setup instructions on the page
3. Save your credentials - they're stored securely in the database

That's it! You can now connect GitHub and start deploying sites.

## Database Access (TablePlus)

To connect to MySQL databases on your server using TablePlus or other database tools:

1. **Create a new MySQL connection** in TablePlus
2. **Enable "Over SSH" (recommended for security)**:
   - **SSH Host**: Your server's IP address
   - **SSH Port**: 22
   - **SSH User**: Your SSH username
   - **SSH Password/Key**: Your SSH credentials
3. **Database Connection**:
   - **Host**: 127.0.0.1
   - **Port**: 3306
   - **User**: root
   - **Password**: Check the installation output or `/root/mysql-root-credentials.txt` on your server

This method is more secure than exposing MySQL directly to the network, as it tunnels the connection through SSH.

## Local Development (Laravel Herd)

You can run HomeDeploy locally using Laravel Herd for testing and development.

1. **Database Setup**:
   Ensure your `.env` is configured for SQLite (default) and the database file exists:
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

2. **Create Admin User**:
   Create a user to log in with:
   ```bash
   php artisan user:create --name="Admin" --email="admin@localhost" --password="password"
   ```

3. **Access the App**:
   Open `http://homedeploy.test` (or your Herd domain) and login.

4. **Testing Deployments**:
   To test the deployment engine locally without messing up your system:
   - Create a new site with a public repo (e.g., `https://github.com/laravel/laravel.git`).
   - Set the **Deployment Path** to a local writable directory, e.g., `/Users/yourname/Herd/HomeDeploy/storage/app/test-deploy`.
   - Click **Deploy Now** to see the process in action.

## License

HomeDeploy is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
