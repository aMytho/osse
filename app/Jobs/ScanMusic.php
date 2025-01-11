<?php

namespace App\Jobs;

use App\Events\ScanFailed;
use App\Services\MusicProcessor\ArtExtractor;
use App\Services\MusicProcessor\MusicProcessor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Events\ScanStarted;
use App\Events\ScanProgressed;
use App\Events\ScanCompleted;
use Throwable;

class ScanMusic implements ShouldQueue, ShouldBeUnique
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

        ScanStarted::dispatch($files->count());

        foreach ($files as $dir => $directoryGroup) {
            Log::info('Processing ' . $dir);
            $processor = new MusicProcessor($directoryGroup);
            $processor->scan();

            // Album art
            $artProcessor = new ArtExtractor($processor->getScannedFiles(), $directoryGroup);
            $artProcessor->storeArt();

            Log::info('Finished ' . $dir . ' with ' . $processor->filesScanned . ' files scanned and ' . $processor->filesSkipped . ' files skipped.');

            // Emit the event. We have to cast dir as a string, or it may interpert it as a class.
            // Only in php...
            ScanProgressed::dispatch(strval($dir), $processor->filesScanned, $processor->filesSkipped);
        }

        // TODO: prune models

        ScanCompleted::dispatch($files->count());
    }

    public function failed(?Throwable $exception): void
    {
        ScanFailed::dispatch($exception?->getMessage() ?? 'Unknown Error');
    }
}
