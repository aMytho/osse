<?php

namespace App\Services\MusicProcessor;

use App\Models\Album;
use App\Models\CoverArt;
use App\Models\Artist;
use App\Models\Track;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MusicPruner
{
    /**
     * Create a new class instance.
     * @param string $directory
     * @param Collection<array-key,MusicMetadata> $filesInDir
     */
    public function __construct(private string $directory, private Collection $filesInDir)
    {
        $this->filesInDir = $this->filesInDir->map(fn ($f) => $f->getRealPath());
    }

    public function prune(): void
    {
        $this->pruneTracks();
    }

    /**
    * Deletes every file that used to be in this directory but isn't anymore.
    */
    private function pruneTracks(): void
    {
        // All files that are in the directory from the LAST scan.
        // They may not be in the filesystem anymore.
        // TODO: Handle backslashes \. Windows uses those. IDK if this matters since you need WSL/mac/linux...
        $tracksOld = Track::where('location', 'LIKE', $this->directory . '/%')
            ->where('location', 'NOT LIKE', $this->directory . '/%/%')
            ->select('id')
            ->get();

        // Every track that is in the db but not in the filelist was moved/deleted. Remove from db.
        $missingTracks = $tracksOld->diff(Track::select('id')->whereIn('location', $this->filesInDir)->get());

        if ($missingTracks->isNotEmpty()) {
            Track::whereIn('id', $missingTracks->pluck('id'))
                ->delete();
        }
    }

    private static function pruneTrackRelations(): void
    {
        // Cleanup old data that no longer have tracks.
        Album::doesntHave('tracks')->delete();
        Artist::doesntHave('tracks')->delete();
    }

    private static function pruneCoverArt(): void
    {
        // Delete the extraced art and then the DB entry.
        $covers = CoverArt::doesntHave('tracks')->get();
        foreach ($covers as $cover) {
            Storage::delete('cover-art/' . $cover->hash);
        }
        CoverArt::whereIn('id', $covers->pluck('id'))->delete();
    }

    /**
     * Find any dirs that were in the scan list but no longer are and remove their tracks.
     * @param Collection<array-key,mixed> $directoriesScannedNow
     */
    public static function pruneDirectoriesThatUsedToExist(Collection $directoriesScannedNow): void
    {
        // Process 999 tracks at a time since that is the max args sqlite can handle at a time.
        Track::select(['id', 'location'])->chunk(999, function ($tracks) use ($directoriesScannedNow) {
            // Get the dir names
            $dirs = $tracks->map(function ($t) {
                $t->directory = dirname($t->location);
                return $t;
            })->unique('directory');

            // If we have a directory that is not in directoriesScannedNow, delete it.
            $tracksToRemove = $dirs->where(fn ($t) => $directoriesScannedNow->doesntContain($t->directory));
            if ($tracksToRemove->isNotEmpty()) {
                Track::whereIn('id', $tracksToRemove->pluck('id'))
                    ->delete();
            }
        });

        self::pruneTrackRelations();
        self::pruneCoverArt();
    }
}
