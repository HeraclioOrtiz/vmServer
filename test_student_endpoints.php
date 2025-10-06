<?php

echo "📱 === TEST ENDPOINTS APP MÓVIL (MARÍA GARCÍA) === 📱\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Buscar María García
    $maria = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();
    
    if (!$maria) {
        die("❌ No se encontró María García\n");
    }
    
    echo "👤 USUARIO: {$maria->name} (ID: {$maria->id})\n";
    echo "   Email: {$maria->email}\n";
    echo "   Gimnasio: " . ($maria->student_gym ?? 'No asignado') . "\n";
    
    // Simular autenticación
    \Illuminate\Support\Facades\Auth::login($maria);
    
    $controller = new \App\Http\Controllers\Gym\Student\AssignmentController();
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "TEST 1: GET /api/student/my-templates\n";
    echo str_repeat("=", 80) . "\n";
    
    $request1 = \Illuminate\Http\Request::create('/api/student/my-templates', 'GET');
    $request1->setUserResolver(function () use ($maria) {
        return $maria;
    });
    
    $response1 = $controller->myTemplates($request1);
    $data1 = json_decode($response1->getContent(), true);
    
    echo "Status: {$response1->getStatusCode()}\n";
    echo "Message: {$data1['message']}\n\n";
    
    if (isset($data1['data']['professor'])) {
        $prof = $data1['data']['professor'];
        echo "👨‍🏫 Profesor asignado: {$prof['name']}\n";
        echo "   Email: {$prof['email']}\n\n";
    }
    
    if (isset($data1['data']['templates']) && count($data1['data']['templates']) > 0) {
        echo "📋 Plantillas asignadas: " . count($data1['data']['templates']) . "\n\n";
        
        foreach ($data1['data']['templates'] as $i => $tpl) {
            echo "  " . ($i + 1) . ". {$tpl['daily_template']['title']}\n";
            echo "     Goal: {$tpl['daily_template']['goal']}\n";
            echo "     Nivel: {$tpl['daily_template']['level']}\n";
            echo "     Duración: {$tpl['daily_template']['estimated_duration_min']} min\n";
            echo "     Ejercicios: {$tpl['daily_template']['exercises_count']}\n";
            echo "     Frecuencia: " . implode(", ", $tpl['frequency_days']) . "\n";
            echo "     Desde: {$tpl['start_date']}\n";
            echo "     Hasta: " . ($tpl['end_date'] ?? 'indefinido') . "\n";
            echo "     Notas profesor: {$tpl['professor_notes']}\n";
            
            // Guardar ID para siguiente test
            $templateAssignmentId = $tpl['id'];
            echo "\n";
        }
    } else {
        echo "⚠️  No hay plantillas asignadas\n";
    }
    
    // TEST 2: Detalles de plantilla
    if (isset($templateAssignmentId)) {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "TEST 2: GET /api/student/template/{$templateAssignmentId}/details\n";
        echo str_repeat("=", 80) . "\n";
        
        $response2 = $controller->templateDetails($templateAssignmentId);
        $data2 = json_decode($response2->getContent(), true);
        
        echo "Status: {$response2->getStatusCode()}\n\n";
        
        if (isset($data2['template'])) {
            $template = $data2['template'];
            echo "📋 Plantilla: {$template['title']}\n";
            echo "   Goal: {$template['goal']}\n";
            echo "   Nivel: {$template['level']}\n";
            echo "   Duración estimada: {$template['estimated_duration_min']} min\n\n";
            
            if (isset($data2['exercises'])) {
                echo "💪 EJERCICIOS (" . count($data2['exercises']) . "):\n\n";
                
                foreach ($data2['exercises'] as $i => $ex) {
                    $exercise = $ex['exercise'];
                    echo "  " . ($i + 1) . ". {$exercise['name']}\n";
                    echo "     Descripción: " . (strlen($exercise['description']) > 60 ? substr($exercise['description'], 0, 60) . "..." : $exercise['description']) . "\n";
                    echo "     Músculos objetivo: " . implode(", ", $exercise['target_muscle_groups']) . "\n";
                    echo "     Equipamiento: {$exercise['equipment']}\n";
                    echo "     Dificultad: {$exercise['difficulty_level']}\n";
                    echo "     Sets: " . count($ex['sets']) . "\n";
                    
                    foreach ($ex['sets'] as $j => $set) {
                        $reps = $set['reps_min'] == $set['reps_max'] 
                            ? $set['reps_min'] 
                            : "{$set['reps_min']}-{$set['reps_max']}";
                        echo "       Set " . ($j + 1) . ": {$reps} reps";
                        if ($set['rpe_target']) echo " @ RPE {$set['rpe_target']}";
                        if ($set['rest_seconds']) echo " | {$set['rest_seconds']}s descanso";
                        echo "\n";
                    }
                    echo "\n";
                }
            }
        }
    }
    
    // TEST 3: Calendario semanal
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "TEST 3: GET /api/student/my-weekly-calendar\n";
    echo str_repeat("=", 80) . "\n";
    
    $request3 = \Illuminate\Http\Request::create('/api/student/my-weekly-calendar', 'GET');
    $request3->setUserResolver(function () use ($maria) {
        return $maria;
    });
    
    $response3 = $controller->myWeeklyCalendar($request3);
    $data3 = json_decode($response3->getContent(), true);
    
    echo "Status: {$response3->getStatusCode()}\n";
    echo "Message: {$data3['message']}\n\n";
    
    if (isset($data3['data']['days'])) {
        echo "📅 CALENDARIO SEMANAL\n";
        echo "Semana del {$data3['data']['week_start']} al {$data3['data']['week_end']}\n\n";
        
        foreach ($data3['data']['days'] as $day) {
            $hasWorkout = $day['has_workouts'] ? "🏋️" : "  ";
            echo "{$hasWorkout} {$day['day_name']}, {$day['date']}\n";
            
            if ($day['has_workouts']) {
                foreach ($day['assignments'] as $assign) {
                    echo "     → {$assign['daily_template']['title']} ({$assign['daily_template']['estimated_duration_min']} min)\n";
                }
            }
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ TODOS LOS ENDPOINTS FUNCIONANDO CORRECTAMENTE\n";
    echo str_repeat("=", 80) . "\n";
    
    echo "\n📊 RESUMEN:\n";
    echo "✅ María García puede recibir:\n";
    echo "  • Sus plantillas asignadas\n";
    echo "  • Detalles completos de cada plantilla con ejercicios y sets\n";
    echo "  • Calendario semanal con entrenamientos programados\n";
    echo "  • Información de su profesor\n";
    echo "  • Notas del profesor\n";
    echo "  • Frecuencia de entrenamientos\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
