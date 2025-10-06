<?php

echo "📱 === TESTING ENDPOINTS PARA DOCUMENTACIÓN MÓVIL === 📱\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Buscar María García
    $maria = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();
    
    if (!$maria) {
        die("❌ No se encontró María García\n");
    }
    
    echo "👤 USUARIO DE PRUEBA: {$maria->name} (ID: {$maria->id})\n\n";
    
    // Simular autenticación
    \Illuminate\Support\Facades\Auth::login($maria);
    
    $controller = new \App\Http\Controllers\Gym\Student\AssignmentController();
    
    echo str_repeat("=", 100) . "\n";
    echo "ENDPOINT 1: GET /api/student/my-templates\n";
    echo str_repeat("=", 100) . "\n";
    
    $request1 = \Illuminate\Http\Request::create('/api/student/my-templates', 'GET');
    $request1->setUserResolver(function () use ($maria) {
        return $maria;
    });
    
    $response1 = $controller->myTemplates($request1);
    $data1 = json_decode($response1->getContent(), true);
    
    echo "Status: {$response1->getStatusCode()}\n\n";
    echo "ESTRUCTURA COMPLETA DE RESPUESTA:\n";
    echo "```json\n";
    echo json_encode($data1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n```\n\n";
    
    // Guardar ID de template para siguiente test
    $templateAssignmentId = null;
    if (isset($data1['data']['templates']) && count($data1['data']['templates']) > 0) {
        $templateAssignmentId = $data1['data']['templates'][0]['id'];
    }
    
    echo str_repeat("=", 100) . "\n";
    echo "ENDPOINT 2: GET /api/student/template/{id}/details\n";
    echo str_repeat("=", 100) . "\n";
    
    if ($templateAssignmentId) {
        $response2 = $controller->templateDetails($templateAssignmentId);
        $data2 = json_decode($response2->getContent(), true);
        
        echo "Status: {$response2->getStatusCode()}\n\n";
        echo "ESTRUCTURA COMPLETA DE RESPUESTA:\n";
        echo "```json\n";
        echo json_encode($data2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "\n```\n\n";
    } else {
        echo "⚠️  No hay template assignment ID para probar\n\n";
    }
    
    echo str_repeat("=", 100) . "\n";
    echo "ENDPOINT 3: GET /api/student/my-weekly-calendar\n";
    echo str_repeat("=", 100) . "\n";
    
    $request3 = \Illuminate\Http\Request::create('/api/student/my-weekly-calendar', 'GET');
    $request3->setUserResolver(function () use ($maria) {
        return $maria;
    });
    
    $response3 = $controller->myWeeklyCalendar($request3);
    $data3 = json_decode($response3->getContent(), true);
    
    echo "Status: {$response3->getStatusCode()}\n\n";
    echo "ESTRUCTURA COMPLETA DE RESPUESTA:\n";
    echo "```json\n";
    echo json_encode($data3, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n```\n\n";
    
    echo str_repeat("=", 100) . "\n";
    echo "ANÁLISIS DE CAMPOS\n";
    echo str_repeat("=", 100) . "\n";
    
    echo "\n📋 ENDPOINT 1 - my-templates:\n";
    if (isset($data1['data'])) {
        echo "CAMPOS RAÍZ:\n";
        foreach ($data1['data'] as $key => $value) {
            $type = is_array($value) ? 'array' : gettype($value);
            echo "  • {$key}: {$type}\n";
            
            if ($key === 'professor' && is_array($value)) {
                echo "    SUBCAMPOS professor:\n";
                foreach ($value as $subkey => $subvalue) {
                    $subtype = gettype($subvalue);
                    echo "      - {$subkey}: {$subtype}\n";
                }
            }
            
            if ($key === 'templates' && is_array($value) && count($value) > 0) {
                echo "    SUBCAMPOS templates[0]:\n";
                foreach ($value[0] as $subkey => $subvalue) {
                    $subtype = is_array($subvalue) ? 'array' : gettype($subvalue);
                    echo "      - {$subkey}: {$subtype}\n";
                    
                    if ($subkey === 'daily_template' && is_array($subvalue)) {
                        echo "        SUBCAMPOS daily_template:\n";
                        foreach ($subvalue as $subsubkey => $subsubvalue) {
                            $subsubtype = gettype($subsubvalue);
                            echo "          * {$subsubkey}: {$subsubtype}\n";
                        }
                    }
                    
                    if ($subkey === 'assigned_by' && is_array($subvalue)) {
                        echo "        SUBCAMPOS assigned_by:\n";
                        foreach ($subvalue as $subsubkey => $subsubvalue) {
                            $subsubtype = gettype($subsubvalue);
                            echo "          * {$subsubkey}: {$subsubtype}\n";
                        }
                    }
                }
            }
        }
    }
    
    echo "\n📋 ENDPOINT 2 - template details:\n";
    if (isset($data2)) {
        echo "CAMPOS RAÍZ:\n";
        foreach ($data2 as $key => $value) {
            $type = is_array($value) ? 'array' : gettype($value);
            echo "  • {$key}: {$type}\n";
            
            if ($key === 'exercises' && is_array($value) && count($value) > 0) {
                echo "    SUBCAMPOS exercises[0]:\n";
                foreach ($value[0] as $subkey => $subvalue) {
                    $subtype = is_array($subvalue) ? 'array' : gettype($subvalue);
                    echo "      - {$subkey}: {$subtype}\n";
                    
                    if ($subkey === 'exercise' && is_array($subvalue)) {
                        echo "        SUBCAMPOS exercise:\n";
                        foreach ($subvalue as $subsubkey => $subsubvalue) {
                            $subsubtype = is_array($subsubvalue) ? 'array' : gettype($subsubvalue);
                            echo "          * {$subsubkey}: {$subsubtype}\n";
                        }
                    }
                    
                    if ($subkey === 'sets' && is_array($subvalue) && count($subvalue) > 0) {
                        echo "        SUBCAMPOS sets[0]:\n";
                        foreach ($subvalue[0] as $subsubkey => $subsubvalue) {
                            $subsubtype = gettype($subsubvalue);
                            echo "          * {$subsubkey}: {$subsubtype}\n";
                        }
                    }
                }
            }
        }
    }
    
    echo "\n📋 ENDPOINT 3 - weekly calendar:\n";
    if (isset($data3['data'])) {
        echo "CAMPOS RAÍZ data:\n";
        foreach ($data3['data'] as $key => $value) {
            $type = is_array($value) ? 'array' : gettype($value);
            echo "  • {$key}: {$type}\n";
            
            if ($key === 'days' && is_array($value) && count($value) > 0) {
                echo "    SUBCAMPOS days[0]:\n";
                foreach ($value[0] as $subkey => $subvalue) {
                    $subtype = is_array($subvalue) ? 'array' : gettype($subvalue);
                    echo "      - {$subkey}: {$subtype}\n";
                    
                    if ($subkey === 'assignments' && is_array($subvalue) && count($subvalue) > 0) {
                        echo "        SUBCAMPOS assignments[0]:\n";
                        foreach ($subvalue[0] as $subsubkey => $subsubvalue) {
                            $subsubtype = is_array($subsubvalue) ? 'array' : gettype($subsubvalue);
                            echo "          * {$subsubkey}: {$subsubtype}\n";
                            
                            if ($subsubkey === 'daily_template' && is_array($subsubvalue)) {
                                echo "            SUBCAMPOS daily_template:\n";
                                foreach ($subsubvalue as $subsubsubkey => $subsubsubvalue) {
                                    $subsubsubtype = gettype($subsubsubvalue);
                                    echo "              + {$subsubsubkey}: {$subsubsubtype}\n";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
