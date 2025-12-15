<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Facades\Process;

class ServicesController extends Controller
{
    public function index(): View
    {
        return view('services.index');
    }

    public function restart(string $service): RedirectResponse
    {
        $allowedServices = ['nginx', 'mysql', 'redis-server', 'php8.2-fpm', 'php8.3-fpm'];

        if (!in_array($service, $allowedServices)) {
            return back()->with('error', 'Service not allowed.');
        }

        try {
            $result = Process::run("sudo systemctl restart {$service}");

            if ($result->failed()) {
                return back()->with('error', "Failed to restart {$service}: " . $result->errorOutput());
            }

            return back()->with('success', ucfirst($service) . ' restarted successfully.');
        } catch (ProcessFailedException $e) {
            return back()->with('error', 'Process failed: ' . $e->getMessage());
        }
    }

    public function status(string $service): RedirectResponse
    {
        $allowedServices = ['nginx', 'mysql', 'redis-server', 'php8.2-fpm', 'php8.3-fpm'];

        if (!in_array($service, $allowedServices)) {
            return back()->with('error', 'Service not allowed.');
        }

        try {
            $result = Process::run("sudo systemctl is-active {$service}");
            $status = trim($result->output());

            return back()->with('info', ucfirst($service) . " status: {$status}");
        } catch (ProcessFailedException $e) {
            return back()->with('error', 'Process failed: ' . $e->getMessage());
        }
    }
}
