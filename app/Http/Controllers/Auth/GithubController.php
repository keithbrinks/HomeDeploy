<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domains\Identity\Actions\HandleGithubCallbackAction;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GithubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $settings = Settings::get();
        
        if (!$settings->hasGithubOAuth()) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'GitHub OAuth is not configured. Please add your credentials in Settings.');
        }

        // Configure Socialite dynamically from database
        config([
            'services.github.client_id' => $settings->github_client_id,
            'services.github.client_secret' => $settings->github_client_secret,
            'services.github.redirect' => $settings->github_redirect_uri,
        ]);

        return Socialite::driver('github')
            ->scopes(['repo', 'read:user'])
            ->redirect();
    }

    public function callback(HandleGithubCallbackAction $action): RedirectResponse
    {
        $settings = Settings::get();
        
        if (!$settings->hasGithubOAuth()) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'GitHub OAuth is not configured');
        }

        // Configure Socialite dynamically from database
        config([
            'services.github.client_id' => $settings->github_client_id,
            'services.github.client_secret' => $settings->github_client_secret,
            'services.github.redirect' => $settings->github_redirect_uri,
        ]);

        $socialiteUser = Socialite::driver('github')->user();

        // Store the token in Settings instead of per-user
        $settings->update([
            'github_token' => $socialiteUser->token,
            'github_user' => $socialiteUser->nickname ?? $socialiteUser->name,
        ]);

        return redirect()->route('settings.index')->with('status', 'GitHub connected successfully!');
    }
}
