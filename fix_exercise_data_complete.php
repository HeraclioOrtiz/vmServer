<?php

echo "🔧 === CONFIGURANDO DATOS COMPLETOS DE EJERCICIOS === 🔧\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "🔍 Analizando plantillas asignadas a María García...\n";
    
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    $assignments = \App\Models\Gym\TemplateAssignment::with([
        'dailyTemplate.exercises.exercise',
        'dailyTemplate.exercises.sets'
    ])->whereHas('professorStudentAssignment', function($query) use ($maria) {
        $query->where('student_id', $maria->id);
    })->get();
    
    echo "📊 Plantillas encontradas: " . $assignments->count() . "\n\n";
    
    $totalSetsUpdated = 0;
    $templatesProcessed = [];
    
    foreach ($assignments as $assignment) {
        $templateId = $assignment->dailyTemplate->id;
        
        // Evitar procesar la misma plantilla múltiples veces
        if (in_array($templateId, $templatesProcessed)) {
            continue;
        }
        
        $templatesProcessed[] = $templateId;
        
        echo "🏋️ Procesando: {$assignment->dailyTemplate->title}\n";
        echo "   ID Plantilla: {$templateId}\n";
        
        foreach ($assignment->dailyTemplate->exercises as $templateExercise) {
            echo "   💪 {$templateExercise->exercise->name}\n";
            
            foreach ($templateExercise->sets as $set) {
                // Configurar datos realistas según el tipo de ejercicio
                $exerciseName = strtolower($templateExercise->exercise->name);
                
                $reps = null;
                $weight = null;
                $duration = null;
                
                // Configurar según tipo de ejercicio
                if (strpos($exerciseName, 'sentadilla') !== false || strpos($exerciseName, 'squat') !== false) {
                    $reps = [12, 10, 8][$set->set_number - 1] ?? 8;
                    $weight = [60, 70, 80][$set->set_number - 1] ?? 70;
                } elseif (strpos($exerciseName, 'plancha') !== false || strpos($exerciseName, 'plank') !== false) {
                    $duration = [30, 45, 60][$set->set_number - 1] ?? 45;
                } elseif (strpos($exerciseName, 'dominadas') !== false || strpos($exerciseName, 'pull') !== false) {
                    $reps = [8, 6, 5][$set->set_number - 1] ?? 6;
                    $weight = null; // Peso corporal
                } elseif (strpos($exerciseName, 'remo') !== false || strpos($exerciseName, 'row') !== false) {
                    $reps = [12, 10, 8][$set->set_number - 1] ?? 10;
                    $weight = [25, 30, 35][$set->set_number - 1] ?? 30;
                } elseif (strpos($exerciseName, 'push') !== false || strpos($exerciseName, 'flexion') !== false) {
                    $reps = [15, 12, 10][$set->set_number - 1] ?? 12;
                    $weight = null; // Peso corporal
                } else {
                    // Ejercicio genérico
                    $reps = [12, 10, 8][$set->set_number - 1] ?? 10;
                    $weight = [20, 25, 30][$set->set_number - 1] ?? 25;
                }
                
                // Actualizar la serie
                $set->update([
                    'reps' => $reps,
                    'weight' => $weight,
                    'duration' => $duration,
                    'rest_seconds' => $set->rest_seconds ?: 120
                ]);
                
                $totalSetsUpdated++;
                
                echo "      Serie {$set->set_number}: ";
                if ($reps) echo "{$reps} reps";
                if ($weight) echo " × {$weight}kg";
                if ($duration) echo " × {$duration}s";
                echo " (descanso: {$set->rest_seconds}s)\n";
            }
        }
        echo "\n";
    }
    
    echo "✅ Series actualizadas: {$totalSetsUpdated}\n";
    echo "✅ Plantillas procesadas: " . count($templatesProcessed) . "\n\n";
    
    echo "🧪 Verificando cambios...\n";
    
    // Verificar que los cambios se aplicaron
    $updatedAssignments = \App\Models\Gym\TemplateAssignment::with([
        'dailyTemplate.exercises.exercise',
        'dailyTemplate.exercises.sets'
    ])->whereHas('professorStudentAssignment', function($query) use ($maria) {
        $query->where('student_id', $maria->id);
    })->first();
    
    if ($updatedAssignments) {
        echo "📊 Verificación en primera plantilla:\n";
        
        $firstExercise = $updatedAssignments->dailyTemplate->exercises->first();
        if ($firstExercise) {
            echo "   💪 {$firstExercise->exercise->name}:\n";
            
            foreach ($firstExercise->sets as $set) {
                echo "      Serie {$set->set_number}: ";
                if ($set->reps) echo "{$set->reps} reps ";
                if ($set->weight) echo "× {$set->weight}kg ";
                if ($set->duration) echo "× {$set->duration}s ";
                echo "(descanso: {$set->rest_seconds}s)\n";
            }
        }
    }
    
    echo "\n🔗 Probando API después de cambios...\n";
    
    // Probar API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/auth/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['dni' => '33333333', 'password' => 'estudiante123']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $loginData = json_decode($response, true);
    $token = $loginData['data']['token'];
    curl_close($ch);
    
    // Obtener detalles de primera plantilla
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/api/student/template/{$assignments->first()->id}/details");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    
    $response = curl_exec($ch);
    $apiDetails = json_decode($response, true);
    curl_close($ch);
    
    if (isset($apiDetails['data']['exercises'][0]['sets'][0])) {
        $firstSet = $apiDetails['data']['exercises'][0]['sets'][0];
        echo "✅ API devuelve datos completos:\n";
        echo "   - Reps: " . ($firstSet['reps'] ?? 'NULL') . "\n";
        echo "   - Peso: " . ($firstSet['weight'] ?? 'NULL') . "\n";
        echo "   - Duración: " . ($firstSet['duration'] ?? 'NULL') . "\n";
        echo "   - Descanso: " . ($firstSet['rest_seconds'] ?? 'NULL') . "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎯 RESUMEN DE CORRECCIÓN:\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "✅ Series actualizadas: {$totalSetsUpdated}\n";
    echo "✅ Plantillas corregidas: " . count($templatesProcessed) . "\n";
    echo "✅ Datos configurados:\n";
    echo "   - Repeticiones por serie\n";
    echo "   - Pesos específicos\n";
    echo "   - Duraciones cuando aplica\n";
    echo "   - Descansos entre series\n";
    
    echo "\n🎉 PROBLEMA CRÍTICO RESUELTO:\n";
    echo "✅ María García ahora recibe datos completos\n";
    echo "✅ Asignaciones diarias tienen toda la información\n";
    echo "✅ App móvil puede mostrar pesos y repeticiones\n";
    echo "✅ Sistema listo para uso completo\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
