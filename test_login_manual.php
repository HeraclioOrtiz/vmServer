<?php

echo "💪 === ANÁLISIS: EJERCICIOS DE GIMNASIO === 💪\n\n";LOGIN ===\n\n";

// Test de login con cURL
function testLogin($dni, $password, $description) {
    echo "🔐 Probando login: $description\n";
    echo "   DNI: $dni\n";
    echo "   Password: $password\n";
{{ ... }}
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/auth/login');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'dni' => $dni,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Status: $httpCode\n";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if (isset($data['token'])) {
            echo "   ✅ Login exitoso - Token obtenido\n";
            echo "   Usuario: " . ($data['user']['name'] ?? 'N/A') . "\n";
            echo "   Admin: " . ($data['user']['is_admin'] ? 'Sí' : 'No') . "\n";
            echo "   Profesor: " . ($data['user']['is_professor'] ? 'Sí' : 'No') . "\n";
            return $data['token'];
        } else {
            echo "   ❌ Login exitoso pero sin token\n";
            echo "   Respuesta: " . substr($response, 0, 200) . "\n";
        }
    } else {
        echo "   ❌ Login falló\n";
        echo "   Respuesta: " . substr($response, 0, 200) . "\n";
    }
    
    echo "\n";
    return null;
}

// Test de endpoint admin con token
function testAdminEndpoint($token, $endpoint, $description) {
    echo "🔧 Probando endpoint: $description\n";
    echo "   URL: http://127.0.0.1:8000$endpoint\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000$endpoint");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Status: $httpCode\n";
    
    if ($httpCode == 200) {
        echo "   ✅ Endpoint funcionando\n";
        $data = json_decode($response, true);
        if (isset($data['data'])) {
            echo "   Datos: " . count($data['data']) . " elementos\n";
        }
    } else {
        echo "   ❌ Endpoint falló\n";
        echo "   Respuesta: " . substr($response, 0, 200) . "\n";
    }
    
    echo "\n";
}

// Ejecutar tests
echo "Verificando que el servidor esté funcionando...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    echo "❌ Servidor no disponible en http://127.0.0.1:8000\n";
    echo "   Asegúrate de ejecutar: php artisan serve\n";
    exit(1);
}

echo "✅ Servidor funcionando\n\n";

// Test 1: Login Admin
$adminToken = testLogin('11111111', 'admin123', 'Administrador');

// Test 2: Login Profesor
$professorToken = testLogin('22222222', 'profesor123', 'Profesor');

// Test 3: Login inválido
testLogin('99999999', 'invalid', 'Credenciales inválidas');

// Test 4: Endpoints con token admin
if ($adminToken) {
    echo "=== TESTING ENDPOINTS ADMIN ===\n";
    testAdminEndpoint($adminToken, '/api/admin/users', 'Lista de usuarios');
    testAdminEndpoint($adminToken, '/api/admin/professors', 'Lista de profesores');
    testAdminEndpoint($adminToken, '/api/admin/audit', 'Logs de auditoría');
}

// Test 5: Endpoints gym con token profesor
if ($professorToken) {
    echo "=== TESTING ENDPOINTS GIMNASIO ===\n";
    testAdminEndpoint($professorToken, '/api/admin/gym/exercises', 'Lista de ejercicios');
    testAdminEndpoint($professorToken, '/api/admin/gym/daily-templates', 'Plantillas diarias');
}

// Test 6: Seguridad sin token
echo "=== TESTING SEGURIDAD ===\n";
echo "🔒 Probando acceso sin token\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/admin/users');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status: $httpCode\n";
if ($httpCode == 401) {
    echo "   ✅ Seguridad funcionando - Acceso bloqueado sin token\n";
} else {
    echo "   ❌ Problema de seguridad - Acceso permitido sin token\n";
}

echo "\n🎉 TESTING MANUAL COMPLETADO\n";
