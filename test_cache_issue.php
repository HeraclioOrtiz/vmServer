<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 === TESTING CACHE ISSUE === 🔍\n\n";

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

// TEST 1: Verificar estado actual en BD vs API
echo "TEST 1: Verificar estado en BD vs API\n";

// Contar en BD directamente
$countBD = \App\Models\Gym\Exercise::count();
echo "📊 Ejercicios en BD (directo): {$countBD}\n";

// Contar via API
$apiResponse = makeRequest('GET', '/admin/gym/exercises?per_page=100', $professorToken);
if ($apiResponse['status'] == 200) {
    $apiCount = $apiResponse['data']['total'] ?? count($apiResponse['data']['data'] ?? []);
    echo "📊 Ejercicios via API: {$apiCount}\n";
    
    if ($countBD != $apiCount) {
        echo "⚠️  DISCREPANCIA DETECTADA - BD: {$countBD}, API: {$apiCount}\n";
    } else {
        echo "✅ BD y API coinciden\n";
    }
} else {
    echo "❌ Error en API: {$apiResponse['status']}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 2: Crear, eliminar y verificar inmediatamente
echo "TEST 2: Crear, eliminar y verificar cache\n";

$uniqueTime = time();
$exerciseName = 'Test Cache ' . $uniqueTime;

// Crear ejercicio
echo "Creando ejercicio: {$exerciseName}\n";
$createResponse = makeRequest('POST', '/admin/gym/exercises', $professorToken, [
    'name' => $exerciseName,
    'description' => 'Test cache issue',
    'muscle_group' => 'Test',
    'equipment' => 'Ninguno',
    'difficulty' => 'beginner'
]);

if ($createResponse['status'] == 201) {
    $exerciseId = $createResponse['data']['id'];
    echo "✅ Ejercicio creado con ID: {$exerciseId}\n";
    
    // Verificar que aparece en BD
    $exerciseInBD = \App\Models\Gym\Exercise::find($exerciseId);
    echo "📊 Ejercicio en BD: " . ($exerciseInBD ? "✅ Existe" : "❌ No existe") . "\n";
    
    // Verificar que aparece en API
    $apiAfterCreate = makeRequest('GET', '/admin/gym/exercises', $professorToken);
    $foundInAPI = false;
    if ($apiAfterCreate['status'] == 200) {
        foreach ($apiAfterCreate['data']['data'] as $exercise) {
            if ($exercise['id'] == $exerciseId) {
                $foundInAPI = true;
                break;
            }
        }
    }
    echo "📊 Ejercicio en API: " . ($foundInAPI ? "✅ Existe" : "❌ No existe") . "\n";
    
    // Eliminar ejercicio
    echo "\nEliminando ejercicio...\n";
    $deleteResponse = makeRequest('DELETE', "/admin/gym/exercises/{$exerciseId}", $professorToken);
    echo "Delete Status: {$deleteResponse['status']}\n";
    
    if ($deleteResponse['status'] == 200) {
        echo "✅ Eliminación reportada como exitosa\n";
        
        // Verificar inmediatamente en BD
        $exerciseInBDAfter = \App\Models\Gym\Exercise::find($exerciseId);
        echo "📊 Ejercicio en BD después: " . ($exerciseInBDAfter ? "❌ Aún existe" : "✅ Eliminado") . "\n";
        
        // Verificar inmediatamente en API
        $apiAfterDelete = makeRequest('GET', '/admin/gym/exercises', $professorToken);
        $stillInAPI = false;
        if ($apiAfterDelete['status'] == 200) {
            foreach ($apiAfterDelete['data']['data'] as $exercise) {
                if ($exercise['id'] == $exerciseId) {
                    $stillInAPI = true;
                    break;
                }
            }
        }
        echo "📊 Ejercicio en API después: " . ($stillInAPI ? "❌ Aún aparece" : "✅ Eliminado") . "\n";
        
        if ($stillInAPI && !$exerciseInBDAfter) {
            echo "🔥 PROBLEMA DE CACHE CONFIRMADO - BD actualizada pero API no\n";
        }
    }
} else {
    echo "❌ No se pudo crear ejercicio de prueba\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 3: Verificar cache del service
echo "TEST 3: Verificar cache del ExerciseService\n";

try {
    $service = app(\App\Services\Gym\ExerciseService::class);
    
    // Verificar si hay cache activo
    $cacheKeys = [
        'exercise_stats',
        'exercise_filter_options',
        'most_used_exercises_5',
        'most_used_exercises_10'
    ];
    
    foreach ($cacheKeys as $key) {
        $hasCache = \Cache::has($key);
        echo "📊 Cache '{$key}': " . ($hasCache ? "✅ Activo" : "❌ No activo") . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error verificando cache: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 4: Verificar el controlador index
echo "TEST 4: Verificar lógica del controlador index\n";

// Simular la query del controlador
$query = \App\Models\Gym\Exercise::query();
$perPage = 20;
$exercises = $query->orderBy('name')->paginate($perPage);

echo "📊 Ejercicios desde controlador logic: {$exercises->total()}\n";
echo "📊 Ejercicios en página actual: {$exercises->count()}\n";

// Verificar si hay algún filtro o scope aplicado
$allExercises = \App\Models\Gym\Exercise::all();
echo "📊 Todos los ejercicios (sin filtros): {$allExercises->count()}\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 DIAGNÓSTICO COMPLETADO\n";
