<?php

echo "🔍 === DEBUG /auth/me ERROR === 🔍\n\n";

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
    } elseif ($method == 'GET') {
        // No additional setup needed
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

echo "1. Creando token fresco:\n";
$freshLogin = makeRequest('POST', '/auth/login', null, ['dni' => '11111111', 'password' => 'admin123']);
echo "Status login: {$freshLogin['status']}\n";

if ($freshLogin['status'] == 200) {
    $freshToken = $freshLogin['data']['data']['token'] ?? null;
    echo "Token obtenido: " . ($freshToken ? "✅" : "❌") . "\n";
    
    if ($freshToken) {
        echo "Token preview: " . substr($freshToken, 0, 30) . "...\n\n";
        
        echo "2. Probando /auth/me:\n";
        $meResult = makeRequest('GET', '/auth/me', $freshToken);
        echo "Status: {$meResult['status']}\n";
        
        if ($meResult['status'] == 200) {
            echo "✅ /auth/me funciona\n";
        } elseif ($meResult['status'] == 500) {
            echo "❌ Error 500 en /auth/me\n";
            echo "Raw body: " . substr($meResult['raw_body'], 0, 300) . "...\n";
            
            // Buscar información del error
            if (strpos($meResult['raw_body'], 'Exception') !== false || strpos($meResult['raw_body'], 'Error') !== false) {
                $lines = explode("\n", $meResult['raw_body']);
                foreach ($lines as $line) {
                    if (strpos($line, 'Exception') !== false || 
                        strpos($line, 'Error') !== false ||
                        strpos($line, 'Call to') !== false) {
                        echo "Error: " . trim($line) . "\n";
                        break;
                    }
                }
            }
        } else {
            echo "❌ Status inesperado: {$meResult['status']}\n";
            if (isset($meResult['data']['message'])) {
                echo "Mensaje: " . $meResult['data']['message'] . "\n";
            }
        }
    }
} else {
    echo "❌ No se pudo crear token fresco\n";
}

echo "\n🎯 DEBUG COMPLETADO\n";
