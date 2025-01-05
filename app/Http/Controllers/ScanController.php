<?php

namespace App\Http\Controllers;

use App\Jobs\ScanMusic;

class ScanController extends Controller
{
  public function startScan()
  {
    ScanMusic::dispatch();
  }
}
