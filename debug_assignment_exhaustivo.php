<?php

echo "🔬 === ANÁLISIS EXHAUSTIVO CREAR ASIGNACIÓN === 🔬\n\n";

function makeRequest($method, $endpoint, $token, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
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
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    return [
        'status' => $httpCode,
        'headers' => $headers,
        'data' => json_decode($body, true),
        'raw_body' => $body
    ];
}

// Login como profesor
echo "1. Login como profesor:\n";
$professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
if ($professorLogin['status'] == 200) {
    $professorToken = $professorLogin['data']['token'];
    echo "   ✅ Token profesor obtenido\n\n";
    
    // ANÁLISIS PASO A PASO DE LAS VALIDACIONES
    echo "2. ANÁLISIS PASO A PASO:\n";
    
    // Paso 1: Datos mínimos absolutos
    echo "   a) Test con datos mínimos absolutos:\n";
    $minimalData = [
        'user_id' => 3,
        'week_start' => '2024-02-01',
        'week_end' => '2024-02-07'
    ];
    
    $result = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $minimalData);
    echo "      Status: {$result['status']}\n";
    if ($result['status'] == 422) {
        echo "      Errores: " . json_encode($result['data']['errors'] ?? [], JSON_PRETTY_PRINT) . "\n";
    }
    
    // Paso 2: Agregar campos opcionales uno por uno
    echo "\n   b) Agregando source_type:\n";
    $withSourceType = $minimalData + ['source_type' => 'manual'];
    $result = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $withSourceType);
    echo "      Status: {$result['status']}\n";
    if ($result['status'] == 422) {
        echo "      Errores: " . json_encode($result['data']['errors'] ?? [], JSON_PRETTY_PRINT) . "\n";
    }
    
    // Paso 3: Agregar notes
    echo "\n   c) Agregando notes:\n";
    $withNotes = $withSourceType + ['notes' => 'Test assignment'];
    $result = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $withNotes);
    echo "      Status: {$result['status']}\n";
    if ($result['status'] == 422) {
        echo "      Errores: " . json_encode($result['data']['errors'] ?? [], JSON_PRETTY_PRINT) . "\n";
    }
    
    // Paso 4: Agregar days vacío
    echo "\n   d) Agregando days array vacío:\n";
    $withEmptyDays = $withNotes + ['days' => []];
    $result = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $withEmptyDays);
    echo "      Status: {$result['status']}\n";
    if ($result['status'] == 422) {
        echo "      Errores: " . json_encode($result['data']['errors'] ?? [], JSON_PRETTY_PRINT) . "\n";
    }
    
    // Paso 5: Agregar un día mínimo
    echo "\n   e) Agregando un día mínimo:\n";
    $withMinimalDay = $withNotes + [
        'days' => [
            [
                'weekday' => 1,
                'date' => '2024-02-01'
            ]
        ]
    ];
    $result = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $withMinimalDay);
    echo "      Status: {$result['status']}\n";
    if ($result['status'] == 422) {
        echo "      Errores: " . json_encode($result['data']['errors'] ?? [], JSON_PRETTY_PRINT) . "\n";
    } elseif ($result['status'] == 201) {
        echo "      ✅ ¡ÉXITO! Asignación creada\n";
        echo "      ID: " . ($result['data']['id'] ?? 'N/A') . "\n";
    }
    
    // Paso 6: Agregar campos opcionales del día
    echo "\n   f) Agregando campos opcionales del día:\n";
    $withCompleteDay = $withNotes + [
        'days' => [
            [
                'weekday' => 1,
                'date' => '2024-02-01',
                'title' => 'Día 1',
                'notes' => 'Primer día',
                'exercises' => []
            ]
        ]
    ];
    $result = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $withCompleteDay);
    echo "      Status: {$result['status']}\n";
    if ($result['status'] == 422) {
        echo "      Errores: " . json_encode($result['data']['errors'] ?? [], JSON_PRETTY_PRINT) . "\n";
    } elseif ($result['status'] == 201) {
        echo "      ✅ ¡ÉXITO! Asignación completa creada\n";
        echo "      ID: " . ($result['data']['id'] ?? 'N/A') . "\n";
    }
    
    // Paso 7: Test con fechas diferentes para evitar conflictos
    echo "\n   g) Test con fechas únicas:\n";
    $uniqueTime = time();
    $uniqueData = [
        'user_id' => 3,
        'week_start' => '2024-03-01',
        'week_end' => '2024-03-07',
        'source_type' => 'manual',
        'notes' => 'Assignment ' . $uniqueTime,
        'days' => [
            [
                'weekday' => 1,
                'date' => '2024-03-01',
                'title' => 'Day ' . $uniqueTime,
                'notes' => 'Test day',
                'exercises' => []
            ]
        ]
    ];
    
    $result = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $uniqueData);
    echo "      Status: {$result['status']}\n";
    if ($result['status'] == 422) {
        echo "      Errores: " . json_encode($result['data']['errors'] ?? [], JSON_PRETTY_PRINT) . "\n";
    } elseif ($result['status'] == 201) {
        echo "      ✅ ¡ÉXITO! Asignación única creada\n";
        echo "      ID: " . ($result['data']['id'] ?? 'N/A') . "\n";
    } elseif ($result['status'] == 500) {
        echo "      ❌ Error 500 del servidor\n";
        echo "      Body: " . substr($result['raw_body'], 0, 500) . "\n";
    }
    
} else {
    echo "   ❌ Login profesor falló\n";
}

echo "\n🎯 ANÁLISIS COMPLETADO - Identificando solución exacta\n";
