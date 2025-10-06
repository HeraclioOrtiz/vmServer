<?php

echo "🔍 === TEST CORRECCIÓN PROFESSOR CONTROLLER === 🔍\n\n";

function makeRequest($endpoint, $token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['status' => $httpCode, 'data' => json_decode($response, true)];
}

// Login como admin
echo "1. Login como admin:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/test/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['dni' => '11111111', 'password' => 'admin123']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $data = json_decode($response, true);
    $token = $data['token'];
    echo "   ✅ Token admin obtenido\n\n";
    
    // Test professor endpoint
    echo "2. Testing professor endpoint (antes Error 500):\n";
    $result = makeRequest('/admin/professors', $token);
    echo "   Status: {$result['status']}\n";
    
    if ($result['status'] == 200) {
        echo "   ✅ Professor endpoint CORREGIDO!\n";
        echo "   Profesores encontrados: " . count($result['data']['professors'] ?? []) . "\n";
    } elseif ($result['status'] == 500) {
        echo "   ❌ Aún da Error 500\n";
        if (isset($result['data']['error'])) {
            echo "   Error: " . $result['data']['error'] . "\n";
        }
    } else {
        echo "   ⚠️ Status inesperado: {$result['status']}\n";
        echo "   Respuesta: " . json_encode($result['data']) . "\n";
    }
    
} else {
    echo "   ❌ Login falló - Status: $httpCode\n";
}

echo "\n📊 RESULTADO: ";
echo ($result['status'] ?? 0) == 200 ? "CORRECCIÓN EXITOSA ✅" : "AÚN REQUIERE TRABAJO ❌";
echo "\n";
