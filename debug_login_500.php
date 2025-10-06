<?php

echo "🔍 === DEBUGGING LOGIN ERROR 500 === 🔍\n\n";

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

echo "Probando diferentes endpoints de login:\n\n";

// 1. Login con /test/login (que funciona en setup)
echo "1. Login con /test/login:\n";
$testLogin = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
echo "   Status: {$testLogin['status']}\n";
if ($testLogin['status'] == 200) {
    echo "   ✅ /test/login funciona correctamente\n";
    echo "   Token: " . substr($testLogin['data']['token'] ?? 'N/A', 0, 20) . "...\n";
} else {
    echo "   ❌ /test/login también falla\n";
}

echo "\n";

// 2. Login con /auth/login (que falla en el test)
echo "2. Login con /auth/login:\n";
$authLogin = makeRequest('POST', '/auth/login', null, ['dni' => '11111111', 'password' => 'admin123']);
echo "   Status: {$authLogin['status']}\n";

if ($authLogin['status'] == 500) {
    echo "   ❌ Error 500 confirmado\n";
    echo "   Buscando detalles del error...\n";
    
    // Buscar información útil en el body
    if (strpos($authLogin['raw_body'], 'Exception') !== false || strpos($authLogin['raw_body'], 'Error') !== false) {
        $lines = explode("\n", $authLogin['raw_body']);
        foreach ($lines as $line) {
            if (strpos($line, 'Exception') !== false || 
                strpos($line, 'Error') !== false ||
                strpos($line, 'Call to') !== false ||
                strpos($line, 'Class') !== false) {
                echo "   Error: " . trim($line) . "\n";
                break;
            }
        }
    }
    
    // Mostrar primeros 300 caracteres para más contexto
    echo "   Body preview: " . substr($authLogin['raw_body'], 0, 300) . "...\n";
    
} elseif ($authLogin['status'] == 200) {
    echo "   ✅ /auth/login funciona correctamente\n";
    echo "   Token: " . substr($authLogin['data']['token'] ?? 'N/A', 0, 20) . "...\n";
} else {
    echo "   ⚠️  Status inesperado: {$authLogin['status']}\n";
    if (isset($authLogin['data']['message'])) {
        echo "   Mensaje: " . $authLogin['data']['message'] . "\n";
    }
}

echo "\n";

// 3. Verificar si el problema es con credenciales específicas
echo "3. Probando con diferentes credenciales:\n";

$credentials = [
    ['dni' => '22222222', 'password' => 'profesor123', 'desc' => 'Profesor'],
    ['dni' => '55555555', 'password' => 'student123', 'desc' => 'Estudiante'],
    ['dni' => '11111111', 'password' => 'wrong', 'desc' => 'Admin password incorrecta']
];

foreach ($credentials as $cred) {
    echo "   {$cred['desc']} (DNI: {$cred['dni']}):\n";
    $result = makeRequest('POST', '/auth/login', null, ['dni' => $cred['dni'], 'password' => $cred['password']]);
    echo "      Status: {$result['status']}\n";
    
    if ($result['status'] == 500) {
        echo "      ❌ También da error 500\n";
    } elseif ($result['status'] == 200) {
        echo "      ✅ Funciona correctamente\n";
    } else {
        echo "      ⚠️  Status: {$result['status']}\n";
    }
}

echo "\n🎯 DEBUGGING COMPLETADO\n";
