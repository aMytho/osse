<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Queue;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->user = User::factory()->create();
    }
}
