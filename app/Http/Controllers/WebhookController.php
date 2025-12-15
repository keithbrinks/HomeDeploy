<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Sites\Site;
use App\Jobs\DeploySiteJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Site $site, Request $request): JsonResponse
    {
        // Verify GitHub webhook signature
        if (! $this->verifySignature($request, $site->webhook_secret)) {
            Log::warning('Invalid webhook signature', ['site' => $site->id]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();
        $event = $request->header('X-GitHub-Event');

        // Only handle push events
        if ($event !== 'push') {
            return response()->json(['message' => 'Event ignored'], 200);
        }

        // Check if push is to the configured branch
        $ref = $payload['ref'] ?? '';
        $branch = str_replace('refs/heads/', '', $ref);

        if ($branch !== $site->branch) {
            return response()->json(['message' => 'Branch ignored'], 200);
        }

        // Extract commit info
        $headCommit = $payload['head_commit'] ?? null;
        
        $deployment = $site->deployments()->create([
            'status' => 'pending',
            'deployed_by' => 'webhook',
            'commit_hash' => $headCommit['id'] ?? null,
            'commit_message' => $headCommit['message'] ?? null,
        ]);

        DeploySiteJob::dispatch($deployment);

        return response()->json([
            'message' => 'Deployment triggered',
            'deployment_id' => $deployment->id,
        ], 200);
    }

    private function verifySignature(Request $request, ?string $secret): bool
    {
        if (! $secret) {
            return false;
        }

        $signature = $request->header('X-Hub-Signature-256');
        if (! $signature) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    public function regenerateSecret(Site $site): JsonResponse
    {
        $secret = bin2hex(random_bytes(32));
        $site->update(['webhook_secret' => $secret]);

        return response()->json([
            'secret' => $secret,
            'webhook_url' => route('webhook.handle', $site),
        ]);
    }
}
