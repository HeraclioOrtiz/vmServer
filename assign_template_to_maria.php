<?php

echo "👩‍🎓 === ASIGNANDO PLANTILLA A MARÍA === 👩‍🎓\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Buscar usuarios
    $maria = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();
    $profesor = \App\Models\User::where('is_professor', true)->first();
    
    if (!$maria || !$profesor) {
        echo "❌ No se encontraron los usuarios necesarios\n";
        exit(1);
    }
    
    echo "👤 María García: {$maria->name} (ID: {$maria->id})\n";
    echo "👨‍🏫 Profesor: {$profesor->name} (ID: {$profesor->id})\n\n";
    
    // Buscar asignación profesor-estudiante existente
    $profStudentAssignment = \App\Models\Gym\ProfessorStudentAssignment::where([
        'professor_id' => $profesor->id,
        'student_id' => $maria->id
    ])->first();
    
    if (!$profStudentAssignment) {
        echo "🔗 Creando asignación profesor-estudiante...\n";
        $profStudentAssignment = \App\Models\Gym\ProfessorStudentAssignment::create([
            'professor_id' => $profesor->id,
            'student_id' => $maria->id,
            'assigned_by' => 1, // Admin
            'assigned_at' => now(),
            'notes' => 'Asignación para testing app móvil'
        ]);
        echo "  ✅ Asignación creada (ID: {$profStudentAssignment->id})\n";
    } else {
        echo "🔗 Asignación profesor-estudiante ya existe (ID: {$profStudentAssignment->id})\n";
    }
    
    // Buscar plantilla
    $template = \App\Models\Gym\DailyTemplate::first();
    
    if (!$template) {
        echo "❌ No hay plantillas disponibles\n";
        exit(1);
    }
    
    echo "📋 Plantilla a asignar: {$template->title} (ID: {$template->id})\n\n";
    
    // Verificar si ya existe una asignación de plantilla
    $existingAssignment = \App\Models\Gym\TemplateAssignment::where([
        'professor_student_assignment_id' => $profStudentAssignment->id,
        'daily_template_id' => $template->id
    ])->first();
    
    if ($existingAssignment) {
        echo "📋 Asignación de plantilla ya existe (ID: {$existingAssignment->id})\n";
        echo "📅 Estado: {$existingAssignment->status}\n";
    } else {
        echo "📋 Creando nueva asignación de plantilla...\n";
        
        $templateAssignment = \App\Models\Gym\TemplateAssignment::create([
            'professor_student_assignment_id' => $profStudentAssignment->id,
            'daily_template_id' => $template->id,
            'assigned_by' => $profesor->id,
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(21),
            'frequency' => [1, 3, 5], // Lunes, Miércoles, Viernes
            'professor_notes' => 'Plantilla de prueba con campos de peso actualizados',
            'status' => 'active'
        ]);
        
        echo "  ✅ Asignación creada (ID: {$templateAssignment->id})\n";
        echo "  📅 Frecuencia: Lunes, Miércoles, Viernes\n";
        echo "  📝 Notas: {$templateAssignment->professor_notes}\n";
    }
    
    echo "\n🔍 Verificando asignaciones de María...\n";
    
    $assignments = \App\Models\Gym\TemplateAssignment::with([
        'dailyTemplate.exercises.exercise',
        'dailyTemplate.exercises.sets'
    ])
    ->whereHas('professorStudentAssignment', function($query) use ($maria) {
        $query->where('student_id', $maria->id);
    })
    ->get();
    
    echo "📊 Total asignaciones: " . $assignments->count() . "\n\n";
    
    foreach ($assignments as $assignment) {
        $template = $assignment->dailyTemplate;
        echo "📋 {$template->title}\n";
        echo "   📊 Ejercicios: " . $template->exercises->count() . "\n";
        
        $totalSets = 0;
        $setsWithWeight = 0;
        
        foreach ($template->exercises as $exercise) {
            $totalSets += $exercise->sets->count();
            $setsWithWeight += $exercise->sets->whereNotNull('weight_target')->count();
            
            echo "   🏋️ {$exercise->exercise->name}: " . $exercise->sets->count() . " sets\n";
            foreach ($exercise->sets as $set) {
                echo "      Set {$set->set_number}: {$set->reps_min}-{$set->reps_max} reps";
                if ($set->weight_target) {
                    echo ", {$set->weight_target}kg objetivo";
                }
                echo "\n";
            }
        }
        
        echo "   📊 Total sets: {$totalSets}\n";
        echo "   🏋️ Sets con peso: {$setsWithWeight} (" . round($setsWithWeight/$totalSets*100, 1) . "%)\n";
        echo "   📅 Estado: {$assignment->status}\n\n";
    }
    
    echo "✅ MARÍA TIENE PLANTILLAS ASIGNADAS\n";
    echo "🚀 Ahora puede probar la API móvil\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
