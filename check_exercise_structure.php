<?php

echo "🔍 === VERIFICACIÓN COMPLETA DE INTERFAZ === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "🏋️ Analizando estructura de ejercicios...\n\n";
    
    // 1. Revisar tabla gym_exercises
    echo "📋 TABLA: gym_exercises\n";
    echo str_repeat("=", 50) . "\n";
    
    $exerciseColumns = \Illuminate\Support\Facades\Schema::getColumnListing('gym_exercises');
    echo "Columnas (" . count($exerciseColumns) . "):\n";
    foreach ($exerciseColumns as $i => $column) {
        echo "  " . ($i + 1) . ". {$column}\n";
    }
    
    // Obtener un ejercicio de ejemplo
    $sampleExercise = \Illuminate\Support\Facades\DB::table('gym_exercises')->first();
    if ($sampleExercise) {
        echo "\n📊 EJEMPLO DE EJERCICIO:\n";
        foreach ($sampleExercise as $key => $value) {
            $displayValue = $value ?? 'NULL';
            if (is_string($value) && strlen($value) > 50) {
                $displayValue = substr($value, 0, 50) . '...';
            }
            echo "  {$key}: {$displayValue}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    
    // 2. Revisar tabla gym_daily_template_exercises
    echo "📋 TABLA: gym_daily_template_exercises\n";
    echo str_repeat("=", 50) . "\n";
    
    $templateExerciseColumns = \Illuminate\Support\Facades\Schema::getColumnListing('gym_daily_template_exercises');
    echo "Columnas (" . count($templateExerciseColumns) . "):\n";
    foreach ($templateExerciseColumns as $i => $column) {
        echo "  " . ($i + 1) . ". {$column}\n";
    }
    
    // Obtener un template exercise de ejemplo
    $sampleTemplateExercise = \Illuminate\Support\Facades\DB::table('gym_daily_template_exercises')->first();
    if ($sampleTemplateExercise) {
        echo "\n📊 EJEMPLO DE TEMPLATE EXERCISE:\n";
        foreach ($sampleTemplateExercise as $key => $value) {
            $displayValue = $value ?? 'NULL';
            echo "  {$key}: {$displayValue}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    
    // 3. Revisar tabla gym_daily_template_sets
    echo "📋 TABLA: gym_daily_template_sets\n";
    echo str_repeat("=", 50) . "\n";
    
    $setsColumns = \Illuminate\Support\Facades\Schema::getColumnListing('gym_daily_template_sets');
    echo "Columnas (" . count($setsColumns) . "):\n";
    foreach ($setsColumns as $i => $column) {
        echo "  " . ($i + 1) . ". {$column}\n";
    }
    
    // Obtener un set de ejemplo
    $sampleSet = \Illuminate\Support\Facades\DB::table('gym_daily_template_sets')->first();
    if ($sampleSet) {
        echo "\n📊 EJEMPLO DE SET:\n";
        foreach ($sampleSet as $key => $value) {
            $displayValue = $value ?? 'NULL';
            echo "  {$key}: {$displayValue}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    
    // 4. Obtener estructura completa desde API
    echo "🌐 ESTRUCTURA DESDE API\n";
    echo str_repeat("=", 50) . "\n";
    
    // Login
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/auth/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['dni' => '33333333', 'password' => 'estudiante123']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $loginData = json_decode($response, true);
    $token = $loginData['data']['token'];
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
    
    if (isset($templatesData['data']['templates'][0])) {
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
        
        if (isset($details['data']['exercises'][0])) {
            $firstExercise = $details['data']['exercises'][0];
            
            echo "📊 VERIFICACIÓN COMPLETA DE INTERFAZ VS REALIDAD:\n\n";
            
            // Extraer datos para análisis
            $template = $details['data']['template'];
            $exercise = $firstExercise;
            $exerciseBase = $firstExercise['exercise'];
            $firstSet = $firstExercise['sets'][0];
            
            echo "🎯 PASO 1: VERIFICAR TEMPLATE\n";
            echo str_repeat("-", 40) . "\n";
            
            $templateExpected = [
                'id' => 'number',
                'title' => 'string',
                'description' => 'string|null',
                'duration_minutes' => 'number',
                'difficulty_level' => 'string',
                'created_at' => 'string',
                'updated_at' => 'string'
            ];
            
            foreach ($templateExpected as $field => $expectedType) {
                if (isset($template[$field])) {
                    $value = $template[$field];
                    echo "  ✅ {$field}: " . gettype($value) . " = " . ($value ?? 'NULL') . "\n";
                } else {
                    echo "  ❌ {$field}: NO EXISTE\n";
                }
            }
            
            echo "\n🎯 PASO 2: VERIFICAR EXERCISE (nivel plantilla)\n";
            echo str_repeat("-", 40) . "\n";
            
            $exerciseExpected = [
                'id' => 'number',
                'order' => 'number|null',
                'notes' => 'string|null'
            ];
            
            foreach ($exerciseExpected as $field => $expectedType) {
                if (isset($exercise[$field])) {
                    $value = $exercise[$field];
                    echo "  ✅ {$field}: " . gettype($value) . " = " . ($value ?? 'NULL') . "\n";
                } else {
                    echo "  ❌ {$field}: NO EXISTE\n";
                }
            }
            
            echo "\n🎯 PASO 3: VERIFICAR EXERCISE BASE (catálogo)\n";
            echo str_repeat("-", 40) . "\n";
            
            $exerciseBaseExpected = [
                'id' => 'number',
                'name' => 'string',
                'description' => 'string|null',
                'instructions' => 'string|null',
                'muscle_groups' => 'string|null',
                'equipment' => 'string|null',
                'difficulty_level' => 'string|null',
                'created_at' => 'string',
                'updated_at' => 'string'
            ];
            
            foreach ($exerciseBaseExpected as $field => $expectedType) {
                if (isset($exerciseBase[$field])) {
                    $value = $exerciseBase[$field];
                    $displayValue = is_string($value) && strlen($value) > 30 ? substr($value, 0, 30) . '...' : ($value ?? 'NULL');
                    echo "  ✅ {$field}: " . gettype($value) . " = {$displayValue}\n";
                } else {
                    echo "  ❌ {$field}: NO EXISTE\n";
                }
            }
            
            echo "\n🎯 PASO 4: VERIFICAR SET\n";
            echo str_repeat("-", 40) . "\n";
            
            $setExpected = [
                'id' => 'number',
                'set_number' => 'number',
                'reps' => 'number|null',
                'weight' => 'number|null',
                'duration' => 'number|null',
                'rest_seconds' => 'number',
                'notes' => 'string|null'
            ];
            
            foreach ($setExpected as $field => $expectedType) {
                if (isset($firstSet[$field])) {
                    $value = $firstSet[$field];
                    echo "  ✅ {$field}: " . gettype($value) . " = " . ($value ?? 'NULL') . "\n";
                } else {
                    echo "  ❌ {$field}: NO EXISTE\n";
                }
            }
            
            echo "\n📋 CAMPOS REALES EN SET (pueden ser diferentes):\n";
            foreach ($firstSet as $field => $value) {
                if (!isset($setExpected[$field])) {
                    echo "  ➕ {$field}: " . gettype($value) . " = " . ($value ?? 'NULL') . "\n";
                }
            }
            
            echo "\n📋 CAMPOS ADICIONALES EN EXERCISE BASE:\n";
            foreach ($exerciseBase as $field => $value) {
                if (!isset($exerciseBaseExpected[$field])) {
                    $displayValue = is_string($value) && strlen($value) > 20 ? substr($value, 0, 20) . '...' : ($value ?? 'NULL');
                    echo "  ➕ {$field}: " . gettype($value) . " = {$displayValue}\n";
                }
            }
            
            echo "\n" . str_repeat("=", 80) . "\n";
            echo "🎯 RESUMEN DE DISCREPANCIAS ENCONTRADAS\n";
            echo str_repeat("=", 80) . "\n";
            
            $discrepancies = [];
            
            // Verificar Template
            foreach ($templateExpected as $field => $expectedType) {
                if (!isset($template[$field])) {
                    $discrepancies[] = "❌ Template.{$field}: FALTANTE";
                }
            }
            
            // Verificar Exercise
            foreach ($exerciseExpected as $field => $expectedType) {
                if (!isset($exercise[$field])) {
                    $discrepancies[] = "❌ Exercise.{$field}: FALTANTE";
                }
            }
            
            // Verificar Exercise Base
            foreach ($exerciseBaseExpected as $field => $expectedType) {
                if (!isset($exerciseBase[$field])) {
                    $discrepancies[] = "❌ Exercise.exercise.{$field}: FALTANTE";
                }
            }
            
            // Verificar Set
            foreach ($setExpected as $field => $expectedType) {
                if (!isset($firstSet[$field])) {
                    $discrepancies[] = "❌ Set.{$field}: FALTANTE";
                }
            }
            
            if (count($discrepancies) > 0) {
                echo "🚨 PROBLEMAS ENCONTRADOS:\n";
                foreach ($discrepancies as $discrepancy) {
                    echo "  {$discrepancy}\n";
                }
            } else {
                echo "✅ NO HAY DISCREPANCIAS - Interfaz coincide perfectamente\n";
            }
            
            echo "\n🔧 CAMPOS ADICIONALES (no documentados en interfaz):\n";
            
            // Template adicionales
            foreach ($template as $field => $value) {
                if (!isset($templateExpected[$field])) {
                    echo "  ➕ Template.{$field}: " . gettype($value) . "\n";
                }
            }
            
            // Exercise adicionales
            foreach ($exercise as $field => $value) {
                if (!isset($exerciseExpected[$field]) && $field !== 'exercise' && $field !== 'sets') {
                    echo "  ➕ Exercise.{$field}: " . gettype($value) . "\n";
                }
            }
            
            // Exercise Base adicionales
            foreach ($exerciseBase as $field => $value) {
                if (!isset($exerciseBaseExpected[$field])) {
                    echo "  ➕ Exercise.exercise.{$field}: " . gettype($value) . "\n";
                }
            }
            
            // Set adicionales
            foreach ($firstSet as $field => $value) {
                if (!isset($setExpected[$field])) {
                    echo "  ➕ Set.{$field}: " . gettype($value) . "\n";
                }
            }
            
            echo "\n" . str_repeat("=", 80) . "\n";
            echo "📝 INTERFAZ CORREGIDA BASADA EN DATOS REALES\n";
            echo str_repeat("=", 80) . "\n";
            
            echo "```typescript\n";
            echo "// INTERFAZ CORREGIDA - Basada en datos reales de la API\n\n";
            
            // Template corregido
            echo "interface Template {\n";
            foreach ($template as $field => $value) {
                $type = gettype($value);
                if ($type === 'integer') $type = 'number';
                if ($type === 'double') $type = 'number';
                if ($value === null) $type .= ' | null';
                echo "  {$field}: {$type};\n";
            }
            echo "}\n\n";
            
            // Exercise corregido
            echo "interface Exercise {\n";
            foreach ($exercise as $field => $value) {
                if ($field === 'exercise' || $field === 'sets') continue;
                $type = gettype($value);
                if ($type === 'integer') $type = 'number';
                if ($type === 'double') $type = 'number';
                if ($value === null) $type .= ' | null';
                echo "  {$field}: {$type};\n";
            }
            echo "  exercise: ExerciseBase;\n";
            echo "  sets: Set[];\n";
            echo "}\n\n";
            
            // Exercise Base corregido
            echo "interface ExerciseBase {\n";
            foreach ($exerciseBase as $field => $value) {
                $type = gettype($value);
                if ($type === 'integer') $type = 'number';
                if ($type === 'double') $type = 'number';
                if ($value === null) $type .= ' | null';
                echo "  {$field}: {$type};\n";
            }
            echo "}\n\n";
            
            // Set corregido
            echo "interface Set {\n";
            foreach ($firstSet as $field => $value) {
                $type = gettype($value);
                if ($type === 'integer') $type = 'number';
                if ($type === 'double') $type = 'number';
                if ($value === null) $type .= ' | null';
                echo "  {$field}: {$type};\n";
            }
            echo "}\n";
            echo "```\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
