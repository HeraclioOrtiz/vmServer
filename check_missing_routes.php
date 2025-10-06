<?php

echo "🔍 === VERIFICANDO RUTAS FALTANTES === 🔍\n\n";

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

// Endpoints que fallan con 404
$missing_endpoints = [
    ['GET', '/users/search', $adminToken, 'Búsqueda de usuarios'],
    ['GET', '/users/stats', $adminToken, 'Estadísticas de usuarios'],
    ['GET', '/users/needing-refresh', $adminToken, 'Usuarios que necesitan refresh'],
    ['GET', '/gym/my-day', $studentToken, 'Ver mi día de entrenamiento'],
    ['GET', '/gym/my-day?date=2024-01-15', $studentToken, 'Ver día específico'],
];

echo "Verificando endpoints que devuelven 404:\n\n";

foreach ($missing_endpoints as $test) {
    [$method, $endpoint, $token, $description] = $test;
    
    echo "🔍 {$description}:\n";
    echo "   Endpoint: {$method} {$endpoint}\n";
    
    $result = makeRequest($method, $endpoint, $token);
    echo "   Status: {$result['status']}\n";
    
    if ($result['status'] == 404) {
        echo "   ❌ RUTA NO ENCONTRADA\n";
    } elseif ($result['status'] == 200) {
        echo "   ✅ RUTA FUNCIONA\n";
    } else {
        echo "   ⚠️  Status: {$result['status']}\n";
    }
    
    echo "\n";
}

// Verificar rutas alternativas
echo "Verificando rutas alternativas:\n\n";

$alternatives = [
    ['GET', '/users?search=admin', $adminToken, 'Búsqueda por parámetro'],
    ['GET', '/admin/users/stats', $adminToken, 'Stats en admin'],
    ['GET', '/admin/gym/my-day', $studentToken, 'My-day en admin/gym'],
];

foreach ($alternatives as $test) {
    [$method, $endpoint, $token, $description] = $test;
    
    echo "🔍 {$description}:\n";
    echo "   Endpoint: {$method} {$endpoint}\n";
    
    $result = makeRequest($method, $endpoint, $token);
    echo "   Status: {$result['status']}\n";
    
    echo "\n";
}

echo "🎯 VERIFICACIÓN COMPLETADA\n";
