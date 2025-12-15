<?php

declare(strict_types=1);

namespace App\Domains\Identity\Actions;

use Illuminate\Support\Facades\Http;

class FetchGithubBranchesAction
{
    public function execute(string $token, string $owner, string $repo): array
    {
        $response = Http::withToken($token)
            ->get("https://api.github.com/repos/{$owner}/{$repo}/branches");

        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch branches from GitHub');
        }

        return collect($response->json())
            ->map(fn ($branch) => [
                'name' => $branch['name'],
                'protected' => $branch['protected'] ?? false,
            ])
            ->toArray();
    }
}
