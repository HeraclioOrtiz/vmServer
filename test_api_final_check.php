<?php

echo "🔍 === VERIFICACIÓN FINAL DE API === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "🔐 Login como María García...\n";
    
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
    
    echo "✅ Login exitoso\n\n";
    
    echo "📋 Obteniendo plantillas...\n";
    
    // Obtener plantillas
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/student/my-templates');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    
    $response = curl_exec($ch);
    $templatesData = json_decode($response, true);
    curl_close($ch);
    
    $templates = $templatesData['data']['templates'];
    echo "✅ Plantillas: " . count($templates) . "\n\n";
    
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
        
        echo "📊 Respuesta API completa:\n";
        echo "Status: " . (isset($details['data']) ? "✅ 200" : "❌ Error") . "\n\n";
        
        if (isset($details['data']['exercises'])) {
            echo "💪 Ejercicios: " . count($details['data']['exercises']) . "\n\n";
            
            foreach ($details['data']['exercises'] as $i => $exercise) {
                echo "EJERCICIO #" . ($i + 1) . ":\n";
                echo "  Nombre: {$exercise['exercise']['name']}\n";
                echo "  Series: " . count($exercise['sets']) . "\n";
                
                foreach ($exercise['sets'] as $j => $set) {
                    echo "    Serie " . ($j + 1) . ":\n";
                    
                    // Mostrar TODOS los campos de la serie
                    foreach ($set as $key => $value) {
                        echo "      {$key}: " . ($value ?? 'NULL') . "\n";
                    }
                    echo "\n";
                }
                
                if ($i >= 1) break; // Solo mostrar 2 ejercicios para no saturar
            }
            
            // Análisis de datos disponibles
            echo "📊 ANÁLISIS DE DATOS DISPONIBLES:\n";
            echo str_repeat("=", 50) . "\n";
            
            $totalSets = 0;
            $setsWithRepsMin = 0;
            $setsWithRepsMax = 0;
            $setsWithWeight = 0;
            $setsWithDuration = 0;
            $setsWithRpe = 0;
            $setsWithRest = 0;
            
            foreach ($details['data']['exercises'] as $exercise) {
                foreach ($exercise['sets'] as $set) {
                    $totalSets++;
                    
                    if (isset($set['reps_min']) && $set['reps_min']) $setsWithRepsMin++;
                    if (isset($set['reps_max']) && $set['reps_max']) $setsWithRepsMax++;
                    if (isset($set['weight']) && $set['weight']) $setsWithWeight++;
                    if (isset($set['duration']) && $set['duration']) $setsWithDuration++;
                    if (isset($set['rpe_target']) && $set['rpe_target']) $setsWithRpe++;
                    if (isset($set['rest_seconds']) && $set['rest_seconds']) $setsWithRest++;
                }
            }
            
            echo "Total series: {$totalSets}\n";
            echo "Con reps_min: {$setsWithRepsMin} (" . round(($setsWithRepsMin/$totalSets)*100, 1) . "%)\n";
            echo "Con reps_max: {$setsWithRepsMax} (" . round(($setsWithRepsMax/$totalSets)*100, 1) . "%)\n";
            echo "Con weight: {$setsWithWeight} (" . round(($setsWithWeight/$totalSets)*100, 1) . "%)\n";
            echo "Con duration: {$setsWithDuration} (" . round(($setsWithDuration/$totalSets)*100, 1) . "%)\n";
            echo "Con rpe_target: {$setsWithRpe} (" . round(($setsWithRpe/$totalSets)*100, 1) . "%)\n";
            echo "Con rest_seconds: {$setsWithRest} (" . round(($setsWithRest/$totalSets)*100, 1) . "%)\n";
            
            echo "\n🎯 EVALUACIÓN PARA APP MÓVIL:\n";
            
            if ($setsWithRepsMin > 0 && $setsWithRepsMax > 0) {
                echo "✅ RANGOS DE REPETICIONES: Disponibles ({$setsWithRepsMin}-{$setsWithRepsMax} series)\n";
            } else {
                echo "❌ RANGOS DE REPETICIONES: No disponibles\n";
            }
            
            if ($setsWithWeight > 0) {
                echo "✅ PESOS: Disponibles ({$setsWithWeight} series)\n";
            } else {
                echo "⚠️  PESOS: No configurados (puede ser peso corporal)\n";
            }
            
            if ($setsWithRpe > 0) {
                echo "✅ RPE (INTENSIDAD): Disponible ({$setsWithRpe} series)\n";
            } else {
                echo "❌ RPE: No disponible\n";
            }
            
            if ($setsWithRest > 0) {
                echo "✅ DESCANSOS: Configurados ({$setsWithRest} series)\n";
            } else {
                echo "❌ DESCANSOS: No configurados\n";
            }
            
            echo "\n📱 CONCLUSIÓN PARA DESARROLLO MÓVIL:\n";
            
            $dataCompleteness = ($setsWithRepsMin + $setsWithRepsMax + $setsWithRpe + $setsWithRest) / ($totalSets * 4) * 100;
            
            if ($dataCompleteness >= 75) {
                echo "🎉 DATOS SUFICIENTES PARA APP MÓVIL\n";
                echo "✅ María García recibe información completa\n";
                echo "✅ Rangos de repeticiones (min-max)\n";
                echo "✅ Intensidad objetivo (RPE)\n";
                echo "✅ Tiempos de descanso\n";
                echo "✅ Información estructurada correctamente\n";
                
                echo "\n📋 LISTO PARA CREAR DOCUMENTACIÓN\n";
                
            } else {
                echo "⚠️  DATOS PARCIALES ({$dataCompleteness}% completos)\n";
                echo "🔧 Algunos campos pueden necesitar configuración\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
