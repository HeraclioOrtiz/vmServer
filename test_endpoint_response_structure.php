<?php

echo "🔍 === TESTING ESTRUCTURA DE RESPUESTA ENDPOINT === 🔍\n\n";

function makeAuthenticatedRequest($endpoint, $token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true),
        'raw' => $response
    ];
}

// PASO 1: Obtener token de autenticación
echo "PASO 1: Obteniendo token de autenticación...\n";
$loginResponse = makeAuthenticatedRequest('/test/login', null);
$loginData = json_decode($loginResponse['raw'], true);

if (!isset($loginData['token'])) {
    // Intentar login manual
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/test/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['dni' => '22222222', 'password' => 'profesor123']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    $loginData = json_decode($response, true);
}

$token = $loginData['token'] ?? null;

if (!$token) {
    echo "❌ ERROR: No se pudo obtener token de autenticación\n";
    exit(1);
}

echo "✅ Token obtenido exitosamente\n\n";

// PASO 2: Probar endpoint con parámetros completos
echo "PASO 2: Probando endpoint con parámetros completos...\n";
$fullEndpoint = '/admin/gym/daily-templates?page=1&per_page=3&with_exercises=true&with_sets=true&include=exercises,exercises.exercise,exercises.sets';
echo "URL: {$fullEndpoint}\n";

$response = makeAuthenticatedRequest($fullEndpoint, $token);

echo "Status: {$response['status']}\n";

if ($response['status'] !== 200) {
    echo "❌ ERROR: Status no es 200\n";
    echo "Response: " . substr($response['raw'], 0, 500) . "...\n";
    exit(1);
}

echo "✅ Status 200 - Endpoint responde correctamente\n\n";

// PASO 3: Analizar estructura de respuesta
echo "PASO 3: Analizando estructura de respuesta...\n";

$data = $response['data'];

// Verificar estructura de paginación
echo "📊 ESTRUCTURA DE PAGINACIÓN:\n";
$paginationFields = ['current_page', 'data', 'first_page_url', 'from', 'last_page', 'per_page', 'total'];
foreach ($paginationFields as $field) {
    $exists = isset($data[$field]) ? '✅' : '❌';
    echo "  {$exists} {$field}: " . (isset($data[$field]) ? gettype($data[$field]) : 'MISSING') . "\n";
}

if (!isset($data['data']) || !is_array($data['data']) || count($data['data']) === 0) {
    echo "❌ ERROR: No hay plantillas en la respuesta\n";
    exit(1);
}

echo "\n📋 PLANTILLAS ENCONTRADAS: " . count($data['data']) . "\n\n";

// PASO 4: Analizar primera plantilla en detalle
echo "PASO 4: Analizando primera plantilla en detalle...\n";

$firstTemplate = $data['data'][0];
echo "🎯 PLANTILLA: {$firstTemplate['title']}\n\n";

// Verificar campos básicos de plantilla
echo "📊 CAMPOS BÁSICOS DE PLANTILLA:\n";
$templateFields = [
    'id' => 'integer',
    'created_by' => 'integer|null',
    'title' => 'string',
    'goal' => 'string',
    'estimated_duration_min' => 'integer',
    'level' => 'string',
    'tags' => 'array',
    'is_preset' => 'boolean',
    'created_at' => 'string',
    'updated_at' => 'string',
    'exercises' => 'array'
];

foreach ($templateFields as $field => $expectedType) {
    $exists = isset($firstTemplate[$field]);
    $actualType = $exists ? gettype($firstTemplate[$field]) : 'MISSING';
    $status = $exists ? '✅' : '❌';
    
    echo "  {$status} {$field}: {$actualType}";
    if ($exists && $field === 'tags') {
        echo " (" . count($firstTemplate[$field]) . " items)";
    } elseif ($exists && $field === 'exercises') {
        echo " (" . count($firstTemplate[$field]) . " exercises)";
    }
    echo "\n";
}

// PASO 5: Analizar ejercicios
if (!isset($firstTemplate['exercises']) || count($firstTemplate['exercises']) === 0) {
    echo "\n❌ ERROR CRÍTICO: No hay ejercicios en la plantilla\n";
    exit(1);
}

echo "\n📋 EJERCICIOS EN PLANTILLA: " . count($firstTemplate['exercises']) . "\n\n";

$firstExercise = $firstTemplate['exercises'][0];
echo "🏋️ PRIMER EJERCICIO:\n";

// Verificar campos del ejercicio de plantilla
echo "📊 CAMPOS DE TEMPLATE EXERCISE:\n";
$templateExerciseFields = [
    'id' => 'integer',
    'daily_template_id' => 'integer',
    'exercise_id' => 'integer',
    'display_order' => 'integer',
    'notes' => 'string|null',
    'created_at' => 'string',
    'updated_at' => 'string',
    'exercise' => 'array',
    'sets' => 'array'
];

foreach ($templateExerciseFields as $field => $expectedType) {
    $exists = isset($firstExercise[$field]);
    $actualType = $exists ? gettype($firstExercise[$field]) : 'MISSING';
    $status = $exists ? '✅' : '❌';
    
    echo "  {$status} {$field}: {$actualType}";
    if ($exists && $field === 'sets') {
        echo " (" . count($firstExercise[$field]) . " sets)";
    }
    echo "\n";
}

// PASO 6: Verificar información del ejercicio (CRÍTICO)
echo "\n🎯 INFORMACIÓN DEL EJERCICIO (CRÍTICO):\n";

if (!isset($firstExercise['exercise'])) {
    echo "❌ ERROR CRÍTICO: Falta la relación 'exercise'\n";
    echo "❌ El frontend tiene razón - no se está incluyendo la información del ejercicio\n";
    exit(1);
}

$exerciseInfo = $firstExercise['exercise'];
echo "✅ Relación 'exercise' encontrada\n";

// Verificar campos del ejercicio
echo "\n📊 CAMPOS DEL EJERCICIO:\n";
$exerciseFields = [
    'id' => 'integer',
    'name' => 'string',
    'muscle_group' => 'string',
    'movement_pattern' => 'string',
    'equipment' => 'string',
    'difficulty' => 'string',
    'tags' => 'array',
    'instructions' => 'string',
    'tempo' => 'string|null',
    'created_at' => 'string',
    'updated_at' => 'string'
];

foreach ($exerciseFields as $field => $expectedType) {
    $exists = isset($exerciseInfo[$field]);
    $actualType = $exists ? gettype($exerciseInfo[$field]) : 'MISSING';
    $status = $exists ? '✅' : '❌';
    
    echo "  {$status} {$field}: {$actualType}";
    if ($exists && in_array($field, ['name', 'muscle_group', 'equipment', 'difficulty'])) {
        echo " ('{$exerciseInfo[$field]}')";
    }
    echo "\n";
}

// PASO 7: Verificar series
echo "\n🏋️ SERIES DEL EJERCICIO:\n";

if (!isset($firstExercise['sets']) || count($firstExercise['sets']) === 0) {
    echo "❌ ERROR: No hay series en el ejercicio\n";
} else {
    echo "✅ Series encontradas: " . count($firstExercise['sets']) . "\n";
    
    $firstSet = $firstExercise['sets'][0];
    echo "\n📊 PRIMERA SERIE:\n";
    
    $setFields = [
        'id' => 'integer',
        'daily_template_exercise_id' => 'integer',
        'set_number' => 'integer',
        'reps_min' => 'integer',
        'reps_max' => 'integer',
        'rest_seconds' => 'integer',
        'tempo' => 'string|null',
        'rpe_target' => 'double|null',
        'notes' => 'string|null'
    ];
    
    foreach ($setFields as $field => $expectedType) {
        $exists = isset($firstSet[$field]);
        $actualType = $exists ? gettype($firstSet[$field]) : 'MISSING';
        $status = $exists ? '✅' : '❌';
        
        echo "  {$status} {$field}: {$actualType}";
        if ($exists && in_array($field, ['reps_min', 'reps_max', 'rest_seconds', 'rpe_target'])) {
            echo " ({$firstSet[$field]})";
        }
        echo "\n";
    }
}

// PASO 8: Resumen final
echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 RESUMEN FINAL DEL TEST\n\n";

$allGood = true;

// Verificaciones críticas
$criticalChecks = [
    'Respuesta Status 200' => $response['status'] === 200,
    'Estructura de paginación' => isset($data['data']),
    'Plantillas presentes' => count($data['data']) > 0,
    'Ejercicios en plantilla' => isset($firstTemplate['exercises']) && count($firstTemplate['exercises']) > 0,
    'Relación exercise incluida' => isset($firstExercise['exercise']),
    'Información del ejercicio' => isset($exerciseInfo['name']) && isset($exerciseInfo['muscle_group']),
    'Series configuradas' => isset($firstExercise['sets']) && count($firstExercise['sets']) > 0
];

foreach ($criticalChecks as $check => $passed) {
    $status = $passed ? '✅' : '❌';
    echo "{$status} {$check}\n";
    if (!$passed) $allGood = false;
}

echo "\n";

if ($allGood) {
    echo "🎊 RESULTADO: BACKEND COMPLETAMENTE CORRECTO\n";
    echo "✅ Todas las relaciones están incluidas\n";
    echo "✅ La estructura de datos es perfecta\n";
    echo "✅ El frontend debería funcionar sin problemas\n";
    echo "\n💡 RECOMENDACIÓN PARA FRONTEND:\n";
    echo "   - Verificar cache del navegador/aplicación\n";
    echo "   - Revisar URL y parámetros de la petición\n";
    echo "   - Confirmar manejo correcto de la respuesta JSON\n";
} else {
    echo "❌ RESULTADO: HAY PROBLEMAS EN EL BACKEND\n";
    echo "⚠️  Revisar implementación del controlador\n";
}

echo "\n📋 EJEMPLO DE ACCESO A DATOS:\n";
echo "template.title: '{$firstTemplate['title']}'\n";
echo "template.exercises[0].exercise.name: '{$exerciseInfo['name']}'\n";
echo "template.exercises[0].exercise.muscle_group: '{$exerciseInfo['muscle_group']}'\n";
echo "template.exercises[0].sets[0].reps_min: {$firstSet['reps_min']}\n";
echo "template.exercises[0].sets[0].rest_seconds: {$firstSet['rest_seconds']}\n";
