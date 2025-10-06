<?php

echo "📋 === ASIGNAR PLANTILLA A ESTUDIANTE (PROFESOR) === 📋\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Buscar profesor
    $profesor = \App\Models\User::where('email', 'profesor@villamitre.com')->first();
    if (!$profesor) {
        die("❌ No se encontró el profesor\n");
    }
    
    // Buscar la asignación profesor-estudiante más reciente
    $professorStudentAssignment = \App\Models\Gym\ProfessorStudentAssignment::where('professor_id', $profesor->id)
        ->where('status', 'active')
        ->orderBy('id', 'desc')
        ->first();
    
    if (!$professorStudentAssignment) {
        die("❌ No hay asignaciones activas para este profesor\n");
    }
    
    // Buscar una plantilla diaria
    $template = \App\Models\Gym\DailyTemplate::where('title', 'Full Body - General')->first();
    if (!$template) {
        $template = \App\Models\Gym\DailyTemplate::first();
    }
    
    if (!$template) {
        die("❌ No hay plantillas diarias disponibles\n");
    }
    
    echo "📋 DATOS DE LA ASIGNACIÓN:\n";
    echo str_repeat("=", 80) . "\n";
    echo "Profesor: {$profesor->name} (ID: {$profesor->id})\n";
    echo "Estudiante: {$professorStudentAssignment->student->name} (ID: {$professorStudentAssignment->student_id})\n";
    echo "Plantilla: {$template->title} (ID: {$template->id})\n";
    echo "  Goal: {$template->goal}\n";
    echo "  Nivel: {$template->level}\n";
    echo "  Duración: {$template->estimated_duration_min} min\n";
    echo "  Ejercicios: {$template->exercises->count()}\n";
    
    // Configurar frecuencia
    echo "\n📅 FRECUENCIA:\n";
    echo str_repeat("=", 80) . "\n";
    $frequency = [1, 3, 5]; // Lunes, Miércoles, Viernes
    $frequencyNames = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ];
    
    echo "Días seleccionados: ";
    foreach ($frequency as $day) {
        echo $frequencyNames[$day] . ", ";
    }
    echo "\n";
    
    // Configurar período
    $startDate = now();
    $endDate = now()->addWeeks(4); // 4 semanas
    
    echo "\n📆 PERÍODO:\n";
    echo str_repeat("=", 80) . "\n";
    echo "Desde: {$startDate->format('Y-m-d')} ({$startDate->format('l, d/m/Y')})\n";
    echo "Hasta: {$endDate->format('Y-m-d')} ({$endDate->format('l, d/m/Y')})\n";
    echo "Duración: 4 semanas\n";
    
    // Simular autenticación como profesor
    \Illuminate\Support\Facades\Auth::login($profesor);
    
    // Crear asignación usando el service
    echo "\n🔄 Creando asignación de plantilla...\n";
    
    $assignmentService = app(\App\Services\Gym\AssignmentService::class);
    
    $data = [
        'professor_student_assignment_id' => $professorStudentAssignment->id,
        'daily_template_id' => $template->id,
        'assigned_by' => $profesor->id,
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
        'frequency' => $frequency,
        'status' => 'active',
        'professor_notes' => 'Empezar con pesos moderados. Enfocarse en técnica correcta.'
    ];
    
    $templateAssignment = $assignmentService->assignTemplateToStudent($data);
    
    echo "✅ ASIGNACIÓN DE PLANTILLA CREADA EXITOSAMENTE\n\n";
    echo "📊 DETALLES:\n";
    echo str_repeat("=", 80) . "\n";
    echo "ID Asignación: {$templateAssignment->id}\n";
    echo "Plantilla: {$templateAssignment->dailyTemplate->title}\n";
    echo "Estudiante: {$templateAssignment->professorStudentAssignment->student->name}\n";
    echo "Profesor: {$profesor->name}\n";
    echo "Fecha inicio: {$templateAssignment->start_date->format('Y-m-d')}\n";
    echo "Fecha fin: {$templateAssignment->end_date->format('Y-m-d')}\n";
    echo "Frecuencia: " . implode(", ", array_map(fn($d) => $frequencyNames[$d], $templateAssignment->frequency)) . "\n";
    echo "Status: {$templateAssignment->status}\n";
    echo "Notas profesor: {$templateAssignment->professor_notes}\n";
    
    // Contar sesiones generadas
    $sessionsCount = \App\Models\Gym\AssignmentProgress::where('daily_assignment_id', $templateAssignment->id)->count();
    
    echo "\n🗓️ SESIONES GENERADAS AUTOMÁTICAMENTE:\n";
    echo str_repeat("=", 80) . "\n";
    echo "Total de sesiones programadas: {$sessionsCount}\n\n";
    
    // Mostrar primeras 5 sesiones
    $sessions = \App\Models\Gym\AssignmentProgress::where('daily_assignment_id', $templateAssignment->id)
        ->orderBy('scheduled_date')
        ->limit(10)
        ->get();
    
    echo "Primeras 10 sesiones:\n";
    foreach ($sessions as $i => $session) {
        $dayName = $session->scheduled_date->format('l');
        $dateFormatted = $session->scheduled_date->format('d/m/Y');
        echo "  " . ($i + 1) . ". {$dateFormatted} ({$dayName}) - {$session->status}\n";
    }
    
    if ($sessionsCount > 10) {
        echo "  ... y " . ($sessionsCount - 10) . " sesiones más\n";
    }
    
    echo "\n✅ PROCESO COMPLETADO\n";
    echo "El estudiante ahora tiene {$sessionsCount} sesiones programadas.\n";
    echo "Cada sesión incluirá los ejercicios de la plantilla '{$template->title}':\n";
    
    foreach ($template->exercises as $i => $te) {
        echo "  " . ($i + 1) . ". {$te->exercise->name} ({$te->sets->count()} sets)\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\n";
    echo $e->getTraceAsString() . "\n";
}
