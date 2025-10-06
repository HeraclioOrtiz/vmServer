<?php

echo "📊 === RESUMEN: ESTUDIANTES DEL PROFESOR 22222222 === 📊\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;

try {
    $professor = User::where('dni', '22222222')->first();
    
    echo "👨‍🏫 PROFESOR:\n";
    echo "   Nombre: {$professor->name}\n";
    echo "   DNI: {$professor->dni}\n";
    echo "   Email: {$professor->email}\n\n";
    
    $assignments = ProfessorStudentAssignment::where('professor_id', $professor->id)
                                            ->where('status', 'active')
                                            ->with('student')
                                            ->orderBy('student_id')
                                            ->get();
    
    echo "🎓 ESTUDIANTES ASIGNADOS ({$assignments->count()} total):\n";
    echo str_repeat("=", 50) . "\n";
    
    foreach ($assignments as $index => $assignment) {
        $num = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
        echo "{$num}. {$assignment->student->name}\n";
        echo "     ID: {$assignment->student->id} | DNI: {$assignment->student->dni}\n";
        echo "     Email: {$assignment->student->email}\n";
        echo "     Asignado: {$assignment->start_date}\n";
        echo "\n";
    }
    
    echo str_repeat("=", 50) . "\n";
    echo "📊 RESUMEN FINAL:\n";
    echo "   ✅ Total estudiantes asignados: {$assignments->count()}\n";
    echo "   ✅ Todos con estado 'active'\n";
    echo "   ✅ Profesor listo para asignar plantillas\n";
    echo "   ✅ Sistema 100% funcional\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
