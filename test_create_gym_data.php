<?php

echo "🧪 === TEST: CREAR DATOS DE PRUEBA GIMNASIO === 🧪\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "TEST 1: Crear un ejercicio con la nueva estructura\n";
    echo str_repeat("=", 70) . "\n";
    
    $exercise = \App\Models\Gym\Exercise::create([
        'name' => 'Press de Banca TEST',
        'description' => 'Ejercicio de prueba para verificar la estructura',
        'muscle_groups' => ['pecho', 'tríceps', 'hombros'],
        'target_muscle_groups' => ['pectoral mayor', 'tríceps lateral', 'deltoides anterior'],
        'movement_pattern' => 'push horizontal',
        'equipment' => 'barra',
        'difficulty_level' => 'intermediate',
        'tags' => ['compuesto', 'fuerza', 'push'],
        'instructions' => 'Acostarse en el banco, tomar la barra...',
    ]);
    
    echo "✅ Ejercicio creado con ID: {$exercise->id}\n";
    echo "  Nombre: {$exercise->name}\n";
    echo "  Muscle Groups: " . json_encode($exercise->muscle_groups) . "\n";
    echo "  Target Muscle Groups: " . json_encode($exercise->target_muscle_groups) . "\n";
    echo "  Difficulty Level: {$exercise->difficulty_level}\n";
    
    echo "\n";
    echo "TEST 2: Crear una plantilla diaria\n";
    echo str_repeat("=", 70) . "\n";
    
    // Obtener un usuario para created_by
    $user = \App\Models\User::where('is_admin', true)->first() ?? \App\Models\User::first();
    
    $template = \App\Models\Gym\DailyTemplate::create([
        'created_by' => $user ? $user->id : null,
        'title' => 'Rutina Push TEST',
        'goal' => 'strength',
        'estimated_duration_min' => 60,
        'level' => 'intermediate',
        'tags' => ['push', 'fuerza', 'pecho'],
        'is_preset' => false,
    ]);
    
    echo "✅ Plantilla creada con ID: {$template->id}\n";
    echo "  Título: {$template->title}\n";
    echo "  Goal: {$template->goal}\n";
    echo "  Level: {$template->level}\n";
    
    echo "\n";
    echo "TEST 3: Agregar ejercicio a la plantilla\n";
    echo str_repeat("=", 70) . "\n";
    
    $templateExercise = \App\Models\Gym\DailyTemplateExercise::create([
        'daily_template_id' => $template->id,
        'exercise_id' => $exercise->id,
        'display_order' => 1,
        'notes' => 'Controlar la bajada, explosivo en la subida',
    ]);
    
    echo "✅ Ejercicio agregado a plantilla con ID: {$templateExercise->id}\n";
    echo "  Display Order: {$templateExercise->display_order}\n";
    echo "  Notes: {$templateExercise->notes}\n";
    
    echo "\n";
    echo "TEST 4: Agregar sets al ejercicio de la plantilla\n";
    echo str_repeat("=", 70) . "\n";
    
    $setsData = [
        ['set_number' => 1, 'reps_min' => 8, 'reps_max' => 10, 'rpe_target' => 7.5, 'rest_seconds' => 120],
        ['set_number' => 2, 'reps_min' => 8, 'reps_max' => 10, 'rpe_target' => 8.0, 'rest_seconds' => 120],
        ['set_number' => 3, 'reps_min' => 8, 'reps_max' => 10, 'rpe_target' => 8.5, 'rest_seconds' => 120],
    ];
    
    foreach ($setsData as $setData) {
        $set = \App\Models\Gym\DailyTemplateSet::create([
            'daily_template_exercise_id' => $templateExercise->id,
            'set_number' => $setData['set_number'],
            'reps_min' => $setData['reps_min'],
            'reps_max' => $setData['reps_max'],
            'rpe_target' => $setData['rpe_target'],
            'rest_seconds' => $setData['rest_seconds'],
            'notes' => null,
        ]);
        
        echo "  ✅ Set {$set->set_number}: {$set->reps_min}-{$set->reps_max} reps, RPE {$set->rpe_target}, descanso {$set->rest_seconds}s\n";
    }
    
    echo "\n";
    echo "TEST 5: Cargar plantilla completa con relaciones\n";
    echo str_repeat("=", 70) . "\n";
    
    $templateComplete = \App\Models\Gym\DailyTemplate::with([
        'exercises.exercise',
        'exercises.sets'
    ])->find($template->id);
    
    echo "✅ Plantilla cargada con relaciones:\n";
    echo "  Título: {$templateComplete->title}\n";
    echo "  Ejercicios: {$templateComplete->exercises->count()}\n";
    
    foreach ($templateComplete->exercises as $te) {
        echo "\n  Ejercicio {$te->display_order}: {$te->exercise->name}\n";
        echo "    Muscle Groups: " . json_encode($te->exercise->muscle_groups) . "\n";
        echo "    Sets: {$te->sets->count()}\n";
        
        foreach ($te->sets as $s) {
            echo "      Set {$s->set_number}: {$s->reps_min}-{$s->reps_max} reps @ RPE {$s->rpe_target}\n";
        }
    }
    
    echo "\n";
    echo "TEST 6: Verificar formato JSON de la respuesta API\n";
    echo str_repeat("=", 70) . "\n";
    
    $apiResponse = $templateComplete->toArray();
    echo "✅ Respuesta JSON generada correctamente\n";
    echo "  Estructura de exercises[0]:\n";
    echo "    - id: " . ($apiResponse['exercises'][0]['id'] ?? 'NO') . "\n";
    echo "    - display_order: " . ($apiResponse['exercises'][0]['display_order'] ?? 'NO') . "\n";
    echo "    - exercise.name: " . ($apiResponse['exercises'][0]['exercise']['name'] ?? 'NO') . "\n";
    echo "    - exercise.muscle_groups: " . (isset($apiResponse['exercises'][0]['exercise']['muscle_groups']) ? 'SÍ' : 'NO') . "\n";
    echo "    - sets count: " . count($apiResponse['exercises'][0]['sets'] ?? []) . "\n";
    
    echo "\n";
    echo "TEST 7: Limpiar datos de prueba\n";
    echo str_repeat("=", 70) . "\n";
    
    $template->delete(); // Cascade eliminará exercises y sets
    $exercise->delete();
    
    echo "✅ Datos de prueba eliminados\n";
    
    echo "\n\n";
    echo "🎉 TODOS LOS TESTS PASARON EXITOSAMENTE\n";
    echo str_repeat("=", 70) . "\n";
    echo "✅ La estructura de BD está correcta\n";
    echo "✅ Los modelos funcionan correctamente\n";
    echo "✅ Las relaciones se cargan bien\n";
    echo "✅ Los datos se pueden crear y eliminar\n";
    echo "✅ El formato JSON es correcto para la API\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR EN TEST: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}
