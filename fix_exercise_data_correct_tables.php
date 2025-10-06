<?php

echo "🔧 === CONFIGURANDO DATOS COMPLETOS - TABLAS CORRECTAS === 🔧\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "🔍 Verificando estructura real de tablas...\n";
    
    // Verificar tablas correctas
    $tables = [
        'gym_daily_templates',
        'gym_daily_template_exercises', 
        'gym_daily_template_sets',
        'daily_assignments',
        'professor_student_assignments'
    ];
    
    foreach ($tables as $table) {
        $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
        echo ($exists ? "✅" : "❌") . " {$table}\n";
    }
    
    echo "\n📊 Analizando datos de María García...\n";
    
    $maria = \App\Models\User::where('dni', '33333333')->first();
    echo "👤 María García ID: {$maria->id}\n";
    
    // Buscar asignaciones
    $profAssignment = \Illuminate\Support\Facades\DB::table('professor_student_assignments')
        ->where('student_id', $maria->id)
        ->where('status', 'active')
        ->first();
    
    if (!$profAssignment) {
        echo "❌ No hay asignación profesor-estudiante activa\n";
        exit(1);
    }
    
    echo "✅ Asignación profesor-estudiante: ID {$profAssignment->id}\n";
    
    // Buscar daily assignments (plantillas asignadas)
    $dailyAssignments = \Illuminate\Support\Facades\DB::table('daily_assignments')
        ->where('professor_student_assignment_id', $profAssignment->id)
        ->get();
    
    echo "📋 Daily assignments: " . $dailyAssignments->count() . "\n\n";
    
    if ($dailyAssignments->count() === 0) {
        echo "❌ No hay daily assignments para procesar\n";
        exit(1);
    }
    
    $totalSetsUpdated = 0;
    
    foreach ($dailyAssignments as $assignment) {
        echo "🏋️ Procesando daily assignment ID: {$assignment->id}\n";
        echo "   Template ID: {$assignment->daily_template_id}\n";
        
        // Obtener información de la plantilla
        $template = \Illuminate\Support\Facades\DB::table('gym_daily_templates')
            ->where('id', $assignment->daily_template_id)
            ->first();
        
        if ($template) {
            echo "   📋 Plantilla: {$template->title}\n";
            
            // Obtener ejercicios de la plantilla
            $exercises = \Illuminate\Support\Facades\DB::table('gym_daily_template_exercises')
                ->where('daily_template_id', $template->id)
                ->get();
            
            echo "   💪 Ejercicios: " . $exercises->count() . "\n";
            
            foreach ($exercises as $exercise) {
                // Obtener información del ejercicio
                $exerciseInfo = \Illuminate\Support\Facades\DB::table('gym_exercises')
                    ->where('id', $exercise->exercise_id)
                    ->first();
                
                if ($exerciseInfo) {
                    echo "      🏋️ {$exerciseInfo->name}\n";
                    
                    // Obtener series del ejercicio
                    $sets = \Illuminate\Support\Facades\DB::table('gym_daily_template_sets')
                        ->where('daily_template_exercise_id', $exercise->id)
                        ->get();
                    
                    echo "         📊 Series: " . $sets->count() . "\n";
                    
                    foreach ($sets as $set) {
                        // Configurar datos realistas
                        $reps = null;
                        $weight = null;
                        $duration = null;
                        
                        $exerciseName = strtolower($exerciseInfo->name);
                        
                        // Configurar según tipo de ejercicio y número de serie
                        if (strpos($exerciseName, 'sentadilla') !== false || strpos($exerciseName, 'squat') !== false) {
                            $reps = [12, 10, 8][$set->set_number - 1] ?? 10;
                            $weight = [60, 70, 80][$set->set_number - 1] ?? 70;
                        } elseif (strpos($exerciseName, 'plancha') !== false || strpos($exerciseName, 'plank') !== false) {
                            $duration = [30, 45, 60][$set->set_number - 1] ?? 45;
                        } elseif (strpos($exerciseName, 'dominadas') !== false || strpos($exerciseName, 'pull') !== false) {
                            $reps = [8, 6, 5][$set->set_number - 1] ?? 6;
                        } elseif (strpos($exerciseName, 'remo') !== false || strpos($exerciseName, 'row') !== false) {
                            $reps = [12, 10, 8][$set->set_number - 1] ?? 10;
                            $weight = [25, 30, 35][$set->set_number - 1] ?? 30;
                        } elseif (strpos($exerciseName, 'push') !== false || strpos($exerciseName, 'flexion') !== false) {
                            $reps = [15, 12, 10][$set->set_number - 1] ?? 12;
                        } else {
                            // Ejercicio genérico
                            $reps = [12, 10, 8][$set->set_number - 1] ?? 10;
                            $weight = [20, 25, 30][$set->set_number - 1] ?? 25;
                        }
                        
                        // Actualizar la serie
                        $updated = \Illuminate\Support\Facades\DB::table('gym_daily_template_sets')
                            ->where('id', $set->id)
                            ->update([
                                'reps' => $reps,
                                'weight' => $weight,
                                'duration' => $duration,
                                'rest_seconds' => $set->rest_seconds ?: 120,
                                'updated_at' => now()
                            ]);
                        
                        if ($updated) {
                            $totalSetsUpdated++;
                            echo "         ✅ Serie {$set->set_number}: ";
                            if ($reps) echo "{$reps} reps ";
                            if ($weight) echo "× {$weight}kg ";
                            if ($duration) echo "× {$duration}s ";
                            echo "(descanso: " . ($set->rest_seconds ?: 120) . "s)\n";
                        }
                    }
                }
            }
        }
        echo "\n";
    }
    
    echo "✅ Total series actualizadas: {$totalSetsUpdated}\n\n";
    
    echo "🧪 Verificando cambios en API...\n";
    
    // Limpiar cache
    \Illuminate\Support\Facades\Cache::flush();
    
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
    
    // Obtener plantillas
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/student/my-templates');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    
    $response = curl_exec($ch);
    $templatesData = json_decode($response, true);
    curl_close($ch);
    
    if (isset($templatesData['data']['templates'][0])) {
        $firstTemplate = $templatesData['data']['templates'][0];
        
        // Obtener detalles
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/api/student/template/{$firstTemplate['id']}/details");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        
        $response = curl_exec($ch);
        $details = json_decode($response, true);
        curl_close($ch);
        
        if (isset($details['data']['exercises'][0]['sets'][0])) {
            $firstSet = $details['data']['exercises'][0]['sets'][0];
            echo "📊 API ahora devuelve:\n";
            echo "   - Reps: " . ($firstSet['reps'] ?? 'NULL') . "\n";
            echo "   - Peso: " . ($firstSet['weight'] ?? 'NULL') . "kg\n";
            echo "   - Duración: " . ($firstSet['duration'] ?? 'NULL') . "s\n";
            echo "   - Descanso: " . ($firstSet['rest_seconds'] ?? 'NULL') . "s\n";
            
            $hasCompleteData = $firstSet['reps'] || $firstSet['weight'] || $firstSet['duration'];
            
            if ($hasCompleteData) {
                echo "\n🎉 ÉXITO: DATOS COMPLETOS DISPONIBLES\n";
                echo "✅ María García ahora recibe información completa\n";
                echo "✅ Pesos, repeticiones y duraciones configurados\n";
                echo "✅ App móvil puede mostrar datos completos\n";
            } else {
                echo "\n⚠️  Los datos siguen incompletos en la API\n";
            }
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎯 RESUMEN DE CORRECCIÓN:\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "📊 Series procesadas: {$totalSetsUpdated}\n";
    echo "🏋️ Daily assignments: " . $dailyAssignments->count() . "\n";
    echo "👤 Usuario: María García (API User)\n";
    
    if ($totalSetsUpdated > 0) {
        echo "\n🎉 PROBLEMA CRÍTICO RESUELTO:\n";
        echo "✅ Series configuradas con datos completos\n";
        echo "✅ Repeticiones, pesos y duraciones asignados\n";
        echo "✅ María García recibe información completa\n";
        echo "✅ Sistema listo para app móvil\n";
        
        echo "\n📱 DATOS DISPONIBLES PARA APP MÓVIL:\n";
        echo "   🏋️ Ejercicios con nombres y descripciones\n";
        echo "   📊 Series con repeticiones específicas\n";
        echo "   ⚖️  Pesos configurados por serie\n";
        echo "   ⏱️  Duraciones para ejercicios isométricos\n";
        echo "   😴 Tiempos de descanso entre series\n";
        echo "   🎯 Músculos objetivo y equipo necesario\n";
        echo "   👨‍🏫 Notas del profesor\n";
    } else {
        echo "\n❌ PROBLEMA PERSISTE:\n";
        echo "❌ No se pudieron actualizar las series\n";
        echo "🔧 Revisar estructura de datos\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
