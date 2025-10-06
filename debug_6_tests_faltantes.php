<?php

echo "🎯 === ANÁLISIS DE LOS 6 TESTS FALTANTES PARA 100% === 🎯\n\n";

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

// Setup tokens
$adminLogin = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
$adminToken = $adminLogin['data']['token'] ?? null;

$studentLogin = makeRequest('POST', '/test/login', null, ['dni' => '55555555', 'password' => 'student123']);
$studentToken = $studentLogin['data']['token'] ?? null;

echo "Tokens: Admin=" . ($adminToken ? "✅" : "❌") . ", Student=" . ($studentToken ? "✅" : "❌") . "\n\n";

// Los 6 tests que fallan
$failing_tests = [
    [
        'name' => '1. Registro nuevo usuario',
        'method' => 'POST',
        'endpoint' => '/auth/register',
        'token' => null,
        'data' => [
            'name' => 'Test User ' . time(),
            'email' => 'test' . time() . '@test.com',
            'dni' => '9999' . substr(time(), -4),
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ],
        'expected' => 201
    ],
    [
        'name' => '2. Cambiar tipo de usuario',
        'method' => 'POST',
        'endpoint' => '/users/1/change-type',
        'token' => $adminToken,
        'data' => ['type' => 'api'],
        'expected' => 200
    ],
    [
        'name' => '3. Verificar DNI en club',
        'method' => 'POST',
        'endpoint' => '/promotion/check-dni',
        'token' => $studentToken,
        'data' => ['dni' => '55555555'],
        'expected' => 200
    ],
    [
        'name' => '4. Solicitar promoción',
        'method' => 'POST',
        'endpoint' => '/promotion/request',
        'token' => $studentToken,
        'data' => [
            'reason' => 'Test promotion request',
            'additional_info' => 'Testing system',
            'club_password' => 'test123'
        ],
        'expected' => 201
    ],
    [
        'name' => '5. Obtener perfil usuario (token fresco)',
        'method' => 'GET',
        'endpoint' => '/auth/me',
        'token' => 'FRESH_TOKEN',
        'data' => null,
        'expected' => 200
    ],
    [
        'name' => '6. Logout usuario (token fresco)',
        'method' => 'POST',
        'endpoint' => '/auth/logout',
        'token' => 'FRESH_TOKEN',
        'data' => null,
        'expected' => 200
    ]
];

foreach ($failing_tests as $test) {
    echo "🔍 {$test['name']}:\n";
    echo "   Endpoint: {$test['method']} {$test['endpoint']}\n";
    
    $token = $test['token'];
    if ($token === 'FRESH_TOKEN') {
        // Crear token fresco
        $freshLogin = makeRequest('POST', '/auth/login', null, ['dni' => '11111111', 'password' => 'admin123']);
        $token = $freshLogin['data']['token'] ?? null;
        echo "   Token fresco: " . ($token ? "✅" : "❌") . "\n";
    }
    
    $result = makeRequest($test['method'], $test['endpoint'], $token, $test['data']);
    echo "   Status: {$result['status']} (esperado: {$test['expected']})\n";
    
    if ($result['status'] == $test['expected']) {
        echo "   ✅ TEST PASARÍA\n";
    } else {
        echo "   ❌ PROBLEMA IDENTIFICADO:\n";
        
        if ($result['status'] == 500) {
            echo "      Error 500 - Problema del servidor\n";
            // Buscar información del error
            if (strpos($result['raw_body'], 'Exception') !== false || strpos($result['raw_body'], 'Error') !== false) {
                $lines = explode("\n", $result['raw_body']);
                foreach ($lines as $line) {
                    if (strpos($line, 'Exception') !== false || strpos($line, 'Error') !== false) {
                        echo "      " . trim(substr($line, 0, 100)) . "...\n";
                        break;
                    }
                }
            }
        } elseif ($result['status'] == 422) {
            echo "      Error 422 - Validación\n";
            if (isset($result['data']['errors'])) {
                foreach ($result['data']['errors'] as $field => $errors) {
                    echo "      - {$field}: " . implode(', ', $errors) . "\n";
                }
            }
            if (isset($result['data']['message'])) {
                echo "      Mensaje: {$result['data']['message']}\n";
            }
        } else {
            echo "      Status inesperado: {$result['status']}\n";
            if (isset($result['data']['message'])) {
                echo "      Mensaje: {$result['data']['message']}\n";
            }
        }
    }
    
    echo "\n";
}

echo "🎯 ANÁLISIS COMPLETADO - Procediendo con correcciones\n";
