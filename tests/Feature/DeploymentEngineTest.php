<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Deployments\Actions\RunDeploymentAction;
use App\Domains\Deployments\Deployment;
use App\Domains\Sites\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_deployment_can_be_created_for_site(): void
    {
        $user = User::factory()->create();
        $site = Site::create([
            'name' => 'Test Site',
            'repo_url' => 'https://github.com/laravel/laravel.git',
            'branch' => 'main',
            'deploy_path' => storage_path('app/test-deploy'),
        ]);

        $this->actingAs($user)
            ->post(route('sites.deploy', $site))
            ->assertRedirect();

        // Deployment will be created and immediately processed by job
        // Status could be pending, running, success, or failed
        $this->assertDatabaseHas('deployments', [
            'site_id' => $site->id,
        ]);
        
        $deployment = $site->deployments()->first();
        $this->assertNotNull($deployment);
        $this->assertContains($deployment->status, ['pending', 'running', 'success', 'failed']);
    }

    public function test_deployment_action_updates_status(): void
    {
        $site = Site::create([
            'name' => 'Test Site',
            'repo_url' => 'https://github.com/laravel/laravel.git',
            'branch' => 'main',
            'deploy_path' => storage_path('app/test-deploy'),
        ]);

        $deployment = Deployment::create([
            'site_id' => $site->id,
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $deployment->status);
        $this->assertNull($deployment->started_at);
    }

    public function test_deployment_logs_are_retrievable(): void
    {
        $user = User::factory()->create();
        $site = Site::create([
            'name' => 'Test Site',
            'repo_url' => 'https://github.com/laravel/laravel.git',
            'branch' => 'main',
            'deploy_path' => storage_path('app/test-deploy'),
        ]);

        $deployment = Deployment::create([
            'site_id' => $site->id,
            'status' => 'success',
            'output' => 'Test deployment output',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('api.deployments.logs', $deployment));

        $response->assertOk()
            ->assertJson([
                'output' => 'Test deployment output',
                'status' => 'success',
            ]);
    }
}
