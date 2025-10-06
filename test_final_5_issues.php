<?php

echo "🎯 === TESTING ÚLTIMOS 5 PROBLEMAS === 🎯\n\n";

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

// Login como admin
echo "1. Login como admin:\n";
$loginResponse = makeRequest('POST', '/test/login', null, ['dni' => '11111111', 'password' => 'admin123']);
if ($loginResponse['status'] == 200) {
    $adminToken = $loginResponse['data']['token'];
    echo "   ✅ Token admin obtenido\n\n";
    
    // Problema 1: Asignar profesor (422)
    echo "2. Test asignar profesor (422):\n";
    $assignData = [
        'qualifications' => [
            'education' => 'Licenciatura en Educación Física',
            'certifications' => ['Entrenador Personal Certificado', 'Instructor de Fitness'],
            'experience_years' => 5,
            'specialties' => ['strength', 'hypertrophy']
        ],
        'permissions' => [
            'can_create_templates' => true,
            'can_assign_routines' => true,
            'can_view_all_students' => false,
            'can_export_data' => false,
            'max_students' => 20
        ],
        'schedule' => [
            'available_days' => [1, 2, 3, 4, 5], // Lunes a Viernes
            'start_time' => '09:00',
            'end_time' => '17:00'
        ],
        'notes' => 'Profesor de prueba para testing'
    ];
    $result = makeRequest('POST', '/admin/professors/3/assign', $adminToken, $assignData);
    echo "   Status: {$result['status']} " . ($result['status'] == 200 ? '✅' : '❌') . "\n";
    if ($result['status'] == 422) {
        echo "   Errores: " . json_encode($result['data']['errors'] ?? []) . "\n";
    }
    
    // Problema 2: Reasignar estudiante (405)
    echo "\n3. Test reasignar estudiante (405):\n";
    $reassignData = [
        'student_id' => 3,
        'new_professor_id' => 2,
        'reason' => 'Test'
    ];
    $result = makeRequest('POST', '/admin/professors/2/reassign-student', $adminToken, $reassignData);
    echo "   Status: {$result['status']} " . ($result['status'] == 200 ? '✅' : '❌') . "\n";
    
    // Problema 3: Stats weekly-assignments (404)
    echo "\n4. Test stats weekly-assignments (404):\n";
    $result = makeRequest('GET', '/admin/gym/weekly-assignments/stats', $adminToken);
    echo "   Status: {$result['status']} " . ($result['status'] == 200 ? '✅' : '❌') . "\n";
    
    // Problema 4: Configuración específica (404)
    echo "\n5. Test configuración específica (404):\n";
    $result = makeRequest('GET', '/admin/settings/test_setting', $adminToken);
    echo "   Status: {$result['status']} " . ($result['status'] == 200 ? '✅' : '❌') . "\n";
    
} else {
    echo "   ❌ Login falló\n";
}

echo "\n📊 OBJETIVO: Corregir estos 5 problemas para llegar al 100%\n";
