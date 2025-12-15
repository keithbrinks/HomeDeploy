<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domains\Identity\Actions\HandleGithubCallbackAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GithubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        // Check if GitHub OAuth is configured
        if (!config('services.github.client_id') || !config('services.github.client_secret')) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'GitHub OAuth is not configured. Please set GITHUB_CLIENT_ID and GITHUB_CLIENT_SECRET in your .env file.');
        }

        return Socialite::driver('github')
            ->scopes(['repo', 'read:user'])
            ->redirect();
    }

    public function callback(HandleGithubCallbackAction $action): RedirectResponse
    {
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
