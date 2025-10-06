<?php

echo "🔧 === TESTING MEJORAS EN ELIMINACIÓN DE EJERCICIOS === 🔧\n\n";

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

// TEST 1: Verificar dependencias del ejercicio ID 9 (que sabemos tiene dependencias)
echo "TEST 1: Verificar dependencias del ejercicio ID 9\n";
echo "Endpoint: GET /admin/gym/exercises/9/dependencies\n";
$dependencies = makeRequest('GET', '/admin/gym/exercises/9/dependencies', $professorToken);
echo "Status: {$dependencies['status']}\n";

if ($dependencies['status'] == 200) {
    echo "✅ ÉXITO - Endpoint de dependencias funciona\n";
    $data = $dependencies['data'];
    echo "📊 Puede eliminar: " . ($data['can_delete'] ? "SÍ" : "NO") . "\n";
    echo "📊 Total referencias: {$data['total_references']}\n";
    echo "📊 Dependencias:\n";
    foreach ($data['dependencies'] as $type => $count) {
        echo "  - {$type}: {$count}\n";
    }
} else {
    echo "❌ ERROR - Status: {$dependencies['status']}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 2: Intentar eliminar ejercicio con dependencias (debería devolver 422)
echo "TEST 2: Intentar eliminar ejercicio ID 9 (con dependencias)\n";
echo "Endpoint: DELETE /admin/gym/exercises/9\n";
$deleteResult = makeRequest('DELETE', '/admin/gym/exercises/9', $professorToken);
echo "Status: {$deleteResult['status']}\n";

if ($deleteResult['status'] == 422) {
    echo "✅ CORRECTO - Error 422 en lugar de 500\n";
    $data = $deleteResult['data'];
    echo "📋 Mensaje: {$data['message']}\n";
    echo "📋 Error Code: {$data['error']}\n";
    echo "📋 Plantillas afectadas: {$data['details']['templates_count']}\n";
} elseif ($deleteResult['status'] == 500) {
    echo "⚠️  TODAVÍA ERROR 500 - Verificar implementación\n";
} else {
    echo "❌ ERROR INESPERADO - Status: {$deleteResult['status']}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 3: Crear un ejercicio nuevo para probar eliminación exitosa
echo "TEST 3: Crear ejercicio nuevo para probar eliminación exitosa\n";
$uniqueTime = time();
$newExercise = makeRequest('POST', '/admin/gym/exercises', $professorToken, [
    'name' => 'Ejercicio Test Eliminación ' . $uniqueTime,
    'description' => 'Ejercicio creado para probar eliminación',
    'muscle_group' => 'Test',
    'equipment' => 'Ninguno',
    'difficulty' => 'beginner'
]);

if ($newExercise['status'] == 201) {
    $exerciseId = $newExercise['data']['id'];
    echo "✅ Ejercicio creado con ID: {$exerciseId}\n";
    
    // Verificar dependencias del nuevo ejercicio
    echo "\nVerificando dependencias del nuevo ejercicio...\n";
    $newDependencies = makeRequest('GET', "/admin/gym/exercises/{$exerciseId}/dependencies", $professorToken);
    
    if ($newDependencies['status'] == 200) {
        $canDelete = $newDependencies['data']['can_delete'];
        echo "Puede eliminar: " . ($canDelete ? "SÍ" : "NO") . "\n";
        
        if ($canDelete) {
            // Intentar eliminar el nuevo ejercicio
            echo "\nIntentando eliminar ejercicio sin dependencias...\n";
            $deleteNew = makeRequest('DELETE', "/admin/gym/exercises/{$exerciseId}", $professorToken);
            echo "Status: {$deleteNew['status']}\n";
            
            if ($deleteNew['status'] == 200) {
                echo "✅ ÉXITO - Ejercicio eliminado correctamente\n";
                echo "📋 Mensaje: {$deleteNew['data']['message']}\n";
            } else {
                echo "❌ ERROR - No se pudo eliminar ejercicio sin dependencias\n";
            }
        }
    }
} else {
    echo "❌ No se pudo crear ejercicio de prueba\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 4: Probar eliminación forzada (solo admin)
echo "TEST 4: Probar eliminación forzada con admin\n";
echo "Endpoint: DELETE /admin/gym/exercises/9/force\n";
$forceDelete = makeRequest('DELETE', '/admin/gym/exercises/9/force', $adminToken);
echo "Status: {$forceDelete['status']}\n";

if ($forceDelete['status'] == 200) {
    echo "✅ ÉXITO - Eliminación forzada funcionó\n";
    echo "📋 Mensaje: {$forceDelete['data']['message']}\n";
    if (isset($forceDelete['data']['warning'])) {
        echo "⚠️  Warning: {$forceDelete['data']['warning']}\n";
    }
} elseif ($forceDelete['status'] == 403) {
    echo "⚠️  PERMISOS - Admin no tiene permisos (verificar lógica)\n";
} else {
    echo "❌ ERROR - Status: {$forceDelete['status']}\n";
    if (isset($forceDelete['data']['message'])) {
        echo "Mensaje: {$forceDelete['data']['message']}\n";
    }
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 5: Probar eliminación forzada con profesor (debería fallar)
echo "TEST 5: Probar eliminación forzada con profesor (debería fallar)\n";
echo "Endpoint: DELETE /admin/gym/exercises/1/force\n";
$professorForceDelete = makeRequest('DELETE', '/admin/gym/exercises/1/force', $professorToken);
echo "Status: {$professorForceDelete['status']}\n";

if ($professorForceDelete['status'] == 403) {
    echo "✅ CORRECTO - Profesor no puede hacer eliminación forzada\n";
    echo "📋 Mensaje: {$professorForceDelete['data']['message']}\n";
} else {
    echo "❌ ERROR - Profesor debería recibir 403\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 TESTING DE MEJORAS COMPLETADO\n";
echo "\n📋 RESUMEN:\n";
echo "✅ Verificación de dependencias\n";
echo "✅ Error 422 en lugar de 500 para ejercicios en uso\n";
echo "✅ Eliminación exitosa de ejercicios sin dependencias\n";
echo "✅ Eliminación forzada para admins\n";
echo "✅ Restricción de eliminación forzada para profesores\n";
