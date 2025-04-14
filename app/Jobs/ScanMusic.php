<?php

namespace App\Jobs;

use App\Events\ScanCancelled;
use App\Events\ScanCompleted;
use App\Events\ScanError;
use App\Events\ScanFailed;
use App\Events\ScanProgressed;
use App\Events\ScanStarted;
use App\Services\MusicProcessor\ArtExtractor;
use App\Services\MusicProcessor\MusicProcessor;
use App\Services\MusicProcessor\MusicPruner;
use FilesystemIterator;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Throwable;

class ScanMusic implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Wait a max of 1 hour to process a library.
     * TODO: This will probably need to be a user setting.
     */
    public $timeout = 3600;

    /**
     * Mark job as failed if time exceeded.
     */
    public $failOnTimeout = true;

    /**
     * Only run this once.
     */
    public $tries = 1;

    /**
     * @var Collection<int, string>
     */
    public $directoriesToScan;

    /**
     * Create a new job instance.
     *
     * @param  mixed  $freshScan  If true, all audio data will be deleted before scan.
     */
    public function __construct($freshScan = false)
    {
        // Set the max memory limit to 2gb.
        // We also set the limit in the larael queue worker and the php init limit for that worker.
        ini_set('memory_limit', '2G');

        // Read in the directories, group by folder.
        $directories = config('scan.directories');
        $directoriesToScan = collect();
        foreach ($directories as $dirEntry) {
            // Make sure the directory exists. If not, fail.
            if (! File::isDirectory($dirEntry)) {
                throw new DirectoryNotFoundException(message: 'Directory '.$dirEntry.' was not found. Please check for typos and ensure network drives are mounted.');
            }

            // Loop through the dirs and get each dirPath
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirEntry, FilesystemIterator::FOLLOW_SYMLINKS));
            foreach ($rii as $ri) {
                if ($ri->isDir()) {
                    $directoriesToScan[] = $ri->getPath();
                }
            }
        }

        $this->directoriesToScan = $directoriesToScan->unique()->values();
        Log::info('Scan read directories from config.');

        // If freshScan is true, clear out all user data first.
        if ($freshScan) {
            $this->clearOldData();
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Start the scan loop
        $directoryCounter = 0;
        Cache::put('scan_progress', ['total_directories' => $this->directoriesToScan->count(), 'finished_count' => $directoryCounter]);
        broadcast(new ScanStarted($this->directoriesToScan->count()));

        foreach ($this->directoriesToScan as $dir) {
            // Check if the user cancelled the job mid-operation.
            if (! $this->allowedToRun()) {
                // Emit the event and stop all execution. This won't delete what has been scanned and no pruning will be done either.
                broadcast(new ScanCancelled($directoryCounter));
                // Allow future scans to run.
                Cache::delete('scan_cancelled');

                return;
            }

            Log::info('Processing '.$dir);
            $files = collect(File::files($dir, false));
            if ($files->isEmpty()) {
                // Prune files, skip processing.
                $this->pruneDirectory($dir, $files);
                Log::info('Finished '.$dir.' with 0 files scanned and 0 files skipped.');

                $directoryCounter++;
                Cache::put('scan_progress', ['total_directories' => $this->directoriesToScan->count(), 'finished_count' => $directoryCounter]);
                broadcast(new ScanProgressed(strval($dir), 0, 0, $this->directoriesToScan->count(), $directoryCounter, $this->directoriesToScan[$directoryCounter] ?? null));

                continue;
            }

            // Process music files.
            $processor = $this->processMusic($files);

            // Album art
            $this->processTrackArtwork($processor->getScannedFiles(), $files);

            // If there was a file that was here previously, but is no longer here, remove it.
            $this->pruneDirectory($dir, $processor->getAllFiles());

            Log::info('Finished '.$dir.' with '.$processor->filesScanned.' files scanned and '.$processor->filesSkipped.' files skipped.');

            // Emit the event. We have to cast dir as a string, or it may interpert it as a class.
            // Only in php...
            $directoryCounter++;
            Cache::put('scan_progress', ['total_directories' => $this->directoriesToScan->count(), 'finished_count' => $directoryCounter]);
            broadcast(new ScanProgressed(strval($dir), $processor->filesScanned, $processor->filesSkipped, $this->directoriesToScan->count(), $directoryCounter, $this->directoriesToScan[$directoryCounter] ?? null));
        }

        // Prune any directories that used to exist, but were not in this scan list.
        // Also prunes relations
        MusicPruner::pruneDirectoriesThatUsedToExist($this->directoriesToScan->values());
        Cache::forget('scan_progress');
        broadcast(new ScanCompleted($this->directoriesToScan->count()));
    }

    /**
     * Deletes old DB entries that used to exist in this directory, but are no longer there.
     *
     * @param  string  $dirName
     * @param  Collection<array-key,mixed>  $files
     */
    private function pruneDirectory($dirName, $files): void
    {
        try {
            // Prune files, skip processing.
            $musicPruner = new MusicPruner($dirName, $files);
            $musicPruner->prune();
        } catch (\Throwable $th) {
            Log::error('Error during scan prune files for directory '.$dirName.'. '.$th->getMessage());
            ScanError::dispatch('Error during scan prune files for directory '.$dirName.'. '.$th->getMessage());
        }
    }

    /**
     * Processes the music files into the DB.
     * Tracks, artists, and albums are created here.
     *
     * @param  Collection<array-key,mixed>  $files
     */
    private function processMusic(Collection $files): MusicProcessor
    {
        $processor = new MusicProcessor($files);
        try {
            $processor->scan();
        } catch (\Throwable $th) {
            Log::error('Error during scan music processing.'.$th->getMessage());
            ScanError::dispatch('Error during scan music processing'.$th->getMessage());
        } finally {
            return $processor;
        }
    }

    /**
     * @param  Collection<array-key,MusicMetadata>  $files
     * @param  Collection  $allFiles
     * @param  mixed  $scannedFiles
     */
    private function processTrackArtwork($scannedFiles, $allFiles): void
    {
        try {
            $artProcessor = new ArtExtractor($scannedFiles, $allFiles);
            $artProcessor->storeArt();
        } catch (\Throwable $th) {
            Log::error('Error during track artwork extraction'.$th->getMessage());
            ScanError::dispatch('Error during track artwork extraction.'.$th->getMessage());
        }
    }

    /**
     * Clear all music data. Only used in fresh scans.
     */
    private function clearOldData(): void
    {
        // Clear data.
        DB::table('track_artist')->truncate();
        DB::table('album_artist')->truncate();
        DB::table('playlist_track')->truncate();
        DB::table('playlists')->truncate();
        DB::table('tracks')->truncate();
        DB::table('albums')->truncate();
        DB::table('cover_art')->truncate();
        DB::table('artists')->truncate();

        // Delete extracted cover art
        Storage::disk(config('scan.cover_art_disk'))->deleteDirectory('private/cover-art');
    }

    public function failed(?Throwable $exception): void
    {
        broadcast(new ScanFailed($exception?->getMessage() ?? 'Unknown Error'));
        Cache::forget('scan_cancelled');
        Cache::forget('scan_progress');
    }

    /**
     * Checks if the scan has been cancelled. If so, we should stop the job.
     */
    private function allowedToRun(): bool
    {
        return ! Cache::has('scan_cancelled');
    }
}
