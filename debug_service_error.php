<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 === DEBUG SERVICE ERROR === 🔍\n\n";

try {
    // Intentar instanciar el servicio
    $service = app(\App\Services\Gym\ExerciseService::class);
    echo "✅ Service instantiated successfully\n";
    
    // Obtener un ejercicio de prueba
    $exercise = \App\Models\Gym\Exercise::find(86);
    if (!$exercise) {
        echo "❌ Exercise 86 not found\n";
        exit(1);
    }
    
    echo "✅ Exercise found: {$exercise->name}\n";
    
    // Obtener un usuario
    $user = \App\Models\User::where('dni', '22222222')->first();
    if (!$user) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    echo "✅ User found: {$user->name}\n";
    
    // Intentar verificar dependencias
    echo "\nTesting checkExerciseDependencies...\n";
    $dependencies = $service->checkExerciseDependencies($exercise);
    echo "✅ Dependencies checked successfully\n";
    var_dump($dependencies);
    
    // Intentar eliminar ejercicio
    echo "\nTesting deleteExercise...\n";
    $result = $service->deleteExercise($exercise, $user);
    echo "✅ Delete method executed\n";
    var_dump($result);
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🎯 DEBUG COMPLETED\n";
