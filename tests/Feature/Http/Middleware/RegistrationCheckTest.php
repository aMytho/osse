<?php

namespace Tests\Feature\Http\Middleware;

use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('Auth')]
#[Group('Middleware')]
class RegistrationCheckTest extends TestCase
{
    public function test_when_registration_closed_users_cant_be_created(): void
    {
        config(['auth.allow_registration' => false]);
        $response = $this->post('register', ['username' => 'osse2', 'password' => 'abc123'])
            ->assertForbidden();
    }

    public function test_when_registration_allowed_users_can_be_created(): void
    {
        config(['auth.allow_registration' => true]);
        $response = $this->post('register', ['username' => 'osse2', 'password' => 'abc123'])
            ->assertCreated();
    }
}
