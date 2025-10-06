<?php

echo "🔍 === DEBUG LOGIN RESPONSE === 🔍\n\n";

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

echo "Probando login con /auth/login:\n";
$result = makeRequest('POST', '/auth/login', null, ['dni' => '11111111', 'password' => 'admin123']);

echo "Status: {$result['status']}\n";
echo "Raw body: " . $result['raw_body'] . "\n\n";

if ($result['data']) {
    echo "Parsed data:\n";
    print_r($result['data']);
} else {
    echo "No se pudo parsear JSON\n";
}

echo "\n🎯 DEBUG COMPLETADO\n";
