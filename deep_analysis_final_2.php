<?php

echo "🔬 === ANÁLISIS PROFUNDO ÚLTIMOS 2 PROBLEMAS === 🔬\n\n";

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

// Login como admin
echo "1. Setup - Login como admin:\n";
$adminLogin = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
if ($adminLogin['status'] == 200) {
    $adminToken = $adminLogin['data']['token'];
    echo "   ✅ Token admin obtenido\n\n";
    
    // PROBLEMA 1: Configuración específica - Análisis detallado
    echo "2. ANÁLISIS DETALLADO - Configuración específica:\n";
    
    // Paso 1: Verificar que existe la configuración
    echo "   a) Listar todas las configuraciones:\n";
    $listResult = makeRequest('GET', '/admin/settings', $adminToken);
    echo "      Status: {$listResult['status']}\n";
    if ($listResult['status'] == 200 && isset($listResult['data']['data'])) {
        $configs = $listResult['data']['data'];
        echo "      Configuraciones encontradas: " . count($configs) . "\n";
        foreach ($configs as $category => $items) {
            if (is_array($items)) {
                foreach ($items as $item) {
                    echo "        - {$item['key']} (categoria: {$item['category']})\n";
                }
            }
        }
    }
    
    // Paso 2: Intentar leer configuración específica que sabemos que existe
    echo "\n   b) Leer configuración específica 'test_setting':\n";
    $readResult = makeRequest('GET', '/admin/settings/test_setting', $adminToken);
    echo "      Status: {$readResult['status']}\n";
    echo "      Headers: " . substr($readResult['headers'], 0, 200) . "...\n";
    if ($readResult['status'] != 200) {
        echo "      Error body: " . $readResult['raw_body'] . "\n";
    } else {
        echo "      ✅ Configuración leída correctamente\n";
    }
    
    // Paso 3: Crear nueva configuración y leerla inmediatamente
    echo "\n   c) Crear nueva configuración y leerla:\n";
    $newKey = 'test_deep_analysis_' . time();
    $createResult = makeRequest('POST', '/admin/settings', $adminToken, [
        'key' => $newKey,
        'value' => 'deep_analysis_value',
        'category' => 'testing',
        'description' => 'Configuración para análisis profundo'
    ]);
    echo "      Crear: Status {$createResult['status']}\n";
    
    if ($createResult['status'] == 201) {
        // Leer inmediatamente
        $readNewResult = makeRequest('GET', "/admin/settings/{$newKey}", $adminToken);
        echo "      Leer nueva: Status {$readNewResult['status']}\n";
        if ($readNewResult['status'] == 200) {
            echo "      ✅ Configuración nueva leída correctamente\n";
        } else {
            echo "      ❌ Error leyendo configuración recién creada\n";
            echo "      Error: " . $readNewResult['raw_body'] . "\n";
        }
    }
    
} else {
    echo "   ❌ Login admin falló\n";
}

// Login como profesor para análisis de asignaciones
echo "\n3. Setup - Login como profesor:\n";
$professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
if ($professorLogin['status'] == 200) {
    $professorToken = $professorLogin['data']['token'];
    echo "   ✅ Token profesor obtenido\n\n";
    
    // PROBLEMA 2: Crear asignación - Análisis detallado
    echo "4. ANÁLISIS DETALLADO - Crear asignación:\n";
    
    // Paso 1: Verificar que podemos listar asignaciones
    echo "   a) Listar asignaciones existentes:\n";
    $listAssignments = makeRequest('GET', '/admin/gym/weekly-assignments', $professorToken);
    echo "      Status: {$listAssignments['status']}\n";
    if ($listAssignments['status'] == 200) {
        echo "      ✅ Lista de asignaciones funciona\n";
    }
    
    // Paso 2: Verificar que el usuario 3 existe
    echo "\n   b) Verificar usuario 3 existe:\n";
    $checkUser = makeRequest('GET', '/admin/users/3', $adminToken);
    echo "      Status: {$checkUser['status']}\n";
    if ($checkUser['status'] == 200) {
        echo "      ✅ Usuario 3 existe: " . $checkUser['data']['name'] . "\n";
    }
    
    // Paso 3: Crear asignación con datos mínimos
    echo "\n   c) Crear asignación con datos mínimos:\n";
    $minimalData = [
        'user_id' => 3,
        'week_start' => '2024-01-01',
        'week_end' => '2024-01-07',
        'source_type' => 'manual',
        'notes' => 'Asignación mínima para análisis'
    ];
    
    $createAssignment = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $minimalData);
    echo "      Status: {$createAssignment['status']}\n";
    echo "      Headers: " . substr($createAssignment['headers'], 0, 200) . "...\n";
    
    if ($createAssignment['status'] == 201) {
        echo "      ✅ Asignación creada correctamente\n";
    } elseif ($createAssignment['status'] == 500) {
        echo "      ❌ Error 500 del servidor\n";
        echo "      Error body: " . $createAssignment['raw_body'] . "\n";
    } elseif ($createAssignment['status'] == 422) {
        echo "      ❌ Error de validación\n";
        echo "      Errores: " . json_encode($createAssignment['data']['errors'] ?? []) . "\n";
    }
    
    // Paso 4: Intentar con datos más completos
    echo "\n   d) Crear asignación con datos completos:\n";
    $completeData = [
        'user_id' => 3,
        'week_start' => '2024-01-08',
        'week_end' => '2024-01-14',
        'source_type' => 'manual',
        'notes' => 'Asignación completa para análisis',
        'days' => [
            [
                'weekday' => 1,
                'date' => '2024-01-08',
                'title' => 'Día 1 - Análisis',
                'notes' => 'Primer día de entrenamiento',
                'exercises' => []
            ]
        ]
    ];
    
    $createComplete = makeRequest('POST', '/admin/gym/weekly-assignments', $professorToken, $completeData);
    echo "      Status: {$createComplete['status']}\n";
    
    if ($createComplete['status'] == 201) {
        echo "      ✅ Asignación completa creada correctamente\n";
    } else {
        echo "      ❌ Error en asignación completa\n";
        echo "      Error: " . $createComplete['raw_body'] . "\n";
    }
    
} else {
    echo "   ❌ Login profesor falló\n";
}

echo "\n🎯 ANÁLISIS COMPLETADO - Implementando soluciones robustas\n";
