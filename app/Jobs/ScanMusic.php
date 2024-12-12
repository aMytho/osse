<?php

namespace App\Jobs;

use App\Services\MusicProcessor\MusicProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ScanMusic implements ShouldQueue
{
    use Queueable;

    /**
    * Wait a max of 10 minutes to process a library.
    * Todo: This will probably need to be a user setting.
    */
    public $timeout = 600;
    /**
    * Mark job as failed if time exceeded.
    */
    public $failOnTimeout = true;
    /**
    * Only run this once.
    */
    public $tries = 1;

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
        Log::info('Scan read directories from config.');
        $files = collect(File::allFiles($directories));
        $files = $files->groupBy(fn ($f) => $f->getPath());

        foreach ($files as $dir => $directoryGroup) {
            Log::info('Processing ' . $dir);
            $processor = new MusicProcessor($directoryGroup);
            $processor->scan();
            Log::info('Finished ' . $dir . ' with ' . $processor->filesScanned . ' files scanned and ' . $processor->filesSkipped . ' files skipped.');
        }

        // TODO: prune models
    }
}
