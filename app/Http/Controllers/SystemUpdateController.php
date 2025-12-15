<?php

namespace App\Http\Controllers;

use App\Actions\System\CheckForUpdates;
use App\Actions\System\PerformUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SystemUpdateController extends Controller
{
    public function index(CheckForUpdates $checkForUpdates): View
    {
        $updateInfo = $checkForUpdates->execute();
        
        return view('system.update', [
            'updateInfo' => $updateInfo,
        ]);
    }
    
    public function check(CheckForUpdates $checkForUpdates): JsonResponse
    {
        return response()->json($checkForUpdates->execute());
    }
    
    public function update(PerformUpdate $performUpdate): RedirectResponse
    {
        $result = $performUpdate->execute();
        
        if ($result['success']) {
            return redirect()
                ->route('system.update')
                ->with('success', $result['message']);
        }
        
        return redirect()
            ->route('system.update')
            ->with('error', $result['message']);
    }
}
