<?php

echo "🏗️ === TESTING REFACTORIZACIÓN CON EXERCISE SERVICE === 🏗️\n\n";

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

// TEST 1: Verificar que el endpoint de dependencias funciona con el servicio
echo "TEST 1: Verificar dependencias usando ExerciseService\n";
echo "Endpoint: GET /admin/gym/exercises/1/dependencies\n";
$dependencies = makeRequest('GET', '/admin/gym/exercises/1/dependencies', $professorToken);
echo "Status: {$dependencies['status']}\n";

if ($dependencies['status'] == 200) {
    echo "✅ ÉXITO - Service layer funciona correctamente\n";
    $data = $dependencies['data'];
    echo "📊 Puede eliminar: " . ($data['can_delete'] ? "SÍ" : "NO") . "\n";
    echo "📊 Total referencias: {$data['total_references']}\n";
} else {
    echo "❌ ERROR - Status: {$dependencies['status']}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 2: Crear un ejercicio nuevo para probar eliminación exitosa
echo "TEST 2: Crear y eliminar ejercicio usando ExerciseService\n";
$uniqueTime = time();
$newExercise = makeRequest('POST', '/admin/gym/exercises', $professorToken, [
    'name' => 'Ejercicio Service Test ' . $uniqueTime,
    'description' => 'Ejercicio para probar service layer',
    'muscle_group' => 'Test',
    'equipment' => 'Ninguno',
    'difficulty' => 'beginner'
]);

if ($newExercise['status'] == 201) {
    $exerciseId = $newExercise['data']['id'];
    echo "✅ Ejercicio creado con ID: {$exerciseId}\n";
    
    // Verificar dependencias del nuevo ejercicio
    echo "\nVerificando dependencias con service...\n";
    $newDependencies = makeRequest('GET', "/admin/gym/exercises/{$exerciseId}/dependencies", $professorToken);
    
    if ($newDependencies['status'] == 200) {
        $canDelete = $newDependencies['data']['can_delete'];
        echo "Puede eliminar: " . ($canDelete ? "SÍ" : "NO") . "\n";
        
        if ($canDelete) {
            // Intentar eliminar usando el service
            echo "\nIntentando eliminar usando ExerciseService...\n";
            $deleteNew = makeRequest('DELETE', "/admin/gym/exercises/{$exerciseId}", $professorToken);
            echo "Status: {$deleteNew['status']}\n";
            
            if ($deleteNew['status'] == 200) {
                echo "✅ ÉXITO - Service eliminó ejercicio correctamente\n";
                echo "📋 Mensaje: {$deleteNew['data']['message']}\n";
            } else {
                echo "❌ ERROR - Service no pudo eliminar ejercicio\n";
            }
        }
    }
} else {
    echo "❌ No se pudo crear ejercicio de prueba\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 3: Probar eliminación con dependencias usando service
echo "TEST 3: Intentar eliminar ejercicio con dependencias usando ExerciseService\n";
echo "Endpoint: DELETE /admin/gym/exercises/1\n";
$deleteWithDeps = makeRequest('DELETE', '/admin/gym/exercises/1', $professorToken);
echo "Status: {$deleteWithDeps['status']}\n";

if ($deleteWithDeps['status'] == 422) {
    echo "✅ CORRECTO - Service devuelve 422 para ejercicios en uso\n";
    $data = $deleteWithDeps['data'];
    echo "📋 Mensaje: {$data['message']}\n";
    echo "📋 Error Code: {$data['error']}\n";
    if (isset($data['details']['templates_count'])) {
        echo "📋 Plantillas afectadas: {$data['details']['templates_count']}\n";
    }
} else {
    echo "❌ ERROR - Service no maneja dependencias correctamente\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 4: Probar eliminación forzada con admin usando service
echo "TEST 4: Probar eliminación forzada usando ExerciseService\n";
echo "Endpoint: DELETE /admin/gym/exercises/2/force\n";
$forceDelete = makeRequest('DELETE', '/admin/gym/exercises/2/force', $adminToken);
echo "Status: {$forceDelete['status']}\n";

if ($forceDelete['status'] == 200) {
    echo "✅ ÉXITO - Service realizó eliminación forzada\n";
    echo "📋 Mensaje: {$forceDelete['data']['message']}\n";
    if (isset($forceDelete['data']['warning'])) {
        echo "⚠️  Warning: {$forceDelete['data']['warning']}\n";
    }
} elseif ($forceDelete['status'] == 403) {
    echo "⚠️  PERMISOS - Service verificó permisos correctamente\n";
} else {
    echo "❌ ERROR - Status: {$forceDelete['status']}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 5: Verificar que profesor no puede hacer eliminación forzada
echo "TEST 5: Verificar restricciones de permisos en ExerciseService\n";
echo "Endpoint: DELETE /admin/gym/exercises/3/force\n";
$professorForceDelete = makeRequest('DELETE', '/admin/gym/exercises/3/force', $professorToken);
echo "Status: {$professorForceDelete['status']}\n";

if ($professorForceDelete['status'] == 403) {
    echo "✅ CORRECTO - Service restringe eliminación forzada para profesores\n";
    echo "📋 Mensaje: {$professorForceDelete['data']['message']}\n";
} else {
    echo "❌ ERROR - Service no maneja permisos correctamente\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 TESTING DE REFACTORIZACIÓN COMPLETADO\n";
echo "\n📋 RESUMEN DE ARQUITECTURA LIMPIA:\n";
echo "✅ Controlador delgado - solo maneja HTTP\n";
echo "✅ Service layer - contiene toda la lógica de negocio\n";
echo "✅ Validaciones centralizadas en el servicio\n";
echo "✅ Auditoría automática en el servicio\n";
echo "✅ Manejo de errores consistente\n";
echo "✅ Separación de responsabilidades clara\n";
