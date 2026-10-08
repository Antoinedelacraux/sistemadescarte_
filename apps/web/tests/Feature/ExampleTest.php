<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Unauthenticated user is redirected to login, and login returns 200.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');

        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
    }
}
