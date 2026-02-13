<?php

namespace App\Providers;

use App\Jobs\ScanMusic;
use App\Jobs\GenerateAlbumResponse;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Queue::after(function (JobProcessed $event) {
            if ($event->job->resolveName() == ScanMusic::class) {
                // When the scan job completes, regenerate cached queries.
                GenerateAlbumResponse::dispatch();
            }
        });
    }
}
