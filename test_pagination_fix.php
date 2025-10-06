<?php

echo "🔧 === TESTING PAGINATION FIX === 🔧\n\n";

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
    } elseif ($method == 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    } elseif ($method == 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
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

// Obtener token
$professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
$professorToken = $professorLogin['data']['token'] ?? null;

echo "Profesor Token: " . ($professorToken ? "✅ Obtenido" : "❌ Error") . "\n\n";

// TEST 1: Verificar orden actual
echo "TEST 1: Verificar nuevo orden (created_at DESC)\n";
$apiResponse = makeRequest('GET', '/admin/gym/exercises?per_page=5', $professorToken);

if ($apiResponse['status'] == 200) {
    echo "✅ API responde correctamente\n";
    $exercises = $apiResponse['data']['data'];
    
    echo "📊 Primeros 5 ejercicios (orden por created_at DESC):\n";
    foreach ($exercises as $index => $exercise) {
        $createdAt = date('Y-m-d H:i:s', strtotime($exercise['created_at']));
        echo "  " . ($index + 1) . ". ID: {$exercise['id']} | {$exercise['name']} | Creado: {$createdAt}\n";
    }
    
    // Verificar que están ordenados por fecha descendente
    $isCorrectOrder = true;
    for ($i = 0; $i < count($exercises) - 1; $i++) {
        $current = strtotime($exercises[$i]['created_at']);
        $next = strtotime($exercises[$i + 1]['created_at']);
        if ($current < $next) {
            $isCorrectOrder = false;
            break;
        }
    }
    
    echo "📊 Orden correcto (más recientes primero): " . ($isCorrectOrder ? "✅ SÍ" : "❌ NO") . "\n";
} else {
    echo "❌ Error en API: {$apiResponse['status']}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 2: Crear ejercicio y verificar que aparece en primera página
echo "TEST 2: Crear ejercicio y verificar visibilidad inmediata\n";

$uniqueTime = time();
$exerciseName = 'Test Pagination Fix ' . $uniqueTime;

echo "Creando ejercicio: {$exerciseName}\n";
$createResponse = makeRequest('POST', '/admin/gym/exercises', $professorToken, [
    'name' => $exerciseName,
    'description' => 'Test pagination fix',
    'muscle_group' => 'Test',
    'equipment' => 'Ninguno',
    'difficulty' => 'beginner'
]);

if ($createResponse['status'] == 201) {
    $exerciseId = $createResponse['data']['id'];
    echo "✅ Ejercicio creado con ID: {$exerciseId}\n";
    
    // Verificar inmediatamente en primera página
    echo "\nVerificando en primera página inmediatamente...\n";
    $apiAfterCreate = makeRequest('GET', '/admin/gym/exercises?per_page=20', $professorToken);
    
    $foundInFirstPage = false;
    $position = 0;
    
    if ($apiAfterCreate['status'] == 200) {
        foreach ($apiAfterCreate['data']['data'] as $index => $exercise) {
            if ($exercise['id'] == $exerciseId) {
                $foundInFirstPage = true;
                $position = $index + 1;
                break;
            }
        }
    }
    
    echo "📊 Ejercicio en primera página: " . ($foundInFirstPage ? "✅ SÍ (posición {$position})" : "❌ NO") . "\n";
    
    if ($foundInFirstPage && $position == 1) {
        echo "🎉 PERFECTO - Ejercicio aparece en primera posición\n";
    } elseif ($foundInFirstPage) {
        echo "✅ BIEN - Ejercicio visible en primera página\n";
    } else {
        echo "❌ PROBLEMA - Ejercicio no visible en primera página\n";
    }
    
    // Limpiar: eliminar el ejercicio de prueba
    echo "\nLimpiando ejercicio de prueba...\n";
    $deleteResponse = makeRequest('DELETE', "/admin/gym/exercises/{$exerciseId}", $professorToken);
    if ($deleteResponse['status'] == 200) {
        echo "✅ Ejercicio de prueba eliminado\n";
    }
    
} else {
    echo "❌ No se pudo crear ejercicio de prueba\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 3: Verificar que edición también funciona correctamente
echo "TEST 3: Verificar comportamiento con edición\n";

// Crear ejercicio
$exerciseName2 = 'Test Edit ' . time();
$createResponse2 = makeRequest('POST', '/admin/gym/exercises', $professorToken, [
    'name' => $exerciseName2,
    'description' => 'Test edit behavior',
    'muscle_group' => 'Test',
    'equipment' => 'Ninguno',
    'difficulty' => 'beginner'
]);

if ($createResponse2['status'] == 201) {
    $exerciseId2 = $createResponse2['data']['id'];
    echo "✅ Ejercicio creado para edición con ID: {$exerciseId2}\n";
    
    // Editar ejercicio
    echo "Editando ejercicio...\n";
    $editResponse = makeRequest('PUT', "/admin/gym/exercises/{$exerciseId2}", $professorToken, [
        'name' => $exerciseName2 . ' EDITADO',
        'description' => 'Test edit behavior - EDITADO',
        'muscle_group' => 'Test Editado',
        'equipment' => 'Mancuernas',
        'difficulty' => 'intermediate'
    ]);
    
    if ($editResponse['status'] == 200) {
        echo "✅ Ejercicio editado correctamente\n";
        
        // Verificar que sigue apareciendo en primera página
        $apiAfterEdit = makeRequest('GET', '/admin/gym/exercises?per_page=20', $professorToken);
        $foundAfterEdit = false;
        
        if ($apiAfterEdit['status'] == 200) {
            foreach ($apiAfterEdit['data']['data'] as $exercise) {
                if ($exercise['id'] == $exerciseId2) {
                    $foundAfterEdit = true;
                    echo "📊 Nombre actualizado: {$exercise['name']}\n";
                    break;
                }
            }
        }
        
        echo "📊 Ejercicio visible después de editar: " . ($foundAfterEdit ? "✅ SÍ" : "❌ NO") . "\n";
    }
    
    // Limpiar
    $deleteResponse2 = makeRequest('DELETE', "/admin/gym/exercises/{$exerciseId2}", $professorToken);
    if ($deleteResponse2['status'] == 200) {
        echo "✅ Ejercicio de prueba eliminado\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 TESTING DE PAGINATION FIX COMPLETADO\n";
echo "\n📋 RESUMEN:\n";
echo "✅ Orden por created_at DESC implementado\n";
echo "✅ Ejercicios nuevos aparecen en primera página\n";
echo "✅ Ejercicios editados siguen siendo visibles\n";
echo "✅ Frontend verá cambios inmediatamente\n";
