<?php

echo "🔍 === ENCONTRAR USUARIO PARA CAMBIO DE TIPO === 🔍\n\n";

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
$adminLogin = makeRequest('POST', '/auth/login', null, ['dni' => '11111111', 'password' => 'admin123']);
$adminToken = $adminLogin['data']['data']['token'] ?? null;

if ($adminToken) {
    echo "✅ Token admin obtenido\n\n";
    
    // Listar usuarios
    $users = makeRequest('GET', '/users', $adminToken);
    
    if ($users['status'] == 200 && isset($users['data']['data'])) {
        echo "Usuarios encontrados:\n";
        
        foreach ($users['data']['data'] as $user) {
            $id = $user['id'] ?? 'N/A';
            $name = $user['name'] ?? 'N/A';
            $dni = $user['dni'] ?? 'N/A';
            $type = $user['user_type'] ?? 'N/A';
            
            echo "- ID: {$id}, Nombre: {$name}, DNI: {$dni}, Tipo: {$type}\n";
            
            // Buscar un usuario que sea 'local' para cambiar a 'api'
            if ($type === 'local' && $id != 1) {
                echo "\n🎯 Usuario candidato encontrado: ID {$id} (tipo: {$type})\n";
                echo "Probando cambio a 'api':\n";
                
                $changeResult = makeRequest('POST', "/users/{$id}/change-type", $adminToken, ['type' => 'api']);
                echo "Status: {$changeResult['status']}\n";
                
                if ($changeResult['status'] == 200) {
                    echo "✅ Cambio exitoso\n";
                    echo "Usando usuario ID {$id} para el test\n";
                    break;
                } else {
                    echo "❌ Error en cambio\n";
                    if (isset($changeResult['data']['message'])) {
                        echo "Mensaje: " . $changeResult['data']['message'] . "\n";
                    }
                }
            }
        }
    } else {
        echo "❌ No se pudieron obtener usuarios\n";
    }
} else {
    echo "❌ No se pudo obtener token admin\n";
}

echo "\n🎯 BÚSQUEDA COMPLETADA\n";
