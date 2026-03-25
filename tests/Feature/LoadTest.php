<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoadTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_serve_more_than_100_users()
    {
        // Create 101 distinct users to simulate 100+ concurrent accounts.
        $users = User::factory()->count(101)->create();

        foreach ($users as $user) {
            // each user performs a simple authenticated request
            $response = $this->actingAs($user)->get(route('clients.index'));
            $response->assertStatus(200);
        }
    }
}
