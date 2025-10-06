<?php

namespace Tests\Feature\Gym;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWeeklyTemplateTest extends TestCase
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

    public function test_crud_weekly_template_with_days(): void
    {
        $this->actingAs($this->professor());

        // Crear ejercicio y plantilla diaria para referenciar en el semanal
        $exercise = $this->postJson('/api/admin/gym/exercises', [
            'name' => 'Sentadilla con barra',
            'muscle_group' => 'Piernas',
            'equipment' => 'Barra',
        ])->assertStatus(201)->json();

        $daily = $this->postJson('/api/admin/gym/daily-templates', [
            'title' => "Full-Body 45'",
            'goal' => 'general',
            'estimated_duration_min' => 45,
            'level' => 'beginner',
            'exercises' => [[
                'exercise_id' => $exercise['id'],
                'order' => 1,
                'sets' => [['set_number' => 1, 'reps_min' => 10, 'reps_max' => 10, 'rest_seconds' => 90]]
            ]]
        ])->assertStatus(201)->json();

        // Crear plantilla semanal
        $weekly = $this->postJson('/api/admin/gym/weekly-templates', [
            'title' => 'Plan semanal básico',
            'goal' => 'general',
            'split' => 'FullBody',
            'days_per_week' => 3,
            'days' => [
                ['weekday' => 1, 'daily_template_id' => $daily['id']],
                ['weekday' => 3, 'daily_template_id' => $daily['id']],
                ['weekday' => 5, 'daily_template_id' => $daily['id']],
            ]
        ])->assertStatus(201)->json();

        $this->assertEquals(3, count($weekly['days']));

        // Obtener
        $this->getJson('/api/admin/gym/weekly-templates/'.$weekly['id'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Plan semanal básico']);

        // Actualizar (cambiar days_per_week y días)
        $updated = $this->putJson('/api/admin/gym/weekly-templates/'.$weekly['id'], [
            'days_per_week' => 2,
            'days' => [
                ['weekday' => 2, 'daily_template_id' => $daily['id']],
                ['weekday' => 4, 'daily_template_id' => $daily['id']],
            ]
        ])->assertStatus(200)->json();

        $this->assertEquals(2, $updated['days_per_week']);
        $this->assertEquals(2, count($updated['days']));

        // Borrar
        $this->deleteJson('/api/admin/gym/weekly-templates/'.$weekly['id'])
            ->assertNoContent();
    }
}
