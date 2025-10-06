<?php

echo "👨‍🏫 === ASIGNAR ESTUDIANTE A PROFESOR (ADMIN) === 👨‍🏫\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Buscar admin
    $admin = \App\Models\User::where('is_admin', true)->first();
    if (!$admin) {
        die("❌ No hay admin en el sistema\n");
    }
    
    // Buscar profesor
    $profesor = \App\Models\User::where('email', 'profesor@villamitre.com')->first();
    if (!$profesor) {
        die("❌ No se encontró el profesor\n");
    }
    
    // Buscar estudiante con gimnasio
    $estudiante = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();
    
    if (!$estudiante) {
        die("❌ No se encontró estudiante María García\n");
    }
    
    // Verificar si ya tiene asignación activa
    $existingAssignment = \App\Models\Gym\ProfessorStudentAssignment::where('student_id', $estudiante->id)
        ->where('status', 'active')
        ->first();
    
    if ($existingAssignment) {
        echo "⚠️  María García ya tiene una asignación activa. Cancelándola...\n";
        $existingAssignment->update(['status' => 'cancelled']);
    }
    
    echo "📋 DATOS DE LA ASIGNACIÓN:\n";
    echo str_repeat("=", 80) . "\n";
    echo "Admin: {$admin->name} (ID: {$admin->id})\n";
    echo "Profesor: {$profesor->name} (ID: {$profesor->id})\n";
    echo "Estudiante: {$estudiante->name} (ID: {$estudiante->id})\n";
    echo "  Email: {$estudiante->email}\n";
    echo "  Gimnasio: {$estudiante->student_gym}\n";
    
    // Crear asignación usando el service
    echo "\n🔄 Creando asignación...\n";
    
    $assignmentService = app(\App\Services\Gym\AssignmentService::class);
    
    $data = [
        'professor_id' => $profesor->id,
        'student_id' => $estudiante->id,
        'assigned_by' => $admin->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addMonths(3)->format('Y-m-d'), // 3 meses
        'status' => 'active',
        'admin_notes' => 'Asignación creada desde script de prueba'
    ];
    
    $assignment = $assignmentService->assignStudentToProfessor($data);
    
    echo "✅ ASIGNACIÓN CREADA EXITOSAMENTE\n\n";
    echo "📊 DETALLES:\n";
    echo str_repeat("=", 80) . "\n";
    echo "ID: {$assignment->id}\n";
    echo "Profesor: {$assignment->professor->name}\n";
    echo "Estudiante: {$assignment->student->name}\n";
    echo "Asignado por: {$assignment->assignedBy->name}\n";
    echo "Fecha inicio: {$assignment->start_date->format('Y-m-d')}\n";
    echo "Fecha fin: {$assignment->end_date->format('Y-m-d')}\n";
    echo "Status: {$assignment->status}\n";
    echo "Notas: {$assignment->admin_notes}\n";
    
    echo "\n🎯 PRÓXIMO PASO:\n";
    echo "Ahora el profesor puede asignar una plantilla diaria a este estudiante.\n";
    echo "Usar ID de asignación: {$assignment->id}\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
