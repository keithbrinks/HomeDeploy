# Quick Setup Guide for HomeDeploy (Local Development)

## Prerequisites
- Laravel Herd installed (or Laravel Valet/Homestead)
- PHP 8.2+
- Composer

## Step-by-Step Setup

### 1. Clone and Install Dependencies
```bash
cd ~/Herd/HomeDeploy  # or your Herd/Valet directory
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup
```bash
touch database/database.sqlite
php artisan migrate
```

### 4. Create Admin User
```bash
php artisan user:create --name="Admin" --email="admin@localhost" --password="password"
```

### 5. Configure GitHub OAuth (Optional but Recommended)

**Without GitHub OAuth**, you can still create sites manually by entering repository URLs.

**With GitHub OAuth**, you get:
- Repository browsing from your GitHub account
- Auto-populated site details
- Branch selection dropdown
- Private repository access

#### Setup Steps:
1. Go to https://github.com/settings/developers
2. Click **"New OAuth App"**
3. Fill in:
   - **Application name**: `HomeDeploy Local`
   - **Homepage URL**: `http://homedeploy.test`
   - **Callback URL**: `http://homedeploy.test/auth/github/callback`
4. Click **"Register application"**
5. Copy the **Client ID**
6. Click **"Generate a new client secret"** and copy it
7. Add to your `.env` file:
   ```env
   GITHUB_CLIENT_ID=your_client_id_here
   GITHUB_CLIENT_SECRET=your_client_secret_here
   GITHUB_REDIRECT_URI=http://homedeploy.test/auth/github/callback
   ```
8. Clear config cache:
   ```bash
   php artisan config:clear
   ```

### 6. Access the Application
Open your browser and visit: http://homedeploy.test

**Login credentials:**
- Email: `admin@localhost`
- Password: `password`

### 7. Connect GitHub (if configured)
1. After logging in, click **"Connect GitHub"** on the dashboard
2. Authorize the application
3. You'll be redirected back and can now browse your repositories

### 8. Create Your First Site
1. Click **"New Site"** on the dashboard
2. If GitHub is connected: select a repository from the list
3. If not connected: manually enter repository URL, branch, and deployment path
4. Click **"Create Site"**

**Note for local testing:** Set the deployment path to a local directory like:
- `/Users/yourname/Herd/HomeDeploy/storage/app/test-deploy`

This keeps test deployments contained and won't affect your system.

## Troubleshooting

### "GitHub OAuth is not configured" error
- Make sure `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET` are set in `.env`
- Run `php artisan config:clear`
- Verify the callback URL matches exactly in GitHub settings

### "Connect GitHub" link goes to 404
- GitHub OAuth credentials are missing or incorrect
- Check your GitHub OAuth App is not suspended
- Verify the Client ID and Secret in `.env`

### Form fields are blank after selecting a repository
- This is expected - Alpine.js fetches branches asynchronously
- Wait a moment for the branch dropdown to populate
- Check browser console for JavaScript errors

### Database permission errors
- Ensure `database/database.sqlite` exists and is writable
- Run `chmod 664 database/database.sqlite` if needed

## Next Steps

Once you have a site created:
- **Deploy manually**: Click "Deploy Now" on the site detail page
- **Set up webhook**: Configure GitHub webhook for auto-deployments
- **Add environment variables**: Set up app-specific env vars
- **Generate Nginx config**: Auto-generate web server configuration
- **Create database**: One-click MySQL database creation

## Testing Deployments Locally

To test the deployment engine without affecting your system:

1. Create a test directory:
   ```bash
   mkdir -p storage/app/test-deploy
   chmod -R 775 storage/app/test-deploy
   ```

2. Create a site with:
   - **Repository URL**: Any public repo (e.g., `https://github.com/laravel/laravel.git`)
   - **Branch**: `main` or `master`
   - **Deployment Path**: `/Users/yourname/Herd/HomeDeploy/storage/app/test-deploy`

3. Click **"Deploy Now"** and watch the live logs!

## Running Tests

```bash
php artisan test
```

All tests should pass. If not, check that migrations have run successfully.

## Need Help?

- Check the [README.md](README.md) for full documentation
- Review [CODE_REVIEW.md](CODE_REVIEW.md) for architecture insights
- See [PROGRESS.md](PROGRESS.md) for feature status
