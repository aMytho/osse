<?php

namespace App\Http\Controllers;

use App\Jobs\ScanMusic;
use Illuminate\Support\Facades\Cache;

class ScanController extends Controller
{
    public function startScan()
    {
        // If the job has been cancelled, clear that setting.
        Cache::delete('scan_cancelled');

        // Start the job.
        ScanMusic::dispatch();
    }

    public function startScanFresh()
    {
        // If the job has been cancelled, clear that setting.
        Cache::delete('scan_cancelled');

        // Start the job, passing in force as true so all user data is deleted first.
        ScanMusic::dispatch(true);
    }

    public function progress()
    {
        if (Cache::has('scan_progress')) {
            return response()->json(array_merge(Cache::get('scan_progress'), ['active' => true]));
        } else {
            return response()->json(['active' => false]);
        }
    }

    public function cancel()
    {
        // Stop the current execution. When the next dir is processed, it will exit.
        Cache::put('scan_cancelled', true);
        Cache::forget('scan_progress');
    }
}
