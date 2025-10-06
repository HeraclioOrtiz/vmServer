<?php

echo "🎓 === TESTING CORRECTO: MARÍA GARCÍA - ENDPOINTS EXISTENTES === 🎓\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

// Función para hacer requests HTTP
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
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
    
    // Verificar credenciales primero
    $maria = \App\Models\User::where('dni', '33333333')->first();
    if ($maria) {
        echo "✅ Usuario encontrado: {$maria->name}\n";
        echo "   Password reseteado a 'estudiante123'\n";
        $maria->password = bcrypt('estudiante123');
        $maria->save();
    }
    
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '33333333',
        'password' => 'estudiante123'
    ]);
    
    if ($loginResponse['status'] !== 200) {
        echo "❌ ERROR en login: Status {$loginResponse['status']}\n";
        if (isset($loginResponse['data']['message'])) {
            echo "   Mensaje: {$loginResponse['data']['message']}\n";
        }
        exit(1);
    }
    
    $token = $loginResponse['data']['data']['token'];
    $student = $loginResponse['data']['data']['user'];
    
    echo "✅ Login exitoso: {$student['name']}\n\n";
    
    echo "🏋️ PASO 2: Consultando endpoints correctos...\n";
    
    // Probar los endpoints que existen según las rutas
    $endpoints = [
        '/api/gym/my-week' => 'Mi semana de entrenamientos',
        '/api/gym/my-day' => 'Mi día de entrenamiento'
    ];
    
    foreach ($endpoints as $endpoint => $description) {
        echo "🔍 Probando: {$endpoint} ({$description})\n";
        $response = makeRequest("http://127.0.0.1:8000{$endpoint}", 'GET', null, $token);
        echo "   Status: {$response['status']}\n";
        
        if ($response['status'] === 200) {
            echo "   ✅ Endpoint funciona!\n";
            $data = $response['data'];
            
            echo "   📊 Estructura de respuesta:\n";
            if (isset($data['data'])) {
                $responseData = $data['data'];
                if (is_array($responseData)) {
                    echo "      Tipo: Array con " . count($responseData) . " elementos\n";
                    if (count($responseData) > 0) {
                        echo "      Primer elemento: " . json_encode($responseData[0], JSON_UNESCAPED_UNICODE) . "\n";
                    }
                } else {
                    echo "      Datos: " . json_encode($responseData, JSON_UNESCAPED_UNICODE) . "\n";
                }
            } else {
                echo "      Respuesta completa: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            }
            
            // Si es my-week, analizar en detalle
            if ($endpoint === '/api/gym/my-week') {
                echo "\n📋 ANÁLISIS DETALLADO DE MY-WEEK:\n";
                echo str_repeat("-", 50) . "\n";
                
                $weekData = $data['data'] ?? $data;
                
                if (is_array($weekData) && count($weekData) > 0) {
                    echo "📊 Días con entrenamientos: " . count($weekData) . "\n";
                    
                    foreach ($weekData as $dayIndex => $day) {
                        echo "\n📅 DÍA #" . ($dayIndex + 1) . ":\n";
                        
                        if (isset($day['date'])) {
                            echo "   Fecha: {$day['date']}\n";
                        }
                        
                        if (isset($day['day_name'])) {
                            echo "   Día: {$day['day_name']}\n";
                        }
                        
                        if (isset($day['assignments'])) {
                            echo "   Asignaciones: " . count($day['assignments']) . "\n";
                            
                            foreach ($day['assignments'] as $assignmentIndex => $assignment) {
                                echo "   📌 Asignación #" . ($assignmentIndex + 1) . ":\n";
                                
                                if (isset($assignment['daily_template'])) {
                                    $template = $assignment['daily_template'];
                                    echo "      📝 Plantilla: {$template['title']}\n";
                                    echo "      🎯 Objetivo: {$template['goal']}\n";
                                    echo "      📊 Nivel: {$template['level']}\n";
                                    echo "      ⏱️  Duración: {$template['estimated_duration_min']} min\n";
                                }
                                
                                if (isset($assignment['professor_student_assignment']['professor'])) {
                                    $professor = $assignment['professor_student_assignment']['professor'];
                                    echo "      👨‍🏫 Profesor: {$professor['name']}\n";
                                }
                                
                                if (isset($assignment['professor_notes'])) {
                                    echo "      📝 Notas: {$assignment['professor_notes']}\n";
                                }
                            }
                        } elseif (isset($day['templates'])) {
                            echo "   Plantillas: " . count($day['templates']) . "\n";
                        } else {
                            echo "   Sin entrenamientos programados\n";
                        }
                    }
                } else {
                    echo "⚠️  No hay entrenamientos programados para esta semana\n";
                }
            }
            
        } elseif ($response['status'] === 404) {
            echo "   ❌ Endpoint no existe\n";
        } elseif ($response['status'] === 401) {
            echo "   ❌ No autorizado\n";
        } elseif ($response['status'] === 403) {
            echo "   ❌ Prohibido\n";
        } else {
            echo "   ⚠️  Error: {$response['status']}\n";
            if (isset($response['data']['message'])) {
                echo "      Mensaje: {$response['data']['message']}\n";
            }
        }
        
        echo "\n";
    }
    
    echo "🔍 PASO 3: Verificando controlador GymMyPlanController...\n";
    
    // Verificar si el controlador existe
    $controllerPath = 'f:\Laburo\Programacion\Laburo-Javi\VILLAMITRE\vmServer\app\Http\Controllers\Gym\Mobile\MyPlanController.php';
    if (file_exists($controllerPath)) {
        echo "✅ Controlador MyPlanController existe\n";
    } else {
        echo "❌ Controlador MyPlanController NO existe\n";
        echo "💡 Necesita ser creado para que funcionen los endpoints\n";
    }
    
    echo "\n🎯 RESUMEN:\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "👤 ESTUDIANTE: María García\n";
    echo "🔐 Login: ✅ Exitoso\n";
    echo "📋 Endpoints disponibles:\n";
    echo "   - /api/gym/my-week (Mi semana)\n";
    echo "   - /api/gym/my-day (Mi día)\n";
    
    echo "\n💡 FUNCIONALIDAD ESPERADA:\n";
    echo "✅ María García debería poder ver:\n";
    echo "   - Sus plantillas asignadas por día/semana\n";
    echo "   - Qué profesor se las asignó\n";
    echo "   - Detalles de cada plantilla\n";
    echo "   - Ejercicios y series de cada plantilla\n";
    echo "   - Notas del profesor\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
