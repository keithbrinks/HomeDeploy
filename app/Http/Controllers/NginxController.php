<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Server\Actions\GenerateNginxConfigAction;
use App\Domains\Server\Actions\RestartNginxAction;
use App\Domains\Sites\Site;
use Illuminate\Http\RedirectResponse;

class NginxController extends Controller
{
    public function generate(Site $site, GenerateNginxConfigAction $generateAction, RestartNginxAction $restartAction): RedirectResponse
    {
        try {
            $configPath = $generateAction->execute($site);
            $restartAction->execute();

            return back()->with('success', "Nginx config generated at {$configPath} and server reloaded.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate Nginx config: ' . $e->getMessage());
        }
    }
}
