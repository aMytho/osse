<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\ScanMusic;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('Controllers')]
#[Group('ScanMusic')]
class ScanControllerTest extends TestCase
{
    public function test_scan_route_starts_scan(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('scan.start'))
            ->assertOk();

        Queue::assertPushed(ScanMusic::class);
    }

    public function test_only_one_scan_can_be_active(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('scan.start'));
        $response = $this->post(route('scan.start'));

        Queue::assertCount(1);
    }
}
