<?php

namespace Tests\Feature\Gym;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'dni' => (string) random_int(10000000, 99999999),
            'password' => 'secret123',
            'user_type' => 'local',
        ], $attrs));
    }

    public function test_non_professor_cannot_access_admin_gym_routes(): void
    {
        $user = $this->makeUser(['is_professor' => false]);
        $this->actingAs($user);

        $this->getJson('/api/admin/gym/exercises')
            ->assertStatus(403);
    }

    public function test_professor_can_access_admin_gym_routes(): void
    {
        $user = $this->makeUser(['is_professor' => true]);
        $this->actingAs($user);

        $this->getJson('/api/admin/gym/exercises')
            ->assertStatus(200);
    }
}
