<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Sites\Site;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $sites = Site::with(['deployments' => function ($query) {
            $query->latest()->limit(1);
        }])->latest()->get();

        return view('dashboard', [
            'sites' => $sites,
        ]);
    }
}
