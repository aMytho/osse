<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
    * Allow jobs to run.
    * If this method is not called, jobs will run async. This is usually a bad thing for testing.
    */
    public function runJobs(): void
    {
        config(['queue.default' => 'sync']);
    }

    /**
    * Prevents jobs from running. We fake the queue.
    * You can still test laravel job logic, but nothing is executed. They don't actually run.
    */
    public function mockJobs(): void
    {
        Queue::fake();
    }

    /**
    * Prevents events from firing.
    */
    public function mockEvents(): void
    {
        config(['broadcasting.default' => null]);
    }

    /**
    * Mock access to the filesystem.
    */
    public function mockStorage(): void
    {
        Storage::fake('test_files');
        Storage::fake('local');
        Storage::fake('public');
    }

    /**
    * Copies all files in the testing directory to our virual disk for testing.
    * This is only neccessary if you need to modify a file.
    * If you just need to scan the files, use the base_path and don't call this method!
    */
    public function copyTestMusicFiles(): void
    {   // Get all files in tests/files
        $sourceDir = base_path('tests/files');
        $files = File::allFiles($sourceDir);

        // Copy each file to the fake disk (temp_test_files)
        foreach ($files as $file) {
            $relativePath = str_replace($sourceDir, '', $file->getRealPath());
            Storage::disk('test_files')->put($relativePath, File::get($file));
        }
    }
}
