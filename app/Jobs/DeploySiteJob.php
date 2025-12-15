<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Deployments\Actions\RunDeploymentAction;
use App\Domains\Deployments\Deployment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeploySiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Deployment $deployment)
    {
    }

    public function handle(RunDeploymentAction $action): void
    {
        $action->execute($this->deployment);
    }
}
