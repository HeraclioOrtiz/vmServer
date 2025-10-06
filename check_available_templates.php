<?php

echo "📋 === PLANTILLAS DISPONIBLES PARA ASIGNAR === 📋\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\Gym\DailyTemplate;

try {
    echo "🔍 CONSULTANDO PLANTILLAS DIARIAS DISPONIBLES...\n\n";
    
    $templates = DailyTemplate::with(['exercises.exercise', 'exercises.sets'])
                              ->orderBy('title')
                              ->get();
    
    echo "📊 Total plantillas disponibles: {$templates->count()}\n\n";
    
    if ($templates->count() === 0) {
        echo "⚠️  No hay plantillas disponibles\n";
        exit(0);
    }
    
    echo "📋 LISTA DE PLANTILLAS:\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($templates as $index => $template) {
        echo "📌 PLANTILLA #" . ($index + 1) . ":\n";
        echo "   🆔 ID: {$template->id}\n";
        echo "   📝 Título: " . ($template->title ?: 'Sin título') . "\n";
        echo "   🎯 Objetivo: " . ($template->goal ?: 'No especificado') . "\n";
        echo "   📊 Nivel: " . ($template->level ?: 'No especificado') . "\n";
        echo "   ⏱️  Duración estimada: " . ($template->estimated_duration_min ? $template->estimated_duration_min . ' min' : 'No especificada') . "\n";
        echo "   🏋️ Ejercicios: {$template->exercises->count()}\n";
        
        if ($template->exercises->count() > 0) {
            echo "   📋 Lista de ejercicios:\n";
            foreach ($template->exercises->take(5) as $templateExercise) {
                $exercise = $templateExercise->exercise;
                $setsCount = $templateExercise->sets->count();
                echo "      - {$exercise->name} ({$setsCount} series)\n";
            }
            if ($template->exercises->count() > 5) {
                echo "      ... y " . ($template->exercises->count() - 5) . " ejercicios más\n";
            }
        }
        
        echo "   📅 Creada: {$template->created_at}\n";
        echo "\n" . str_repeat("-", 80) . "\n";
    }
    
    echo "\n🎯 PLANTILLAS RECOMENDADAS PARA MARÍA GARCÍA:\n";
    
    // Filtrar plantillas por nivel principiante o intermedio
    $beginnerTemplates = $templates->filter(function($template) {
        return in_array(strtolower($template->level ?? ''), ['beginner', 'principiante', 'intermediate', 'intermedio']);
    });
    
    if ($beginnerTemplates->count() > 0) {
        echo "📋 Plantillas apropiadas para principiante/intermedio:\n";
        foreach ($beginnerTemplates->take(3) as $template) {
            echo "   ✅ {$template->title} (Nivel: {$template->level}, {$template->exercises->count()} ejercicios)\n";
        }
    } else {
        echo "📋 Todas las plantillas están disponibles para asignar\n";
        foreach ($templates->take(3) as $template) {
            echo "   ✅ ID {$template->id}: {$template->title} ({$template->exercises->count()} ejercicios)\n";
        }
    }
    
    echo "\n🚀 PARA ASIGNAR UNA PLANTILLA A MARÍA GARCÍA:\n";
    echo "   1. Login como profesor (DNI: 22222222, password: profesor123)\n";
    echo "   2. Usar endpoint: POST /api/professor/assign-template\n";
    echo "   3. Datos requeridos:\n";
    echo "      {\n";
    echo "        \"professor_student_assignment_id\": 1,\n";
    echo "        \"daily_template_id\": [ID_DE_PLANTILLA],\n";
    echo "        \"start_date\": \"2025-09-27\",\n";
    echo "        \"frequency\": [1, 3, 5],\n";
    echo "        \"professor_notes\": \"Rutina inicial para María\"\n";
    echo "      }\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
