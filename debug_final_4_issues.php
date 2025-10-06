<?php

echo "🔍 === DEBUGGING ÚLTIMOS 4 PROBLEMAS === 🔍\n\n";

function makeRequest($method, $endpoint, $token, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    if ($data) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['status' => $httpCode, 'data' => json_decode($response, true)];
}

// Login como admin
echo "1. Login como admin:\n";
$loginResponse = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
if ($loginResponse['status'] == 200) {
    $adminToken = $loginResponse['data']['token'];
    echo "   ✅ Token admin obtenido\n\n";
    
    echo "🔍 DEBUGGING CADA PROBLEMA:\n\n";
    
    // Problema 1: Estudiantes del profesor (404)
    echo "1. Estudiantes del profesor (404):\n";
    $result = makeRequest('GET', '/admin/professors/2/students', $adminToken);
    echo "   Status: {$result['status']} " . ($result['status'] == 200 ? '✅' : '❌') . "\n";
    if ($result['status'] != 200) {
        echo "   Error: " . json_encode($result['data']) . "\n";
    }
    
    // Problema 2: Reasignar estudiante (405)
    echo "\n2. Reasignar estudiante (405):\n";
    $reassignData = [
        'student_id' => 3,
        'new_professor_id' => 2,
        'reason' => 'Test reasignación'
    ];
    $result = makeRequest('POST', '/admin/professors/2/reassign-student', $adminToken, $reassignData);
    echo "   Status: {$result['status']} " . ($result['status'] == 200 ? '✅' : '❌') . "\n";
    if ($result['status'] != 200) {
        echo "   Error: " . json_encode($result['data']) . "\n";
    }
    
    // Problema 3: Configuración específica (404)
    echo "\n3. Configuración específica (404):\n";
    $result = makeRequest('GET', '/admin/settings/test_setting', $adminToken);
    echo "   Status: {$result['status']} " . ($result['status'] == 200 ? '✅' : '❌') . "\n";
    if ($result['status'] != 200) {
        echo "   Error: " . json_encode($result['data']) . "\n";
    }
    
    // Problema 4: Crear asignación (422)
    echo "\n4. Crear asignación (422):\n";
    $assignmentData = [
        'user_id' => 3,
        'week_start' => '2024-01-01',
        'week_end' => '2024-01-07',
        'source_type' => 'manual',
        'notes' => 'Rutina de prueba',
        'days' => [
            [
                'weekday' => 1,
                'date' => '2024-01-01',
                'title' => 'Día de entrenamiento',
                'notes' => 'Entrenamiento de fuerza',
                'exercises' => [
                    [
                        'exercise_id' => 1,
                        'order' => 1,
                        'name' => 'Push-ups',
                        'muscle_group' => 'chest',
                        'equipment' => 'none',
                        'instructions' => 'Standard push-ups',
                        'sets' => [
                            [
                                'set_number' => 1,
                                'reps_min' => 8,
                                'reps_max' => 12,
                                'rest_seconds' => 60
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    
    // Login como profesor para este test
    $professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
    if ($professorLogin['status'] == 200) {
        $professorToken = $professorLogin['data']['token'];
        $result = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $assignmentData);
        echo "   Status: {$result['status']} " . ($result['status'] == 201 ? '✅' : '❌') . "\n";
        if ($result['status'] != 201) {
            echo "   Error: " . json_encode($result['data']) . "\n";
        }
    }
    
} else {
    echo "   ❌ Login falló\n";
}

echo "\n📊 ANÁLISIS COMPLETO PARA CORRECCIONES FINALES\n";
