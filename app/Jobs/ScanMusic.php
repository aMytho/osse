<?php

namespace App\Jobs;

use App\Models\Artist;
use App\Services\MusicProcessor\MusicProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;

class ScanMusic implements ShouldQueue
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
        // Read in the directories, group by folder.
        $directories = config('scan.directories');
        $files = collect(File::allFiles($directories));
        $files = $files->groupBy(fn ($f) => $f->getRelativePath());

        foreach ($files as $directoryGroup) {
            $processor = new MusicProcessor($directoryGroup);
            $processor->scan();
        }
    }
}
