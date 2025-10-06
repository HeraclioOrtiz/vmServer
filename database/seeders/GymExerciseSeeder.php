<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GymExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $exercises = [
            [
                'name' => 'Sentadilla con barra',
                'muscle_group' => 'Piernas',
                'movement_pattern' => 'Sentadilla',
                'equipment' => 'Barra',
                'difficulty' => 'intermediate',
                'tags' => json_encode(['compound','barbell','strength']),
                'instructions' => 'Mantener columna neutra, bajar hasta paralelo.',
                'tempo' => '3-1-1',
            ],
            [
                'name' => 'Press banca',
                'muscle_group' => 'Pecho',
                'movement_pattern' => 'Empuje horizontal',
                'equipment' => 'Barra',
                'difficulty' => 'intermediate',
                'tags' => json_encode(['compound','barbell','strength']),
                'instructions' => 'Pausa breve en el pecho, codos a 45°.',
                'tempo' => '2-1-1',
            ],
            [
                'name' => 'Peso muerto',
                'muscle_group' => 'Espalda/Bisagra',
                'movement_pattern' => 'Bisagra de cadera',
                'equipment' => 'Barra',
                'difficulty' => 'intermediate',
                'tags' => json_encode(['compound','barbell']),
                'instructions' => 'Mantener la barra pegada a las piernas.',
                'tempo' => '2-0-2',
            ],
            [
                'name' => 'Remo con mancuernas',
                'muscle_group' => 'Espalda',
                'movement_pattern' => 'Tracción horizontal',
                'equipment' => 'Mancuernas',
                'difficulty' => 'beginner',
                'tags' => json_encode(['dumbbell']),
                'instructions' => 'Escápulas retraídas, torso estable.',
                'tempo' => '2-1-1',
            ],
            [
                'name' => 'Press militar',
                'muscle_group' => 'Hombros',
                'movement_pattern' => 'Empuje vertical',
                'equipment' => 'Barra',
                'difficulty' => 'intermediate',
                'tags' => json_encode(['barbell']),
                'instructions' => 'Glúteos y abdomen firmes.',
                'tempo' => '2-0-2',
            ],
            [
                'name' => 'Dominadas',
                'muscle_group' => 'Espalda',
                'movement_pattern' => 'Tracción vertical',
                'equipment' => 'Peso corporal',
                'difficulty' => 'advanced',
                'tags' => json_encode(['bodyweight']),
                'instructions' => 'Mentón por encima de la barra, control.',
                'tempo' => '2-1-2',
            ],
            [
                'name' => 'Zancadas',
                'muscle_group' => 'Piernas',
                'movement_pattern' => 'Desplante',
                'equipment' => 'Mancuernas',
                'difficulty' => 'beginner',
                'tags' => json_encode(['dumbbell','unilateral']),
                'instructions' => 'Paso largo, rodilla alineada.',
                'tempo' => '2-0-2',
            ],
            [
                'name' => 'Plancha',
                'muscle_group' => 'Core',
                'movement_pattern' => 'Anti-extensión',
                'equipment' => 'Peso corporal',
                'difficulty' => 'beginner',
                'tags' => json_encode(['core','isometric','bodyweight']),
                'instructions' => 'Cuerpo en línea, glúteos activos.',
                'tempo' => null,
            ],
            [
                'name' => 'Curl bíceps con mancuernas',
                'muscle_group' => 'Bíceps',
                'movement_pattern' => 'Flexión de codo',
                'equipment' => 'Mancuernas',
                'difficulty' => 'beginner',
                'tags' => json_encode(['dumbbell','isolation']),
                'instructions' => 'Codos pegados al torso, control.',
                'tempo' => '2-1-2',
            ],
            [
                'name' => 'Extensión de tríceps en polea',
                'muscle_group' => 'Tríceps',
                'movement_pattern' => 'Extensión de codo',
                'equipment' => 'Polea',
                'difficulty' => 'beginner',
                'tags' => json_encode(['cable','isolation']),
                'instructions' => 'Codos fijos, extensión completa.',
                'tempo' => '2-1-2',
            ],
        ];

        foreach ($exercises as $e) {
            DB::table('gym_exercises')->updateOrInsert(
                ['name' => $e['name']],
                array_merge($e, ['updated_at' => $now, 'created_at' => $now])
            );
        }
    }
}
