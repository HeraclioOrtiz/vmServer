<?php

echo "🏋️ === TESTING DAILY TEMPLATES ENDPOINT === 🏋️\n\n";

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

// TEST 1: Verificar endpoint de daily templates
echo "TEST 1: Verificar GET /admin/gym/daily-templates\n";
$templatesResponse = makeRequest('GET', '/admin/gym/daily-templates', $professorToken);
echo "Status: {$templatesResponse['status']}\n";

if ($templatesResponse['status'] == 200) {
    echo "✅ ENDPOINT FUNCIONA - Daily templates accesible\n";
    $data = $templatesResponse['data'];
    
    if (isset($data['data'])) {
        $count = count($data['data']);
        $total = $data['total'] ?? $count;
        echo "📊 Plantillas encontradas: {$count} de {$total}\n";
        
        if ($count > 0) {
            echo "📋 Primera plantilla:\n";
            $first = $data['data'][0];
            echo "  - ID: {$first['id']}\n";
            echo "  - Título: {$first['title']}\n";
            echo "  - Objetivo: " . ($first['goal'] ?? 'No definido') . "\n";
            echo "  - Nivel: " . ($first['level'] ?? 'No definido') . "\n";
            echo "  - Duración: " . ($first['estimated_duration_min'] ?? 'No definida') . " min\n";
        } else {
            echo "📊 No hay plantillas en la BD\n";
        }
    } else {
        echo "⚠️  Respuesta inesperada - estructura no estándar\n";
        echo "Raw response: " . substr($templatesResponse['raw_body'], 0, 200) . "...\n";
    }
} else {
    echo "❌ ENDPOINT NO FUNCIONA - Status: {$templatesResponse['status']}\n";
    if (isset($templatesResponse['data']['message'])) {
        echo "Error: {$templatesResponse['data']['message']}\n";
    }
    echo "Raw response: " . substr($templatesResponse['raw_body'], 0, 200) . "...\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 2: Verificar estructura de BD
echo "TEST 2: Verificar estructura de BD\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Verificar tablas
    $tables = [
        'gym_daily_templates',
        'gym_daily_template_exercises', 
        'gym_daily_template_sets'
    ];
    
    foreach ($tables as $table) {
        $exists = \Schema::hasTable($table);
        echo "📊 Tabla '{$table}': " . ($exists ? "✅ Existe" : "❌ No existe") . "\n";
        
        if ($exists) {
            $count = \DB::table($table)->count();
            echo "   Registros: {$count}\n";
        }
    }
    
    // Verificar modelos
    echo "\n📋 Verificando modelos:\n";
    $models = [
        'App\\Models\\Gym\\DailyTemplate',
        'App\\Models\\Gym\\DailyTemplateExercise',
        'App\\Models\\Gym\\DailyTemplateSet'
    ];
    
    foreach ($models as $model) {
        try {
            $instance = new $model;
            echo "📊 Modelo '{$model}': ✅ Instanciable\n";
            
            if (method_exists($instance, 'getTable')) {
                echo "   Tabla: {$instance->getTable()}\n";
            }
        } catch (\Exception $e) {
            echo "📊 Modelo '{$model}': ❌ Error - {$e->getMessage()}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error verificando BD: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 3: Intentar crear una plantilla de prueba
echo "TEST 3: Intentar crear plantilla de prueba\n";

$templateData = [
    'title' => 'Test Template ' . time(),
    'goal' => 'strength',
    'estimated_duration_min' => 45,
    'level' => 'beginner',
    'tags' => ['test', 'strength'],
    'exercises' => [
        [
            'exercise_id' => 1,
            'order' => 1,
            'notes' => 'Ejercicio de prueba',
            'sets' => [
                [
                    'set_number' => 1,
                    'reps_min' => 8,
                    'reps_max' => 12,
                    'rest_seconds' => 120
                ]
            ]
        ]
    ]
];

$createResponse = makeRequest('POST', '/admin/gym/daily-templates', $professorToken, $templateData);
echo "Status: {$createResponse['status']}\n";

if ($createResponse['status'] == 201) {
    echo "✅ CREACIÓN EXITOSA - Plantilla creada\n";
    $created = $createResponse['data'];
    echo "📊 ID creado: {$created['id']}\n";
    echo "📊 Título: {$created['title']}\n";
    
    // Intentar obtener la plantilla específica
    echo "\nVerificando plantilla creada...\n";
    $showResponse = makeRequest('GET', "/admin/gym/daily-templates/{$created['id']}", $professorToken);
    
    if ($showResponse['status'] == 200) {
        echo "✅ SHOW FUNCIONA - Plantilla recuperada\n";
        $template = $showResponse['data'];
        
        if (isset($template['exercises'])) {
            echo "📊 Ejercicios en plantilla: " . count($template['exercises']) . "\n";
        }
    } else {
        echo "❌ SHOW NO FUNCIONA - Status: {$showResponse['status']}\n";
    }
    
} elseif ($createResponse['status'] == 422) {
    echo "⚠️  VALIDACIÓN FALLIDA - Datos incorrectos\n";
    if (isset($createResponse['data']['errors'])) {
        echo "Errores:\n";
        foreach ($createResponse['data']['errors'] as $field => $errors) {
            echo "  - {$field}: " . implode(', ', $errors) . "\n";
        }
    }
} else {
    echo "❌ CREACIÓN FALLIDA - Status: {$createResponse['status']}\n";
    if (isset($createResponse['data']['message'])) {
        echo "Error: {$createResponse['data']['message']}\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 DIAGNÓSTICO DE DAILY TEMPLATES COMPLETADO\n";
