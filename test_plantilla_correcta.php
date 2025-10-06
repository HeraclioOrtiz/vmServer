<?php

echo "🔍 === TEST PLANTILLA CON ESTRUCTURA CORRECTA === 🔍\n\n";

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

// Get tokens
$professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
$professorToken = $professorLogin['data']['token'] ?? null;

if (!$professorToken) {
    echo "❌ No se pudo obtener token profesor\n";
    exit(1);
}

echo "✅ Token profesor obtenido\n\n";

// PASO 1: Obtener un ejercicio existente
echo "PASO 1: Obtener ejercicio existente\n";
$exercisesResult = makeRequest('GET', '/admin/gym/exercises', $professorToken);

if ($exercisesResult['status'] == 200 && isset($exercisesResult['data']['data']) && count($exercisesResult['data']['data']) > 0) {
    $exerciseId = $exercisesResult['data']['data'][0]['id'];
    echo "✅ Ejercicio encontrado con ID: {$exerciseId}\n\n";
} else {
    echo "❌ No se encontraron ejercicios existentes\n";
    exit(1);
}

// PASO 2: Crear plantilla con estructura correcta
echo "PASO 2: Crear plantilla con estructura correcta\n";
$uniqueTime = time();
$correctTemplateData = [
    'title' => 'Plantilla Correcta ' . $uniqueTime,
    'description' => 'Plantilla con estructura correcta para E2E',
    'category' => 'strength',
    'difficulty_level' => 3,
    'estimated_duration' => 45,
    'target_muscle_groups' => ['legs', 'core'],
    'equipment_needed' => ['dumbbells'],
    'is_preset' => false,
    'is_public' => true,
    'tags' => ['e2e', 'test'],
    'notes' => 'Plantilla para testing end-to-end',
    'exercises' => [
        [
            'exercise_id' => $exerciseId,
            'order' => 1,
            'rest_seconds' => 60,
            'notes' => 'Primer ejercicio',
            'sets' => [
                [
                    'set_number' => 1,
                    'reps' => 12,
                    'weight' => 20.5,
                    'rest_seconds' => 60
                ],
                [
                    'set_number' => 2,
                    'reps' => 10,
                    'weight' => 22.5,
                    'rest_seconds' => 60
                ],
                [
                    'set_number' => 3,
                    'reps' => 8,
                    'weight' => 25.0,
                    'rest_seconds' => 90
                ]
            ]
        ]
    ]
];

echo "Datos de la plantilla correcta:\n";
print_r($correctTemplateData);

$templateResult = makeRequest('POST', '/admin/gym/daily-templates', $professorToken, $correctTemplateData);
echo "\nStatus: {$templateResult['status']}\n";

if ($templateResult['status'] == 201) {
    echo "✅ Plantilla creada exitosamente\n";
    if (isset($templateResult['data']['data']['id'])) {
        echo "ID de plantilla creada: " . $templateResult['data']['data']['id'] . "\n";
    }
} elseif ($templateResult['status'] == 422) {
    echo "❌ Error 422 - Validación fallida\n";
    if (isset($templateResult['data']['errors'])) {
        echo "Errores de validación:\n";
        print_r($templateResult['data']['errors']);
    }
} else {
    echo "⚠️  Status inesperado: {$templateResult['status']}\n";
    echo "Response: " . $templateResult['raw_body'] . "\n";
}

echo "\n🎯 TEST COMPLETADO\n";
