<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Server\Actions\CreateDatabaseAction;
use App\Domains\Server\Actions\DropDatabaseAction;
use App\Domains\Sites\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            DB::commit();

            session()->flash('database_credentials', $credentials);

            return back()->with('success', 'Database created successfully. Save these credentials - the password will not be shown again.');
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
}
