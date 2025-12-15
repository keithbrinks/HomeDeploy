<?php

use App\Http\Controllers\Auth\GithubController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeploymentsController;
use App\Http\Controllers\SitesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    
    Route::get('/sites/create', [SitesController::class, 'create'])->name('sites.create');
    Route::post('/sites', [SitesController::class, 'store'])->name('sites.store');
    Route::get('/sites/{site}', [SitesController::class, 'show'])->name('sites.show');
    
    Route::post('/sites/{site}/deploy', [DeploymentsController::class, 'store'])->name('sites.deploy');

    Route::get('/auth/github', [GithubController::class, 'redirect'])->name('auth.github');
    Route::get('/auth/github/callback', [GithubController::class, 'callback'])->name('auth.github.callback');
});

