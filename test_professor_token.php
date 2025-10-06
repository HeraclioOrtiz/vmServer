<?php

echo "🔍 === TESTING PROFESSOR TOKEN === 🔍\n\n";

function makeRequest($method, $endpoint, $token = null, $body = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $headers = ['Accept: application/json'];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    if ($body) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Login como profesor
echo "1. Login como profesor:\n";
$response = makeRequest('POST', '/test/login', null, [
    'dni' => '22222222',
    'password' => 'profesor123'
]);

if ($response['status'] == 200) {
    $professorToken = $response['data']['token'];
    echo "   ✅ Token profesor obtenido\n";
    echo "   Usuario: " . $response['data']['user']['name'] . "\n";
    echo "   Is Professor: " . ($response['data']['user']['is_professor'] ? 'Sí' : 'No') . "\n";
    echo "   Is Admin: " . ($response['data']['user']['is_admin'] ? 'Sí' : 'No') . "\n\n";
    
    // Test endpoints gym con token profesor
    echo "2. Testing endpoints gym con token profesor:\n";
    
    $gymEndpoints = [
        '/admin/gym/exercises',
        '/admin/gym/weekly-assignments',
        '/admin/gym/weekly-assignments/stats'
    ];
    
    foreach ($gymEndpoints as $endpoint) {
        $response = makeRequest('GET', $endpoint, $professorToken);
        $status = $response['status'];
        $icon = $status == 200 ? '✅' : ($status == 404 ? '🔍' : '❌');
        echo "   $icon [$status] $endpoint\n";
        
        if ($status == 404) {
            echo "      → Endpoint no encontrado\n";
        } elseif ($status == 403) {
            echo "      → Sin permisos\n";
        } elseif ($status == 500) {
            echo "      → Error del servidor\n";
        }
    }
    
} else {
    echo "   ❌ No se pudo obtener token profesor\n";
}

echo "\n3. Verificando rutas registradas:\n";
echo "   Ejecutar: php artisan route:list --path=admin/gym\n";
