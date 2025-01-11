<?php

namespace App\Services\MusicProcessor;

use Illuminate\Support\Collection;
use Kiwilan\Audio\Models\AudioCover;

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
            return array_any(STANDALONE_COVERS, fn ($validFileType) => $validFileType == $file->extension);
        });
    }

    /**
    * First, check for a image file in this directory.
    * This overrides any track art.
    * Returns true or false if art was found.
    */
    private function checkForStandaloneArt(): void
    {
        if ($this->coverArtFiles->isEmpty()) {
            return;
        }

        // Check for a cover.extension file. Most albums include one of these files so we check them first.
        $coverArt = $this->coverArtFiles->first(fn ($file) => $file->getBasename('.' . $file->getExtension()));
        if (!is_null($coverArt)) {
            $this->artworkToSave->push(new ArtFile($coverArt));
            return;
        }

        // Get the first image file, whichever it is.
        $this->artworkToSave->push(new ArtFile($this->coverArtFiles->first()));
    }

    private function checkFilesForArt(): void
    {
        foreach ($this->files as $file) {
            if ($file->hasCoverArt) {
                $this->artworkToSave->push(new ArtFile($file->getCoverArt()));
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
            $art = $this->artworkToSave->first();
        }

    }    
}
