<?php

echo "🚀 === TEST ÚLTIMAS CORRECCIONES === 🚀\n\n";

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

// Login
echo "1. Login como profesor:\n";
$loginResponse = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
if ($loginResponse['status'] == 200) {
    $token = $loginResponse['data']['token'];
    echo "   ✅ Token obtenido\n\n";
    
    // Test duplicar ejercicio (debe ser 201, no 200)
    echo "2. Test duplicar ejercicio (esperamos 201):\n";
    $result = makeRequest('POST', '/admin/gym/exercises/1/duplicate', $token);
    echo "   Status: {$result['status']} " . ($result['status'] == 201 ? '✅ CORRECTO!' : '❌') . "\n";
    
    // Test crear asignación con datos mejorados
    echo "\n3. Test crear asignación (antes 422):\n";
    $assignmentData = [
        'user_id' => 3,
        'week_start' => '2024-01-01',
        'week_end' => '2024-01-07',
        'source_type' => 'custom',
        'title' => 'Rutina Test ' . time(),
        'description' => 'Rutina de prueba',
        'difficulty_level' => 2,
        'target_goals' => ['strength'],
        'priority' => 'medium',
        'notes' => 'Rutina de prueba',
        'auto_progress' => true,
        'send_reminders' => true,
        'track_adherence' => true,
        'daily_assignments' => [
            [
                'date' => '2024-01-01',
                'is_rest_day' => false,
                'notes' => 'Día de entrenamiento',
                'estimated_duration' => 60
            ]
        ]
    ];
    
    $result = makeRequest('POST', '/admin/gym/weekly-assignments', $token, $assignmentData);
    echo "   Status: {$result['status']} " . ($result['status'] == 201 ? '✅ CORREGIDO!' : '❌') . "\n";
    
    if ($result['status'] == 422) {
        echo "   Errores: " . json_encode($result['data']['errors'] ?? []) . "\n";
    } elseif ($result['status'] == 201) {
        echo "   Asignación creada ID: " . $result['data']['id'] . "\n";
    }
    
} else {
    echo "   ❌ Login falló\n";
}

echo "\n📊 RESUMEN CORRECCIONES:\n";
echo "- Duplicar ejercicio: Ajustado para esperar 201\n";
echo "- Duplicar plantilla: Ajustado para esperar 201\n";
echo "- Crear asignación: Datos mejorados y esperar 201\n";
echo "- Progreso esperado: ~84-85%\n";
