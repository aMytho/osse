<?php

namespace Tests\Feature;

use App\Jobs\ScanMusic;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('Jobs')]
#[Group('ScanMusic')]
class ScanMusicTest extends TestCase
{
    public function test_example(): void
    {
        ScanMusic::dispatchSync();
    }
}
