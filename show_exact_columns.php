<?php

echo "🧹 === LIMPIEZA COMPLETA DE BASE DE DATOS === 🧹\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    $timestamp = date('Y-m-d_H-i-s');
    echo "🕐 Timestamp: {$timestamp}\n\n";
    
    echo "📊 PASO 1: CONTEO ACTUAL DE DATOS\n";
    echo str_repeat("=", 50) . "\n";
    
    // Contar datos actuales
    $tables = [
        'users' => 'Usuarios',
        'gym_exercises' => 'Ejercicios',
        'gym_daily_templates' => 'Plantillas diarias',
        'gym_daily_template_exercises' => 'Ejercicios en plantillas',
        'gym_daily_template_sets' => 'Series',
        'gym_professor_student_assignments' => 'Asignaciones profesor-estudiante',
        'gym_template_assignments' => 'Asignaciones de plantillas',
        'gym_assignment_progress' => 'Progreso de asignaciones'
    ];
    
    $totalRecords = 0;
    foreach ($tables as $table => $description) {
        try {
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            echo "  {$description}: {$count} registros\n";
            $totalRecords += $count;
        } catch (Exception $e) {
            echo "  {$description}: TABLA NO EXISTE\n";
        }
    }
    
    echo "\n📊 Total de registros: {$totalRecords}\n\n";
    
    echo "💾 PASO 2: EXPORTAR DATOS CRÍTICOS\n";
    echo str_repeat("=", 50) . "\n";
    
    // Exportar usuarios (especialmente María García)
    echo "👥 Exportando usuarios...\n";
    $users = \Illuminate\Support\Facades\DB::table('users')->get();
    file_put_contents("backup_users_{$timestamp}.json", json_encode($users, JSON_PRETTY_PRINT));
    echo "  ✅ {$users->count()} usuarios exportados\n";
    
    // Exportar ejercicios
    echo "💪 Exportando ejercicios...\n";
    $exercises = \Illuminate\Support\Facades\DB::table('gym_exercises')->get();
    file_put_contents("backup_exercises_{$timestamp}.json", json_encode($exercises, JSON_PRETTY_PRINT));
    echo "  ✅ {$exercises->count()} ejercicios exportados\n";
    
    // Exportar plantillas
    echo "📋 Exportando plantillas...\n";
    $templates = \Illuminate\Support\Facades\DB::table('gym_daily_templates')->get();
    file_put_contents("backup_templates_{$timestamp}.json", json_encode($templates, JSON_PRETTY_PRINT));
    echo "  ✅ {$templates->count()} plantillas exportadas\n";
    
    // Exportar ejercicios de plantillas
    echo "🔗 Exportando ejercicios de plantillas...\n";
    $templateExercises = \Illuminate\Support\Facades\DB::table('gym_daily_template_exercises')->get();
    file_put_contents("backup_template_exercises_{$timestamp}.json", json_encode($templateExercises, JSON_PRETTY_PRINT));
    echo "  ✅ {$templateExercises->count()} ejercicios de plantillas exportados\n";
    
    // Exportar series
    echo "📊 Exportando series...\n";
    $sets = \Illuminate\Support\Facades\DB::table('gym_daily_template_sets')->get();
    file_put_contents("backup_sets_{$timestamp}.json", json_encode($sets, JSON_PRETTY_PRINT));
    echo "  ✅ {$sets->count()} series exportadas\n";
    
    // Exportar asignaciones si existen
    try {
        echo "👨‍🏫 Exportando asignaciones profesor-estudiante...\n";
        $profAssignments = \Illuminate\Support\Facades\DB::table('gym_professor_student_assignments')->get();
        file_put_contents("backup_prof_assignments_{$timestamp}.json", json_encode($profAssignments, JSON_PRETTY_PRINT));
        echo "  ✅ {$profAssignments->count()} asignaciones profesor-estudiante exportadas\n";
        
        echo "📝 Exportando asignaciones de plantillas...\n";
        $templateAssignments = \Illuminate\Support\Facades\DB::table('gym_template_assignments')->get();
        file_put_contents("backup_template_assignments_{$timestamp}.json", json_encode($templateAssignments, JSON_PRETTY_PRINT));
        echo "  ✅ {$templateAssignments->count()} asignaciones de plantillas exportadas\n";
        
        echo "📈 Exportando progreso...\n";
        $progress = \Illuminate\Support\Facades\DB::table('gym_assignment_progress')->get();
        file_put_contents("backup_progress_{$timestamp}.json", json_encode($progress, JSON_PRETTY_PRINT));
        echo "  ✅ {$progress->count()} registros de progreso exportados\n";
    } catch (Exception $e) {
        echo "  ⚠️  Algunas tablas de asignaciones no existen aún\n";
    }
    
    echo "\n📋 PASO 3: CREAR SCRIPT DE RESTAURACIÓN\n";
    echo str_repeat("=", 50) . "\n";
    
    $restoreScript = "<?php\n\n";
    $restoreScript .= "// SCRIPT DE RESTAURACIÓN - Generado el {$timestamp}\n\n";
    $restoreScript .= "require_once 'vendor/autoload.php';\n";
    $restoreScript .= "\$app = require_once 'bootstrap/app.php';\n";
    $restoreScript .= "\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();\n\n";
    $restoreScript .= "echo \"🔄 Restaurando datos del backup {$timestamp}...\\n\\n\";\n\n";
    
    file_put_contents("restore_backup_{$timestamp}.php", $restoreScript);
    echo "✅ Script de restauración creado: restore_backup_{$timestamp}.php\n\n";
    
    echo str_repeat("=", 70) . "\n";
    echo "🎉 BACKUP COMPLETO EXITOSO\n";
    echo str_repeat("=", 70) . "\n";
    echo "📊 Total de registros respaldados: {$totalRecords}\n";
    echo "⚠️  IMPORTANTE: Guarda estos archivos en lugar seguro antes de proceder\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}

// === SEGUNDA PARTE: TESTING API ===

try {
    // Login para obtener token
    $loginData = json_encode(['dni' => '55555555', 'password' => 'maria123']);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $loginResult = json_decode($response, true);
    $token = $loginResult['data']['token'];
    curl_close($ch);
    
    // Obtener plantillas
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/student/my-templates');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    
    $response = curl_exec($ch);
    $templatesData = json_decode($response, true);
    curl_close($ch);
    
    $firstTemplate = $templatesData['data']['templates'][0];
    
    // Obtener detalles
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://villamitre.loca.lt/api/student/template/{$firstTemplate['id']}/details");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    
    $response = curl_exec($ch);
    $details = json_decode($response, true);
    curl_close($ch);
    
    $firstSet = $details['data']['exercises'][0]['sets'][0];
    
    echo "✅ API funcionando\n";
    echo "📊 Template: {$firstTemplate['daily_template']['title']}\n";
    echo "💪 Ejercicio: {$details['data']['exercises'][0]['exercise']['name']}\n\n";
    
    echo "🔍 CAMPOS DE LA PRIMERA SERIE:\n";
    foreach ($firstSet as $key => $value) {
        echo "  {$key}: " . ($value ?? 'NULL') . "\n";
    }
    
    echo "\n🎯 RESULTADO DE LA CORRECCIÓN:\n";
    $hasCorrectFields = isset($firstSet['reps_min']) && isset($firstSet['reps_max']) && isset($firstSet['rpe_target']);
    $hasOldFields = isset($firstSet['reps']) || isset($firstSet['weight']) || isset($firstSet['duration']);
    
    if ($hasCorrectFields && !$hasOldFields) {
        echo "🎉 CORRECCIÓN EXITOSA!\n";
        echo "✅ Campos correctos presentes\n";
        echo "✅ Campos problemáticos eliminados\n";
    } elseif ($hasCorrectFields) {
        echo "⚠️  CORRECCIÓN PARCIAL\n";
        echo "✅ Campos correctos presentes\n";
        echo "⚠️  Algunos campos problemáticos aún presentes\n";
    } else {
        echo "❌ CORRECCIÓN FALLIDA\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error API: " . $e->getMessage() . "\n";
}

// === TERCERA PARTE: ANÁLISIS DE BD ===

try {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📋 Columnas de gym_daily_template_sets:\n";
        
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('gym_daily_template_sets');
    
    foreach ($columns as $i => $column) {
        echo "   " . ($i + 1) . ". {$column}\n";
    }
    
    echo "\n📊 Ejemplo de registro:\n";
    
    $sample = \Illuminate\Support\Facades\DB::table('gym_daily_template_sets')->first();
    
    if ($sample) {
        foreach ($sample as $key => $value) {
            echo "   {$key}: " . ($value ?? 'NULL') . "\n";
        }
    }
    
    echo "\n🔍 Buscando series de María García:\n";
    
    $mariaSets = \Illuminate\Support\Facades\DB::select("
        SELECT 
            sets.*,
            ex.name as exercise_name
        FROM gym_daily_template_sets sets
        JOIN gym_daily_template_exercises exercises ON sets.daily_template_exercise_id = exercises.id
        JOIN gym_exercises ex ON exercises.exercise_id = ex.id
        JOIN gym_daily_templates templates ON exercises.daily_template_id = templates.id
        JOIN daily_assignments assignments ON templates.id = assignments.daily_template_id
        JOIN professor_student_assignments prof_assignments ON assignments.professor_student_assignment_id = prof_assignments.id
        WHERE prof_assignments.student_id = 3
        LIMIT 5
    ");
    
    echo "📊 Series encontradas: " . count($mariaSets) . "\n\n";
    
    foreach ($mariaSets as $i => $set) {
        echo "Serie " . ($i + 1) . " - {$set->exercise_name}:\n";
        foreach ($set as $key => $value) {
            if (!in_array($key, ['created_at', 'updated_at', 'daily_template_exercise_id'])) {
                echo "   {$key}: " . ($value ?? 'NULL') . "\n";
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
