<?php

namespace App\Services\MusicProcessor;

use Illuminate\Support\Carbon;
use Kiwilan\Audio\Audio;
use Kiwilan\Audio\Models\AudioCover;
use Kiwilan\Audio\Models\AudioMetadata;

class MusicMetadata
{
    // File data
    private Audio $audio;
    private AudioMetadata $meta;

    // Properties
    public string $path;
    public int $size;
    public float $duration;
    public int $bitrate;

    // Raw metadata
    public ?string $title;
    public array $artists = [];
    public ?string $album;
    public array $albumArtists = [];
    public ?int $discNumber;
    public ?int $trackNumber;
    public ?int $year;

    // DB Data (after identification)
    public ?int $albumID;
    // Array where each val is the artist ID
    public array $artistIDs = [];
    // Array where each val is the album artist ID
    public array $albumArtistIDs = [];

    // Store the date that we scanned the metadata. Used to avoid rescanning unchanged files.
    public Carbon $dateScanned;

    // Cover art (used by ArtExtractor)
    public bool $hasCoverArt = false;

    /**
     * Create a new class instance.
     */
    public function __construct(Audio $audio)
    {
        $this->audio = $audio;
        $this->meta = $audio->getMetadata();
    }

    public function extractProperties(): void
    {
        $this->path = $this->audio->getPath();
        $this->size = $this->meta->getFileSize();
        $this->duration = $this->meta->getDurationSeconds();
        $this->bitrate = $this->meta->getBitrate();
        $this->dateScanned = Carbon::instance($this->meta->getModifiedAt() ?? now());
        $this->hasCoverArt = $this->audio->hasCover();
    }

    public function extractMetadata(): void
    {
        $this->title = $this->audio->getTitle() ?? $this->meta->getFilename();

        // Split the artist on delimiters
        $this->artists = preg_split('/;\s*|\/\s*|,\s*|&\s*/', $this->audio->getArtist() ?? "");

        $this->album = $this->audio->getAlbum();

        // Split the album artist on delimiters
        $this->albumArtists = preg_split('/;\s*|\/\s*|,\s*|&\s*/', $this->audio->getAlbumArtist() ?? "");

        $this->discNumber = $this->audio->getDiscNumberInt();
        $this->trackNumber = $this->audio->getTrackNumberInt();
        $this->year = $this->audio->getYear();
    }

    public function setAlbumFields(?int $albumID): void
    {
        $this->albumID = $albumID;
    }

    public function setArtistFields(array $artistIDs): void
    {
        $this->artistIDs = $artistIDs;
    }

    public function setAlbumArtistFields(array $albumArtistIDs): void
    {
        $this->albumArtistIDs = $albumArtistIDs;
    }

    public function getCoverArt(): ?AudioCover
    {
        return $this->audio->getCover();
    }
}
