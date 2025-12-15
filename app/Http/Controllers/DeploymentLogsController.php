<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Deployments\Deployment;
use Illuminate\Http\JsonResponse;

class DeploymentLogsController extends Controller
{
    public function show(Deployment $deployment): JsonResponse
    {
        return response()->json([
            'output' => $deployment->output ?? '',
            'status' => $deployment->status,
            'started_at' => $deployment->started_at?->toISOString(),
            'completed_at' => $deployment->completed_at?->toISOString(),
        ]);
    }
}
