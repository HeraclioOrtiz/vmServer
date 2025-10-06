<?php

namespace Tests\Feature\Gym;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDailyTemplateTest extends TestCase
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

    public function test_crud_daily_template_with_exercises_and_sets(): void
    {
        $this->actingAs($this->professor());

        // Crear ejercicio base
        $exercise = $this->postJson('/api/admin/gym/exercises', [
            'name' => 'Press banca',
            'muscle_group' => 'Pecho',
            'equipment' => 'Barra',
        ])->assertStatus(201)->json();

        // Crear plantilla diaria con un ejercicio y sets
        $payload = [
            'title' => "Push 45' básica",
            'goal' => 'strength',
            'estimated_duration_min' => 45,
            'level' => 'beginner',
            'tags' => ['push','gym'],
            'exercises' => [
                [
                    'exercise_id' => $exercise['id'],
                    'order' => 1,
                    'notes' => 'Técnica controlada',
                    'sets' => [
                        ['set_number' => 1, 'reps_min' => 8, 'reps_max' => 10, 'rest_seconds' => 90],
                        ['set_number' => 2, 'reps_min' => 8, 'reps_max' => 10, 'rest_seconds' => 90]
                    ]
                ]
            ]
        ];

        $tpl = $this->postJson('/api/admin/gym/daily-templates', $payload)
            ->assertStatus(201)
            ->json();

        // Obtener detalle
        $this->getJson('/api/admin/gym/daily-templates/'.$tpl['id'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => "Push 45' básica"])
            ->assertJsonStructure(['exercises' => [['sets' => [['set_number','reps_min','reps_max','rest_seconds']]]]]);

        // Actualizar título y reemplazar ejercicios
        $updated = $this->putJson('/api/admin/gym/daily-templates/'.$tpl['id'], [
            'title' => "Push 45' updated",
            'exercises' => [
                [
                    'exercise_id' => $exercise['id'],
                    'order' => 1,
                    'sets' => [['set_number' => 1, 'reps_min' => 6, 'reps_max' => 8, 'rest_seconds' => 120]]
                ]
            ]
        ])->assertStatus(200)->json();

        $this->assertEquals("Push 45' updated", $updated['title']);

        // Borrar
        $this->deleteJson('/api/admin/gym/daily-templates/'.$tpl['id'])
            ->assertNoContent();
    }
}
