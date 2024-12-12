<?php

namespace Tests\Feature;

use App\Jobs\ScanMusic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('Scan')]
#[Group('Jobs')]
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
