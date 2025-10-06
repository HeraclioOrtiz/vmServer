<?php

echo "🔍 === VERIFICANDO PESOS Y DATOS DE EJERCICIOS === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "🔐 Login y obtención de datos...\n";
    
    // Login
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
    
    echo "✅ Token obtenido\n\n";
    
    // Obtener plantillas
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/student/my-templates');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    
    $response = curl_exec($ch);
    $templatesData = json_decode($response, true);
    curl_close($ch);
    
    $templates = $templatesData['data']['templates'];
    echo "📋 Plantillas encontradas: " . count($templates) . "\n\n";
    
    // Analizar primera plantilla
    if (count($templates) > 0) {
        $firstTemplate = $templates[0];
        echo "🏋️ Analizando: {$firstTemplate['daily_template']['title']}\n";
        echo "ID: {$firstTemplate['id']}\n\n";
        
        // Obtener detalles
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/api/student/template/{$firstTemplate['id']}/details");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        
        $response = curl_exec($ch);
        $details = json_decode($response, true);
        curl_close($ch);
        
        if (isset($details['data']['exercises'])) {
            $exercises = $details['data']['exercises'];
            echo "💪 Ejercicios en plantilla: " . count($exercises) . "\n\n";
            
            foreach ($exercises as $i => $exercise) {
                echo "EJERCICIO #" . ($i + 1) . ":\n";
                echo "  Nombre: {$exercise['exercise']['name']}\n";
                echo "  Series: " . count($exercise['sets']) . "\n";
                
                foreach ($exercise['sets'] as $j => $set) {
                    echo "    Serie " . ($j + 1) . ":\n";
                    echo "      - Reps: " . ($set['reps'] ?? 'NULL') . "\n";
                    echo "      - Peso: " . ($set['weight'] ?? 'NULL') . "\n";
                    echo "      - Duración: " . ($set['duration'] ?? 'NULL') . "\n";
                    echo "      - Descanso: " . ($set['rest_seconds'] ?? 'NULL') . "\n";
                    if ($set['notes']) {
                        echo "      - Notas: {$set['notes']}\n";
                    }
                }
                echo "\n";
            }
        }
    }
    
    echo "🔍 VERIFICACIÓN DIRECTA EN BD:\n";
    echo str_repeat("=", 50) . "\n";
    
    // Verificar en BD
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    $assignments = \App\Models\Gym\TemplateAssignment::with([
        'dailyTemplate.exercises.exercise',
        'dailyTemplate.exercises.sets'
    ])->whereHas('professorStudentAssignment', function($query) use ($maria) {
        $query->where('student_id', $maria->id);
    })->get();
    
    echo "📊 Asignaciones en BD: " . $assignments->count() . "\n\n";
    
    $totalSets = 0;
    $setsWithWeight = 0;
    $setsWithReps = 0;
    $setsWithDuration = 0;
    
    foreach ($assignments as $assignment) {
        echo "📋 Plantilla: {$assignment->dailyTemplate->title}\n";
        
        foreach ($assignment->dailyTemplate->exercises as $templateExercise) {
            echo "  💪 {$templateExercise->exercise->name}\n";
            
            foreach ($templateExercise->sets as $set) {
                $totalSets++;
                echo "    Serie {$set->set_number}: ";
                
                $parts = [];
                if ($set->reps) {
                    $parts[] = "{$set->reps} reps";
                    $setsWithReps++;
                }
                if ($set->weight) {
                    $parts[] = "{$set->weight}kg";
                    $setsWithWeight++;
                }
                if ($set->duration) {
                    $parts[] = "{$set->duration}s";
                    $setsWithDuration++;
                }
                
                echo implode(' × ', $parts);
                if ($set->rest_seconds) {
                    echo " (descanso: {$set->rest_seconds}s)";
                }
                echo "\n";
            }
        }
        echo "\n";
    }
    
    echo str_repeat("=", 50) . "\n";
    echo "📊 RESUMEN DE DATOS:\n";
    echo "Total series: {$totalSets}\n";
    echo "Series con peso: {$setsWithWeight}\n";
    echo "Series con reps: {$setsWithReps}\n";
    echo "Series con duración: {$setsWithDuration}\n";
    
    echo "\n✅ DISPONIBILIDAD DE DATOS:\n";
    echo "Pesos: " . ($setsWithWeight > 0 ? "✅ ({$setsWithWeight}/{$totalSets})" : "❌ (0/{$totalSets})") . "\n";
    echo "Repeticiones: " . ($setsWithReps > 0 ? "✅ ({$setsWithReps}/{$totalSets})" : "❌ (0/{$totalSets})") . "\n";
    echo "Duraciones: " . ($setsWithDuration > 0 ? "✅ ({$setsWithDuration}/{$totalSets})" : "❌ (0/{$totalSets})") . "\n";
    
    echo "\n🎯 CONCLUSIÓN PARA APP MÓVIL:\n";
    if ($setsWithWeight > 0 && $setsWithReps > 0) {
        echo "✅ SÍ - María García recibe pesos y repeticiones\n";
        echo "✅ Datos completos disponibles para la app\n";
    } else {
        echo "⚠️  PARCIAL - Algunos datos pueden faltar\n";
        if ($setsWithWeight === 0) echo "❌ No hay pesos configurados\n";
        if ($setsWithReps === 0) echo "❌ No hay repeticiones configuradas\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
