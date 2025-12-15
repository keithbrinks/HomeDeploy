<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Deployments\Actions\RunDeploymentAction;
use App\Domains\Deployments\Deployment;
use App\Domains\Sites\Site;
use Illuminate\Http\RedirectResponse;

class DeploymentsController extends Controller
{
    public function store(Site $site, RunDeploymentAction $action): RedirectResponse
    {
        $deployment = $site->deployments()->create([
            'status' => 'pending',
            'deployed_by' => 'manual', // or Auth::user()->name
        ]);

        // In a real app, dispatch a job. For MVP, run inline or dispatch job.
        // Since we set up a queue worker in install.sh, we should use a Job.
        // But for "dead-simple" MVP without Redis, database queue is fine.
        // I'll create a Job wrapper for the Action.
        
        // For now, let's just run it synchronously to test, or create the Job.
        // Let's create the Job.
        
        \App\Jobs\DeploySiteJob::dispatch($deployment);

        return redirect()->route('sites.show', $site);
    }
}
