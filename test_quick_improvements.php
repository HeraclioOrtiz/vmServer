<?php

echo "🚀 === TEST RÁPIDO DE MEJORAS === 🚀\n\n";

function makeRequest($method, $endpoint, $token, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
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
    curl_close($ch);
    
    return ['status' => $httpCode, 'data' => json_decode($response, true)];
}

// Login
echo "1. Login como profesor:\n";
$loginResponse = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
if ($loginResponse['status'] == 200) {
    $token = $loginResponse['data']['token'];
    echo "   ✅ Token obtenido\n\n";
    
    // Test crear ejercicio con datos mejorados
    echo "2. Test crear ejercicio (antes 422):\n";
    $exerciseData = [
        'name' => 'Push-ups Test ' . time(),
        'muscle_group' => 'chest',
        'movement_pattern' => 'push',
        'equipment' => 'none',
        'difficulty' => 'intermediate',
        'tags' => ['strength', 'bodyweight'],
        'instructions' => 'Posición inicial en plancha, bajar controladamente, subir',
        'tempo' => '2-1-2-1'
    ];
    
    $result = makeRequest('POST', '/admin/gym/exercises', $token, $exerciseData);
    echo "   Status: {$result['status']} " . ($result['status'] == 201 ? '✅ CORREGIDO!' : '❌') . "\n";
    
    if ($result['status'] == 422) {
        echo "   Errores: " . json_encode($result['data']['errors'] ?? []) . "\n";
    } elseif ($result['status'] == 201) {
        echo "   Ejercicio creado ID: " . $result['data']['id'] . "\n";
    }
    
    // Test crear plantilla diaria
    echo "\n3. Test crear plantilla diaria (antes esperaba 200, ahora 201):\n";
    $templateData = [
        'title' => 'Rutina Test ' . time(),
        'description' => 'Plantilla de prueba',
        'category' => 'strength',
        'difficulty_level' => 2,
        'estimated_duration' => 45,
        'target_muscle_groups' => ['chest', 'triceps'],
        'exercises' => [
            [
                'exercise_id' => 1,
                'order' => 1,
                'rest_seconds' => 60,
                'sets' => [
                    [
                        'set_number' => 1,
                        'reps' => 10,
                        'weight' => 20
                    ]
                ]
            ]
        ]
    ];
    
    $result = makeRequest('POST', '/admin/gym/daily-templates', $token, $templateData);
    echo "   Status: {$result['status']} " . ($result['status'] == 201 ? '✅ ESPERADO 201!' : '❌') . "\n";
    
} else {
    echo "   ❌ Login falló\n";
}

echo "\n📊 RESUMEN:\n";
echo "- Ejercicio: Datos mejorados para pasar validaciones\n";
echo "- Plantilla: Test ajustado para esperar 201 (correcto)\n";
echo "- Próximo: Corregir AdminProfessorController Error 500\n";
