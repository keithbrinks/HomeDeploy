<?php

namespace App\Http\Controllers;

use App\Actions\Server\GetServerMetrics;
use Illuminate\Http\JsonResponse;

class ServerMetricsController extends Controller
{
    public function __invoke(GetServerMetrics $getServerMetrics): JsonResponse
    {
        return response()->json($getServerMetrics->execute());
    }
}
