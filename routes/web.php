<?php

use App\Http\Controllers\Auth\GithubController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BuildCommandsController;
use App\Http\Controllers\CloudflareController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabasesController;
use App\Http\Controllers\DeploymentLogsController;
use App\Http\Controllers\DeploymentsController;
use App\Http\Controllers\EnvironmentVariablesController;
use App\Http\Controllers\NginxController;
use App\Http\Controllers\RollbackController;
use App\Http\Controllers\ServerMetricsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SitesController;
use App\Http\Controllers\SystemUpdateController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Webhook endpoint (no auth middleware, with rate limiting)
Route::post('/webhook/{site}', [WebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhook.handle');

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    
    Route::get('/sites/create', [SitesController::class, 'create'])->name('sites.create');
    Route::post('/sites', [SitesController::class, 'store'])->name('sites.store');
    Route::get('/sites/{site}', [SitesController::class, 'show'])->name('sites.show');
    Route::get('/api/github/branches', [SitesController::class, 'branches'])->name('api.github.branches');
    
    Route::post('/sites/{site}/deploy', [DeploymentsController::class, 'store'])->name('sites.deploy');
    Route::post('/deployments/{deployment}/rollback', [RollbackController::class, 'store'])->name('deployments.rollback');
    Route::post('/sites/{site}/nginx', [NginxController::class, 'generate'])->name('sites.nginx.generate');
    Route::post('/sites/{site}/webhook/regenerate', [WebhookController::class, 'regenerateSecret'])->name('sites.webhook.regenerate');
    Route::get('/sites/{site}/build-commands', [BuildCommandsController::class, 'edit'])->name('sites.build-commands.edit');
    Route::put('/sites/{site}/build-commands', [BuildCommandsController::class, 'update'])->name('sites.build-commands.update');
    Route::post('/sites/{site}/env', [EnvironmentVariablesController::class, 'store'])->name('sites.env.store');
    Route::delete('/sites/{site}/env/{environmentVariable}', [EnvironmentVariablesController::class, 'destroy'])->name('sites.env.destroy');
    Route::put('/sites/{site}/env-file', [EnvironmentVariablesController::class, 'updateEnvFile'])->name('sites.env-file.update');
    Route::get('/api/deployments/{deployment}/logs', [DeploymentLogsController::class, 'show'])->name('api.deployments.logs');
    
    Route::post('/sites/{site}/database', [DatabasesController::class, 'store'])->name('sites.database.create');
    Route::delete('/sites/{site}/database', [DatabasesController::class, 'destroy'])->name('sites.database.destroy');
    Route::post('/sites/{site}/database/sync', [DatabasesController::class, 'sync'])->name('sites.database.sync');
    
    Route::put('/sites/{site}/domain', [SitesController::class, 'updateDomain'])->name('sites.domain.update');
    
    Route::get('/sites/{site}/cloudflare', [CloudflareController::class, 'edit'])->name('cloudflare.edit');
    Route::post('/sites/{site}/cloudflare', [CloudflareController::class, 'store'])->name('cloudflare.store');
    Route::post('/sites/{site}/cloudflare/start', [CloudflareController::class, 'start'])->name('cloudflare.start');
    Route::post('/sites/{site}/cloudflare/stop', [CloudflareController::class, 'stop'])->name('cloudflare.stop');
    Route::delete('/sites/{site}/cloudflare', [CloudflareController::class, 'destroy'])->name('cloudflare.destroy');
    
    Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
    Route::post('/services/{service}/restart', [ServicesController::class, 'restart'])->name('services.restart');
    Route::get('/services/{service}/status', [ServicesController::class, 'status'])->name('services.status');
    
    Route::get('/api/server-metrics', ServerMetricsController::class)->name('api.server-metrics');
    
    Route::get('/system/update', [SystemUpdateController::class, 'index'])->name('system.update');
    Route::get('/system/update/check', [SystemUpdateController::class, 'check'])->name('system.update.check');
    
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/test-github', [SettingsController::class, 'testGithub'])->name('settings.test-github');
    Route::post('/system/update', [SystemUpdateController::class, 'update'])->name('system.update.perform');

    Route::get('/auth/github', [GithubController::class, 'redirect'])->name('auth.github');
    Route::get('/auth/github/callback', [GithubController::class, 'callback'])->name('auth.github.callback');
});

