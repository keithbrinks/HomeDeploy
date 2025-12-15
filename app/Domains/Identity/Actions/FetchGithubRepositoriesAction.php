<?php

declare(strict_types=1);

namespace App\Domains\Identity\Actions;

use Illuminate\Support\Facades\Http;

class FetchGithubRepositoriesAction
{
    public function execute(string $token): array
    {
        $response = Http::withToken($token)
            ->get('https://api.github.com/user/repos', [
                'per_page' => 100,
                'sort' => 'updated',
                'affiliation' => 'owner,collaborator,organization_member',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch repositories from GitHub');
        }

        return collect($response->json())
            ->map(fn ($repo) => [
                'name' => $repo['name'],
                'full_name' => $repo['full_name'],
                'clone_url' => $repo['clone_url'],
                'default_branch' => $repo['default_branch'],
                'private' => $repo['private'],
                'description' => $repo['description'],
            ])
            ->toArray();
    }
}
