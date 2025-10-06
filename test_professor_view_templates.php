<?php

echo "🔍 === TESTING: PROFESOR VE PLANTILLAS DE ESTUDIANTES === 🔍\n\n";

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
    echo "🔐 PASO 1: Login como profesor...\n";
    
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '22222222',
        'password' => 'profesor123'
    ]);
    
    if ($loginResponse['status'] !== 200) {
        echo "❌ ERROR en login: Status {$loginResponse['status']}\n";
        exit(1);
    }
    
    $token = $loginResponse['data']['data']['token'];
    $professor = $loginResponse['data']['data']['user'];
    
    echo "✅ Login exitoso: {$professor['name']}\n\n";
    
    echo "👥 PASO 2: Consultando estudiantes asignados...\n";
    
    $studentsResponse = makeRequest('http://127.0.0.1:8000/api/professor/my-students', 'GET', null, $token);
    
    if ($studentsResponse['status'] !== 200) {
        echo "❌ ERROR consultando estudiantes: Status {$studentsResponse['status']}\n";
        print_r($studentsResponse['data']);
        exit(1);
    }
    
    $students = $studentsResponse['data']['data'];
    
    echo "📊 Total estudiantes asignados: " . count($students) . "\n\n";
    
    if (count($students) === 0) {
        echo "⚠️  No hay estudiantes asignados\n";
        exit(0);
    }
    
    echo "📋 ANÁLISIS DE LA RESPUESTA:\n";
    echo str_repeat("=", 80) . "\n";
    
    // Buscar específicamente a María García
    $mariaData = null;
    foreach ($students as $student) {
        if ($student['student']['name'] === 'Estudiante María García') {
            $mariaData = $student;
            break;
        }
    }
    
    if ($mariaData) {
        echo "👤 ESTUDIANTE: MARÍA GARCÍA\n";
        echo "   ID Asignación: {$mariaData['id']}\n";
        echo "   Estudiante ID: {$mariaData['student']['id']}\n";
        echo "   Nombre: {$mariaData['student']['name']}\n";
        echo "   Email: {$mariaData['student']['email']}\n";
        echo "   Estado: {$mariaData['status']}\n";
        echo "   Asignado desde: {$mariaData['start_date']}\n";
        
        // Verificar si incluye plantillas asignadas
        echo "\n🔍 VERIFICANDO PLANTILLAS EN LA RESPUESTA:\n";
        
        $responseKeys = array_keys($mariaData);
        echo "   Campos disponibles: " . implode(', ', $responseKeys) . "\n";
        
        // Buscar campos relacionados con plantillas
        $templateFields = array_filter($responseKeys, function($key) {
            return strpos(strtolower($key), 'template') !== false || 
                   strpos(strtolower($key), 'assignment') !== false ||
                   strpos(strtolower($key), 'plantilla') !== false;
        });
        
        if (!empty($templateFields)) {
            echo "   ✅ Campos de plantillas encontrados: " . implode(', ', $templateFields) . "\n";
            
            foreach ($templateFields as $field) {
                echo "   📋 {$field}: ";
                if (is_array($mariaData[$field])) {
                    echo "Array con " . count($mariaData[$field]) . " elementos\n";
                    if (count($mariaData[$field]) > 0) {
                        echo "      Primer elemento: " . json_encode($mariaData[$field][0]) . "\n";
                    }
                } else {
                    echo $mariaData[$field] . "\n";
                }
            }
        } else {
            echo "   ❌ NO se encontraron campos de plantillas en la respuesta\n";
        }
        
    } else {
        echo "⚠️  María García no encontrada en la respuesta\n";
    }
    
    echo "\n📊 PASO 3: Analizando estructura completa de respuesta...\n";
    
    // Analizar el primer estudiante como ejemplo
    $firstStudent = $students[0];
    echo "📋 ESTRUCTURA DEL PRIMER ESTUDIANTE:\n";
    echo json_encode($firstStudent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "🔍 PASO 4: Verificando endpoint específico para plantillas...\n";
    
    // Intentar consultar plantillas específicas de un estudiante
    if ($mariaData) {
        $studentTemplatesUrl = "http://127.0.0.1:8000/api/professor/student/{$mariaData['student']['id']}/templates";
        $templatesResponse = makeRequest($studentTemplatesUrl, 'GET', null, $token);
        
        echo "📋 Consultando: {$studentTemplatesUrl}\n";
        echo "   Status: {$templatesResponse['status']}\n";
        
        if ($templatesResponse['status'] === 200) {
            echo "   ✅ Endpoint específico funciona\n";
            $templates = isset($templatesResponse['data']['data']) ? $templatesResponse['data']['data'] : [];
            echo "   📊 Plantillas encontradas: " . count($templates) . "\n";
            
            if (count($templates) > 0) {
                foreach ($templates as $template) {
                    $title = isset($template['daily_template']['title']) ? $template['daily_template']['title'] : 'Sin título';
                    echo "      - {$title}\n";
                }
            }
        } else {
            echo "   ❌ Endpoint específico no disponible o error\n";
            if (isset($templatesResponse['data']['message'])) {
                echo "   Mensaje: {$templatesResponse['data']['message']}\n";
            }
        }
    }
    
    echo "\n🎯 CONCLUSIONES:\n";
    echo str_repeat("=", 80) . "\n";
    
    $includesTemplates = false;
    foreach ($students as $student) {
        $keys = array_keys($student);
        if (array_intersect($keys, ['templates', 'template_assignments', 'plantillas', 'assigned_templates'])) {
            $includesTemplates = true;
            break;
        }
    }
    
    if ($includesTemplates) {
        echo "✅ ÉXITO: El endpoint /api/professor/my-students INCLUYE plantillas asignadas\n";
        echo "✅ El profesor puede ver las plantillas de cada estudiante directamente\n";
    } else {
        echo "❌ LIMITACIÓN: El endpoint /api/professor/my-students NO incluye plantillas\n";
        echo "⚠️  El profesor necesita hacer consultas adicionales para ver plantillas\n";
        echo "💡 RECOMENDACIÓN: Modificar el endpoint para incluir plantillas asignadas\n";
    }
    
    echo "\n📋 INFORMACIÓN DISPONIBLE ACTUALMENTE:\n";
    echo "   ✅ Lista de estudiantes asignados\n";
    echo "   ✅ Información básica de cada estudiante\n";
    echo "   ✅ Estado de la asignación profesor-estudiante\n";
    
    if ($includesTemplates) {
        echo "   ✅ Plantillas asignadas a cada estudiante\n";
    } else {
        echo "   ❌ Plantillas asignadas (requiere consulta adicional)\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
