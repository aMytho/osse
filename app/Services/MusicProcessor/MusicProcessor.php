<?php

namespace App\Services\MusicProcessor;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Kiwilan\Audio\Audio;

class MusicProcessor
{
    private array $supportedExtensions = ["mp3", "wav", "ogg", "opus", "flac"];
    private Collection $files;
    /**
     * @var Collection<int, MusicMetadata> $filesMetadata
     */
    private Collection $filesMetadata;
    private Carbon $date;

    public int $filesScanned = 0;
    public int $filesSkipped = 0;

    /**
     * Create a new class instance.
     * @param Collection<array-key,mixed> $files
     */
    public function __construct(Collection $files)
    {
        // First, filter out the filetypes. We only want audio.
        $this->files = $files->filter(fn ($f) => in_array($f->getExtension(), $this->supportedExtensions));
        $this->date = now();
    }

    public function scan(): void
    {
        $existingTracksForDirectory = Track::whereIn('location', $this->files->map(fn ($f) => $f->getRealPath()))->get();
        $this->filesMetadata = collect();

        foreach ($this->files as $file) {
            // Make sure that we don't scan a file we already have.
            if ($existingTracksForDirectory->some(function ($f) use ($file) {
                return $f->location == $file->getRealPath() && $file->getMTime() == $f->scanned_at->timestamp;
            })) {
                $this->filesSkipped += 1;
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

        Track::insert(
            $this->filesMetadata->map(fn ($m) => [
                'title' => $m->title,
                'duration' => $m->duration,
                'size' => $m->size,
                'bitrate' => $m->bitrate,
                'location' => $m->path,
                'album_id' => $m->albumID ?? null,
                'year' => $m->year,
                'track_number' => $m->trackNumber,
                'disc_number' => $m->discNumber,
                'scanned_at' => $m->dateScanned,
                'created_at' => $this->date
            ])->toArray()
        );

        // We need to link the track to the artist.
        $tracks = Track::whereIn('location', $this->filesMetadata->pluck('path'))->get();
        $this->linkArtistsToTracks($tracks);

        $this->filesScanned = $this->filesMetadata->count();
    }

    private function createArtists(): void
    {
        // Get each artist from the files
        $fileArtists = collect();
        foreach ($this->filesMetadata as $file) {
            $fileArtists->push($file->artists);
            $fileArtists->push($file->albumArtists);
        }
        $fileArtists = $fileArtists->filter()->flatten()->unique();

        // Get the matching artists in the DB.
        $artists = Artist::whereIn('name', $fileArtists)->get();

        // Assign the IDs for each artist. Store a list of artists that are not yet in the DB.
        $newArtists = collect();
        foreach ($this->filesMetadata as $file) {
            $fileArtists = $artists->whereIn('name', $file->artists);
            $fileAlbumArtists = $artists->whereIn('name', $file->albumArtists);

            if ($fileArtists->isNotEmpty()) {
                $file->setArtistFields($fileArtists->pluck('id')->toArray());
            } else {
                $newArtists->push($file->artists);
            }

            if ($fileAlbumArtists->isNotEmpty()) {
                $file->setAlbumArtistFields($fileAlbumArtists->pluck('id')->toArray());
            } else {
                $newArtists->push($file->albumArtists);
            }
        }

        // Get a unique, non null list of artists to add.
        $newArtists = $newArtists->filter()->flatten()->unique();

        // Add the artists.
        Artist::insert($newArtists
            ->map(fn ($a) => ['name' => $a, 'created_at' => $this->date])
            ->toArray()
        );

        // For each newly inserted artist, assign the artist ID or null if none.
        $artists = Artist::whereIn('name', $newArtists)->get();
        foreach ($this->filesMetadata->filter(fn ($f) => count($f->artistIDs) == 0) as $file) {
            $fileArtists = $artists->whereIn('name', $file->artists);
            $file->setArtistFields($fileArtists->pluck('id')->toArray());
        }

        foreach ($this->filesMetadata->whereNull('albumArtistID') as $file) {
            $fileArtists = $artists->whereIn('name', $file->albumArtists);
            $file->setAlbumArtistFields($fileArtists->pluck('id')->toArray());
        }
    }

    private function createAlbums(): void
    {
        // Get each album from the files
        $fileAlbums = collect();
        foreach ($this->filesMetadata as $file) {
            $fileAlbums->push(collect([
                'title' => $file->title,
                'album' => $file->album,
                'albumArtistsIDs' => $file->albumArtistIDs,
            ]));
        }
        $fileAlbums = $fileAlbums->filter()->unique('album');

        // Get the matching albums in the DB.
        $albums = Album::whereIn('name', $fileAlbums->pluck('album'))->get();

        // Assign the IDs for each album if one was found. Store a list of albums that are not yet in the DB.
        $newAlbums = collect();
        foreach ($this->filesMetadata as $file) {
            $album = $albums->firstWhere('name', $file->album);

            if ($album) {
                $file->setAlbumFields($album->id);
            } else {
                $newAlbums->push($file->album);
            }
        }

        // Get a unique, non null list of albums to add.
        $newAlbums = $newAlbums->filter()->unique();

        // Stop if no new albums to add
        if ($newAlbums->isEmpty()) {
            return;
        }

        // Get the artists from the files. They may be the album artist which we need to link to the new albums
        $artists = Artist::select(['name', 'id'])->get();

        // Add the albums.
        Album::insert($newAlbums
            ->map(fn ($a) => [
                'name' => $a,
                'created_at' => $this->date
            ])
            ->toArray()
        );

        // For each newly inserted album, assign the album ID or null if no album.
        $newAlbums = Album::whereIn('name', $newAlbums)->get();
        $albums = $newAlbums->merge($albums);
        foreach ($this->filesMetadata->whereNull('albumID') as $file) {
            $album = $albums->firstWhere('name', $file->album);
            $file->setAlbumFields($album?->id ?? null);
        }

        $artistsToInsert = [];
        foreach ($newAlbums as $album) {
            $artistOrder = 1;
            foreach ($artists->whereIn('id', $this->filesMetadata->firstWhere('albumID', $album->id)->albumArtistIDs) as $artist) {
                array_push($artistsToInsert, [
                    'album_id' => $album->id,
                    'artist_id' => $artist->id,
                    'artist_order' => $artistOrder
                ]);
                $artistOrder++;
            }
        }

        DB::table('album_artist')->insert($artistsToInsert);
    }

    private function linkArtistsToTracks(Collection $tracks): void
    {
        $artists = [];
        foreach ($this->filesMetadata as $file) {
            if (count($file->artistIDs) == 0) return;

            $artistOrder = 1;
            foreach ($file->artistIDs as $artistID) {
                array_push($artists, ['artist_id' => $artistID, 'artist_order' => $artistOrder, 'track_id' => $tracks->firstWhere('location', $file->path)->id]);
                $artistOrder++;
            }
        }


        DB::table('track_artist')->insert($artists);
    }
    public function getScannedFiles(): Collection
    {
        return $this->filesMetadata;
    }

    public function getAllFiles(): Collection
    {
        return $this->files;
    }
}
