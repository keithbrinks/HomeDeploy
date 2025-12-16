<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Server\Actions\CreateDatabaseAction;
use App\Domains\Server\Actions\DropDatabaseAction;
use App\Domains\Sites\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class DatabasesController extends Controller
{
    public function store(Site $site, CreateDatabaseAction $action): RedirectResponse
    {
        if ($site->database_name) {
            return back()->with('error', 'Database already exists for this site.');
        }

        try {
            DB::beginTransaction();

            $credentials = $action->execute($site->name);

            $site->update([
                'database_name' => $credentials['database'],
                'database_username' => $credentials['username'],
                'database_password' => Crypt::encryptString($credentials['password']),
            ]);

            // Update .env file with database credentials
            $this->updateEnvFile($site, $credentials);

            DB::commit();

            session()->flash('database_credentials', $credentials);

            return back()->with('success', 'Database created successfully and credentials added to .env file.');
        } catch (\RuntimeException $e) {
            DB::rollBack();
            Log::error('Failed to create database', [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unexpected error creating database', [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function destroy(Site $site, DropDatabaseAction $action): RedirectResponse
    {
        if (!$site->database_name) {
            return back()->with('error', 'No database configured for this site.');
        }

        try {
            DB::beginTransaction();

            $action->execute($site->database_name, $site->database_username);

            $site->update([
                'database_name' => null,
                'database_username' => null,
                'database_password' => null,
            ]);

            DB::commit();

            return back()->with('success', 'Database dropped successfully.');
        } catch (\RuntimeException $e) {
            DB::rollBack();
            Log::error('Failed to drop database', [
                'site_id' => $site->id,
                'database' => $site->database_name,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unexpected error dropping database', [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    private function updateEnvFile(Site $site, array $credentials): void
    {
        $envPath = $site->deploy_path . '/.env';
        
        if (!file_exists($envPath)) {
            return; // No .env file yet, will be created on first deployment
        }

        $envContent = file_get_contents($envPath);
        
        // Update or add database credentials
        $updates = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $credentials['host'],
            'DB_PORT' => '3306',
            'DB_DATABASE' => $credentials['database'],
            'DB_USERNAME' => $credentials['username'],
            'DB_PASSWORD' => $credentials['password'],
        ];

        foreach ($updates as $key => $value) {
            // Check if key exists in .env
            if (preg_match("/^{$key}=/m", $envContent)) {
                // Update existing value
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Add new line
                $envContent .= "\n{$key}={$value}";
            }
        }

        // Write to temporary file first
        $tempPath = storage_path('app/temp-env-' . $site->id);
        file_put_contents($tempPath, $envContent);
        
        // Use sudo to copy the file
        $result = Process::run("sudo cp '{$tempPath}' '{$envPath}'");
        if ($result->failed()) {
            unlink($tempPath);
            throw new \RuntimeException('Failed to update .env file: ' . $result->errorOutput());
        }
        
        // Set proper permissions
        Process::run("sudo chmod 644 '{$envPath}'");
        Process::run("sudo chown www-data:www-data '{$envPath}'");
        
        unlink($tempPath);
    }

    public function sync(Site $site): RedirectResponse
    {
        if (!$site->database_name) {
            return back()->with('error', 'No database configured for this site.');
        }

        try {
            $credentials = [
                'database' => $site->database_name,
                'username' => $site->database_username,
                'password' => $site->getDatabasePassword(),
                'host' => 'localhost',
            ];

            $this->updateEnvFile($site, $credentials);

            return back()->with('success', 'Database credentials synced to .env file.');
        } catch (\Exception $e) {
            Log::error('Failed to sync database credentials', [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to sync credentials: ' . $e->getMessage());
        }
    }
