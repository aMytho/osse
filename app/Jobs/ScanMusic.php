<?php

namespace App\Jobs;

use App\Events\ScanCancelled;
use App\Events\ScanFailed;
use App\Services\MusicProcessor\ArtExtractor;
use App\Services\MusicProcessor\MusicProcessor;
use App\Services\MusicProcessor\MusicPruner;
use FilesystemIterator;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Events\ScanStarted;
use App\Events\ScanProgressed;
use App\Events\ScanCompleted;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
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
        // Set the max memory limit to 2gb.
        ini_set('memory_limit', '2G');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Read in the directories, group by folder.
        $directories = config('scan.directories');
        $directoriesToScan = collect();
        foreach ($directories as $dirEntry) {
            // Make sure the directory exists. If not, fail.
            if (!File::isDirectory($dirEntry)) {
                throw new DirectoryNotFoundException();
            }

            // Loop through the dirs and get each dirPath
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirEntry, FilesystemIterator::FOLLOW_SYMLINKS));
            foreach ($rii as $ri) {
                if ($ri->isDir()) {
                    $directoriesToScan[$ri->getPath()] = true;
                }
            }
        }

        Log::info('Scan read directories from config.');

        // Start the scan loop
        $directoryCounter = 0;
        Cache::put('scan_progress', ['total_directories' => $directoriesToScan->count(), 'finished_count' => $directoryCounter]);
        broadcast(new ScanStarted($directoriesToScan->count()));

        foreach ($directoriesToScan as $dir => $_) {
            // Check if the user cancelled the job mid-operation.
            if (!$this->allowedToRun()) {
                // Emit the event and stop all execution. This won't delete what has been scanned, but no pruning will be done either. 
                broadcast(new ScanCancelled($directoryCounter));
                // Allow future scans to run.
                Cache::delete('scan_cancelled');
                return;
            }

            Log::info('Processing ' . $dir);
            $files = collect(File::files($dir, false));
            $processor = new MusicProcessor($files);
            $processor->scan();

            // Album art
            $artProcessor = new ArtExtractor($processor->getScannedFiles(), $files);
            $artProcessor->storeArt();

            // If there was a file that was here previously, but is no longer here, remove it.
            $musicPruner = new MusicPruner($dir, $processor->getAllFiles());
            $musicPruner->prune();

            Log::info('Finished ' . $dir . ' with ' . $processor->filesScanned . ' files scanned and ' . $processor->filesSkipped . ' files skipped.');

            // Emit the event. We have to cast dir as a string, or it may interpert it as a class.
            // Only in php...
            $directoryCounter++;
            Cache::put('scan_progress', ['total_directories' => $directoriesToScan->count(), 'finished_count' => $directoryCounter]);
            broadcast(new ScanProgressed(strval($dir), $processor->filesScanned, $processor->filesSkipped, $directoriesToScan->count(), $directoryCounter));
        }

        // Prune any directories that used to exist, but were not in this scan list.
        // Also prunes relations
        MusicPruner::pruneDirectoriesThatUsedToExist($directoriesToScan->keys());
        Cache::forget('scan_progress');
        broadcast(new ScanCompleted($directoriesToScan->count()));
    }

    public function failed(?Throwable $exception): void
    {
        broadcast(new ScanFailed($exception?->getMessage() ?? 'Unknown Error'));
        Cache::forget('scan_cancelled');
        Cache::forget('scan_progress');
    }

    private function allowedToRun(): bool
    {
        return !Cache::has('scan_cancelled');
    }
}
