<?php

namespace App\Services\MusicProcessor;

use App\Models\CoverArt;
use App\Models\Track;
use Illuminate\Support\Collection;

class ArtExtractor
{
    /**
    * Standalone file types we search for in each directory.
    */
    public const STANDALONE_COVERS = ['jpg', 'jpeg', 'png'];

    /**
    * List of images to save.
    * @var Collection<array-key,ArtFile>
    */
    public Collection $artworkToSave;
    /**
    * Collection of images in this directory. 
    */
    public Collection $coverArtFiles;

    /**
     * @param Collection<array-key,MusicMetadata> $files
     */
    public function __construct(public Collection $files, Collection $allFiles)
    {
        $this->coverArtFiles = $this->getCoverFiles($allFiles);
        $this->artworkToSave = collect();

        // Check the standalone files first, then resort to track extraction.
        $this->checkForStandaloneArt();
        if ($this->artworkToSave->isEmpty()) {
            $this->checkFilesForArt();
        }
    }

    /**
    * Takes in a collection (directory of files) and returns the images.
    */
    private function getCoverFiles(Collection $files): Collection
    {
        return $files->filter(function ($file) {
            return array_any(self::STANDALONE_COVERS, fn ($validFileType) => $validFileType == $file->getExtension());
        });
    }

    /**
    * First, check for a image file in this directory.
    * This overrides any track art.
    */
    private function checkForStandaloneArt(): void
    {
        if ($this->coverArtFiles->isEmpty()) {
            return;
        }

        // Check for a cover.extension file. Most albums include one of these files so we check them first.
        $coverArt = $this->coverArtFiles->first(fn ($file) => 'cover' == $file->getBasename('.' . $file->getExtension()));
        if (!is_null($coverArt)) {
            $this->artworkToSave->push(new ArtFile($coverArt, $coverArt->getRealPath()));
            return;
        }

        // Get the first image file, whichever it is.
        $coverArt = $this->coverArtFiles->first();
        $this->artworkToSave->push(new ArtFile($coverArt, $coverArt->getRealPath()));
    }

    private function checkFilesForArt(): void
    {
        foreach ($this->files as $file) {
            if ($file->hasCoverArt) {
                $this->artworkToSave->push(new ArtFile($file->getCoverArt(), $file->path));
            }
        }
    }

    public function storeArt(): void
    {
        if ($this->artworkToSave->isEmpty()) {
            return;
        }

        // There was a standalone file.
        if ($this->artworkToSave->containsOneItem()) {
            // Store the file.
            $art = $this->artworkToSave->first();
            $art->storeFile();
            
            // Save the db record.
            $artModel = new CoverArt(['hash' => $art->hash, 'mime_type' => $art->getMimeType()]);
            $artModel->save();

            // Link covert art to tracks.
            $tracks = Track::whereIn('location', $this->files->pluck('path'))
                ->update([
                'cover_art_id' => $artModel->id
            ]);

            return;
        }

        // There are many files. Group by hash and only save 1 of duplicate files.
        $filesWithHashes = collect();
        foreach ($this->artworkToSave->groupBy('hash') as $art) {
            foreach ($art as $artFile) {
                $artFile->storeFile();
            }

            $filesWithHashes->push(['art' => $art->first(), 'trackFilePaths' => $art->pluck('trackFilePath')]);
        }

        // Create DB entries.
        CoverArt::insert($filesWithHashes->map(function ($f) {
            return [
                'hash' => $f['art']->hash,
                'mime_type' => $f['art']->getMimeType()
            ];
        })->toArray());

        // Get the cover art and link to track.
        $covers = CoverArt::whereIn('hash', $filesWithHashes->pluck('art.hash'))->get();
        $tracks = Track::whereIn('location', $filesWithHashes->pluck('trackFilePaths')->flatten()->toArray())->get();
        foreach ($tracks as $track) {
            $hashForTrackCover = $filesWithHashes->firstWhere(function ($f) use ($track) {
                return $f['trackFilePaths']->contains($track->location);
            })['art']->hash;

            $cover = $covers->firstWhere('hash', $hashForTrackCover);
            $track->cover_art_id = $cover->id;
            $track->save();
        }
    }
}
