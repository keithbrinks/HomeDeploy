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

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user) {
            // If not logged in, we might want to deny or handle login.
            // For MVP, assume admin is logged in to connect.
            abort(403, 'Must be logged in to connect GitHub.');
        }

        $action->execute($user, $socialiteUser);

        return redirect()->route('dashboard')->with('status', 'GitHub connected successfully!');
    }
}
