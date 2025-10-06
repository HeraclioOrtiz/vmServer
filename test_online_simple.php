<?php

echo "🔧 === TESTING CORRECCIÓN === 🔧\n\n";

try {
    echo "🔐 Login...\n";
    
    // Probar login directamente
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/auth/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'dni' => '33333333',
        'password' => 'estudiante123'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "📊 Status: {$httpCode}\n";
    
    if ($error) {
        echo "❌ Error cURL: {$error}\n";
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo "✅ OK\n";
        
        $token = $data['data']['token'] ?? null;
        if ($token) {
            
            echo "📋 Templates...\n";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/student/my-templates');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
            
            $templatesResponse = curl_exec($ch);
            $templatesCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($templatesCode === 200) {
                $templatesData = json_decode($templatesResponse, true);
                $templates = $templatesData['data']['templates'] ?? [];
                echo "✅ Templates: " . count($templates) . "\n";
                
                if (count($templates) > 0) {
                    $firstTemplate = $templates[0];
                    echo "🏋️ Testing: {$firstTemplate['daily_template']['title']}\n";
                    
                    // Probar detalles CON LA CORRECCIÓN
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://villamitre.loca.lt/api/student/template/{$firstTemplate['id']}/details");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Authorization: Bearer ' . $token,
                        'Content-Type: application/json'
                    ]);
                    
                    $detailsResponse = curl_exec($ch);
                    $detailsCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    echo "📊 Detalles Status: {$detailsCode}\n";
                    
                    if ($detailsCode === 200) {
                        $details = json_decode($detailsResponse, true);
                        
                        if (isset($details['data']['exercises'][0]['sets'][0])) {
                            $firstExercise = $details['data']['exercises'][0];
                            $firstSet = $firstExercise['sets'][0];
                            
                            echo "\n💪 Ejercicio: {$firstExercise['exercise']['name']}\n";
                            echo "📊 Total series: " . count($firstExercise['sets']) . "\n";
                            
                            echo "\n🔍 PRIMERA SERIE - TODOS LOS CAMPOS:\n";
                            echo str_repeat("=", 60) . "\n";
                            foreach ($firstSet as $key => $value) {
                                $displayValue = $value ?? 'NULL';
                                echo "  {$key}: {$displayValue}\n";
                            }
                            
                            echo "\n" . str_repeat("=", 60) . "\n";
                            echo "🎯 VERIFICACIÓN DE CORRECCIÓN:\n";
                            echo str_repeat("=", 60) . "\n";
                            
                            // Verificar campos corregidos
                            $expectedFields = ['reps_min', 'reps_max', 'rpe_target', 'rest_seconds', 'tempo', 'notes'];
                            $problematicFields = ['reps', 'weight', 'duration'];
                            
                            echo "✅ CAMPOS CORRECTOS (deben estar presentes):\n";
                            $foundCorrect = 0;
                            $correctWithData = 0;
                            foreach ($expectedFields as $field) {
                                if (isset($firstSet[$field])) {
                                    $value = $firstSet[$field] ?? 'NULL';
                                    echo "   ✅ {$field}: {$value}\n";
                                    $foundCorrect++;
                                    if ($firstSet[$field] !== null) $correctWithData++;
                                } else {
                                    echo "   ❌ {$field}: NO ENCONTRADO\n";
                                }
                            }
                            
                            echo "\n❌ CAMPOS PROBLEMÁTICOS (deben estar eliminados):\n";
                            $foundProblematic = 0;
                            foreach ($problematicFields as $field) {
                                if (isset($firstSet[$field])) {
                                    echo "   ⚠️  {$field}: TODAVÍA PRESENTE (PROBLEMA)\n";
                                    $foundProblematic++;
                                } else {
                                    echo "   ✅ {$field}: ELIMINADO CORRECTAMENTE\n";
                                }
                            }
                            
                            echo "\n📊 MÉTRICAS DE CORRECCIÓN:\n";
                            echo str_repeat("-", 40) . "\n";
                            $structureCorrectness = ($foundCorrect / count($expectedFields)) * 100;
                            $dataCompleteness = ($correctWithData / count($expectedFields)) * 100;
                            
                            echo "Estructura correcta: {$foundCorrect}/" . count($expectedFields) . " (" . round($structureCorrectness, 1) . "%)\n";
                            echo "Datos completos: {$correctWithData}/" . count($expectedFields) . " (" . round($dataCompleteness, 1) . "%)\n";
                            echo "Campos problemáticos: {$foundProblematic} (debe ser 0)\n";
                            
                            echo "\n🎯 RESULTADO FINAL:\n";
                            echo str_repeat("-", 30) . "\n";
                            
                            if ($structureCorrectness >= 90 && $foundProblematic === 0 && $correctWithData >= 3) {
                                echo "🎉 CORRECCIÓN COMPLETAMENTE EXITOSA\n";
                                echo "✅ Estructura API corregida\n";
                                echo "✅ Campos problemáticos eliminados\n";
                                echo "✅ Datos disponibles para app móvil\n";
                                echo "✅ María García recibe información completa\n";
                                
                                echo "\n📱 DATOS DISPONIBLES PARA APP MÓVIL:\n";
                                echo "   • Repeticiones mínimas: {$firstSet['reps_min']}\n";
                                echo "   • Repeticiones máximas: {$firstSet['reps_max']}\n";
                                echo "   • Intensidad objetivo (RPE): {$firstSet['rpe_target']}\n";
                                echo "   • Descanso entre series: {$firstSet['rest_seconds']}s\n";
                                
                            } elseif ($structureCorrectness >= 70 && $foundProblematic === 0) {
                                echo "⚠️  CORRECCIÓN PARCIALMENTE EXITOSA\n";
                                echo "✅ Campos problemáticos eliminados\n";
                                echo "⚠️  Algunos datos pueden estar incompletos\n";
                                
                            } else {
                                echo "❌ CORRECCIÓN FALLIDA O INCOMPLETA\n";
                                echo "🔧 Revisar mapeo del controlador\n";
                                if ($foundProblematic > 0) {
                                    echo "⚠️  Campos problemáticos aún presentes\n";
                                }
                            }
                            
                        } else {
                            echo "❌ No se encontraron ejercicios o series\n";
                        }
                    } else {
                        echo "❌ Error obteniendo detalles: {$detailsCode}\n";
                    }
                }
            }
        }
        
    } else {
        echo "❌ Error en login: {$httpCode}\n";
        echo "📝 Respuesta: " . substr($response, 0, 200) . "...\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎯 RESUMEN PARA PRESENTACIÓN:\n";
    echo str_repeat("=", 60) . "\n";
    
    if ($httpCode === 200) {
        echo "🎉 SERVIDOR ONLINE FUNCIONANDO\n\n";
        
        echo "🌐 URLS PARA CLIENTES:\n";
        echo "   📱 API Base: https://villamitre.loca.lt/api\n";
        echo "   🖥️  Panel Admin: https://villamitre.loca.lt/admin\n";
        echo "   🔐 Login: https://villamitre.loca.lt/api/auth/login\n\n";
        
        echo "👤 USUARIO DE PRUEBA:\n";
        echo "   📧 DNI: 33333333\n";
        echo "   🔒 Password: estudiante123\n";
        echo "   🎯 Tipo: Usuario API\n\n";
        
        echo "📱 PARA APP MÓVIL:\n";
        echo "   Cambiar URL base a: https://villamitre.loca.lt\n\n";
        
        echo "🖥️  PARA PANEL ADMIN:\n";
        echo "   Acceder desde navegador: https://villamitre.loca.lt/admin\n\n";
        
        echo "⚠️  MANTENER ACTIVO:\n";
        echo "   - Terminal Laravel: php artisan serve --host=0.0.0.0 --port=8000\n";
        echo "   - Terminal LocalTunnel: lt --port 8000 --subdomain villamitre\n";
        
    } else {
        echo "❌ PROBLEMAS CON EL SERVIDOR\n";
        echo "🔧 Verificar que ambos procesos estén ejecutándose\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
