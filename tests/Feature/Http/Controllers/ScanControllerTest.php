<?php

namespace Tests\Feature\Http\Controllers;

use App\Events\ScanCancelled;
use App\Jobs\ScanMusic;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('Controllers')]
#[Group('ScanMusic')]
class ScanControllerTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_scan_route_starts_scan(): void
    {
        $this->mockJobs();
        $this->actingAs($this->user);

        $response = $this->post(route('scan.start'))
            ->assertOk();

        Queue::assertPushed(ScanMusic::class);
    }

    public function test_only_one_scan_can_be_active(): void
    {
        $this->mockJobs();
        $this->actingAs($this->user);

        $response = $this->post(route('scan.start'));
        $response = $this->post(route('scan.start'));

        Queue::assertCount(1);
    }

    public function test_progress_works(): void
    {
        $this->mockEvents();

        Cache::spy();
        config(['scan.directories' => [base_path('tests/files/no_metadata')]]);

        // We have to run the job in this thread so it gets the cache spy instance.
        new ScanMusic()->handle();

        // Check that the cache events were stored.
        // We do this by examining the cache. Ideally we would make the HTTP request to the /scan route, but we don't know when the directories are being processed.
        Cache::shouldHaveReceived('put')->twice();
        Cache::shouldHaveReceived('put')->with('scan_progress', ['total_directories' => 1, 'finished_count' => 0]);
        Cache::shouldHaveReceived('put')->with('scan_progress', ['total_directories' => 1, 'finished_count' => 1]);

        // Check that the HTTP route now shows an inactive scan since its complete.
        // This is the only thing we can manually test for the route, but we tested the expected responses with the cache.
        $this->actingAs($this->user)
            ->get(route('scan.status'))
            ->assertOk()
            ->assertJson([
                'active' => false
            ]);
    }

    public function test_cancel_works(): void
    {
        $this->mockEvents();
        $this->runJobs();
        $this->actingAs($this->user);

        config(['scan.directories' => [base_path('tests/files/no_metadata')]]);

        // We have to run the job in this thread so we can cancel after the job has been created.
        // If a scans are cancelled before the job is ran, it is reset and the job runs normally.
        $job = new ScanMusic();
        $this->post(route('scan.cancel'));

        // Check that the scan was cancelled.
        $job->dispatchSync();
        Event::assertDispatched(ScanCancelled::class);
    }

    public function test_cancel_wont_stop_new_scan()
    {
        $this->mockEvents();
        $this->runJobs();
        $this->actingAs($this->user);

        config(['scan.directories' => [base_path('tests/files/no_metadata')]]);

        $this->post(route('scan.cancel'));
        $this->post(route('scan.start'));

        // The scan shouldn't have been cancelled.
        Event::assertNotDispatched(ScanCancelled::class);
    }

}
