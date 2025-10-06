<?php

echo "📋 === OBTENER RESPUESTA COMPLETA TEMPLATE DETAILS === 📋\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Buscar María García
    $maria = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();
    
    if (!$maria) {
        die("❌ No se encontró María García\n");
    }
    
    // Simular autenticación
    \Illuminate\Support\Facades\Auth::login($maria);
    
    // Buscar una asignación de plantilla
    $templateAssignment = \App\Models\Gym\TemplateAssignment::whereHas('professorStudentAssignment', function($q) use ($maria) {
        $q->where('student_id', $maria->id);
    })->first();
    
    if (!$templateAssignment) {
        die("❌ No se encontró asignación de plantilla para María\n");
    }
    
    echo "🎯 Template Assignment ID: {$templateAssignment->id}\n\n";
    
    $controller = new \App\Http\Controllers\Gym\Student\AssignmentController();
    $response = $controller->templateDetails($templateAssignment->id);
    $data = json_decode($response->getContent(), true);
    
    echo "Status: {$response->getStatusCode()}\n\n";
    echo "RESPUESTA COMPLETA:\n";
    echo "```json\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n```\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
