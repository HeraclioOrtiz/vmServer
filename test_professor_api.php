<?php

echo "🔍 === TESTING API DEL PROFESOR === 🔍\n\n";

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
    echo "🔐 PASO 1: Login del profesor...\n";
    
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '22222222',
        'password' => 'profesor123'
    ]);
    
    if ($loginResponse['status'] !== 200) {
        echo "❌ ERROR en login: Status {$loginResponse['status']}\n";
        print_r($loginResponse['data']);
        exit(1);
    }
    
    $token = $loginResponse['data']['data']['token'];
    $user = $loginResponse['data']['data']['user'];
    
    echo "✅ Login exitoso:\n";
    echo "   Usuario: {$user['name']}\n";
    echo "   Email: {$user['email']}\n";
    echo "   Es profesor: " . ($user['is_professor'] ? 'Sí' : 'No') . "\n";
    echo "   Token obtenido: " . substr($token, 0, 20) . "...\n\n";
    
    echo "🎓 PASO 2: Consultando estudiantes asignados...\n";
    
    $studentsResponse = makeRequest('http://127.0.0.1:8000/api/professor/my-students', 'GET', null, $token);
    
    if ($studentsResponse['status'] !== 200) {
        echo "❌ ERROR consultando estudiantes: Status {$studentsResponse['status']}\n";
        print_r($studentsResponse['data']);
        exit(1);
    }
    
    $students = $studentsResponse['data']['data'];
    
    echo "✅ Consulta exitosa:\n";
    echo "📊 Total estudiantes asignados: " . count($students) . "\n\n";
    
    if (count($students) === 0) {
        echo "⚠️  No hay estudiantes asignados\n";
        exit(0);
    }
    
    echo "📋 LISTA DE ESTUDIANTES:\n";
    echo str_repeat("-", 60) . "\n";
    
    foreach ($students as $index => $assignment) {
        echo "👤 ESTUDIANTE #" . ($index + 1) . ":\n";
        echo "   Nombre: {$assignment['student']['name']}\n";
        echo "   ID: {$assignment['student']['id']}\n";
        echo "   Email: {$assignment['student']['email']}\n";
        echo "   Asignado desde: {$assignment['start_date']}\n";
        echo "   Estado: {$assignment['status']}\n";
        echo "\n";
        
        if ($index >= 4) { // Mostrar solo los primeros 5
            echo "   ... y " . (count($students) - 5) . " estudiantes más\n";
            break;
        }
    }
    
    echo "\n📊 PASO 3: Consultando estadísticas del profesor...\n";
    
    $statsResponse = makeRequest('http://127.0.0.1:8000/api/professor/my-stats', 'GET', null, $token);
    
    if ($statsResponse['status'] === 200) {
        $stats = $statsResponse['data']['data'];
        echo "✅ Estadísticas obtenidas:\n";
        echo "   🎓 Estudiantes asignados: {$stats['total_students']}\n";
        echo "   📋 Asignaciones de plantillas: {$stats['total_assignments']}\n";
        echo "   ✅ Sesiones completadas: {$stats['completed_sessions']}\n";
        echo "   ⏳ Sesiones pendientes: {$stats['pending_sessions']}\n";
    } else {
        echo "⚠️  No se pudieron obtener estadísticas (Status: {$statsResponse['status']})\n";
    }
    
    echo "\n🎯 RESUMEN:\n";
    echo "   ✅ API funcionando correctamente\n";
    echo "   ✅ Profesor autenticado exitosamente\n";
    echo "   ✅ " . count($students) . " estudiantes asignados\n";
    echo "   🚀 Sistema listo para frontend\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
