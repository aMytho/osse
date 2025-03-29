<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Clear out expired access tokens daily (assuming the token has been invalid for 24 hours, check once per day)
Schedule::command('sanctum:prune-expired --hours=24')->daily();
