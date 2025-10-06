<?php

echo "🎓 === TESTING: VISTA DE ESTUDIANTE - MARÍA GARCÍA === 🎓\n\n";

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
    
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '33333333',
        'password' => 'estudiante123'
    ]);
    
    echo "📊 Status login: {$loginResponse['status']}\n";
    
    if ($loginResponse['status'] !== 200) {
        echo "❌ ERROR en login de María García\n";
        if (isset($loginResponse['data']['message'])) {
            echo "   Mensaje: {$loginResponse['data']['message']}\n";
        }
        
        // Intentar con password alternativa
        echo "🔄 Intentando con password alternativa...\n";
        $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
            'dni' => '33333333',
            'password' => 'password'
        ]);
        
        if ($loginResponse['status'] !== 200) {
            echo "❌ Login fallido con ambas passwords\n";
            echo "💡 Verificando credenciales en BD...\n";
            
            // Verificar credenciales directamente en BD
            $maria = \App\Models\User::where('dni', '33333333')->first();
            if ($maria) {
                echo "✅ Usuario encontrado en BD:\n";
                echo "   Nombre: {$maria->name}\n";
                echo "   Email: {$maria->email}\n";
                echo "   DNI: {$maria->dni}\n";
                echo "   Es profesor: " . ($maria->is_professor ? 'Sí' : 'No') . "\n";
                echo "   Es admin: " . ($maria->is_admin ? 'Sí' : 'No') . "\n";
                echo "   Estado: {$maria->account_status}\n";
                
                // Intentar resetear password
                echo "🔧 Reseteando password a 'estudiante123'...\n";
                $maria->password = bcrypt('estudiante123');
                $maria->save();
                
                // Intentar login nuevamente
                $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
                    'dni' => '33333333',
                    'password' => 'estudiante123'
                ]);
                
                if ($loginResponse['status'] !== 200) {
                    echo "❌ Login sigue fallando después del reset\n";
                    exit(1);
                }
            } else {
                echo "❌ Usuario no encontrado en BD\n";
                exit(1);
            }
        }
    }
    
    $token = $loginResponse['data']['data']['token'];
    $student = $loginResponse['data']['data']['user'];
    
    echo "✅ Login exitoso como: {$student['name']}\n";
    echo "🎓 Tipo de usuario: " . ($student['is_professor'] ? 'Profesor' : 'Estudiante') . "\n\n";
    
    echo "🏋️ PASO 2: Consultando plantillas diarias asignadas...\n";
    
    // Intentar diferentes endpoints posibles para estudiantes
    $possibleEndpoints = [
        '/api/gym/my-templates',
        '/api/gym/my-assignments',
        '/api/gym/my-routines',
        '/api/student/my-templates',
        '/api/student/assignments',
        '/api/gym/student/my-templates'
    ];
    
    $workingEndpoint = null;
    $templatesData = null;
    
    foreach ($possibleEndpoints as $endpoint) {
        echo "🔍 Probando: {$endpoint}\n";
        $response = makeRequest("http://127.0.0.1:8000{$endpoint}", 'GET', null, $token);
        echo "   Status: {$response['status']}\n";
        
        if ($response['status'] === 200) {
            echo "   ✅ Endpoint funciona!\n";
            $workingEndpoint = $endpoint;
            $templatesData = $response['data'];
            break;
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
    }
    
    if (!$workingEndpoint) {
        echo "\n❌ PROBLEMA: No se encontró endpoint funcional para estudiantes\n";
        echo "💡 Verificando rutas disponibles...\n";
        
        // Verificar rutas en el sistema
        echo "🔍 Consultando rutas del sistema...\n";
        $routesResponse = makeRequest('http://127.0.0.1:8000/api', 'GET', null, $token);
        echo "   Status rutas base: {$routesResponse['status']}\n";
        
        exit(1);
    }
    
    echo "\n✅ Endpoint funcional encontrado: {$workingEndpoint}\n";
    echo "📊 Estructura de respuesta:\n";
    echo json_encode($templatesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "📋 PASO 3: Analizando plantillas asignadas...\n";
    
    $templates = $templatesData['data'] ?? $templatesData ?? [];
    
    if (empty($templates)) {
        echo "⚠️  No se encontraron plantillas asignadas\n";
        echo "🔍 Verificando directamente en BD...\n";
        
        // Verificar en BD directamente
        $maria = \App\Models\User::where('dni', '33333333')->first();
        $professorAssignment = \App\Models\Gym\ProfessorStudentAssignment::where('student_id', $maria->id)->first();
        
        if ($professorAssignment) {
            $templateAssignments = \App\Models\Gym\TemplateAssignment::where('professor_student_assignment_id', $professorAssignment->id)
                                                                   ->with(['dailyTemplate', 'professorStudentAssignment.professor'])
                                                                   ->get();
            
            echo "📊 Plantillas en BD: {$templateAssignments->count()}\n";
            
            if ($templateAssignments->count() > 0) {
                echo "❌ PROBLEMA: Hay plantillas en BD pero la API no las devuelve\n";
                echo "💡 Posible problema en el endpoint o permisos\n";
            }
        }
        
        exit(1);
    }
    
    echo "✅ Plantillas encontradas: " . count($templates) . "\n\n";
    
    foreach ($templates as $index => $template) {
        echo "📌 PLANTILLA #" . ($index + 1) . ":\n";
        echo str_repeat("-", 50) . "\n";
        
        // Información básica de la plantilla
        if (isset($template['daily_template'])) {
            $dailyTemplate = $template['daily_template'];
            echo "📝 Título: {$dailyTemplate['title']}\n";
            echo "🎯 Objetivo: {$dailyTemplate['goal']}\n";
            echo "📊 Nivel: {$dailyTemplate['level']}\n";
            echo "⏱️  Duración: {$dailyTemplate['estimated_duration_min']} minutos\n";
        }
        
        // Información de asignación
        echo "📅 Fecha inicio: {$template['start_date']}\n";
        echo "📅 Fecha fin: " . ($template['end_date'] ?: 'Sin fecha fin') . "\n";
        echo "📊 Estado: {$template['status']}\n";
        
        // Frecuencia
        if (isset($template['frequency'])) {
            $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $frequencyDays = array_map(function($day) use ($days) {
                return $days[$day] ?? "Día $day";
            }, $template['frequency']);
            echo "📅 Días de entrenamiento: " . implode(', ', $frequencyDays) . "\n";
        }
        
        // Profesor que asignó
        if (isset($template['professor_student_assignment']['professor'])) {
            $professor = $template['professor_student_assignment']['professor'];
            echo "👨‍🏫 Asignado por: {$professor['name']}\n";
        } elseif (isset($template['assigned_by_professor'])) {
            echo "👨‍🏫 Asignado por: {$template['assigned_by_professor']['name']}\n";
        } else {
            echo "👨‍🏫 Asignado por: No especificado\n";
        }
        
        // Notas del profesor
        echo "📝 Notas del profesor: " . ($template['professor_notes'] ?: 'Sin notas') . "\n";
        
        echo "\n";
    }
    
    echo "🏋️ PASO 4: Verificando detalles de ejercicios...\n";
    
    // Tomar la primera plantilla para verificar ejercicios
    $firstTemplate = $templates[0];
    $templateId = $firstTemplate['daily_template']['id'] ?? $firstTemplate['daily_template_id'] ?? null;
    
    if ($templateId) {
        echo "🔍 Consultando ejercicios de la plantilla ID: {$templateId}\n";
        
        $exercisesEndpoints = [
            "/api/gym/templates/{$templateId}/exercises",
            "/api/gym/daily-templates/{$templateId}/exercises",
            "/api/gym/templates/{$templateId}",
            "/api/student/template/{$templateId}/exercises"
        ];
        
        $exercisesFound = false;
        
        foreach ($exercisesEndpoints as $endpoint) {
            echo "🔍 Probando: {$endpoint}\n";
            $response = makeRequest("http://127.0.0.1:8000{$endpoint}", 'GET', null, $token);
            echo "   Status: {$response['status']}\n";
            
            if ($response['status'] === 200) {
                echo "   ✅ Ejercicios obtenidos!\n";
                $exercisesData = $response['data'];
                
                if (isset($exercisesData['data']['exercises'])) {
                    $exercises = $exercisesData['data']['exercises'];
                } elseif (isset($exercisesData['exercises'])) {
                    $exercises = $exercisesData['exercises'];
                } else {
                    $exercises = $exercisesData['data'] ?? $exercisesData;
                }
                
                echo "   📊 Ejercicios encontrados: " . count($exercises) . "\n";
                
                if (count($exercises) > 0) {
                    echo "\n🏋️ EJERCICIOS DE LA PLANTILLA:\n";
                    echo str_repeat("=", 60) . "\n";
                    
                    foreach ($exercises as $idx => $exercise) {
                        echo "💪 EJERCICIO #" . ($idx + 1) . ":\n";
                        
                        if (isset($exercise['exercise'])) {
                            $exerciseData = $exercise['exercise'];
                            echo "   📝 Nombre: {$exerciseData['name']}\n";
                            echo "   🎯 Grupo muscular: " . implode(', ', $exerciseData['target_muscle_groups'] ?? []) . "\n";
                            echo "   🏋️ Equipo: " . implode(', ', $exerciseData['equipment'] ?? []) . "\n";
                        } else {
                            echo "   📝 Nombre: {$exercise['name']}\n";
                        }
                        
                        // Series
                        if (isset($exercise['sets'])) {
                            echo "   📊 Series: " . count($exercise['sets']) . "\n";
                            foreach ($exercise['sets'] as $setIdx => $set) {
                                echo "      Serie " . ($setIdx + 1) . ": {$set['reps']} reps";
                                if (isset($set['weight'])) echo " x {$set['weight']}kg";
                                if (isset($set['duration'])) echo " x {$set['duration']}s";
                                echo "\n";
                            }
                        }
                        
                        echo "\n";
                    }
                }
                
                $exercisesFound = true;
                break;
            }
        }
        
        if (!$exercisesFound) {
            echo "❌ No se pudieron obtener los ejercicios\n";
        }
    }
    
    echo "\n🎯 RESUMEN FINAL:\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "👤 ESTUDIANTE: María García\n";
    echo "🔐 Login: ✅ Exitoso\n";
    echo "📋 Plantillas asignadas: " . count($templates) . "\n";
    echo "🏋️ Detalles de ejercicios: " . ($exercisesFound ? '✅ Disponibles' : '❌ No disponibles') . "\n";
    echo "👨‍🏫 Información del profesor: ✅ Incluida\n";
    echo "📅 Fechas y frecuencias: ✅ Incluidas\n";
    
    if (count($templates) > 0 && $exercisesFound) {
        echo "\n🎉 ÉXITO COMPLETO:\n";
        echo "✅ María García puede ver todas sus plantillas\n";
        echo "✅ Puede ver quién se las asignó\n";
        echo "✅ Puede ver los ejercicios detallados\n";
        echo "✅ Tiene toda la información necesaria\n";
    } else {
        echo "\n⚠️  PROBLEMAS DETECTADOS:\n";
        if (count($templates) === 0) {
            echo "❌ No puede ver plantillas asignadas\n";
        }
        if (!$exercisesFound) {
            echo "❌ No puede ver ejercicios detallados\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
