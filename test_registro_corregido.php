<?php

echo "🔍 === TEST REGISTRO CORREGIDO === 🔍\n\n";

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

echo "Probando registro con contraseña fuerte:\n\n";

$uniqueTime = time();
$registerData = [
    'name' => 'Test User ' . $uniqueTime,
    'email' => 'test' . $uniqueTime . '@test.com',
    'dni' => '9999' . substr($uniqueTime, -4),
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!'
];

echo "Datos de registro:\n";
echo "- Nombre: {$registerData['name']}\n";
echo "- Email: {$registerData['email']}\n";
echo "- DNI: {$registerData['dni']}\n";
echo "- Password: {$registerData['password']}\n\n";

$result = makeRequest('POST', '/auth/register', null, $registerData);
echo "Status: {$result['status']}\n";

if ($result['status'] == 201) {
    echo "✅ Registro exitoso\n";
    if (isset($result['data']['user'])) {
        echo "Usuario creado: ID " . ($result['data']['user']['id'] ?? 'N/A') . "\n";
    }
    if (isset($result['data']['token'])) {
        echo "Token generado: " . substr($result['data']['token'], 0, 20) . "...\n";
    }
} elseif ($result['status'] == 422) {
    echo "❌ Error de validación\n";
    if (isset($result['data']['errors'])) {
        foreach ($result['data']['errors'] as $field => $errors) {
            echo "- {$field}: " . implode(', ', $errors) . "\n";
        }
    }
} elseif ($result['status'] == 500) {
    echo "❌ Error 500 del servidor\n";
    echo "Body preview: " . substr($result['raw_body'], 0, 200) . "...\n";
} else {
    echo "⚠️  Status inesperado: {$result['status']}\n";
}

echo "\n🎯 TEST COMPLETADO\n";
