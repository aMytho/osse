<?php

namespace App\Services\MusicProcessor;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use Illuminate\Support\Collection;
use Kiwilan\Audio\Audio;

class MusicProcessor
{
    private array $supportedExtensions = ["mp3", "wav", "ogg", "opus", "flac"];
    private Collection $files;
    /**
     * @var Collection<int, MusicMetadata> $filesMetadata
     */
    private Collection $filesMetadata;

    /**
     * Create a new class instance.
     * @param Collection<array-key,mixed> $files
     */
    public function __construct(Collection $files)
    {
        // First, filter out the filetypes. We only want audo.
        $this->files = $files->filter(fn ($f) => in_array($f->getExtension(), $this->supportedExtensions));
    }

    public function scan(): void
    {
        $existingTracksForDirectory = Track::whereIn('location', $this->files->map(fn ($f) => $f->getRealPath()))->get();
        $this->filesMetadata = collect();

        foreach ($this->files as $file) {
            // Make sure that we don't scan a file we already have.
            if ($existingTracksForDirectory->some(function ($f) use ($file) {
                return $f->location == $file->getRealPath() && $f->updated_at == $file->getMTime();
            })) {
                continue;
            }

            // Read the audio file and extract the data.
            $audio = Audio::read($file->getRealPath());
            $metadata = new MusicMetadata($audio);
            $metadata->extractProperties();
            $metadata->extractMetadata();

            $this->filesMetadata->push($metadata);
        }

        // Insert/update existing relations (artists, albums, etc.)
        // This also sets the revelant ids on the file metadata
        $this->createArtists();
        $this->createAlbums();

        Track::insert($this->filesMetadata->map(function ($m) {
            return [
                'title' => $m->title,
                'artist_id' => $m->artistID,
                'duration' => $m->duration,
                'size' => $m->size,
                'bitrate' => $m->bitrate,
                'location' => $m->path,
                'album_id' => $m->albumID,
                'year' => $m->year,
                'track_number' => $m->trackNumber,
                'disc_number' => $m->discNumber,
            ];
        })->toArray());
    }

    private function createArtists(): void
    {
        // Get each artist from the files
        $fileArtists = collect();
        foreach ($this->filesMetadata as $file) {
            $fileArtists->push($file->artist);
            $fileArtists->push($file->albumArtist);
        }
        $fileArtists = $fileArtists->filter();

        // Get the matching artists in the DB.
        $artists = Artist::whereIn('name', $fileArtists->unique())->get();

        // Assign the IDs for each artist. Store a list of artists that are not yet in the DB.
        $newArtists = collect();
        foreach ($this->filesMetadata as $file) {
            $artist = $artists->firstWhere('name', $file->artist);
            $albumArtist = $artists->firstWhere('name', $file->albumArtist);
            $file->setArtistFields($artist?->id, $albumArtist?->id);

            // If the artist doesn't exist, add it to the list.
            if (is_null($artist)) {
                $newArtists->push($file->artist);
            }
            if (is_null($albumArtist)) {
                $newArtists->push($file->albumArtist);
            }
        }

        // Insert new artists
        if ($newArtists->isNotEmpty()) {
            Artist::insert($newArtists->filter()->unique()->map(fn ($a) => collect(['name' => $a]))->toArray());
        }
    }

    private function createAlbums(): void
    {
        // Get each album from the files
        $fileAlbums = collect();
        foreach ($this->filesMetadata as $file) {
            $fileAlbums->push($file->album);
        }
        $fileAlbums = $fileAlbums->filter();

        // Get the matching albums in the DB.
        $albums = Album::whereIn('name', $fileAlbums->unique())->get();

        // Assign the IDs for each album. Store a list of albums that are not yet in the DB.
        $newAlbums = collect();
        foreach ($this->filesMetadata as $file) {
            $album = $albums->firstWhere('name', $file->album);
            $file->setAlbumFields($album?->id);

            // If the album doesn't exist, add it to the list.
            if (is_null($album)) {
                $newAlbums->push($file->album);
            }
        }

        // Insert new albums 
        if ($newAlbums->isNotEmpty()) {
            Album::insert($newAlbums->filter()->unique()->map(fn ($a) => collect(['name' => $a]))->toArray());
        }
    }
}
