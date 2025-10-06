<?php

echo "🏋️ === TESTING GET EJERCICIOS === 🏋️\n\n";

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

// Obtener tokens
echo "🔐 OBTENIENDO TOKENS...\n";

// Token admin
$adminLogin = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
$adminToken = $adminLogin['data']['token'] ?? null;

// Token profesor
$professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
$professorToken = $professorLogin['data']['token'] ?? null;

// Token estudiante
$studentLogin = makeRequest('POST', '/auth/login', null, ['dni' => '33333333', 'password' => 'estudiante123']);
$studentToken = $studentLogin['data']['data']['token'] ?? null;

echo "Admin Token: " . ($adminToken ? "✅ Obtenido" : "❌ Error") . "\n";
echo "Profesor Token: " . ($professorToken ? "✅ Obtenido" : "❌ Error") . "\n";
echo "Estudiante Token: " . ($studentToken ? "✅ Obtenido" : "❌ Error") . "\n\n";

// TEST 1: Admin accede a lista de ejercicios
echo "TEST 1: Admin obtiene lista de ejercicios\n";
echo "Endpoint: GET /admin/gym/exercises\n";
$adminExercises = makeRequest('GET', '/admin/gym/exercises', $adminToken);
echo "Status: {$adminExercises['status']}\n";

if ($adminExercises['status'] == 200) {
    echo "✅ ÉXITO - Admin puede ver ejercicios\n";
    if (isset($adminExercises['data']['data'])) {
        $count = count($adminExercises['data']['data']);
        echo "📊 Total ejercicios: {$count}\n";
        
        // Mostrar primeros 3 ejercicios
        echo "📋 Primeros 3 ejercicios:\n";
        foreach (array_slice($adminExercises['data']['data'], 0, 3) as $exercise) {
            echo "  - ID: {$exercise['id']} | {$exercise['name']} | {$exercise['muscle_group']}\n";
        }
    }
} else {
    echo "❌ ERROR - Status: {$adminExercises['status']}\n";
    if (isset($adminExercises['data']['message'])) {
        echo "Mensaje: {$adminExercises['data']['message']}\n";
    }
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 2: Profesor accede a lista de ejercicios
echo "TEST 2: Profesor obtiene lista de ejercicios\n";
echo "Endpoint: GET /admin/gym/exercises\n";
$professorExercises = makeRequest('GET', '/admin/gym/exercises', $professorToken);
echo "Status: {$professorExercises['status']}\n";

if ($professorExercises['status'] == 200) {
    echo "✅ ÉXITO - Profesor puede ver ejercicios\n";
    if (isset($professorExercises['data']['data'])) {
        $count = count($professorExercises['data']['data']);
        echo "📊 Total ejercicios: {$count}\n";
    }
} else {
    echo "❌ ERROR - Status: {$professorExercises['status']}\n";
    if (isset($professorExercises['data']['message'])) {
        echo "Mensaje: {$professorExercises['data']['message']}\n";
    }
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 3: Estudiante intenta acceder (debería fallar)
echo "TEST 3: Estudiante intenta obtener lista de ejercicios\n";
echo "Endpoint: GET /admin/gym/exercises\n";
$studentExercises = makeRequest('GET', '/admin/gym/exercises', $studentToken);
echo "Status: {$studentExercises['status']}\n";

if ($studentExercises['status'] == 403) {
    echo "✅ CORRECTO - Estudiante no tiene permisos (403 Forbidden)\n";
} elseif ($studentExercises['status'] == 200) {
    echo "⚠️  INESPERADO - Estudiante puede ver ejercicios\n";
} else {
    echo "❌ ERROR INESPERADO - Status: {$studentExercises['status']}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 4: Ver ejercicio específico
if ($adminToken) {
    echo "TEST 4: Admin ve ejercicio específico (ID: 1)\n";
    echo "Endpoint: GET /admin/gym/exercises/1\n";
    $specificExercise = makeRequest('GET', '/admin/gym/exercises/1', $adminToken);
    echo "Status: {$specificExercise['status']}\n";
    
    if ($specificExercise['status'] == 200) {
        echo "✅ ÉXITO - Ejercicio específico obtenido\n";
        if (isset($specificExercise['data']['data'])) {
            $exercise = $specificExercise['data']['data'];
            echo "📋 Detalles del ejercicio:\n";
            echo "  - ID: {$exercise['id']}\n";
            echo "  - Nombre: {$exercise['name']}\n";
            echo "  - Grupo Muscular: {$exercise['muscle_group']}\n";
            echo "  - Equipamiento: {$exercise['equipment']}\n";
            echo "  - Dificultad: {$exercise['difficulty']}\n";
        }
    } else {
        echo "❌ ERROR - Status: {$specificExercise['status']}\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 TESTING COMPLETADO\n";
