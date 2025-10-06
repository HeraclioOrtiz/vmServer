<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "👨‍🏫 === INFORMACIÓN DETALLADA DE PROFESORES === 👨‍🏫\n\n";

// 1. Profesores disponibles
$professors = \App\Models\User::where('is_professor', true)->orWhere('is_admin', true)->get();
echo "📊 PROFESORES DISPONIBLES: " . $professors->count() . "\n";
echo str_repeat("=", 60) . "\n";

foreach($professors as $professor) {
    echo "👨‍🏫 {$professor->name}\n";
    echo "   📧 Email: {$professor->email}\n";
    echo "   🆔 ID: {$professor->id}\n";
    echo "   👔 Tipo: " . ($professor->is_admin ? 'Administrador' : 'Profesor') . "\n";
    
    // Contar estudiantes asignados
    $studentCount = \App\Models\Gym\ProfessorStudentAssignment::where('professor_id', $professor->id)->count();
    echo "   👥 Estudiantes asignados: {$studentCount}\n\n";
}

// 2. Estudiantes del Profesor Juan Pérez (ID: 2)
echo "👤 ESTUDIANTES DEL PROFESOR JUAN PÉREZ:\n";
echo str_repeat("=", 60) . "\n";

$juanAssignments = \App\Models\Gym\ProfessorStudentAssignment::where('professor_id', 2)
    ->with('student')
    ->get();

echo "📊 Total estudiantes: " . $juanAssignments->count() . "\n\n";

foreach($juanAssignments as $assignment) {
    $student = $assignment->student;
    echo "👤 {$student->name}\n";
    echo "   📧 Email: {$student->email}\n";
    echo "   🆔 DNI: {$student->dni}\n";
    echo "   🆔 ID: {$student->id}\n";
    
    // Verificar plantillas asignadas a este estudiante
    $templateCount = \App\Models\Gym\TemplateAssignment::whereHas('professorStudentAssignment', function($query) use ($assignment) {
        $query->where('id', $assignment->id);
    })->count();
    
    echo "   📋 Plantillas asignadas: {$templateCount}\n\n";
}

// 3. Credenciales de login para estudiantes destacados
echo "🔑 CREDENCIALES DE LOGIN PRINCIPALES:\n";
echo str_repeat("=", 60) . "\n";

$mainStudents = \App\Models\User::whereIn('email', [
    'maria.garcia@villamitre.com',
    'carlos.rodriguez@villamitre.com', 
    'ana.martinez@villamitre.com'
])->get();

foreach($mainStudents as $student) {
    echo "👤 {$student->name}\n";
    echo "   📧 Email: {$student->email}\n";
    echo "   🆔 DNI: {$student->dni}\n";
    echo "   🔑 Password sugerido: " . strtolower(explode(' ', $student->name)[0]) . "123\n\n";
}

echo "📋 RESUMEN FINAL:\n";
echo str_repeat("=", 60) . "\n";
echo "👨‍🏫 Profesores activos: " . $professors->count() . "\n";
echo "👤 Estudiantes del Prof. Juan: " . $juanAssignments->count() . "\n";
echo "📋 Total plantillas en sistema: " . \App\Models\Gym\TemplateAssignment::count() . "\n";
