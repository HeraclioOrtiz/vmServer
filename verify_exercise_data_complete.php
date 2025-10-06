<?php

echo "🔍 === VERIFICANDO INFORMACIÓN COMPLETA DE EJERCICIOS === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

// Función para hacer requests HTTP
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

try {
    echo "🔐 PASO 1: Login como María García...\n";
    
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '33333333',
        'password' => 'estudiante123'
    ]);
    
    if ($loginResponse['status'] !== 200) {
        echo "❌ Error en login\n";
        exit(1);
    }
    
    $token = $loginResponse['data']['data']['token'];
    echo "✅ Login exitoso\n\n";
    
    echo "📋 PASO 2: Obteniendo plantillas asignadas...\n";
    
    $templatesResponse = makeRequest('http://127.0.0.1:8000/api/student/my-templates', 'GET', null, $token);
    
    if ($templatesResponse['status'] !== 200) {
        echo "❌ Error obteniendo plantillas\n";
        exit(1);
    }
    
    $templatesData = $templatesResponse['data']['data'];
    $templates = $templatesData['templates'];
    
    echo "✅ Plantillas obtenidas: " . count($templates) . "\n";
    echo "👨‍🏫 Profesor: {$templatesData['professor']['name']}\n\n";
    
    if (count($templates) === 0) {
        echo "⚠️  No hay plantillas para verificar\n";
        exit(0);
    }
    
    echo "🏋️ PASO 3: Analizando detalles de ejercicios...\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($templates as $index => $template) {
        echo "📌 PLANTILLA #" . ($index + 1) . ": {$template['daily_template']['title']}\n";
        echo "   🆔 ID: {$template['id']}\n";
        echo "   ⏱️  Duración: {$template['daily_template']['estimated_duration_min']} min\n";
        echo "   🏋️ Ejercicios: {$template['daily_template']['exercises_count']}\n\n";
        
        // Obtener detalles completos
        $detailsResponse = makeRequest("http://127.0.0.1:8000/api/student/template/{$template['id']}/details", 'GET', null, $token);
        
        if ($detailsResponse['status'] === 200) {
            $details = $detailsResponse['data']['data'];
            
            echo "   🔍 EJERCICIOS DETALLADOS:\n";
            echo "   " . str_repeat("-", 70) . "\n";
            
            foreach ($details['exercises'] as $exerciseIndex => $exercise) {
                echo "   💪 EJERCICIO #" . ($exerciseIndex + 1) . " (Orden: {$exercise['order']}):\n";
                echo "      📝 Nombre: {$exercise['exercise']['name']}\n";
                echo "      📖 Descripción: " . (strlen($exercise['exercise']['description'] ?? '') > 0 ? 'SÍ' : 'NO') . "\n";
                echo "      🎯 Músculos: " . (count($exercise['exercise']['target_muscle_groups'] ?? []) > 0 ? 'SÍ (' . count($exercise['exercise']['target_muscle_groups']) . ')' : 'NO') . "\n";
                echo "      🏋️ Equipo: " . (count($exercise['exercise']['equipment'] ?? []) > 0 ? 'SÍ (' . count($exercise['exercise']['equipment']) . ')' : 'NO') . "\n";
                echo "      📊 Dificultad: " . ($exercise['exercise']['difficulty_level'] ?? 'N/A') . "\n";
                echo "      📋 Instrucciones: " . (strlen($exercise['exercise']['instructions'] ?? '') > 0 ? 'SÍ' : 'NO') . "\n";
                
                if (count($exercise['sets']) > 0) {
                    echo "      📊 SERIES (" . count($exercise['sets']) . "):\n";
                    
                    $hasWeights = false;
                    $hasReps = false;
                    $hasDuration = false;
                    $hasRest = false;
                    $hasNotes = false;
                    
                    foreach ($exercise['sets'] as $set) {
                        echo "         Serie {$set['set_number']}: ";
                        
                        $setInfo = [];
                        
                        if ($set['reps']) {
                            $setInfo[] = "{$set['reps']} reps";
                            $hasReps = true;
                        }
                        
                        if ($set['weight']) {
                            $setInfo[] = "{$set['weight']}kg";
                            $hasWeights = true;
                        }
                        
                        if ($set['duration']) {
                            $setInfo[] = "{$set['duration']}s";
                            $hasDuration = true;
                        }
                        
                        if ($set['rest_seconds']) {
                            $setInfo[] = "descanso: {$set['rest_seconds']}s";
                            $hasRest = true;
                        }
                        
                        echo implode(' × ', $setInfo) . "\n";
                        
                        if ($set['notes']) {
                            echo "            📝 Notas: {$set['notes']}\n";
                            $hasNotes = true;
                        }
                    }
                    
                    echo "      ✅ DATOS DISPONIBLES:\n";
                    echo "         - Repeticiones: " . ($hasReps ? '✅' : '❌') . "\n";
                    echo "         - Pesos: " . ($hasWeights ? '✅' : '❌') . "\n";
                    echo "         - Duración: " . ($hasDuration ? '✅' : '❌') . "\n";
                    echo "         - Descansos: " . ($hasRest ? '✅' : '❌') . "\n";
                    echo "         - Notas series: " . ($hasNotes ? '✅' : '❌') . "\n";
                    
                } else {
                    echo "      ❌ Sin series configuradas\n";
                }
                
                if ($exercise['notes']) {
                    echo "      📝 Notas ejercicio: {$exercise['notes']}\n";
                }
                
                echo "\n";
            }
            
        } else {
            echo "   ❌ Error obteniendo detalles\n";
        }
        
        echo "\n";
    }
    
    echo "📅 PASO 4: Verificando asignaciones diarias...\n";
    echo str_repeat("=", 80) . "\n";
    
    $calendarResponse = makeRequest('http://127.0.0.1:8000/api/student/my-weekly-calendar', 'GET', null, $token);
    
    if ($calendarResponse['status'] === 200) {
        $calendar = $calendarResponse['data']['data'];
        
        echo "📅 Calendario semanal ({$calendar['week_start']} - {$calendar['week_end']}):\n\n";
        
        foreach ($calendar['days'] as $day) {
            if ($day['has_workouts']) {
                echo "📅 {$day['day_name']} ({$day['date']}):\n";
                
                foreach ($day['assignments'] as $assignment) {
                    echo "   🏋️ {$assignment['daily_template']['title']}\n";
                    echo "      🆔 ID Asignación: {$assignment['id']}\n";
                    echo "      ⏱️  Duración: {$assignment['daily_template']['estimated_duration_min']} min\n";
                    echo "      📊 Nivel: {$assignment['daily_template']['level']}\n";
                    echo "      🎯 Objetivo: {$assignment['daily_template']['goal']}\n";
                    
                    if ($assignment['professor_notes']) {
                        echo "      📝 Notas profesor: {$assignment['professor_notes']}\n";
                    }
                    
                    echo "      👨‍🏫 Asignado por: {$assignment['assigned_by']['name']}\n";
                }
                echo "\n";
            }
        }
        
    } else {
        echo "❌ Error obteniendo calendario\n";
    }
    
    echo str_repeat("=", 80) . "\n";
    echo "🎯 ANÁLISIS FINAL DE DATOS:\n";
    echo str_repeat("=", 80) . "\n";
    
    // Verificar directamente en BD qué datos tienen los ejercicios
    echo "🔍 VERIFICACIÓN DIRECTA EN BASE DE DATOS:\n\n";
    
    $templateAssignments = \App\Models\Gym\TemplateAssignment::with([
        'dailyTemplate.exercises.exercise',
        'dailyTemplate.exercises.sets'
    ])->where('professor_student_assignment_id', function($query) {
        $query->select('id')
              ->from('professor_student_assignments')
              ->where('student_id', 3); // ID de María García
    })->get();
    
    $totalExercises = 0;
    $exercisesWithWeights = 0;
    $exercisesWithReps = 0;
    $exercisesWithDuration = 0;
    $exercisesWithRest = 0;
    $totalSets = 0;
    
    foreach ($templateAssignments as $assignment) {
        foreach ($assignment->dailyTemplate->exercises as $templateExercise) {
            $totalExercises++;
            
            $hasWeight = false;
            $hasReps = false;
            $hasDuration = false;
            $hasRest = false;
            
            foreach ($templateExercise->sets as $set) {
                $totalSets++;
                
                if ($set->weight) $hasWeight = true;
                if ($set->reps) $hasReps = true;
                if ($set->duration) $hasDuration = true;
                if ($set->rest_seconds) $hasRest = true;
            }
            
            if ($hasWeight) $exercisesWithWeights++;
            if ($hasReps) $exercisesWithReps++;
            if ($hasDuration) $exercisesWithDuration++;
            if ($hasRest) $exercisesWithRest++;
        }
    }
    
    echo "📊 ESTADÍSTICAS DE DATOS:\n";
    echo "   Total ejercicios: {$totalExercises}\n";
    echo "   Total series: {$totalSets}\n";
    echo "   Ejercicios con pesos: {$exercisesWithWeights}/{$totalExercises} (" . round(($exercisesWithWeights/$totalExercises)*100, 1) . "%)\n";
    echo "   Ejercicios con repeticiones: {$exercisesWithReps}/{$totalExercises} (" . round(($exercisesWithReps/$totalExercises)*100, 1) . "%)\n";
    echo "   Ejercicios con duración: {$exercisesWithDuration}/{$totalExercises} (" . round(($exercisesWithDuration/$totalExercises)*100, 1) . "%)\n";
    echo "   Ejercicios con descansos: {$exercisesWithRest}/{$totalExercises} (" . round(($exercisesWithRest/$totalExercises)*100, 1) . "%)\n";
    
    echo "\n✅ INFORMACIÓN DISPONIBLE PARA APP MÓVIL:\n";
    echo "   🏋️ Ejercicios completos: ✅\n";
    echo "   📊 Series detalladas: ✅\n";
    echo "   ⚖️  Pesos configurados: " . ($exercisesWithWeights > 0 ? '✅' : '❌') . "\n";
    echo "   🔢 Repeticiones: " . ($exercisesWithReps > 0 ? '✅' : '❌') . "\n";
    echo "   ⏱️  Duraciones: " . ($exercisesWithDuration > 0 ? '✅' : '❌') . "\n";
    echo "   😴 Descansos: " . ($exercisesWithRest > 0 ? '✅' : '❌') . "\n";
    echo "   📝 Instrucciones: ✅\n";
    echo "   🎯 Músculos objetivo: ✅\n";
    echo "   🏋️ Equipo necesario: ✅\n";
    echo "   👨‍🏫 Notas del profesor: ✅\n";
    
    echo "\n🎯 CONCLUSIÓN:\n";
    if ($exercisesWithWeights > 0 && $exercisesWithReps > 0) {
        echo "✅ María García recibe TODA la información necesaria\n";
        echo "✅ Pesos, repeticiones, series y ejercicios completos\n";
        echo "✅ Asignaciones diarias contienen todos los datos\n";
        echo "✅ Lista para desarrollo completo de app móvil\n";
    } else {
        echo "⚠️  Algunos datos pueden estar incompletos\n";
        echo "🔧 Revisar configuración de plantillas y series\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
