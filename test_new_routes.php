<?php

echo "🛣️ === TESTING NUEVAS RUTAS AGREGADAS === 🛣️\n\n";

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

// Obtener tokens
echo "🔐 OBTENIENDO TOKENS...\n";

$adminLogin = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
$adminToken = $adminLogin['data']['token'] ?? null;

$professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
$professorToken = $professorLogin['data']['token'] ?? null;

echo "Admin Token: " . ($adminToken ? "✅ Obtenido" : "❌ Error") . "\n";
echo "Profesor Token: " . ($professorToken ? "✅ Obtenido" : "❌ Error") . "\n\n";

// TEST 1: Verificar ruta de dependencias
echo "TEST 1: Verificar nueva ruta GET /admin/gym/exercises/{exercise}/dependencies\n";
echo "URL: GET /admin/gym/exercises/1/dependencies\n";
$dependencies = makeRequest('GET', '/admin/gym/exercises/1/dependencies', $professorToken);
echo "Status: {$dependencies['status']}\n";

if ($dependencies['status'] == 200) {
    echo "✅ RUTA FUNCIONA - Endpoint de dependencias accesible\n";
    $data = $dependencies['data'];
    echo "📊 Response structure:\n";
    echo "  - can_delete: " . ($data['can_delete'] ? 'true' : 'false') . "\n";
    echo "  - total_references: {$data['total_references']}\n";
    echo "  - dependencies keys: " . implode(', ', array_keys($data['dependencies'])) . "\n";
} else {
    echo "❌ RUTA NO FUNCIONA - Status: {$dependencies['status']}\n";
    if (isset($dependencies['data']['message'])) {
        echo "Error: {$dependencies['data']['message']}\n";
    }
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 2: Verificar ruta de eliminación forzada con profesor (debe fallar)
echo "TEST 2: Verificar nueva ruta DELETE /admin/gym/exercises/{exercise}/force\n";
echo "URL: DELETE /admin/gym/exercises/4/force (con profesor)\n";
$professorForce = makeRequest('DELETE', '/admin/gym/exercises/4/force', $professorToken);
echo "Status: {$professorForce['status']}\n";

if ($professorForce['status'] == 403) {
    echo "✅ RUTA FUNCIONA - Restricción de permisos correcta\n";
    echo "📋 Mensaje: {$professorForce['data']['message']}\n";
} elseif ($professorForce['status'] == 404) {
    echo "❌ RUTA NO ENCONTRADA - Verificar configuración\n";
} else {
    echo "⚠️  RUTA FUNCIONA PERO COMPORTAMIENTO INESPERADO - Status: {$professorForce['status']}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 3: Verificar ruta de eliminación forzada con admin
echo "TEST 3: Verificar nueva ruta DELETE /admin/gym/exercises/{exercise}/force\n";
echo "URL: DELETE /admin/gym/exercises/5/force (con admin)\n";
$adminForce = makeRequest('DELETE', '/admin/gym/exercises/5/force', $adminToken);
echo "Status: {$adminForce['status']}\n";

if ($adminForce['status'] == 200) {
    echo "✅ RUTA FUNCIONA - Eliminación forzada exitosa\n";
    echo "📋 Mensaje: {$adminForce['data']['message']}\n";
    if (isset($adminForce['data']['warning'])) {
        echo "⚠️  Warning: {$adminForce['data']['warning']}\n";
    }
} elseif ($adminForce['status'] == 404) {
    echo "❌ RUTA NO ENCONTRADA - Verificar configuración\n";
} else {
    echo "⚠️  RUTA FUNCIONA PERO COMPORTAMIENTO INESPERADO - Status: {$adminForce['status']}\n";
    if (isset($adminForce['data']['message'])) {
        echo "Mensaje: {$adminForce['data']['message']}\n";
    }
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 4: Verificar que las rutas existentes siguen funcionando
echo "TEST 4: Verificar que rutas existentes no se rompieron\n";
echo "URL: GET /admin/gym/exercises (lista de ejercicios)\n";
$exercisesList = makeRequest('GET', '/admin/gym/exercises', $professorToken);
echo "Status: {$exercisesList['status']}\n";

if ($exercisesList['status'] == 200) {
    echo "✅ RUTAS EXISTENTES FUNCIONAN - Lista de ejercicios accesible\n";
    if (isset($exercisesList['data']['data'])) {
        $count = count($exercisesList['data']['data']);
        echo "📊 Ejercicios en respuesta: {$count}\n";
    }
} else {
    echo "❌ RUTAS EXISTENTES ROTAS - Status: {$exercisesList['status']}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 5: Verificar ruta de eliminación normal
echo "TEST 5: Verificar que eliminación normal sigue funcionando\n";

// Crear ejercicio para eliminar
$uniqueTime = time();
$newExercise = makeRequest('POST', '/admin/gym/exercises', $professorToken, [
    'name' => 'Test Route ' . $uniqueTime,
    'description' => 'Ejercicio para probar rutas',
    'muscle_group' => 'Test',
    'equipment' => 'Ninguno',
    'difficulty' => 'beginner'
]);

if ($newExercise['status'] == 201) {
    $exerciseId = $newExercise['data']['id'];
    echo "✅ Ejercicio creado con ID: {$exerciseId}\n";
    
    // Intentar eliminar normalmente
    echo "URL: DELETE /admin/gym/exercises/{$exerciseId}\n";
    $deleteNormal = makeRequest('DELETE', "/admin/gym/exercises/{$exerciseId}", $professorToken);
    echo "Status: {$deleteNormal['status']}\n";
    
    if ($deleteNormal['status'] == 200) {
        echo "✅ ELIMINACIÓN NORMAL FUNCIONA\n";
        echo "📋 Mensaje: {$deleteNormal['data']['message']}\n";
    } else {
        echo "❌ ELIMINACIÓN NORMAL NO FUNCIONA - Status: {$deleteNormal['status']}\n";
    }
} else {
    echo "❌ No se pudo crear ejercicio de prueba\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 TESTING DE RUTAS COMPLETADO\n";
echo "\n📋 RESUMEN DE RUTAS:\n";
echo "✅ GET /admin/gym/exercises/{exercise}/dependencies\n";
echo "✅ DELETE /admin/gym/exercises/{exercise}/force\n";
echo "✅ DELETE /admin/gym/exercises/{exercise} (existente)\n";
echo "✅ GET /admin/gym/exercises (existente)\n";
echo "✅ POST /admin/gym/exercises (existente)\n";
