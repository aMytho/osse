<?php

namespace App\Providers;

use App\Jobs\ScanMusic;
use App\Models\Album;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

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
                Cache::put('albumsWithTracks', json_encode(Album::with(['tracks', 'tracks.artists', 'artists'])->get()));
            }
        });
    }
}
