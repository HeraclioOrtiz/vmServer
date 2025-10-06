<?php

echo "🔍 === DEBUGGING ÚLTIMOS 2 PROBLEMAS === 🔍\n\n";

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
$adminLogin = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
if ($adminLogin['status'] == 200) {
    $adminToken = $adminLogin['data']['token'];
    echo "   ✅ Token admin obtenido\n\n";
    
    // Problema 1: Configuración específica (404)
    echo "2. Configuración específica (404):\n";
    
    // Primero crear una configuración
    $createResult = makeRequest('POST', '/admin/settings', $adminToken, [
        'key' => 'test_config_final',
        'value' => 'test_value_final',
        'category' => 'testing',
        'description' => 'Config para testing final'
    ]);
    echo "   Crear config: {$createResult['status']}\n";
    
    // Luego intentar leerla
    $result = makeRequest('GET', '/admin/settings/test_config_final', $adminToken);
    echo "   Leer config: {$result['status']} " . ($result['status'] == 200 ? '✅' : '❌') . "\n";
    if ($result['status'] != 200) {
        echo "   Error: " . json_encode($result['data']) . "\n";
    }
    
} else {
    echo "   ❌ Login admin falló\n";
}

// Login como profesor para crear asignación
echo "\n3. Login como profesor:\n";
$professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
if ($professorLogin['status'] == 200) {
    $professorToken = $professorLogin['data']['token'];
    echo "   ✅ Token profesor obtenido\n\n";
    
    // Problema 2: Crear asignación (500)
    echo "4. Crear asignación (500 → 201):\n";
    $assignmentData = [
        'user_id' => 3,
        'week_start' => '2024-01-01',
        'week_end' => '2024-01-07',
        'source_type' => 'manual',
        'notes' => 'Rutina de prueba final',
        'days' => [
            [
                'weekday' => 1,
                'date' => '2024-01-01',
                'title' => 'Día 1',
                'notes' => 'Entrenamiento básico',
                'exercises' => []  // Simplificado sin ejercicios para evitar errores
            ]
        ]
    ];
    
    $result = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $assignmentData);
    echo "   Status: {$result['status']} " . ($result['status'] == 201 ? '✅' : '❌') . "\n";
    if ($result['status'] != 201) {
        echo "   Error: " . json_encode($result['data']) . "\n";
    }
    
} else {
    echo "   ❌ Login profesor falló\n";
}

echo "\n🎯 OBJETIVO: Corregir estos 2 últimos problemas para llegar al 98%+\n";
