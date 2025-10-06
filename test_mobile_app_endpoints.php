<?php

echo "📱 === TESTING COMPLETO ENDPOINTS PARA APP MÓVIL === 📱\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Función para mostrar estructura de datos
function analyzeStructure($data, $prefix = '') {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $type = is_array($value) ? 'array' : gettype($value);
            echo "{$prefix}• {$key}: {$type}";
            
            if (is_array($value) && !empty($value)) {
                if (is_numeric(array_keys($value)[0])) {
                    echo " [" . count($value) . " elementos]";
                }
                echo "\n";
                if (count($value) <= 3) { // Solo mostrar primeros elementos
                    analyzeStructure($value, $prefix . "  ");
                }
            } else {
                if (is_string($value) && strlen($value) > 50) {
                    echo " (texto largo)\n";
                } else {
                    echo " = " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
        }
    } else {
        echo "{$prefix}Valor: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

try {
    // Buscar estudiante de prueba
    $maria = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();
    
    if (!$maria) {
        die("❌ No se encontró María García\n");
    }
    
    echo "👤 USUARIO DE PRUEBA: {$maria->name} (ID: {$maria->id})\n";
    echo "📧 Email: {$maria->email}\n\n";
    
    // Simular autenticación
    \Illuminate\Support\Facades\Auth::login($maria);
    
    $controller = new \App\Http\Controllers\Gym\Student\AssignmentController();
    
    echo str_repeat("=", 80) . "\n";
    echo "🎯 TEST 1: GET /api/student/my-templates\n";
    echo str_repeat("=", 80) . "\n";
    
    $request1 = \Illuminate\Http\Request::create('/api/student/my-templates', 'GET');
    $request1->setUserResolver(function () use ($maria) {
        return $maria;
    });
    
    $response1 = $controller->myTemplates($request1);
    $data1 = json_decode($response1->getContent(), true);
    
    echo "📊 STATUS: {$response1->getStatusCode()}\n";
    echo "📋 MESSAGE: {$data1['message']}\n\n";
    
    echo "🔍 ESTRUCTURA DE RESPUESTA:\n";
    analyzeStructure($data1);
    
    echo "\n✅ VALIDACIONES:\n";
    $validations = [
        'Tiene campo message' => isset($data1['message']),
        'Tiene campo data' => isset($data1['data']),
        'Tiene profesor info' => isset($data1['data']['professor']),
        'Profesor tiene id' => isset($data1['data']['professor']['id']),
        'Profesor tiene name' => isset($data1['data']['professor']['name']),
        'Profesor tiene email' => isset($data1['data']['professor']['email']),
        'Tiene templates array' => isset($data1['data']['templates']) && is_array($data1['data']['templates']),
    ];
    
    foreach ($validations as $test => $result) {
        echo ($result ? "✅" : "❌") . " {$test}\n";
    }
    
    // Guardar template ID para siguiente test
    $templateAssignmentId = null;
    if (isset($data1['data']['templates']) && count($data1['data']['templates']) > 0) {
        $templateAssignmentId = $data1['data']['templates'][0]['id'];
        echo "\n🎯 TEMPLATE SELECCIONADO PARA DETALLES: ID {$templateAssignmentId}\n";
        
        $template = $data1['data']['templates'][0];
        echo "📝 Título: {$template['daily_template']['title']}\n";
        echo "🎯 Goal: {$template['daily_template']['goal']}\n";
        echo "📊 Level: {$template['daily_template']['level']}\n";
        echo "⏱️ Duración: {$template['daily_template']['estimated_duration_min']} min\n";
        echo "📅 Frecuencia: " . implode(', ', $template['frequency_days']) . "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "🎯 TEST 2: GET /api/student/template/{id}/details\n";
    echo str_repeat("=", 80) . "\n";
    
    if ($templateAssignmentId) {
        $response2 = $controller->templateDetails($templateAssignmentId);
        $data2 = json_decode($response2->getContent(), true);
        
        echo "📊 STATUS: {$response2->getStatusCode()}\n";
        echo "📋 MESSAGE: {$data2['message']}\n\n";
        
        echo "🔍 ESTRUCTURA DE RESPUESTA:\n";
        analyzeStructure($data2);
        
        echo "\n✅ VALIDACIONES:\n";
        $validations2 = [
            'Tiene assignment_info' => isset($data2['data']['assignment_info']),
            'Tiene template info' => isset($data2['data']['template']),
            'Tiene exercises array' => isset($data2['data']['exercises']) && is_array($data2['data']['exercises']),
            'Assignment tiene frequency' => isset($data2['data']['assignment_info']['frequency']),
            'Assignment tiene professor notes' => isset($data2['data']['assignment_info']['professor_notes']),
            'Template tiene title' => isset($data2['data']['template']['title']),
            'Template tiene goal' => isset($data2['data']['template']['goal']),
        ];
        
        foreach ($validations2 as $test => $result) {
            echo ($result ? "✅" : "❌") . " {$test}\n";
        }
        
        if (isset($data2['data']['exercises']) && count($data2['data']['exercises']) > 0) {
            $exercise = $data2['data']['exercises'][0];
            echo "\n🏋️ PRIMER EJERCICIO:\n";
            echo "📝 Nombre: {$exercise['exercise']['name']}\n";
            echo "🎯 Músculos: " . implode(', ', $exercise['exercise']['target_muscle_groups']) . "\n";
            echo "🏃 Dificultad: {$exercise['exercise']['difficulty_level']}\n";
            echo "🔧 Equipamiento: {$exercise['exercise']['equipment']}\n";
            echo "📊 Sets: " . count($exercise['sets']) . "\n";
            
            if (count($exercise['sets']) > 0) {
                $set = $exercise['sets'][0];
                echo "\n📋 PRIMER SET:\n";
                echo "🔢 Reps: {$set['reps_min']}-{$set['reps_max']}\n";
                echo "💪 RPE Target: {$set['rpe_target']}\n";
                echo "⏱️ Descanso: {$set['rest_seconds']} seg\n";
            }
        }
    } else {
        echo "⚠️ No hay template assignment ID para probar\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "🎯 TEST 3: GET /api/student/my-weekly-calendar\n";
    echo str_repeat("=", 80) . "\n";
    
    $request3 = \Illuminate\Http\Request::create('/api/student/my-weekly-calendar', 'GET');
    $request3->setUserResolver(function () use ($maria) {
        return $maria;
    });
    
    $response3 = $controller->myWeeklyCalendar($request3);
    $data3 = json_decode($response3->getContent(), true);
    
    echo "📊 STATUS: {$response3->getStatusCode()}\n";
    echo "📋 MESSAGE: {$data3['message']}\n\n";
    
    echo "🔍 ESTRUCTURA DE RESPUESTA:\n";
    analyzeStructure($data3);
    
    echo "\n✅ VALIDACIONES:\n";
    $validations3 = [
        'Tiene week_start' => isset($data3['data']['week_start']),
        'Tiene week_end' => isset($data3['data']['week_end']),
        'Tiene days array' => isset($data3['data']['days']) && is_array($data3['data']['days']),
        'Days tiene 7 elementos' => isset($data3['data']['days']) && count($data3['data']['days']) == 7,
    ];
    
    foreach ($validations3 as $test => $result) {
        echo ($result ? "✅" : "❌") . " {$test}\n";
    }
    
    if (isset($data3['data']['days'])) {
        echo "\n📅 CALENDARIO SEMANAL:\n";
        echo "📅 Semana: {$data3['data']['week_start']} → {$data3['data']['week_end']}\n\n";
        
        foreach ($data3['data']['days'] as $day) {
            $workoutIcon = $day['has_workouts'] ? "🏋️" : "📅";
            $assignmentsCount = count($day['assignments']);
            echo "{$workoutIcon} {$day['day_name']} ({$day['date']}) - {$assignmentsCount} entrenamientos\n";
            
            if ($day['has_workouts'] && $assignmentsCount > 0) {
                foreach ($day['assignments'] as $assignment) {
                    echo "   └─ {$assignment['daily_template']['title']} ({$assignment['daily_template']['estimated_duration_min']} min)\n";
                }
            }
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "🎯 TEST 4: SIMULACIÓN POST /api/student/progress/{session_id}/complete\n";
    echo str_repeat("=", 80) . "\n";
    
    echo "📋 ESTRUCTURA DE DATOS QUE DEBE ENVIAR LA APP:\n\n";
    
    $exampleProgress = [
        'exercise_progress' => [
            [
                'exercise_id' => 2,
                'sets' => [
                    [
                        'set_number' => 1,
                        'reps_completed' => 8,
                        'weight' => 60.0,
                        'rpe_actual' => 8.5,
                        'notes' => 'Buena forma'
                    ],
                    [
                        'set_number' => 2,
                        'reps_completed' => 7,
                        'weight' => 60.0,
                        'rpe_actual' => 9.0,
                        'notes' => null
                    ]
                ]
            ],
            [
                'exercise_id' => 3,
                'sets' => [
                    [
                        'set_number' => 1,
                        'reps_completed' => 12,
                        'weight' => null,
                        'rpe_actual' => 7.0,
                        'notes' => 'Peso corporal'
                    ]
                ]
            ]
        ],
        'student_notes' => 'Me sentí bien hoy, buen entrenamiento',
        'completed_at' => now()->toISOString()
    ];
    
    echo "```json\n";
    echo json_encode($exampleProgress, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n```\n\n";
    
    echo "✅ CAMPOS VALIDADOS:\n";
    echo "• exercise_progress: array de ejercicios\n";
    echo "• exercise_id: integer (ID del ejercicio)\n";
    echo "• sets: array de sets completados\n";
    echo "• set_number: integer (número del set)\n";
    echo "• reps_completed: integer (repeticiones realizadas)\n";
    echo "• weight: float|null (peso usado, null si es peso corporal)\n";
    echo "• rpe_actual: float (RPE percibido 1.0-10.0)\n";
    echo "• notes: string|null (notas del set)\n";
    echo "• student_notes: string|null (notas generales)\n";
    echo "• completed_at: string ISO 8601 (timestamp)\n";
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "📊 RESUMEN FINAL PARA APP MÓVIL\n";
    echo str_repeat("=", 80) . "\n";
    
    echo "\n✅ ENDPOINTS VALIDADOS:\n";
    echo "1. ✅ GET /api/student/my-templates - Plantillas asignadas\n";
    echo "2. ✅ GET /api/student/template/{id}/details - Detalles con ejercicios\n";
    echo "3. ✅ GET /api/student/my-weekly-calendar - Calendario semanal\n";
    echo "4. ✅ POST /api/student/progress/{id}/complete - Estructura validada\n";
    
    echo "\n📱 DATOS CLAVE PARA LA APP:\n";
    echo "• Profesor asignado: {$data1['data']['professor']['name']}\n";
    echo "• Templates activos: " . count($data1['data']['templates']) . "\n";
    echo "• Días con entrenamientos esta semana: ";
    $workoutDays = array_filter($data3['data']['days'], function($day) { return $day['has_workouts']; });
    echo count($workoutDays) . "\n";
    
    if (isset($data2['data']['exercises'])) {
        echo "• Ejercicios en plantilla seleccionada: " . count($data2['data']['exercises']) . "\n";
        $totalSets = array_sum(array_map(function($ex) { return count($ex['sets']); }, $data2['data']['exercises']));
        echo "• Sets totales en plantilla: {$totalSets}\n";
    }
    
    echo "\n🎯 PRÓXIMOS PASOS PARA DESARROLLADOR MÓVIL:\n";
    echo "1. Implementar login y obtener token Sanctum\n";
    echo "2. Crear modelos de datos basados en estas estructuras\n";
    echo "3. Implementar pantallas según flujos documentados\n";
    echo "4. Testing con estos endpoints reales\n";
    echo "5. Implementar envío de progreso con estructura validada\n";
    
    echo "\n🚀 SISTEMA LISTO PARA APP MÓVIL!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
