<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $maria = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();
    \Illuminate\Support\Facades\Auth::login($maria);
    
    $controller = new \App\Http\Controllers\Gym\Student\AssignmentController();
    
    // Template details
    $templateAssignment = \App\Models\Gym\TemplateAssignment::whereHas('professorStudentAssignment', function($q) use ($maria) {
        $q->where('student_id', $maria->id);
    })->first();
    
    if ($templateAssignment) {
        $response = $controller->templateDetails($templateAssignment->id);
        $data = json_decode($response->getContent(), true);
        
        echo "TEMPLATE DETAILS STRUCTURE:\n";
        echo "==========================\n";
        
        // Mostrar estructura sin contenido largo
        if (isset($data['assignment_info'])) {
            echo "assignment_info:\n";
            foreach ($data['assignment_info'] as $key => $value) {
                $type = is_array($value) ? 'array' : gettype($value);
                echo "  {$key}: {$type}\n";
                if ($key === 'assigned_by' && is_array($value)) {
                    foreach ($value as $subkey => $subvalue) {
                        echo "    {$subkey}: " . gettype($subvalue) . "\n";
                    }
                }
            }
        }
        
        if (isset($data['template'])) {
            echo "template:\n";
            foreach ($data['template'] as $key => $value) {
                $type = is_array($value) ? 'array' : gettype($value);
                echo "  {$key}: {$type}\n";
            }
        }
        
        if (isset($data['exercises']) && count($data['exercises']) > 0) {
            echo "exercises[0]:\n";
            foreach ($data['exercises'][0] as $key => $value) {
                $type = is_array($value) ? 'array' : gettype($value);
                echo "  {$key}: {$type}\n";
                
                if ($key === 'exercise' && is_array($value)) {
                    echo "    exercise fields:\n";
                    foreach ($value as $subkey => $subvalue) {
                        $subtype = is_array($subvalue) ? 'array' : gettype($subvalue);
                        echo "      {$subkey}: {$subtype}\n";
                    }
                }
                
                if ($key === 'sets' && is_array($value) && count($value) > 0) {
                    echo "    sets[0] fields:\n";
                    foreach ($value[0] as $subkey => $subvalue) {
                        echo "      {$subkey}: " . gettype($subvalue) . "\n";
                    }
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
