<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ScanMusic;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('Jobs')]
#[Group('ScanMusic')]
#[Group('current')]
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
}
