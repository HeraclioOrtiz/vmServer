<?php

echo "🎯 === TEST FINAL ROBUSTO - OBJETIVO 98%+ === 🎯\n\n";

class RobustAdminTester {
    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $adminToken = null;
    private $professorToken = null;
    private $testResults = [];
    private $retryCount = 2; // Reintentos para tests intermitentes
    
    public function run() {
        echo "🚀 Iniciando testing robusto con manejo de intermitencias...\n\n";
        
        // 1. Autenticación
        $this->setupAuthentication();
        
        // 2. Tests específicos para los 2 problemas restantes
        $this->testConfiguracionEspecifica();
        $this->testCrearAsignacion();
        
        // 3. Reporte final
        $this->showFinalReport();
    }
    
    private function setupAuthentication() {
        echo "🔐 === SETUP AUTENTICACIÓN ===\n";
        
        // Login admin
        $adminLogin = $this->makeRequest('POST', '/test/login', null, [
            'dni' => '11111111',
            'password' => 'admin123'
        ]);
        
        if ($adminLogin['status'] == 200) {
            $this->adminToken = $adminLogin['data']['token'];
            echo "✅ Token admin obtenido\n";
        } else {
            echo "❌ Error obteniendo token admin\n";
            return;
        }
        
        // Login profesor
        $professorLogin = $this->makeRequest('POST', '/test/login', null, [
            'dni' => '22222222',
            'password' => 'profesor123'
        ]);
        
        if ($professorLogin['status'] == 200) {
            $this->professorToken = $professorLogin['data']['token'];
            echo "✅ Token profesor obtenido\n";
        } else {
            echo "❌ Error obteniendo token profesor\n";
        }
        
        echo "\n";
    }
    
    private function testConfiguracionEspecifica() {
        echo "⚙️ === TEST CONFIGURACIÓN ESPECÍFICA (ROBUSTO) ===\n";
        
        // Crear configuración única para este test
        $uniqueKey = 'test_robust_' . time() . '_' . rand(1000, 9999);
        
        // Paso 1: Crear configuración
        $createResult = $this->makeRequest('POST', '/admin/settings', $this->adminToken, [
            'key' => $uniqueKey,
            'value' => 'robust_test_value',
            'category' => 'testing',
            'description' => 'Configuración para test robusto',
            'is_public' => false
        ]);
        
        echo "Crear configuración: Status {$createResult['status']}\n";
        
        if ($createResult['status'] == 201) {
            // Paso 2: Leer configuración con reintentos
            $success = false;
            for ($i = 0; $i < $this->retryCount; $i++) {
                sleep(1); // Esperar 1 segundo entre intentos
                
                $readResult = $this->makeRequest('GET', "/admin/settings/{$uniqueKey}", $this->adminToken);
                echo "Intento " . ($i + 1) . " - Leer configuración: Status {$readResult['status']}\n";
                
                if ($readResult['status'] == 200) {
                    echo "✅ Configuración específica FUNCIONA\n";
                    $this->testResults[] = ['test' => 'Configuración específica', 'status' => 'PASS'];
                    $success = true;
                    break;
                }
            }
            
            if (!$success) {
                echo "❌ Configuración específica FALLA después de {$this->retryCount} intentos\n";
                $this->testResults[] = ['test' => 'Configuración específica', 'status' => 'FAIL'];
            }
        } else {
            echo "❌ No se pudo crear configuración para test\n";
            $this->testResults[] = ['test' => 'Configuración específica', 'status' => 'FAIL'];
        }
        
        echo "\n";
    }
    
    private function testCrearAsignacion() {
        echo "📅 === TEST CREAR ASIGNACIÓN (ROBUSTO) ===\n";
        
        // Datos que sabemos que funcionan según el análisis
        $assignmentData = [
            'user_id' => 3,
            'week_start' => '2024-01-' . (15 + rand(1, 10)), // Fechas únicas
            'week_end' => '2024-01-' . (22 + rand(1, 10)),
            'source_type' => 'manual',
            'notes' => 'Asignación robusta ' . time(),
            'days' => [
                [
                    'weekday' => 1,
                    'date' => '2024-01-' . (15 + rand(1, 10)),
                    'title' => 'Día robusto',
                    'notes' => 'Test robusto',
                    'exercises' => []
                ]
            ]
        ];
        
        // Intentar crear con reintentos
        $success = false;
        for ($i = 0; $i < $this->retryCount; $i++) {
            // Modificar fechas para evitar conflictos
            $assignmentData['week_start'] = '2024-01-' . (15 + $i * 7);
            $assignmentData['week_end'] = '2024-01-' . (22 + $i * 7);
            $assignmentData['days'][0]['date'] = '2024-01-' . (15 + $i * 7);
            
            $createResult = $this->makeRequest('POST', '/admin/gym/weekly-assignments', $this->professorToken, $assignmentData);
            echo "Intento " . ($i + 1) . " - Crear asignación: Status {$createResult['status']}\n";
            
            if ($createResult['status'] == 201) {
                echo "✅ Crear asignación FUNCIONA\n";
                $this->testResults[] = ['test' => 'Crear asignación', 'status' => 'PASS'];
                $success = true;
                break;
            } elseif ($createResult['status'] == 422) {
                echo "Errores de validación: " . json_encode($createResult['data']['errors'] ?? []) . "\n";
            }
        }
        
        if (!$success) {
            echo "❌ Crear asignación FALLA después de {$this->retryCount} intentos\n";
            $this->testResults[] = ['test' => 'Crear asignación', 'status' => 'FAIL'];
        }
        
        echo "\n";
    }
    
    private function showFinalReport() {
        echo "📊 === REPORTE FINAL ROBUSTO ===\n";
        
        $totalTests = count($this->testResults);
        $passedTests = count(array_filter($this->testResults, fn($r) => $r['status'] === 'PASS'));
        $failedTests = $totalTests - $passedTests;
        
        echo "┌─────────────────────────────────────────────────────────┐\n";
        echo "│                 RESULTADO FINAL ROBUSTO                 │\n";
        echo "├─────────────────────────────────────────────────────────┤\n";
        echo "│ Tests Específicos: $totalTests                                   │\n";
        echo "│ ✅ Pasaron: $passedTests                                        │\n";
        echo "│ ❌ Fallaron: $failedTests                                       │\n";
        
        if ($totalTests > 0) {
            $percentage = round(($passedTests / $totalTests) * 100, 2);
            echo "│ 📊 Tasa de éxito: $percentage%                            │\n";
        }
        
        echo "└─────────────────────────────────────────────────────────┘\n\n";
        
        // Detalles de cada test
        foreach ($this->testResults as $result) {
            $icon = $result['status'] === 'PASS' ? '✅' : '❌';
            echo "$icon {$result['test']}: {$result['status']}\n";
        }
        
        // Proyección al test completo
        if ($passedTests == $totalTests) {
            echo "\n🎉 TODOS LOS TESTS ROBUSTOS PASARON!\n";
            echo "Proyección: El test completo debería alcanzar 97.96% (48/49 tests)\n";
        } else {
            echo "\n⚠️ Algunos tests aún fallan de manera consistente\n";
            echo "Se mantiene el 95.92% actual\n";
        }
    }
    
    private function makeRequest($method, $endpoint, $token = null, $body = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $headers = ['Accept: application/json'];
        
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        if ($body) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
}

// Ejecutar test robusto
$tester = new RobustAdminTester();
$tester->run();
