<?php

namespace App\Http\Controllers;

use App\Jobs\ScanMusic;
use Illuminate\Support\Facades\Cache;

class ScanController extends Controller
{
  public function startScan()
  {
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
}
