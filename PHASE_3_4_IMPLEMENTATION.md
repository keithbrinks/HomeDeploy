# Phase 3-4 Features Implementation

This document outlines the newly implemented Phase 3 and Phase 4 features in HomeDeploy.

## Phase 3 - Enhanced Features

### ✅ Build Command Configuration UI

**Purpose**: Configure custom commands that run during deployment for each site.

**Location**: Site Detail → Build Commands button

**Features**:
- Define unlimited custom build commands
- Pre-configured presets for common frameworks:
  - Laravel (composer install, artisan cache commands)
  - Laravel + Vite (includes npm install/build)
  - Node.js/React (npm install/build)
  - Next.js (npm install/build)
  - Static Sites (npm install/build)
- Commands execute in order during deployment
- Dynamic command management with Alpine.js
- Persisted as JSON in `sites.build_commands` column

**Files**:
- `app/Http/Controllers/BuildCommandsController.php` - Controller with edit/update
- `resources/views/sites/build-commands.blade.php` - UI with dynamic command fields
- Routes: `GET /sites/{site}/build-commands`, `PUT /sites/{site}/build-commands`

**Usage**:
1. Navigate to site detail page
2. Click "Build Commands" button
3. Add custom commands or load a preset
4. Commands will execute during next deployment

---

### ✅ Server Resource Monitoring

**Purpose**: Real-time monitoring of server resources (CPU, Memory, Disk, Uptime).

**Location**: Dashboard (top section) 

**Features**:
- CPU usage percentage with load averages (1m, 5m, 15m)
- Memory usage with used/total display
- Disk usage with used/total display  
- System uptime in human-readable format
- Auto-refresh every 5 seconds via Alpine.js polling
- Color-coded progress bars (green < 60%, yellow < 80%, red ≥ 80%)
- Works on both macOS (Herd) and Linux servers

**Files**:
- `app/Actions/Server/GetServerMetrics.php` - Server metrics collection
- `app/Http/Controllers/ServerMetricsController.php` - API endpoint
- `resources/views/components/server-metrics.blade.php` - Metrics widget
- Route: `GET /api/server-metrics`

**Technical Details**:
- Uses `/proc` filesystem on Linux
- Uses `sysctl` and `vm_stat` on macOS
- No external dependencies required
- Responsive grid layout (4 cards)

---

### ✅ Self-Update System

**Purpose**: Update HomeDeploy itself from GitHub without SSH access.

**Location**: Navigation → System → System Update

**Features**:
- Check for updates from GitHub repository
- View commit log of pending updates
- One-click update with automatic backup
- Runs `git pull`, `composer install`, `php artisan migrate`
- Automatic cache clearing and optimization
- Backup created before update in `storage/backups/`
- Detailed error handling and logging

**Files**:
- `app/Actions/System/CheckForUpdates.php` - Check GitHub for new commits
- `app/Actions/System/PerformUpdate.php` - Execute update process
- `app/Http/Controllers/SystemUpdateController.php` - Controller
- `resources/views/system/update.blade.php` - Update UI
- Routes: `GET /system/update`, `GET /system/update/check`, `POST /system/update`

**Update Process**:
1. Fetches latest changes from `origin/main`
2. Creates backup info in `storage/backups/update-info.json`
3. Stashes local changes (if any)
4. Pulls latest code
5. Runs `composer install --no-dev --optimize-autoloader`
6. Runs database migrations
7. Clears and rebuilds cache

**Safety**:
- Current commit hash saved for rollback
- Sites continue running during update
- Error messages displayed if update fails
- Detailed logging to Laravel log

---

## Phase 4 - Advanced Features

### ✅ Cloudflare Tunnel Management

**Purpose**: Expose sites publicly via Cloudflare Tunnel without port forwarding.

**Location**: Site Detail → Cloudflare button

**Features**:
- Configure Cloudflare Tunnel per site
- Store tunnel credentials securely (encrypted)
- Generate `config.yml` and credentials JSON automatically
- Start/Stop tunnel from UI
- Monitor tunnel status (Running/Stopped)
- Support for custom service URLs
- Public hostname configuration
- Automatic cleanup on deletion

**Files**:
- `app/Domains/Cloudflare/CloudflareConfig.php` - Model with encrypted token
- `app/Actions/Cloudflare/GenerateTunnelConfig.php` - Config file generation
- `app/Actions/Cloudflare/StartTunnel.php` - Start cloudflared process
- `app/Actions/Cloudflare/StopTunnel.php` - Stop cloudflared process
- `app/Http/Controllers/CloudflareController.php` - CRUD controller
- `resources/views/cloudflare/edit.blade.php` - Configuration UI
- Migration: `2025_12_15_120101_create_cloudflare_configs_table.php`
- Routes: `GET|POST /sites/{site}/cloudflare`, `POST /sites/{site}/cloudflare/start|stop`, `DELETE /sites/{site}/cloudflare`

**Required Fields**:
- Tunnel ID (UUID from Cloudflare)
- Tunnel Name (friendly name)
- Tunnel Token (authentication token)
- Account ID (Cloudflare account ID)
- Public Hostname (e.g., `myapp.example.com`)
- Service URL (optional, defaults to `http://localhost:{port}`)

**Setup Instructions** (in UI):
1. Install `cloudflared` CLI tool
2. Create tunnel in Cloudflare Zero Trust Dashboard
3. Copy tunnel credentials
4. Configure in HomeDeploy
5. Start tunnel

**Configuration Files**:
- `/etc/cloudflared/config-{tunnel_id}.yml` - Tunnel config
- `/etc/cloudflared/{tunnel_id}.json` - Credentials file (600 permissions)

**Database Schema**:
```sql
CREATE TABLE cloudflare_configs (
    id INTEGER PRIMARY KEY,
    site_id INTEGER NOT NULL,
    tunnel_id VARCHAR UNIQUE,
    tunnel_name VARCHAR,
    tunnel_token TEXT (encrypted),
    account_id VARCHAR,
    hostname VARCHAR,
    service_url VARCHAR DEFAULT 'http://localhost:3000',
    enabled BOOLEAN DEFAULT 1,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY(site_id) REFERENCES sites(id) ON DELETE CASCADE
);
```

---

## Summary of Changes

### New Controllers
1. `BuildCommandsController` - Manage build commands
2. `ServerMetricsController` - API for server metrics
3. `SystemUpdateController` - Self-update system
4. `CloudflareController` - Cloudflare Tunnel management

### New Actions
1. `GetServerMetrics` - Collect CPU/RAM/Disk/Uptime
2. `CheckForUpdates` - Check GitHub for updates
3. `PerformUpdate` - Execute update process
4. `GenerateTunnelConfig` - Create Cloudflare config files
5. `StartTunnel` - Start cloudflared process
6. `StopTunnel` - Stop cloudflared process

### New Models
1. `CloudflareConfig` - Cloudflare Tunnel configuration

### New Views/Components
1. `sites/build-commands.blade.php` - Build commands editor
2. `components/server-metrics.blade.php` - Metrics dashboard widget
3. `system/update.blade.php` - Update page
4. `cloudflare/edit.blade.php` - Tunnel configuration

### Updated Views
1. `dashboard.blade.php` - Added server metrics
2. `sites/show.blade.php` - Added "Build Commands" and "Cloudflare" buttons
3. `components/layouts/app.blade.php` - Added "System" nav link

### New Routes
- `GET /sites/{site}/build-commands` - Edit build commands
- `PUT /sites/{site}/build-commands` - Update build commands
- `GET /api/server-metrics` - Server metrics JSON
- `GET /system/update` - Update page
- `GET /system/update/check` - Check for updates API
- `POST /system/update` - Perform update
- `GET /sites/{site}/cloudflare` - Cloudflare config page
- `POST /sites/{site}/cloudflare` - Save/update config
- `POST /sites/{site}/cloudflare/start` - Start tunnel
- `POST /sites/{site}/cloudflare/stop` - Stop tunnel
- `DELETE /sites/{site}/cloudflare` - Delete config

### Database Changes
- `cloudflare_configs` table already exists (from earlier migration)
- `sites.build_commands` column (JSON, nullable)

---

## Testing Recommendations

### Build Commands
1. Create a new site
2. Configure build commands with a preset
3. Add custom commands
4. Deploy and verify commands execute

### Server Monitoring
1. View dashboard
2. Verify metrics update every 5 seconds
3. Check CPU/Memory/Disk percentages
4. Verify color coding at different thresholds

### Self-Update
1. Navigate to System → Update
2. Click "Check for Updates"
3. If updates available, review commit log
4. Click "Install Update"
5. Verify backup created in `storage/backups/`
6. Check migrations ran successfully

### Cloudflare Tunnel
1. Install `cloudflared` on server
2. Create tunnel in Cloudflare Dashboard
3. Configure tunnel in HomeDeploy
4. Start tunnel
5. Verify hostname is accessible
6. Stop tunnel
7. Delete configuration

---

## Security Notes

1. **Encrypted Fields**: 
   - `CloudflareConfig.tunnel_token` uses Laravel's encrypted cast
   - Stored securely in database

2. **File Permissions**:
   - Cloudflare credentials files set to 600 (owner read/write only)

3. **Validation**:
   - Hostname validated with regex (valid domain format)
   - Service URL validated as proper URL

4. **Process Management**:
   - Tunnel processes managed via `pgrep` and `kill`
   - No shell injection vulnerabilities (uses Process facade)

---

## Future Enhancements (Not Yet Implemented)

From the original roadmap, these features are still pending:

### Phase 3
- ❌ SSL certificate management (Let's Encrypt)
- ❌ Multi-user support with roles/permissions
- ❌ Deployment notifications (email, Slack)

### Phase 4
- ❌ Cron job management UI
- ❌ Backup/restore functionality
- ❌ Deployment scheduling
- ❌ Blue/green deployments

These can be implemented in future iterations following the same patterns established in this implementation.
