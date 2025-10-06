<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🏗️ === REGENERANDO PLANTILLAS DIARIAS === 🏗️\n\n";

// PASO 1: Eliminar todas las plantillas existentes
echo "PASO 1: Eliminando plantillas existentes...\n";

$existingTemplates = \App\Models\Gym\DailyTemplate::all();
echo "📊 Plantillas existentes: " . $existingTemplates->count() . "\n";

foreach ($existingTemplates as $template) {
    echo "  - Eliminando: {$template->title}\n";
    $template->delete();
}

echo "✅ Todas las plantillas eliminadas\n\n";

// PASO 2: Obtener ejercicios disponibles
echo "PASO 2: Obteniendo ejercicios disponibles...\n";

$exercises = \App\Models\Gym\Exercise::all();
echo "📊 Ejercicios disponibles: " . $exercises->count() . "\n";

if ($exercises->count() < 10) {
    echo "❌ ERROR: Necesitamos al menos 10 ejercicios para crear plantillas variadas\n";
    exit(1);
}

echo "✅ Ejercicios suficientes para crear plantillas\n\n";

// PASO 3: Definir plantillas a crear
echo "PASO 3: Definiendo plantillas a crear...\n";

$templateDefinitions = [
    // FUERZA
    [
        'title' => 'Fuerza Upper Body 60\'',
        'goal' => 'strength',
        'level' => 'intermediate',
        'duration' => 60,
        'tags' => ['strength', 'upper', 'gym'],
        'muscle_groups' => ['Pecho', 'Espalda', 'Hombros', 'Brazos'],
        'exercise_count' => 5
    ],
    [
        'title' => 'Fuerza Lower Body 50\'',
        'goal' => 'strength',
        'level' => 'intermediate',
        'duration' => 50,
        'tags' => ['strength', 'lower', 'legs'],
        'muscle_groups' => ['Piernas', 'Glúteos'],
        'exercise_count' => 4
    ],
    [
        'title' => 'Fuerza Full Body 70\'',
        'goal' => 'strength',
        'level' => 'advanced',
        'duration' => 70,
        'tags' => ['strength', 'fullbody', 'compound'],
        'muscle_groups' => ['Piernas', 'Pecho', 'Espalda'],
        'exercise_count' => 6
    ],
    
    // HIPERTROFIA
    [
        'title' => 'Hipertrofia Pecho + Tríceps 55\'',
        'goal' => 'hypertrophy',
        'level' => 'intermediate',
        'duration' => 55,
        'tags' => ['hypertrophy', 'chest', 'triceps'],
        'muscle_groups' => ['Pecho', 'Brazos'],
        'exercise_count' => 5
    ],
    [
        'title' => 'Hipertrofia Espalda + Bíceps 55\'',
        'goal' => 'hypertrophy',
        'level' => 'intermediate',
        'duration' => 55,
        'tags' => ['hypertrophy', 'back', 'biceps'],
        'muscle_groups' => ['Espalda', 'Brazos'],
        'exercise_count' => 5
    ],
    [
        'title' => 'Hipertrofia Piernas + Glúteos 60\'',
        'goal' => 'hypertrophy',
        'level' => 'intermediate',
        'duration' => 60,
        'tags' => ['hypertrophy', 'legs', 'glutes'],
        'muscle_groups' => ['Piernas', 'Glúteos'],
        'exercise_count' => 5
    ],
    
    // RESISTENCIA
    [
        'title' => 'Cardio HIIT 30\'',
        'goal' => 'endurance',
        'level' => 'beginner',
        'duration' => 30,
        'tags' => ['cardio', 'hiit', 'endurance'],
        'muscle_groups' => ['Core', 'Piernas'],
        'exercise_count' => 4
    ],
    [
        'title' => 'Circuito Metabólico 40\'',
        'goal' => 'endurance',
        'level' => 'intermediate',
        'duration' => 40,
        'tags' => ['metabolic', 'circuit', 'cardio'],
        'muscle_groups' => ['Core', 'Piernas', 'Brazos'],
        'exercise_count' => 6
    ],
    [
        'title' => 'Resistencia Funcional 45\'',
        'goal' => 'endurance',
        'level' => 'intermediate',
        'duration' => 45,
        'tags' => ['functional', 'endurance', 'bodyweight'],
        'muscle_groups' => ['Core', 'Piernas'],
        'exercise_count' => 5
    ],
    
    // MOVILIDAD Y CORE
    [
        'title' => 'Core Intensivo 35\'',
        'goal' => 'general',
        'level' => 'beginner',
        'duration' => 35,
        'tags' => ['core', 'stability', 'abs'],
        'muscle_groups' => ['Core'],
        'exercise_count' => 4
    ],
    [
        'title' => 'Movilidad + Flexibilidad 25\'',
        'goal' => 'mobility',
        'level' => 'beginner',
        'duration' => 25,
        'tags' => ['mobility', 'flexibility', 'recovery'],
        'muscle_groups' => ['Core'],
        'exercise_count' => 3
    ],
    [
        'title' => 'Activación Matutina 20\'',
        'goal' => 'general',
        'level' => 'beginner',
        'duration' => 20,
        'tags' => ['morning', 'activation', 'mobility'],
        'muscle_groups' => ['Core', 'Piernas'],
        'exercise_count' => 3
    ],
    
    // PRINCIPIANTES
    [
        'title' => 'Iniciación Full Body 40\'',
        'goal' => 'general',
        'level' => 'beginner',
        'duration' => 40,
        'tags' => ['beginner', 'fullbody', 'basic'],
        'muscle_groups' => ['Piernas', 'Pecho', 'Core'],
        'exercise_count' => 4
    ],
    [
        'title' => 'Básico Upper Body 35\'',
        'goal' => 'general',
        'level' => 'beginner',
        'duration' => 35,
        'tags' => ['beginner', 'upper', 'basic'],
        'muscle_groups' => ['Pecho', 'Espalda', 'Brazos'],
        'exercise_count' => 4
    ],
    [
        'title' => 'Básico Lower Body 35\'',
        'goal' => 'general',
        'level' => 'beginner',
        'duration' => 35,
        'tags' => ['beginner', 'lower', 'basic'],
        'muscle_groups' => ['Piernas', 'Glúteos'],
        'exercise_count' => 4
    ],
    
    // AVANZADOS
    [
        'title' => 'Avanzado Push/Pull 65\'',
        'goal' => 'strength',
        'level' => 'advanced',
        'duration' => 65,
        'tags' => ['advanced', 'push', 'pull'],
        'muscle_groups' => ['Pecho', 'Espalda', 'Hombros'],
        'exercise_count' => 6
    ],
    [
        'title' => 'Powerlifting Básico 75\'',
        'goal' => 'strength',
        'level' => 'advanced',
        'duration' => 75,
        'tags' => ['powerlifting', 'compound', 'strength'],
        'muscle_groups' => ['Piernas', 'Pecho', 'Espalda'],
        'exercise_count' => 3
    ],
    [
        'title' => 'Atlético Explosivo 50\'',
        'goal' => 'strength',
        'level' => 'advanced',
        'duration' => 50,
        'tags' => ['athletic', 'explosive', 'power'],
        'muscle_groups' => ['Piernas', 'Core'],
        'exercise_count' => 5
    ],
    
    // ESPECIALIZADOS
    [
        'title' => 'Rehabilitación Postural 30\'',
        'goal' => 'mobility',
        'level' => 'beginner',
        'duration' => 30,
        'tags' => ['rehab', 'posture', 'corrective'],
        'muscle_groups' => ['Espalda', 'Core'],
        'exercise_count' => 4
    ],
    [
        'title' => 'Express Total 25\'',
        'goal' => 'general',
        'level' => 'intermediate',
        'duration' => 25,
        'tags' => ['express', 'quick', 'efficient'],
        'muscle_groups' => ['Core', 'Piernas', 'Brazos'],
        'exercise_count' => 4
    ]
];

echo "📋 Plantillas definidas: " . count($templateDefinitions) . "\n\n";

// PASO 4: Crear plantillas con ejercicios
echo "PASO 4: Creando plantillas con ejercicios...\n";

foreach ($templateDefinitions as $index => $templateDef) {
    echo "\n" . ($index + 1) . ". Creando: {$templateDef['title']}\n";
    
    // Crear plantilla
    $template = \App\Models\Gym\DailyTemplate::create([
        'created_by' => null,
        'title' => $templateDef['title'],
        'goal' => $templateDef['goal'],
        'estimated_duration_min' => $templateDef['duration'],
        'level' => $templateDef['level'],
        'tags' => $templateDef['tags'],
        'is_preset' => true,
    ]);
    
    // Seleccionar ejercicios apropiados
    $selectedExercises = $exercises->filter(function($exercise) use ($templateDef) {
        return in_array($exercise->muscle_group, $templateDef['muscle_groups']);
    })->shuffle()->take($templateDef['exercise_count']);
    
    // Si no hay suficientes ejercicios específicos, tomar aleatorios
    if ($selectedExercises->count() < $templateDef['exercise_count']) {
        $remaining = $templateDef['exercise_count'] - $selectedExercises->count();
        $additionalExercises = $exercises->whereNotIn('id', $selectedExercises->pluck('id'))
                                       ->shuffle()
                                       ->take($remaining);
        $selectedExercises = $selectedExercises->merge($additionalExercises);
    }
    
    echo "   📋 Ejercicios seleccionados: " . $selectedExercises->count() . "\n";
    
    // Crear ejercicios de plantilla con series
    foreach ($selectedExercises as $order => $exercise) {
        echo "     - {$exercise->name} ({$exercise->muscle_group})\n";
        
        $templateExercise = \App\Models\Gym\DailyTemplateExercise::create([
            'daily_template_id' => $template->id,
            'exercise_id' => $exercise->id,
            'display_order' => $order + 1,
            'notes' => null,
        ]);
        
        // Crear series según el objetivo
        $setsConfig = getSetsConfiguration($templateDef['goal'], $templateDef['level']);
        
        foreach ($setsConfig as $setNumber => $setData) {
            \App\Models\Gym\DailyTemplateSet::create([
                'daily_template_exercise_id' => $templateExercise->id,
                'set_number' => $setNumber + 1,
                'reps_min' => $setData['reps_min'],
                'reps_max' => $setData['reps_max'],
                'rest_seconds' => $setData['rest_seconds'],
                'tempo' => $setData['tempo'],
                'rpe_target' => $setData['rpe_target'],
                'notes' => $setData['notes'],
            ]);
        }
        
        echo "       ✅ " . count($setsConfig) . " series creadas\n";
    }
    
    echo "   ✅ Plantilla '{$templateDef['title']}' creada exitosamente\n";
}

// Función para configurar series según objetivo
function getSetsConfiguration($goal, $level) {
    switch ($goal) {
        case 'strength':
            return [
                ['reps_min' => 3, 'reps_max' => 5, 'rest_seconds' => 180, 'tempo' => null, 'rpe_target' => 8.5, 'notes' => null],
                ['reps_min' => 3, 'reps_max' => 5, 'rest_seconds' => 180, 'tempo' => null, 'rpe_target' => 9.0, 'notes' => null],
                ['reps_min' => 3, 'reps_max' => 5, 'rest_seconds' => 180, 'tempo' => null, 'rpe_target' => 9.5, 'notes' => null],
            ];
            
        case 'hypertrophy':
            return [
                ['reps_min' => 8, 'reps_max' => 12, 'rest_seconds' => 90, 'tempo' => null, 'rpe_target' => 7.5, 'notes' => null],
                ['reps_min' => 8, 'reps_max' => 12, 'rest_seconds' => 90, 'tempo' => null, 'rpe_target' => 8.0, 'notes' => null],
                ['reps_min' => 8, 'reps_max' => 12, 'rest_seconds' => 90, 'tempo' => null, 'rpe_target' => 8.5, 'notes' => null],
                ['reps_min' => 8, 'reps_max' => 12, 'rest_seconds' => 90, 'tempo' => null, 'rpe_target' => 9.0, 'notes' => null],
            ];
            
        case 'endurance':
            return [
                ['reps_min' => 15, 'reps_max' => 20, 'rest_seconds' => 45, 'tempo' => null, 'rpe_target' => 7.0, 'notes' => null],
                ['reps_min' => 15, 'reps_max' => 20, 'rest_seconds' => 45, 'tempo' => null, 'rpe_target' => 7.5, 'notes' => null],
                ['reps_min' => 15, 'reps_max' => 20, 'rest_seconds' => 45, 'tempo' => null, 'rpe_target' => 8.0, 'notes' => null],
            ];
            
        case 'mobility':
            return [
                ['reps_min' => 30, 'reps_max' => 60, 'rest_seconds' => 30, 'tempo' => 'slow', 'rpe_target' => 5.0, 'notes' => 'Mantener posición'],
                ['reps_min' => 30, 'reps_max' => 60, 'rest_seconds' => 30, 'tempo' => 'slow', 'rpe_target' => 5.5, 'notes' => 'Respiración profunda'],
            ];
            
        default: // general
            return [
                ['reps_min' => 10, 'reps_max' => 15, 'rest_seconds' => 60, 'tempo' => null, 'rpe_target' => 7.0, 'notes' => null],
                ['reps_min' => 10, 'reps_max' => 15, 'rest_seconds' => 60, 'tempo' => null, 'rpe_target' => 7.5, 'notes' => null],
                ['reps_min' => 10, 'reps_max' => 15, 'rest_seconds' => 60, 'tempo' => null, 'rpe_target' => 8.0, 'notes' => null],
            ];
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 REGENERACIÓN COMPLETADA\n\n";

// PASO 5: Estadísticas finales
echo "PASO 5: Estadísticas finales...\n";

$totalTemplates = \App\Models\Gym\DailyTemplate::count();
$totalExercises = \App\Models\Gym\DailyTemplateExercise::count();
$totalSets = \App\Models\Gym\DailyTemplateSet::count();

echo "📊 ESTADÍSTICAS FINALES:\n";
echo "  - Plantillas creadas: {$totalTemplates}\n";
echo "  - Ejercicios asignados: {$totalExercises}\n";
echo "  - Series configuradas: {$totalSets}\n";
echo "  - Promedio ejercicios por plantilla: " . round($totalExercises / $totalTemplates, 1) . "\n";
echo "  - Promedio series por ejercicio: " . round($totalSets / $totalExercises, 1) . "\n";

echo "\n✅ PLANTILLAS DIARIAS REGENERADAS EXITOSAMENTE\n";
echo "🎯 Todas las plantillas incluyen ejercicios reales con series configuradas\n";
echo "🚀 Frontend recibirá datos completos y funcionales\n";
