<?php

echo "🔍 === ANÁLISIS DETALLADO DE ERRORES 500 === 🔍\n\n";

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

echo "Tokens obtenidos: Admin=" . ($adminToken ? "✅" : "❌") . ", Student=" . ($studentToken ? "✅" : "❌") . "\n\n";

// Test endpoints que fallan con 500
$endpoints = [
    ['GET', '/users', $adminToken, 'Lista de usuarios'],
    ['GET', '/users/1', $adminToken, 'Ver usuario específico'],
    ['POST', '/users/1/change-type', $adminToken, 'Cambiar tipo usuario', ['type' => 'student']],
    ['GET', '/promotion/eligibility', $studentToken, 'Verificar elegibilidad'],
    ['POST', '/promotion/check-dni', $studentToken, 'Verificar DNI', ['dni' => '55555555']],
    ['POST', '/promotion/request', $studentToken, 'Solicitar promoción', ['reason' => 'test']],
    ['GET', '/promotion/stats', $adminToken, 'Stats promociones'],
];

foreach ($endpoints as $test) {
    [$method, $endpoint, $token, $description, $data] = array_pad($test, 5, null);
    
    echo "🔍 Testing: {$description}\n";
    echo "   Endpoint: {$method} {$endpoint}\n";
    
    $result = makeRequest($method, $endpoint, $token, $data);
    echo "   Status: {$result['status']}\n";
    
    if ($result['status'] == 500) {
        echo "   ❌ ERROR 500 - Detalles:\n";
        
        // Buscar información útil en el body
        if (strpos($result['raw_body'], 'Exception') !== false) {
            // Extraer líneas que contengan información de error
            $lines = explode("\n", $result['raw_body']);
            foreach ($lines as $line) {
                if (strpos($line, 'Exception') !== false || 
                    strpos($line, 'Error') !== false ||
                    strpos($line, 'Fatal') !== false ||
                    strpos($line, 'Call to') !== false) {
                    echo "      " . trim($line) . "\n";
                    break;
                }
            }
        }
        
        if (isset($result['data']['message'])) {
            echo "      Message: " . $result['data']['message'] . "\n";
        }
        
        // Mostrar primeros 200 caracteres del body para diagnóstico
        echo "      Body preview: " . substr($result['raw_body'], 0, 200) . "...\n";
    } elseif ($result['status'] == 404) {
        echo "   ⚠️  ERROR 404 - Endpoint no encontrado\n";
    } elseif ($result['status'] >= 400) {
        echo "   ⚠️  ERROR {$result['status']}\n";
        if (isset($result['data']['message'])) {
            echo "      Message: " . $result['data']['message'] . "\n";
        }
        if (isset($result['data']['errors'])) {
            echo "      Errors: " . json_encode($result['data']['errors']) . "\n";
        }
    } else {
        echo "   ✅ OK - Status {$result['status']}\n";
    }
    
    echo "\n";
}

echo "🎯 ANÁLISIS COMPLETADO\n";
