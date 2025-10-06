<?php

echo "🔍 === DEBUG ERROR 422 EN PLANTILLAS === 🔍\n\n";

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

// Get tokens
$professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
$professorToken = $professorLogin['data']['token'] ?? null;

if (!$professorToken) {
    echo "❌ No se pudo obtener token profesor\n";
    exit(1);
}

echo "✅ Token profesor obtenido\n\n";

// Test 1: Crear ejercicio
echo "TEST 1: Crear ejercicio\n";
$uniqueTime = time();
$exerciseData = [
    'name' => 'Ejercicio Debug ' . $uniqueTime,
    'description' => 'Ejercicio para debug',
    'muscle_group' => 'Piernas',
    'equipment' => 'Ninguno',
    'difficulty' => 'Intermedio'
];

echo "Datos del ejercicio:\n";
print_r($exerciseData);

$exerciseResult = makeRequest('POST', '/admin/gym/exercises', $professorToken, $exerciseData);
echo "Status: {$exerciseResult['status']}\n";

if ($exerciseResult['status'] == 422) {
    echo "❌ Error 422 - Validación fallida\n";
    if (isset($exerciseResult['data']['errors'])) {
        echo "Errores de validación:\n";
        print_r($exerciseResult['data']['errors']);
    }
} elseif ($exerciseResult['status'] == 201) {
    echo "✅ Ejercicio creado exitosamente\n";
} else {
    echo "⚠️  Status inesperado: {$exerciseResult['status']}\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test 2: Crear plantilla diaria
echo "TEST 2: Crear plantilla diaria\n";
$templateData = [
    'title' => 'Plantilla Debug ' . $uniqueTime,
    'description' => 'Plantilla para debug',
    'exercises' => [
        [
            'name' => 'Sentadillas Debug',
            'sets' => 3,
            'reps' => '12-15',
            'rest_seconds' => 60,
            'order' => 1
        ]
    ]
];

echo "Datos de la plantilla:\n";
print_r($templateData);

$templateResult = makeRequest('POST', '/admin/gym/daily-templates', $professorToken, $templateData);
echo "Status: {$templateResult['status']}\n";

if ($templateResult['status'] == 422) {
    echo "❌ Error 422 - Validación fallida\n";
    if (isset($templateResult['data']['errors'])) {
        echo "Errores de validación:\n";
        print_r($templateResult['data']['errors']);
    }
} elseif ($templateResult['status'] == 201) {
    echo "✅ Plantilla creada exitosamente\n";
} else {
    echo "⚠️  Status inesperado: {$templateResult['status']}\n";
}

echo "\n🎯 DEBUG COMPLETADO\n";
