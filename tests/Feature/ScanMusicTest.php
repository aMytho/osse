<?php

namespace Tests\Feature;

use App\Jobs\ScanMusic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
* @group Jobs
* @group Scan
*/
class ScanMusicTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        ScanMusic::dispatchSync();
    }
}
