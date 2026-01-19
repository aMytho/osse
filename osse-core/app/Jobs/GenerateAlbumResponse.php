<?php

namespace App\Jobs;

use App\Models\Album;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class GenerateAlbumResponse implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Cache::put('albumsWithTracks', json_encode(Album::with(['tracks', 'tracks.artists', 'artists'])->get()));
    }
}
