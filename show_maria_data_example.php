<?php

echo "📊 === EJEMPLO DE DATOS REALES - MARÍA GARCÍA === 📊\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

// Función para hacer requests HTTP
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

try {
    // Login
    $maria = \App\Models\User::where('dni', '33333333')->first();
    $maria->password = bcrypt('estudiante123');
    $maria->save();
    
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '33333333',
        'password' => 'estudiante123'
    ]);
    
    $token = $loginResponse['data']['data']['token'];
    
    echo "🔐 1. DATOS DE LOGIN:\n";
    echo str_repeat("=", 60) . "\n";
    echo json_encode($loginResponse['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "📋 2. DATOS DE PLANTILLAS ASIGNADAS:\n";
    echo str_repeat("=", 60) . "\n";
    
    $templatesResponse = makeRequest('http://127.0.0.1:8000/api/student/my-templates', 'GET', null, $token);
    echo json_encode($templatesResponse['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "🏋️ 3. DATOS DE DETALLES DE PLANTILLA:\n";
    echo str_repeat("=", 60) . "\n";
    
    $templatesData = $templatesResponse['data']['data'];
    $firstTemplateId = $templatesData['templates'][0]['id'];
    
    $detailsResponse = makeRequest("http://127.0.0.1:8000/api/student/template/{$firstTemplateId}/details", 'GET', null, $token);
    echo json_encode($detailsResponse['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "📅 4. DATOS DE CALENDARIO SEMANAL:\n";
    echo str_repeat("=", 60) . "\n";
    
    $calendarResponse = makeRequest('http://127.0.0.1:8000/api/student/my-weekly-calendar', 'GET', null, $token);
    echo json_encode($calendarResponse['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
