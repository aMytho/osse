<?php

namespace App\Services\MusicProcessor;

use Kiwilan\Audio\Audio;
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
    public ?string $artist;
    public ?string $album;
    public ?string $albumArtist;
    public ?int $discNumber;
    public ?int $trackNumber;
    public ?int $year;

    // DB Data (after identification)
    public ?int $albumID;
    public ?int $artistID;
    public ?int $albumArtistID;

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
    }

    public function extractMetadata(): void
    {
        $this->title = $this->audio->getTitle() ?? $this->meta->getFilename();
        $this->artist = $this->audio->getArtist();
        $this->album = $this->audio->getAlbum();
        $this->albumArtist = $this->audio->getAlbumArtist();
        $this->discNumber = $this->audio->getDiscNumberInt();
        $this->trackNumber = $this->audio->getTrackNumberInt();
        $this->year = $this->audio->getYear();
    }

    public function setAlbumFields(?int $albumID): void
    {
        $this->albumID = $albumID;
    }

    public function setArtistFields(?int $artistID, ?int $albumArtistID): void
    {
        $this->artistID = $artistID;
        $this->albumArtistID = $albumArtistID;
    }
}
