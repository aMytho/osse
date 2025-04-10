<?php

namespace App\Http\Controllers;

use App\Jobs\ScanMusic;
use Illuminate\Support\Facades\Cache;

class ScanController extends Controller
{
  public function startScan()
  {
    // If the job has been cancelled, clear that setting.
    // We have middleware that will not start this new job while another job is running.
    Cache::delete('scan_cancelled');

    // Start the job.
    ScanMusic::dispatch();
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
  }
}
