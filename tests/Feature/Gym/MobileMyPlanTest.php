<?php

namespace Tests\Feature\Gym;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileMyPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function student(): User
    {
        return User::factory()->create([
            'dni' => (string) random_int(10000000, 99999999),
            'password' => 'secret123',
            'user_type' => 'local',
            'is_professor' => false,
        ]);
    }

    public function test_my_week_and_my_day_endpoints(): void
    {
        $student = $this->student();
        $this->actingAs($student);

        // Sin asignación: my-week responde vacío
        $this->getJson('/api/gym/my-week?date=2025-09-22')
            ->assertStatus(200)
            ->assertJson([
                'week_start' => null,
                'week_end' => null,
                'days' => []
            ]);

        // Crear asignación rápida por admin: usamos actingAs profesor para crear
        $prof = User::factory()->create([
            'dni' => (string) random_int(10000000, 99999999),
            'password' => 'secret123',
            'user_type' => 'local',
            'is_professor' => true,
        ]);
        $this->actingAs($prof);

        $payload = [
            'user_id' => $student->id,
            'week_start' => '2025-09-22',
            'week_end' => '2025-09-28',
            'source_type' => 'manual',
            'days' => [
                [
                    'weekday' => 1,
                    'date' => '2025-09-22',
                    'title' => 'FB 45',
                    'exercises' => [[
                        'order' => 1, 'name' => 'Sentadilla con barra',
                        'sets' => [['set_number' => 1, 'reps_min' => 8, 'reps_max' => 8, 'rest_seconds' => 120]]
                    ]]
                ]
            ]
        ];

        $this->postJson('/api/admin/gym/weekly-assignments', $payload)
            ->assertStatus(201);

        // Ahora como alumno consultamos mi semana y mi día
        $this->actingAs($student);

        $this->getJson('/api/gym/my-week?date=2025-09-22')
            ->assertStatus(200)
            ->assertJsonFragment(['week_start' => '2025-09-22']);

        $this->getJson('/api/gym/my-day?date=2025-09-22')
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'FB 45'])
            ->assertJsonStructure(['exercises' => [['sets' => [['reps','rest_seconds']]]]]);
    }
}
