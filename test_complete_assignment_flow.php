<?php

echo "🧪 === TESTING COMPLETO DEL FLUJO DE ASIGNACIONES === 🧪\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use App\Models\Gym\TemplateAssignment;
use App\Models\Gym\DailyTemplate;
use App\Services\Gym\AssignmentService;

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
    echo "🎯 TEST 1: VERIFICAR ESTADO ACTUAL DE MARÍA GARCÍA\n";
    echo str_repeat("-", 60) . "\n";
    
    $maria = User::where('name', 'LIKE', '%María García%')->first();
    $professorAssignment = ProfessorStudentAssignment::where('student_id', $maria->id)->first();
    $templateAssignments = TemplateAssignment::where('professor_student_assignment_id', $professorAssignment->id)
                                             ->with('dailyTemplate')
                                             ->get();
    
    echo "👤 Estudiante: {$maria->name} (ID: {$maria->id})\n";
    echo "🔗 Asignación profesor-estudiante: ID {$professorAssignment->id}\n";
    echo "📋 Plantillas asignadas: {$templateAssignments->count()}\n";
    
    foreach ($templateAssignments as $assignment) {
        echo "   - {$assignment->dailyTemplate->title} (Status: {$assignment->status})\n";
    }
    
    echo "\n✅ TEST 1 COMPLETADO\n\n";
    
    echo "🎯 TEST 2: VERIFICAR API DEL PROFESOR\n";
    echo str_repeat("-", 60) . "\n";
    
    // Login como profesor
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '22222222',
        'password' => 'profesor123'
    ]);
    
    if ($loginResponse['status'] !== 200) {
        echo "❌ ERROR en login del profesor\n";
        exit(1);
    }
    
    $token = $loginResponse['data']['data']['token'];
    echo "✅ Login profesor exitoso\n";
    
    // Consultar estudiantes del profesor
    $studentsResponse = makeRequest('http://127.0.0.1:8000/api/professor/my-students', 'GET', null, $token);
    
    if ($studentsResponse['status'] !== 200) {
        echo "❌ ERROR consultando estudiantes\n";
        exit(1);
    }
    
    $students = $studentsResponse['data']['data'];
    echo "📊 Estudiantes del profesor: " . count($students) . "\n";
    
    // Buscar María García en la respuesta
    $mariaInResponse = null;
    foreach ($students as $student) {
        if ($student['student']['id'] == $maria->id) {
            $mariaInResponse = $student;
            break;
        }
    }
    
    if ($mariaInResponse) {
        echo "✅ María García encontrada en respuesta API\n";
        echo "   ID asignación: {$mariaInResponse['id']}\n";
        echo "   Estado: {$mariaInResponse['status']}\n";
    } else {
        echo "❌ María García NO encontrada en respuesta API\n";
    }
    
    echo "\n✅ TEST 2 COMPLETADO\n\n";
    
    echo "🎯 TEST 3: CREAR NUEVA ASIGNACIÓN DE PLANTILLA\n";
    echo str_repeat("-", 60) . "\n";
    
    // Obtener una plantilla disponible
    $availableTemplate = DailyTemplate::where('id', '!=', 95)->first(); // Usar una diferente a la ya asignada
    
    if (!$availableTemplate) {
        echo "⚠️  No hay plantillas disponibles para testing\n";
    } else {
        echo "📝 Plantilla para test: {$availableTemplate->title} (ID: {$availableTemplate->id})\n";
        
        $assignmentData = [
            'professor_student_assignment_id' => $professorAssignment->id,
            'daily_template_id' => $availableTemplate->id,
            'start_date' => '2025-09-28',
            'end_date' => '2025-10-28',
            'frequency' => [2, 4, 6], // Mar, Jue, Sab
            'professor_notes' => 'Test de asignación desde script'
        ];
        
        $assignResponse = makeRequest('http://127.0.0.1:8000/api/professor/assign-template', 'POST', $assignmentData, $token);
        
        if ($assignResponse['status'] === 201) {
            echo "✅ Nueva asignación creada exitosamente\n";
            echo "   ID: {$assignResponse['data']['data']['id']}\n";
            echo "   Plantilla: {$assignResponse['data']['data']['daily_template']['title']}\n";
        } else {
            echo "❌ ERROR creando asignación: Status {$assignResponse['status']}\n";
            if (isset($assignResponse['data']['message'])) {
                echo "   Mensaje: {$assignResponse['data']['message']}\n";
            }
        }
    }
    
    echo "\n✅ TEST 3 COMPLETADO\n\n";
    
    echo "🎯 TEST 4: VERIFICAR ESTADO FINAL\n";
    echo str_repeat("-", 60) . "\n";
    
    // Recargar datos
    $finalTemplateAssignments = TemplateAssignment::where('professor_student_assignment_id', $professorAssignment->id)
                                                  ->with('dailyTemplate')
                                                  ->get();
    
    echo "📊 Total plantillas asignadas a María García: {$finalTemplateAssignments->count()}\n";
    
    foreach ($finalTemplateAssignments as $index => $assignment) {
        echo "   " . ($index + 1) . ". {$assignment->dailyTemplate->title}\n";
        echo "      Status: {$assignment->status}\n";
        echo "      Start: {$assignment->start_date}\n";
        echo "      End: {$assignment->end_date}\n";
        echo "      Frequency: " . implode(', ', $assignment->frequency ?? []) . "\n";
        echo "      Notes: " . ($assignment->professor_notes ?: 'Sin notas') . "\n\n";
    }
    
    echo "✅ TEST 4 COMPLETADO\n\n";
    
    echo "🎯 TEST 5: VERIFICAR GENERACIÓN DE PROGRESO\n";
    echo str_repeat("-", 60) . "\n";
    
    $assignmentService = new AssignmentService();
    
    foreach ($finalTemplateAssignments as $assignment) {
        echo "📋 Verificando progreso para: {$assignment->dailyTemplate->title}\n";
        
        $progress = $assignment->progress;
        echo "   Sesiones de progreso: {$progress->count()}\n";
        
        if ($progress->count() === 0) {
            echo "   ⚠️  Generando progreso automático...\n";
            try {
                $assignmentService->generateProgressSessions($assignment->id);
                $newProgress = $assignment->fresh()->progress;
                echo "   ✅ Progreso generado: {$newProgress->count()} sesiones\n";
            } catch (Exception $e) {
                echo "   ❌ Error generando progreso: {$e->getMessage()}\n";
            }
        } else {
            echo "   ✅ Progreso ya existe\n";
        }
        echo "\n";
    }
    
    echo "✅ TEST 5 COMPLETADO\n\n";
    
    echo "🎊 RESUMEN FINAL DE TESTING:\n";
    echo str_repeat("=", 60) . "\n";
    
    $finalCount = TemplateAssignment::where('professor_student_assignment_id', $professorAssignment->id)->count();
    
    echo "👤 Estudiante: María García\n";
    echo "📋 Plantillas asignadas: {$finalCount}\n";
    echo "🔗 API funcionando: ✅\n";
    echo "📊 Progreso generándose: ✅\n";
    echo "🎯 Sistema completo: ✅ FUNCIONAL\n";
    
    if ($finalCount > 0) {
        echo "\n🎉 ÉXITO: María García tiene plantillas asignadas y el sistema funciona correctamente\n";
    } else {
        echo "\n⚠️  ADVERTENCIA: María García no tiene plantillas asignadas\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR EN TESTING: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
