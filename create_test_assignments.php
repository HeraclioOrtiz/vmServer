<?php

echo "📋 === CREANDO ASIGNACIONES DE PRUEBA === 📋\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Buscar usuarios
    $maria = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();
    $profesor = \App\Models\User::where('is_professor', true)->first();
    $admin = \App\Models\User::where('is_admin', true)->first();
    
    if (!$maria || !$profesor || !$admin) {
        echo "❌ No se encontraron todos los usuarios necesarios\n";
        exit(1);
    }
    
    echo "👤 María García encontrada (ID: {$maria->id})\n";
    echo "👨‍🏫 Profesor encontrado: {$profesor->name} (ID: {$profesor->id})\n";
    echo "👨‍💼 Admin encontrado: {$admin->name} (ID: {$admin->id})\n\n";
    
    // Buscar plantillas
    $templates = \App\Models\Gym\DailyTemplate::all();
    echo "📋 Plantillas disponibles: " . $templates->count() . "\n";
    
    if ($templates->isEmpty()) {
        echo "❌ No hay plantillas disponibles\n";
        exit(1);
    }
    
    foreach ($templates as $template) {
        echo "  • {$template->title} (ID: {$template->id})\n";
    }
    echo "\n";
    
    // 1. Crear asignación profesor-estudiante
    echo "🔗 Creando asignación profesor-estudiante...\n";
    
    $professorStudentAssignment = \App\Models\Gym\ProfessorStudentAssignment::firstOrCreate([
        'professor_id' => $profesor->id,
        'student_id' => $maria->id,
    ], [
        'assigned_by' => $admin->id,
        'assigned_at' => now(),
        'notes' => 'Asignación de prueba para testing de app móvil'
    ]);
    
    echo "  ✅ Asignación creada (ID: {$professorStudentAssignment->id})\n\n";
    
    // 2. Asignar plantilla a María
    echo "🏋️ Asignando plantilla a María...\n";
    
    $template = $templates->first(); // Usar la primera plantilla
    
    $templateAssignment = \App\Models\Gym\TemplateAssignment::create([
        'professor_student_assignment_id' => $professorStudentAssignment->id,
        'daily_template_id' => $template->id,
        'start_date' => now()->subDays(7), // Empezó hace una semana
        'end_date' => now()->addDays(21), // Termina en 3 semanas
        'frequency' => [1, 3, 5], // Lunes, Miércoles, Viernes
        'professor_notes' => 'Empezar con pesos moderados. Enfocarse en técnica correcta.',
        'status' => 'active',
        'assigned_at' => now(),
    ]);
    
    echo "  ✅ Plantilla '{$template->title}' asignada (ID: {$templateAssignment->id})\n";
    echo "  📅 Frecuencia: Lunes, Miércoles, Viernes\n";
    echo "  📝 Notas: {$templateAssignment->professor_notes}\n\n";
    
    // 3. Verificar la asignación
    echo "🔍 Verificando asignación...\n";
    
    $assignedTemplates = \App\Models\Gym\TemplateAssignment::with([
        'dailyTemplate.exercises.exercise',
        'dailyTemplate.exercises.sets'
    ])
    ->whereHas('professorStudentAssignment', function($query) use ($maria) {
        $query->where('student_id', $maria->id);
    })
    ->get();
    
    echo "📊 Plantillas asignadas a María: " . $assignedTemplates->count() . "\n";
    
    foreach ($assignedTemplates as $assignment) {
        $template = $assignment->dailyTemplate;
        echo "  • {$template->title}\n";
        echo "    📊 Ejercicios: " . $template->exercises->count() . "\n";
        
        $totalSets = 0;
        $setsWithWeight = 0;
        
        foreach ($template->exercises as $exercise) {
            $totalSets += $exercise->sets->count();
            $setsWithWeight += $exercise->sets->whereNotNull('weight_target')->count();
        }
        
        echo "    📊 Total sets: {$totalSets}\n";
        echo "    🏋️ Sets con peso: {$setsWithWeight} (" . round($setsWithWeight/$totalSets*100, 1) . "%)\n";
    }
    
    echo "\n✅ ASIGNACIONES CREADAS EXITOSAMENTE\n";
    echo "🚀 María García ahora puede ver sus plantillas en la app móvil\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
