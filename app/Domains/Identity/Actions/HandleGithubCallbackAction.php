<?php

declare(strict_types=1);

namespace App\Domains\Identity\Actions;

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class HandleGithubCallbackAction
{
    public function execute(User $user, SocialiteUser $socialiteUser): void
    {
        $user->update([
            'github_id' => $socialiteUser->getId(),
            'github_username' => $socialiteUser->getNickname(),
            'github_token' => $socialiteUser->token,
            'github_refresh_token' => $socialiteUser->refreshToken,
        ]);
    }
}
