<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ScanMusic;
use App\Models\Track;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Tests\TestCase;

#[Group('Jobs')]
#[Group('ScanMusic')]
class ScanMusicTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runJobs();
        $this->mockEvents();
    }

    public function test_files_are_scanned(): void
    {
        $this->assertDatabaseEmpty('tracks');

        // This dir has 1 file.
        config(['scan.directories' => [base_path('tests/files/no_metadata')]]);
        ScanMusic::dispatchSync();
        
        $this->assertDatabaseCount('tracks', 1);
    }

    public function test_files_without_title_meta_use_filename_as_title(): void
    {
        // The file is this dir has no metadata
        config(['scan.directories' => [base_path('tests/files/no_metadata')]]);
        ScanMusic::dispatchSync();

        $this->assertDatabaseHas('tracks', ['title' => 'test_no_metadata.mp3']);
    }

    public function test_metadata_is_picked_up(): void
    {
        // These files have metadata
        config(['scan.directories' => [base_path('tests/files/has_metadata')]]);
        ScanMusic::dispatchSync();

        $this->assertDatabaseCount('tracks', 2);
        // Each has the same artist and album.
        $this->assertDatabaseCount('artists', 1);
        $this->assertDatabaseCount('albums', 1);

        // Test that artists and albums are grouped together.
        // Test that the track number was picked up.
        // Test title was picked up.
        $this->assertDatabaseHas('tracks', [
            'title' => 'track_one',
            'artist_id' => 1,
            'album_id' => 1,
            'track_number' => 1,
            'disc_number' => 1,
        ]);
        $this->assertDatabaseHas('tracks', [
            'title' => 'track_two',
            'artist_id' => 1,
            'album_id' => 1,
            'track_number' => 2,
            'disc_number' => 1,
        ]);
    }

    public function test_unused_tracks_relations_are_removed(): void
    {
        $this->mockStorage();
        $this->copyTestMusicFiles();
        $testFilePath = Storage::disk('test_files')->path('');

        // Scan in 2 files with metadata.
        config(['scan.directories' => [$testFilePath . 'has_metadata']]);
        ScanMusic::dispatchSync();

        $this->assertDatabaseCount('artists', 1);
        $this->assertDatabaseCount('albums', 1);
        $this->assertDatabaseCount('tracks', 2);

        // Delete 1 file, 1 should be left.
        Storage::disk('test_files')->delete('has_metadata/track_two.mp3');
        ScanMusic::dispatchSync();
        $this->assertDatabaseCount('tracks', 1);

        // Delete the entire directory. Nothing should be left.
        // We set the scan dirs to a new dir with nothing in it to test the old tracks in deleted(unscanned) dirs are pruned.
        Storage::disk('test_files')->deleteDirectory('has_metadata');
        Storage::disk('test_files')->put('empty/foo', 'baz');
        config(['scan.directories' => [$testFilePath . 'empty']]);
        ScanMusic::dispatchSync();

        $this->assertDatabaseEmpty('artists');
        $this->assertDatabaseEmpty('albums');
        $this->assertDatabaseEmpty('tracks');
    }

    public function test_track_covers_are_created_without_duplicates(): void
    {
        $this->mockStorage();
        $this->copyTestMusicFiles();

        $testFilePath = Storage::disk('test_files')->path('');

        // Scan in 2 files with covers. They are the same cover.
        config(['scan.directories' => [$testFilePath . 'covers']]);
        ScanMusic::dispatchSync();

        // The ID is 1 since the art is the first row.
        // Since the files are the same, only 1 file should be extracted.
        $this->assertDatabaseHas('tracks', ['cover_art_id' => 1]);
        $this->assertDatabaseCount('cover_art', 1);
        $this->assertEquals(count(Storage::disk('local')->files('cover-art')), 1);
    }

    #[Group('current')]
    public function test_track_covers_are_deleted_when_track_is_deleted(): void
    {
        $this->mockStorage();
        $this->copyTestMusicFiles();
        $testFilePath = Storage::disk('test_files')->path('');

        // Scan in 2 files with covers. They are the same cover.
        config(['scan.directories' => [$testFilePath . 'covers']]);
        ScanMusic::dispatchSync();

        $this->assertDatabaseCount('cover_art', 1);

        // Create an empty dir to scan. Since the old dir isn't included, its tracks will be deleted.
        config(['app.first' => true]);
        Storage::disk('test_files')->put('empty/foo', 'baz');
        config(['scan.directories' => [$testFilePath . 'empty']]);
        ScanMusic::dispatchSync();

        $this->assertDatabaseCount('cover_art', 0);
        $this->assertEquals(count(Storage::disk('local')->files('cover-art')), 0);
    }

    public function test_scanning_a_invalid_directory_fails(): void
    {
        $this->mockStorage();
        config(['scan.directories' => ['/fake-directory']]);

        $this->expectException(DirectoryNotFoundException::class);
        ScanMusic::dispatchSync();
    }
}
