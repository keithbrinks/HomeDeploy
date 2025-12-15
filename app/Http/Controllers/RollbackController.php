<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Deployments\Deployment;
use App\Jobs\DeploySiteJob;
use Illuminate\Http\RedirectResponse;

class RollbackController extends Controller
{
    public function store(Deployment $deployment): RedirectResponse
    {
        if ($deployment->status !== 'success') {
            return back()->with('error', 'Can only rollback to successful deployments.');
        }

        $site = $deployment->site;

        // Create new deployment with same commit
        $rollbackDeployment = $site->deployments()->create([
            'status' => 'pending',
            'deployed_by' => 'rollback',
            'commit_hash' => $deployment->commit_hash,
            'commit_message' => '[Rollback] ' . $deployment->commit_message,
        ]);

        DeploySiteJob::dispatch($rollbackDeployment);

        return redirect()
            ->route('sites.show', $site)
            ->with('success', 'Rollback initiated to deployment #' . $deployment->id);
    }
}
