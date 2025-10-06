<?php

echo "👨‍🏫 === CONFIRMACIÓN: PROFESOR VE PLANTILLAS DE ESTUDIANTES === 👨‍🏫\n\n";

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
    echo "🔐 PASO 1: Autenticación del profesor...\n";
    
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '22222222',
        'password' => 'profesor123'
    ]);
    
    if ($loginResponse['status'] !== 200) {
        echo "❌ ERROR en login\n";
        exit(1);
    }
    
    $token = $loginResponse['data']['data']['token'];
    echo "✅ Login exitoso\n\n";
    
    echo "👥 PASO 2: Consultando estudiantes con plantillas...\n";
    
    $studentsResponse = makeRequest('http://127.0.0.1:8000/api/professor/my-students', 'GET', null, $token);
    
    if ($studentsResponse['status'] !== 200) {
        echo "❌ ERROR consultando estudiantes\n";
        exit(1);
    }
    
    $responseData = $studentsResponse['data'];
    $students = $responseData['data'];
    
    echo "✅ Respuesta recibida exitosamente\n";
    echo "📊 Total estudiantes: " . count($students) . "\n\n";
    
    echo "🔍 PASO 3: Analizando datos de María García...\n";
    
    $mariaFound = false;
    $mariaData = null;
    
    foreach ($students as $studentAssignment) {
        if (isset($studentAssignment['student']['name']) && 
            strpos($studentAssignment['student']['name'], 'María García') !== false) {
            $mariaFound = true;
            $mariaData = $studentAssignment;
            break;
        }
    }
    
    if (!$mariaFound) {
        echo "❌ María García no encontrada en la respuesta\n";
        exit(1);
    }
    
    echo "✅ María García encontrada\n";
    echo "👤 Nombre: {$mariaData['student']['name']}\n";
    echo "🆔 ID Estudiante: {$mariaData['student']['id']}\n";
    echo "📧 Email: {$mariaData['student']['email']}\n";
    echo "📅 Asignado desde: {$mariaData['start_date']}\n";
    echo "📊 Estado: {$mariaData['status']}\n\n";
    
    echo "📋 PASO 4: Verificando plantillas incluidas...\n";
    
    if (isset($mariaData['template_assignments'])) {
        $templates = $mariaData['template_assignments'];
        echo "✅ Campo 'template_assignments' encontrado\n";
        echo "📊 Número de plantillas: " . count($templates) . "\n\n";
        
        if (count($templates) > 0) {
            echo "📋 PLANTILLAS ASIGNADAS A MARÍA GARCÍA:\n";
            echo str_repeat("=", 60) . "\n";
            
            foreach ($templates as $index => $template) {
                echo "📌 PLANTILLA #" . ($index + 1) . ":\n";
                echo "   🆔 ID: {$template['id']}\n";
                
                if (isset($template['daily_template'])) {
                    echo "   📝 Título: {$template['daily_template']['title']}\n";
                    echo "   🎯 Objetivo: {$template['daily_template']['goal']}\n";
                    echo "   📊 Nivel: {$template['daily_template']['level']}\n";
                    echo "   ⏱️  Duración: {$template['daily_template']['estimated_duration_min']} min\n";
                } else {
                    echo "   ⚠️  Detalles de plantilla no incluidos\n";
                }
                
                echo "   📅 Inicio: {$template['start_date']}\n";
                echo "   📅 Fin: " . ($template['end_date'] ?: 'Sin fecha fin') . "\n";
                echo "   📊 Estado: {$template['status']}\n";
                
                if (isset($template['frequency'])) {
                    $days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                    $frequencyDays = array_map(function($day) use ($days) {
                        return $days[$day] ?? $day;
                    }, $template['frequency']);
                    echo "   📅 Frecuencia: " . implode(', ', $frequencyDays) . "\n";
                }
                
                echo "   📝 Notas: " . ($template['professor_notes'] ?: 'Sin notas') . "\n";
                echo "\n";
            }
        } else {
            echo "⚠️  María García no tiene plantillas asignadas\n";
        }
    } else {
        echo "❌ Campo 'template_assignments' NO encontrado en la respuesta\n";
        echo "🔍 Campos disponibles: " . implode(', ', array_keys($mariaData)) . "\n";
    }
    
    echo "\n🎯 PASO 5: Resumen de capacidades del profesor...\n";
    echo str_repeat("=", 60) . "\n";
    
    $canSeeTemplates = isset($mariaData['template_assignments']);
    $hasTemplateDetails = $canSeeTemplates && 
                         count($mariaData['template_assignments']) > 0 && 
                         isset($mariaData['template_assignments'][0]['daily_template']);
    
    echo "📊 CAPACIDADES DEL PROFESOR:\n";
    echo "   ✅ Ver lista de estudiantes asignados\n";
    echo "   ✅ Ver información básica de estudiantes\n";
    echo "   ✅ Ver estado de asignaciones\n";
    
    if ($canSeeTemplates) {
        echo "   ✅ Ver plantillas asignadas a cada estudiante\n";
        
        if ($hasTemplateDetails) {
            echo "   ✅ Ver detalles completos de cada plantilla\n";
            echo "   ✅ Ver frecuencia y fechas de entrenamiento\n";
            echo "   ✅ Ver notas del profesor\n";
        } else {
            echo "   ⚠️  Ver solo IDs de plantillas (sin detalles)\n";
        }
    } else {
        echo "   ❌ NO puede ver plantillas asignadas directamente\n";
        echo "   💡 Necesitaría consultas adicionales\n";
    }
    
    echo "\n🎊 CONCLUSIÓN FINAL:\n";
    echo str_repeat("=", 60) . "\n";
    
    if ($canSeeTemplates && $hasTemplateDetails) {
        echo "🎉 ÉXITO COMPLETO:\n";
        echo "✅ El profesor PUEDE ver todas las plantillas asignadas\n";
        echo "✅ Incluye detalles completos de cada plantilla\n";
        echo "✅ Incluye frecuencias, fechas y notas\n";
        echo "✅ La interfaz puede mostrar toda la información\n";
        echo "✅ No se necesitan consultas adicionales\n";
    } elseif ($canSeeTemplates) {
        echo "⚠️  ÉXITO PARCIAL:\n";
        echo "✅ El profesor puede ver que hay plantillas asignadas\n";
        echo "❌ Pero no ve los detalles completos\n";
        echo "💡 Recomendación: Mejorar la carga de relaciones\n";
    } else {
        echo "❌ LIMITACIÓN DETECTADA:\n";
        echo "❌ El profesor NO puede ver plantillas directamente\n";
        echo "💡 Recomendación: Modificar el endpoint para incluir plantillas\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
