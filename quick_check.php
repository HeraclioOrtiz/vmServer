<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "👨‍🏫 PROFESORES:\n";
$professors = \App\Models\User::where('is_professor', true)->orWhere('is_admin', true)->get();
foreach($professors as $p) {
    echo "- {$p->name} (ID: {$p->id}, Email: {$p->email})\n";
}

echo "\n🔗 ASIGNACIONES:\n";
$assignments = \App\Models\Gym\ProfessorStudentAssignment::with(['professor', 'student'])->get();
echo "Total: " . $assignments->count() . "\n";
foreach($assignments as $a) {
    echo "- Profesor: {$a->professor->name} → Estudiante: {$a->student->name} ({$a->student->email})\n";
}

echo "\n📋 PLANTILLAS ASIGNADAS:\n";
$templates = \App\Models\Gym\TemplateAssignment::with(['dailyTemplate', 'professorStudentAssignment.student'])->get();
echo "Total: " . $templates->count() . "\n";
foreach($templates as $t) {
    echo "- {$t->dailyTemplate->title} → {$t->professorStudentAssignment->student->name}\n";
}
