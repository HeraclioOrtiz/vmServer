<?php

echo "🔍 === VERIFICANDO TIPO DE USUARIO 1 === 🔍\n\n";

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
    
    if ($method == 'GET') {
        // No additional setup needed
    } elseif ($method == 'POST') {
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
$adminLogin = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
$adminToken = $adminLogin['data']['token'] ?? null;

if ($adminToken) {
    echo "✅ Token admin obtenido\n\n";
    
    // Ver usuario 1
    $user1 = makeRequest('GET', '/users/1', $adminToken);
    echo "Usuario 1:\n";
    echo "Status: {$user1['status']}\n";
    
    if ($user1['status'] == 200 && isset($user1['data'])) {
        $userData = $user1['data'];
        echo "Nombre: " . ($userData['name'] ?? 'N/A') . "\n";
        echo "DNI: " . ($userData['dni'] ?? 'N/A') . "\n";
        echo "Tipo actual: " . ($userData['user_type'] ?? 'N/A') . "\n";
        echo "Es admin: " . (($userData['is_admin'] ?? false) ? 'Sí' : 'No') . "\n";
        echo "Es profesor: " . (($userData['is_professor'] ?? false) ? 'Sí' : 'No') . "\n";
        
        // Determinar qué tipo cambiar
        $currentType = $userData['user_type'] ?? 'local';
        $newType = ($currentType === 'local') ? 'api' : 'local';
        
        echo "\nCambio sugerido: {$currentType} → {$newType}\n";
        
        // Probar el cambio
        echo "\nProbando cambio de tipo:\n";
        $changeResult = makeRequest('POST', '/users/1/change-type', $adminToken, ['type' => $newType]);
        echo "Status: {$changeResult['status']}\n";
        
        if ($changeResult['status'] == 200) {
            echo "✅ Cambio exitoso\n";
        } else {
            echo "❌ Error en cambio\n";
            if (isset($changeResult['data']['message'])) {
                echo "Mensaje: " . $changeResult['data']['message'] . "\n";
            }
            if (isset($changeResult['data']['errors'])) {
                echo "Errores: " . json_encode($changeResult['data']['errors']) . "\n";
            }
        }
    } else {
        echo "❌ No se pudo obtener datos del usuario 1\n";
    }
} else {
    echo "❌ No se pudo obtener token admin\n";
}

echo "\n🎯 VERIFICACIÓN COMPLETADA\n";
