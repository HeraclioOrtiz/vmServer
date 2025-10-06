<?php

namespace Tests\Feature\Gym;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWeeklyAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function professor(): User
    {
        return User::factory()->create([
            'dni' => (string) random_int(10000000, 99999999),
            'password' => 'secret123',
            'user_type' => 'local',
            'is_professor' => true,
        ]);
    }

    protected function student(): User
    {
        return User::factory()->create([
            'dni' => (string) random_int(10000000, 99999999),
            'password' => 'secret123',
            'user_type' => 'local',
            'is_professor' => false,
        ]);
    }

    public function test_create_show_update_delete_weekly_assignment(): void
    {
        $this->actingAs($this->professor());
        $student = $this->student();

        // Crear ejercicio y plantilla diaria para tener datos coherentes (aunque vamos a usar snapshot manual)
        $this->postJson('/api/admin/gym/exercises', [
            'name' => 'Sentadilla con barra',
            'muscle_group' => 'Piernas',
            'equipment' => 'Barra',
        ])->assertStatus(201);

        $payload = [
            'user_id' => $student->id,
            'week_start' => '2025-09-22',
            'week_end' => '2025-09-28',
            'source_type' => 'manual',
            'days' => [
                [
                    'weekday' => 1,
                    'date' => '2025-09-22',
                    'title' => "Full-Body 45",
                    'exercises' => [
                        [
                            'order' => 1,
                            'name' => 'Sentadilla con barra',
                            'muscle_group' => 'Piernas',
                            'equipment' => 'Barra',
                            'sets' => [
                                ['set_number' => 1, 'reps_min' => 8, 'reps_max' => 8, 'rest_seconds' => 120]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Create
        $assignment = $this->postJson('/api/admin/gym/weekly-assignments', $payload)
            ->assertStatus(201)
            ->json();

        $this->assertEquals($student->id, $assignment['user_id']);

        // Show
        $show = $this->getJson('/api/admin/gym/weekly-assignments/'.$assignment['id'])
            ->assertStatus(200)
            ->json();
        
        $this->assertEquals('2025-09-22', substr($show['week_start'], 0, 10));

        // Index filter by user
        $this->getJson('/api/admin/gym/weekly-assignments?user_id='.$student->id)
            ->assertStatus(200)
            ->assertJsonFragment(['user_id' => $student->id]);

        // Update notes
        $this->putJson('/api/admin/gym/weekly-assignments/'.$assignment['id'], [
            'notes' => 'Progresión ligera'
        ])->assertStatus(200)->assertJsonFragment(['notes' => 'Progresión ligera']);

        // Delete
        $this->deleteJson('/api/admin/gym/weekly-assignments/'.$assignment['id'])
            ->assertNoContent();
    }
}
