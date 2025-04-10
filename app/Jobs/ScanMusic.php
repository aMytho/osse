<?php

namespace App\Jobs;

use App\Events\ScanCancelled;
use App\Events\ScanFailed;
use App\Services\MusicProcessor\ArtExtractor;
use App\Services\MusicProcessor\MusicProcessor;
use App\Services\MusicProcessor\MusicPruner;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
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
    * Wait a max of 30 minutes to process a library.
    * TODO: This will probably need to be a user setting.
    */
    public $timeout = 1800;

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
        // Set the max memory limit to 1gb.
        ini_set('memory_limit', '1G');
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

        $directoryCounter = 0;
        Cache::put('scan_progress', ['total_directories' => $files->count(), 'finished_count' => $directoryCounter]);
        ScanStarted::dispatch($files->count());

        foreach ($files as $dir => $directoryGroup) {
            // Check if the user cancelled the job mid-operation.
            if (!$this->allowedToRun()) {
                // Emit the event and stop all execution. This won't delete what has been scanned, but no pruning will be done either. 
                ScanCancelled::dispatch($directoryCounter);
                // Allow future scans to run.
                Cache::delete('scan_cancelled');
                return;
            }

            Log::info('Processing ' . $dir);
            $processor = new MusicProcessor($directoryGroup);
            $processor->scan();

            // Album art
            $artProcessor = new ArtExtractor($processor->getScannedFiles(), $directoryGroup);
            $artProcessor->storeArt();

            // If there was a file that was here previously, but is no longer here, remove it.
            $musicPruner = new MusicPruner($dir, $processor->getAllFiles());
            $musicPruner->prune();

            Log::info('Finished ' . $dir . ' with ' . $processor->filesScanned . ' files scanned and ' . $processor->filesSkipped . ' files skipped.');

            // Emit the event. We have to cast dir as a string, or it may interpert it as a class.
            // Only in php...
            $directoryCounter++;
            Cache::put('scan_progress', ['total_directories' => $files->count(), 'finished_count' => $directoryCounter]);
            ScanProgressed::dispatch(strval($dir), $processor->filesScanned, $processor->filesSkipped, $files->count(), $directoryCounter);
        }

        // Prune any directories that used to exist, but were not in this scan list.
        // Also prunes relations
        MusicPruner::pruneDirectoriesThatUsedToExist($files->keys());
        Cache::forget('scan_progress');
        ScanCompleted::dispatch($files->count());
    }

    public function failed(?Throwable $exception): void
    {
        ScanFailed::dispatch($exception?->getMessage() ?? 'Unknown Error');
    }

    private function allowedToRun(): bool
    {
        return !Cache::has('scan_cancelled');
    }
}
