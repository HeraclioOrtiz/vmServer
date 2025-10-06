<?php

echo "🔍 === VERIFICACIÓN PANEL ADMIN DE PROFESORES === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$issues = [];
$success = [];
$warnings = [];

try {
    echo "PASO 1: VERIFICAR MODELOS Y RELACIONES\n";
    echo str_repeat("=", 80) . "\n";
    
    // Verificar User tiene relaciones
    $userModel = new \App\Models\User();
    
    if (method_exists($userModel, 'professorAssignments')) {
        $success[] = "User::professorAssignments() existe";
        echo "  ✅ User::professorAssignments() existe\n";
    } else {
        $issues[] = "User::professorAssignments() NO existe";
        echo "  ❌ User::professorAssignments() NO existe\n";
    }
    
    if (method_exists($userModel, 'students')) {
        $success[] = "User::students() existe";
        echo "  ✅ User::students() existe\n";
    } else {
        $warnings[] = "User::students() NO existe (opcional)";
        echo "  ⚠️  User::students() NO existe (opcional)\n";
    }
    
    echo "\nPASO 2: VERIFICAR CONTROLADORES\n";
    echo str_repeat("=", 80) . "\n";
    
    $controllers = [
        'App\Http\Controllers\Admin\AdminProfessorController' => 'AdminProfessorController',
        'App\Http\Controllers\Admin\AssignmentController' => 'AssignmentController (Admin)',
    ];
    
    foreach ($controllers as $class => $name) {
        if (class_exists($class)) {
            $success[] = "{$name} existe";
            echo "  ✅ {$name} existe\n";
        } else {
            $issues[] = "{$name} NO existe";
            echo "  ❌ {$name} NO existe\n";
        }
    }
    
    echo "\nPASO 3: VERIFICAR SERVICIOS\n";
    echo str_repeat("=", 80) . "\n";
    
    if (class_exists('App\Services\Gym\AssignmentService')) {
        $service = app(\App\Services\Gym\AssignmentService::class);
        $methods = [
            'assignStudentToProfessor',
            'getAllProfessorStudentAssignments',
            'getProfessorStudents',
            'getUnassignedStudents',
        ];
        
        echo "  AssignmentService encontrado\n";
        foreach ($methods as $method) {
            if (method_exists($service, $method)) {
                $success[] = "AssignmentService::{$method}() existe";
                echo "    ✅ {$method}()\n";
            } else {
                $issues[] = "AssignmentService::{$method}() NO existe";
                echo "    ❌ {$method}()\n";
            }
        }
    } else {
        $issues[] = "AssignmentService NO existe";
        echo "  ❌ AssignmentService NO existe\n";
    }
    
    echo "\nPASO 4: VERIFICAR TABLAS EN BASE DE DATOS\n";
    echo str_repeat("=", 80) . "\n";
    
    $tables = [
        'professor_student_assignments',
        'daily_assignments',
        'assignment_progress'
    ];
    
    foreach ($tables as $table) {
        $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
        if ($exists) {
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            $success[] = "Tabla {$table} existe ({$count} registros)";
            echo "  ✅ {$table} ({$count} registros)\n";
        } else {
            $issues[] = "Tabla {$table} NO existe";
            echo "  ❌ {$table} NO existe\n";
        }
    }
    
    echo "\nPASO 5: PROBAR FLUJO COMPLETO\n";
    echo str_repeat("=", 80) . "\n";
    
    // Buscar profesor
    $profesor = \App\Models\User::where('is_professor', true)->first();
    
    if ($profesor) {
        $success[] = "Profesor encontrado: {$profesor->name}";
        echo "  ✅ Profesor encontrado: {$profesor->name} (ID: {$profesor->id})\n";
        
        // Verificar asignaciones directas
        $assignments = \App\Models\Gym\ProfessorStudentAssignment::where('professor_id', $profesor->id)
            ->where('status', 'active')
            ->count();
        
        $success[] = "Profesor tiene {$assignments} asignaciones activas";
        echo "  ✅ Asignaciones activas: {$assignments}\n";
        
        // Verificar que puede cargar estudiantes via relación
        try {
            if (method_exists($profesor, 'professorAssignments')) {
                $studentsCount = $profesor->professorAssignments()
                    ->where('status', 'active')
                    ->count();
                $success[] = "Puede cargar estudiantes via relación ({$studentsCount})";
                echo "  ✅ Relación professorAssignments funciona ({$studentsCount} estudiantes)\n";
            } else {
                $warnings[] = "Relación professorAssignments no disponible";
                echo "  ⚠️  Relación professorAssignments no disponible\n";
            }
        } catch (Exception $e) {
            $issues[] = "Error al cargar estudiantes: " . $e->getMessage();
            echo "  ❌ Error al cargar estudiantes: " . $e->getMessage() . "\n";
        }
        
        // Verificar plantillas creadas
        $templatesCreated = \App\Models\Gym\DailyTemplate::where('created_by', $profesor->id)->count();
        echo "  ℹ️  Plantillas creadas: {$templatesCreated}\n";
        
        // Verificar asignaciones de plantillas
        if ($assignments > 0) {
            $assignmentIds = \App\Models\Gym\ProfessorStudentAssignment::where('professor_id', $profesor->id)
                ->where('status', 'active')
                ->pluck('id');
            
            $templateAssignments = \App\Models\Gym\TemplateAssignment::whereIn('professor_student_assignment_id', $assignmentIds)
                ->where('status', 'active')
                ->count();
            
            echo "  ℹ️  Plantillas asignadas: {$templateAssignments}\n";
        }
        
    } else {
        $warnings[] = "No hay profesores en el sistema";
        echo "  ⚠️  No hay profesores en el sistema\n";
    }
    
    echo "\nPASO 6: VERIFICAR DATOS DE PRUEBA\n";
    echo str_repeat("=", 80) . "\n";
    
    $profAssignmentsCount = \App\Models\Gym\ProfessorStudentAssignment::count();
    $templateAssignmentsCount = \App\Models\Gym\TemplateAssignment::count();
    $progressCount = \App\Models\Gym\AssignmentProgress::count();
    
    echo "  📊 Asignaciones profesor-estudiante: {$profAssignmentsCount}\n";
    echo "  📊 Asignaciones de plantillas: {$templateAssignmentsCount}\n";
    echo "  📊 Sesiones de progreso: {$progressCount}\n";
    
    if ($profAssignmentsCount > 0) {
        $success[] = "{$profAssignmentsCount} asignaciones profesor-estudiante";
    } else {
        $warnings[] = "No hay asignaciones profesor-estudiante";
    }
    
    echo "\nPASO 7: PROBAR MÉTODOS DEL CONTROLADOR\n";
    echo str_repeat("=", 80) . "\n";
    
    try {
        // Simular request como admin
        $admin = \App\Models\User::where('is_admin', true)->first();
        
        if ($admin) {
            \Illuminate\Support\Facades\Auth::login($admin);
            echo "  ✅ Admin autenticado: {$admin->name}\n";
            
            // Test método index
            try {
                $controller = app(\App\Http\Controllers\Admin\AdminProfessorController::class);
                $request = \Illuminate\Http\Request::create('/api/admin/professors', 'GET');
                $request->setUserResolver(function () use ($admin) {
                    return $admin;
                });
                
                $response = $controller->index($request);
                $data = json_decode($response->getContent(), true);
                
                if ($response->getStatusCode() === 200) {
                    $success[] = "Método index() funciona";
                    echo "  ✅ index() - Status 200\n";
                    echo "    Profesores encontrados: " . count($data['professors'] ?? []) . "\n";
                } else {
                    $issues[] = "Método index() devuelve error " . $response->getStatusCode();
                    echo "  ❌ index() - Status " . $response->getStatusCode() . "\n";
                }
            } catch (Exception $e) {
                $issues[] = "Error en index(): " . $e->getMessage();
                echo "  ❌ Error en index(): " . $e->getMessage() . "\n";
            }
            
            // Test método students
            if ($profesor) {
                try {
                    $response2 = $controller->students($profesor);
                    $data2 = json_decode($response2->getContent(), true);
                    
                    if ($response2->getStatusCode() === 200) {
                        $success[] = "Método students() funciona";
                        echo "  ✅ students() - Status 200\n";
                        echo "    Estudiantes: " . ($data2['total'] ?? 0) . "\n";
                    } else {
                        $issues[] = "Método students() devuelve error " . $response2->getStatusCode();
                        echo "  ❌ students() - Status " . $response2->getStatusCode() . "\n";
                    }
                } catch (Exception $e) {
                    $issues[] = "Error en students(): " . $e->getMessage();
                    echo "  ❌ Error en students(): " . $e->getMessage() . "\n";
                }
            }
            
        } else {
            $warnings[] = "No hay admin para testing";
            echo "  ⚠️  No hay admin para testing\n";
        }
    } catch (Exception $e) {
        $issues[] = "Error en testing de controlador: " . $e->getMessage();
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "RESULTADOS FINALES\n";
    echo str_repeat("=", 80) . "\n\n";
    
    echo "✅ ÉXITOS (" . count($success) . "):\n";
    foreach ($success as $item) {
        echo "  • {$item}\n";
    }
    
    if (!empty($warnings)) {
        echo "\n⚠️  ADVERTENCIAS (" . count($warnings) . "):\n";
        foreach ($warnings as $item) {
            echo "  • {$item}\n";
        }
    }
    
    if (!empty($issues)) {
        echo "\n❌ PROBLEMAS (" . count($issues) . "):\n";
        foreach ($issues as $item) {
            echo "  • {$item}\n";
        }
        echo "\n❌ SE ENCONTRARON PROBLEMAS QUE DEBEN SER CORREGIDOS\n";
    } else {
        echo "\n🎉 PANEL DE ADMIN DE PROFESORES FUNCIONANDO CORRECTAMENTE\n";
        
        // Recomendaciones
        echo "\n📋 PRÓXIMOS PASOS:\n";
        echo "  1. Revisar estadísticas en método index() si están en 0\n";
        echo "  2. Verificar que método students() filtra correctamente\n";
        echo "  3. Testing manual con Postman/CURL\n";
        echo "  4. Verificar performance de queries\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
