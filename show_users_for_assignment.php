<?php

echo "👥 === USUARIOS DISPONIBLES PARA ASIGNACIÓN === 👥\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // PROFESORES
    echo "👨‍🏫 PROFESORES DISPONIBLES:\n";
    echo str_repeat("=", 80) . "\n";
    
    $professors = \App\Models\User::where('is_professor', true)->get();
    
    if ($professors->isEmpty()) {
        echo "❌ No hay profesores disponibles\n";
    } else {
        foreach ($professors as $prof) {
            echo "\n{$prof->id}. {$prof->name}\n";
            echo "   Email: {$prof->email}\n";
            echo "   DNI: {$prof->dni}\n";
        }
    }
    
    // ESTUDIANTES CON GIMNASIO
    echo "\n\n🏋️ ESTUDIANTES CON GIMNASIO (student_gym):\n";
    echo str_repeat("=", 80) . "\n";
    
    $students = \App\Models\User::where('is_professor', false)
        ->where('is_admin', false)
        ->whereNotNull('student_gym')
        ->get();
    
    if ($students->isEmpty()) {
        echo "❌ No hay estudiantes con gimnasio\n";
    } else {
        foreach ($students as $student) {
            echo "\n{$student->id}. {$student->name}\n";
            echo "   Email: {$student->email}\n";
            echo "   DNI: {$student->dni}\n";
            echo "   Gimnasio: {$student->student_gym}\n";
            
            // Ver si ya está asignado
            $assignment = \App\Models\Gym\ProfessorStudentAssignment::where('student_id', $student->id)
                ->where('status', 'active')
                ->first();
            
            if ($assignment) {
                $prof = \App\Models\User::find($assignment->professor_id);
                echo "   ⚠️  Ya asignado a: {$prof->name}\n";
            } else {
                echo "   ✅ Disponible para asignar\n";
            }
        }
    }
    
    // ADMIN
    echo "\n\n👤 ADMINISTRADOR:\n";
    echo str_repeat("=", 80) . "\n";
    
    $admin = \App\Models\User::where('is_admin', true)->first();
    
    if ($admin) {
        echo "\n{$admin->id}. {$admin->name}\n";
        echo "   Email: {$admin->email}\n";
    } else {
        echo "❌ No hay admin\n";
    }
    
    // PLANTILLAS DISPONIBLES
    echo "\n\n📋 PLANTILLAS DIARIAS DISPONIBLES:\n";
    echo str_repeat("=", 80) . "\n";
    
    $templates = \App\Models\Gym\DailyTemplate::all();
    
    foreach ($templates as $tpl) {
        echo "\n{$tpl->id}. {$tpl->title}\n";
        echo "   Goal: {$tpl->goal}\n";
        echo "   Nivel: {$tpl->level}\n";
        echo "   Duración: {$tpl->estimated_duration_min} min\n";
        echo "   Ejercicios: {$tpl->exercises->count()}\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
