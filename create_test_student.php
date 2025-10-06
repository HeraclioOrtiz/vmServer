<?php

echo "👤 === CREANDO USUARIO ESTUDIANTE PARA TESTS ===\n\n";

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

// 1. Login como admin
echo "1. Login como admin:\n";
$adminLogin = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
if ($adminLogin['status'] == 200) {
    $adminToken = $adminLogin['data']['token'];
    echo "   ✅ Token admin obtenido\n";
    
    // 2. Crear usuario estudiante
    echo "\n2. Creando usuario estudiante:\n";
    $studentData = [
        'name' => 'Test Student',
        'email' => 'student@test.com',
        'dni' => '55555555',
        'password' => 'student123',
        'password_confirmation' => 'student123',
        'user_type' => 'local',
        'is_professor' => false,
        'is_admin' => false
    ];
    
    $createStudent = makeRequest('POST', '/admin/users', $adminToken, $studentData);
    echo "   Status: {$createStudent['status']}\n";
    
    if ($createStudent['status'] == 201) {
        echo "   ✅ Usuario estudiante creado exitosamente\n";
        echo "   ID: " . ($createStudent['data']['id'] ?? 'N/A') . "\n";
        
        // 3. Test login del estudiante
        echo "\n3. Test login del estudiante:\n";
        $studentLogin = makeRequest('POST', '/test/login', null, ['dni' => '55555555', 'password' => 'student123']);
        echo "   Status: {$studentLogin['status']}\n";
        
        if ($studentLogin['status'] == 200) {
            echo "   ✅ Login estudiante exitoso\n";
            echo "   Token: " . substr($studentLogin['data']['token'], 0, 20) . "...\n";
        } else {
            echo "   ❌ Login estudiante falló\n";
            if (isset($studentLogin['data']['message'])) {
                echo "   Error: " . $studentLogin['data']['message'] . "\n";
            }
        }
    } else {
        echo "   ❌ Error creando estudiante\n";
        if (isset($createStudent['data']['errors'])) {
            echo "   Errores: " . json_encode($createStudent['data']['errors'], JSON_PRETTY_PRINT) . "\n";
        }
        if (isset($createStudent['data']['message'])) {
            echo "   Mensaje: " . $createStudent['data']['message'] . "\n";
        }
        echo "   Body: " . substr($createStudent['raw_body'], 0, 500) . "\n";
    }
    
} else {
    echo "   ❌ Login admin falló\n";
}

echo "\n✅ Proceso completado\n";
