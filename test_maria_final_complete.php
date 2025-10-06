<?php

echo "🎓 === TESTING FINAL COMPLETO: MARÍA GARCÍA VE SUS PLANTILLAS === 🎓\n\n";

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
    echo "🔐 PASO 1: Login como María García...\n";
    
    // Asegurar credenciales
    $maria = \App\Models\User::where('dni', '33333333')->first();
    if ($maria) {
        $maria->password = bcrypt('estudiante123');
        $maria->save();
        echo "✅ Credenciales actualizadas para: {$maria->name}\n";
    }
    
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '33333333',
        'password' => 'estudiante123'
    ]);
    
    if ($loginResponse['status'] !== 200) {
        echo "❌ ERROR en login: Status {$loginResponse['status']}\n";
        exit(1);
    }
    
    $token = $loginResponse['data']['data']['token'];
    $student = $loginResponse['data']['data']['user'];
    
    echo "✅ Login exitoso: {$student['name']}\n\n";
    
    echo "📋 PASO 2: Consultando mis plantillas asignadas...\n";
    
    $templatesResponse = makeRequest('http://127.0.0.1:8000/api/student/my-templates', 'GET', null, $token);
    
    echo "📊 Status: {$templatesResponse['status']}\n";
    
    if ($templatesResponse['status'] !== 200) {
        echo "❌ ERROR consultando plantillas\n";
        if (isset($templatesResponse['data']['message'])) {
            echo "   Mensaje: {$templatesResponse['data']['message']}\n";
        }
        if (isset($templatesResponse['data']['error'])) {
            echo "   Error: {$templatesResponse['data']['error']}\n";
        }
        exit(1);
    }
    
    $templatesData = $templatesResponse['data']['data'];
    
    echo "✅ Plantillas obtenidas exitosamente\n";
    echo "👨‍🏫 Profesor asignado: {$templatesData['professor']['name']}\n";
    echo "📧 Email profesor: {$templatesData['professor']['email']}\n";
    echo "📊 Total plantillas: " . count($templatesData['templates']) . "\n\n";
    
    if (count($templatesData['templates']) === 0) {
        echo "⚠️  No hay plantillas asignadas\n";
        exit(0);
    }
    
    echo "📋 PLANTILLAS ASIGNADAS A MARÍA GARCÍA:\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($templatesData['templates'] as $index => $template) {
        echo "📌 PLANTILLA #" . ($index + 1) . ":\n";
        echo "   🆔 ID Asignación: {$template['id']}\n";
        echo "   📝 Título: {$template['daily_template']['title']}\n";
        echo "   🎯 Objetivo: {$template['daily_template']['goal']}\n";
        echo "   📊 Nivel: {$template['daily_template']['level']}\n";
        echo "   ⏱️  Duración: {$template['daily_template']['estimated_duration_min']} minutos\n";
        echo "   🏋️ Ejercicios: {$template['daily_template']['exercises_count']}\n";
        echo "   📅 Inicio: {$template['start_date']}\n";
        echo "   📅 Fin: " . ($template['end_date'] ?: 'Sin fecha fin') . "\n";
        echo "   📅 Días: " . implode(', ', $template['frequency_days']) . "\n";
        echo "   👨‍🏫 Asignado por: {$template['assigned_by']['name']}\n";
        echo "   📝 Notas: " . ($template['professor_notes'] ?: 'Sin notas') . "\n";
        echo "   📊 Estado: {$template['status']}\n";
        echo "\n";
    }
    
    echo "🔍 PASO 3: Consultando detalles de la primera plantilla...\n";
    
    $firstTemplate = $templatesData['templates'][0];
    $templateId = $firstTemplate['id'];
    
    $detailsResponse = makeRequest("http://127.0.0.1:8000/api/student/template/{$templateId}/details", 'GET', null, $token);
    
    echo "📊 Status detalles: {$detailsResponse['status']}\n";
    
    if ($detailsResponse['status'] === 200) {
        echo "✅ Detalles obtenidos exitosamente\n";
        $details = $detailsResponse['data']['data'];
        
        echo "\n🏋️ EJERCICIOS DE LA PLANTILLA '{$details['template']['title']}':\n";
        echo str_repeat("=", 80) . "\n";
        
        foreach ($details['exercises'] as $exerciseIndex => $exercise) {
            echo "💪 EJERCICIO #" . ($exerciseIndex + 1) . " (Orden: {$exercise['order']}):\n";
            echo "   📝 Nombre: {$exercise['exercise']['name']}\n";
            echo "   📖 Descripción: " . ($exercise['exercise']['description'] ?: 'Sin descripción') . "\n";
            echo "   🎯 Músculos: " . implode(', ', $exercise['exercise']['target_muscle_groups'] ?? []) . "\n";
            echo "   🏋️ Equipo: " . implode(', ', $exercise['exercise']['equipment'] ?? []) . "\n";
            echo "   📊 Dificultad: {$exercise['exercise']['difficulty_level']}\n";
            
            if (count($exercise['sets']) > 0) {
                echo "   📊 Series (" . count($exercise['sets']) . "):\n";
                foreach ($exercise['sets'] as $set) {
                    echo "      Serie {$set['set_number']}: ";
                    if ($set['reps']) echo "{$set['reps']} reps";
                    if ($set['weight']) echo " x {$set['weight']}kg";
                    if ($set['duration']) echo " x {$set['duration']}s";
                    if ($set['rest_seconds']) echo " (descanso: {$set['rest_seconds']}s)";
                    echo "\n";
                    if ($set['notes']) echo "         Notas: {$set['notes']}\n";
                }
            }
            
            if ($exercise['notes']) {
                echo "   📝 Notas del ejercicio: {$exercise['notes']}\n";
            }
            echo "\n";
        }
        
        echo "👨‍🏫 INFORMACIÓN DEL PROFESOR:\n";
        echo "   Nombre: {$details['assignment_info']['assigned_by']['name']}\n";
        echo "   Email: {$details['assignment_info']['assigned_by']['email']}\n";
        echo "   Notas: " . ($details['assignment_info']['professor_notes'] ?: 'Sin notas') . "\n";
        
    } else {
        echo "❌ ERROR obteniendo detalles\n";
        if (isset($detailsResponse['data']['message'])) {
            echo "   Mensaje: {$detailsResponse['data']['message']}\n";
        }
    }
    
    echo "\n📅 PASO 4: Consultando calendario semanal...\n";
    
    $calendarResponse = makeRequest('http://127.0.0.1:8000/api/student/my-weekly-calendar', 'GET', null, $token);
    
    echo "📊 Status calendario: {$calendarResponse['status']}\n";
    
    if ($calendarResponse['status'] === 200) {
        echo "✅ Calendario obtenido exitosamente\n";
        $calendar = $calendarResponse['data']['data'];
        
        echo "\n📅 CALENDARIO SEMANAL ({$calendar['week_start']} - {$calendar['week_end']}):\n";
        echo str_repeat("=", 80) . "\n";
        
        foreach ($calendar['days'] as $day) {
            echo "📅 {$day['day_name']} ({$day['date']}):\n";
            
            if ($day['has_workouts']) {
                echo "   ✅ Entrenamientos programados: " . count($day['assignments']) . "\n";
                foreach ($day['assignments'] as $assignment) {
                    echo "      🏋️ {$assignment['daily_template']['title']}\n";
                    echo "         Duración: {$assignment['daily_template']['estimated_duration_min']} min\n";
                    echo "         Nivel: {$assignment['daily_template']['level']}\n";
                    if ($assignment['professor_notes']) {
                        echo "         Notas: {$assignment['professor_notes']}\n";
                    }
                }
            } else {
                echo "   💤 Día de descanso\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ ERROR obteniendo calendario\n";
    }
    
    echo "\n🎊 RESUMEN FINAL:\n";
    echo str_repeat("=", 80) . "\n";
    
    $templatesCount = count($templatesData['templates']);
    $hasDetails = $detailsResponse['status'] === 200;
    $hasCalendar = $calendarResponse['status'] === 200;
    
    echo "👤 ESTUDIANTE: María García\n";
    echo "🔐 Login: ✅ Exitoso\n";
    echo "👨‍🏫 Profesor asignado: ✅ {$templatesData['professor']['name']}\n";
    echo "📋 Plantillas asignadas: ✅ {$templatesCount}\n";
    echo "🏋️ Detalles de ejercicios: " . ($hasDetails ? '✅ Disponibles' : '❌ No disponibles') . "\n";
    echo "📅 Calendario semanal: " . ($hasCalendar ? '✅ Disponible' : '❌ No disponible') . "\n";
    
    if ($templatesCount > 0 && $hasDetails && $hasCalendar) {
        echo "\n🎉 ÉXITO COMPLETO:\n";
        echo "✅ María García puede ver todas sus plantillas asignadas\n";
        echo "✅ Puede ver quién se las asignó (profesor)\n";
        echo "✅ Puede ver los ejercicios detallados con series\n";
        echo "✅ Puede ver su calendario semanal de entrenamientos\n";
        echo "✅ Tiene acceso a notas del profesor\n";
        echo "✅ Ve frecuencias y fechas de entrenamiento\n";
        echo "✅ Sistema completamente funcional para estudiantes\n";
        
        echo "\n📱 FUNCIONALIDADES DISPONIBLES PARA EL FRONTEND:\n";
        echo "   📋 Lista de plantillas asignadas\n";
        echo "   🔍 Detalles completos de cada plantilla\n";
        echo "   🏋️ Ejercicios con series, pesos y repeticiones\n";
        echo "   📅 Calendario semanal interactivo\n";
        echo "   👨‍🏫 Información del profesor\n";
        echo "   📝 Notas y comentarios del profesor\n";
        
    } else {
        echo "\n⚠️  PROBLEMAS DETECTADOS:\n";
        if ($templatesCount === 0) {
            echo "❌ No puede ver plantillas asignadas\n";
        }
        if (!$hasDetails) {
            echo "❌ No puede ver detalles de ejercicios\n";
        }
        if (!$hasCalendar) {
            echo "❌ No puede ver calendario semanal\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
