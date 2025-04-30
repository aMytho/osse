<?php

namespace App\Http\Controllers;

use App\Jobs\ScanMusic;
use App\Models\ScanDirectory;
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
            $scanProgress = Cache::get('scan_progress');
            $scanDirectories = ScanDirectory::where('scan_job_id', $scanProgress['job_id'])->get();

            return response()->json(array_merge($scanProgress,
                ['active' => true, 'directories' => $scanDirectories->map(fn ($d) => $d->toBroadcastArray())->toArray()]
            ));
        } else {
            return response()->json(['active' => false, 'directories' => config('scan.directories')]);
        }
    }

    public function cancel()
    {
        // Stop the current execution. When the next dir is processed, it will exit.
        Cache::put('scan_cancelled', true);
        Cache::forget('scan_progress');
    }
}
