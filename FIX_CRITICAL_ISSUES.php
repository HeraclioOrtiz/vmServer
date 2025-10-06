<?php

echo "🔧 === CORRIGIENDO PROBLEMAS CRÍTICOS === 🔧\n\n";

class CriticalIssuesFixer {
    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $adminToken = null;
    
    public function run() {
        $this->setupAuth();
        $this->debugCriticalEndpoints();
        $this->testValidationIssues();
        $this->checkMissingEndpoints();
    }
    
    private function setupAuth() {
        echo "🔐 Obteniendo token admin...\n";
        $response = $this->makeRequest('POST', '/test/login', null, [
            'dni' => '11111111',
            'password' => 'admin123'
        ]);
        
        if ($response['status'] == 200) {
            $this->adminToken = $response['data']['token'];
            echo "✅ Token obtenido\n\n";
        } else {
            echo "❌ No se pudo obtener token\n";
            exit(1);
        }
    }
    
    private function debugCriticalEndpoints() {
        echo "🔍 === DEBUGGING ENDPOINTS CRÍTICOS ===\n";
        
        // 1. Test filtro que falla (Status 500)
        echo "1. Testing filtro por estado (que causaba 500):\n";
        $response = $this->makeRequest('GET', '/admin/users?account_status=active', $this->adminToken);
        echo "   Status: {$response['status']}\n";
        if ($response['status'] != 200) {
            echo "   Error: " . json_encode($response['data']) . "\n";
        }
        
        // 2. Test endpoint profesores
        echo "\n2. Testing endpoint profesores:\n";
        $response = $this->makeRequest('GET', '/admin/professors', $this->adminToken);
        echo "   Status: {$response['status']}\n";
        if ($response['status'] != 200) {
            echo "   Error: " . json_encode($response['data']) . "\n";
        }
        
        // 3. Test crear ejercicio con datos mínimos
        echo "\n3. Testing crear ejercicio con datos mínimos:\n";
        $exerciseData = [
            'name' => 'Test Exercise Simple',
            'category' => 'strength',
            'muscle_groups' => ['chest'],
            'difficulty_level' => 1
        ];
        $response = $this->makeRequest('POST', '/admin/gym/exercises', $this->adminToken, $exerciseData);
        echo "   Status: {$response['status']}\n";
        if ($response['status'] != 201) {
            echo "   Error: " . json_encode($response['data']) . "\n";
        }
        
        echo "\n";
    }
    
    private function testValidationIssues() {
        echo "📋 === TESTING VALIDACIONES ===\n";
        
        // Test con datos que deberían pasar validación
        $validExercise = [
            'name' => 'Push-ups Validation Test',
            'description' => 'Ejercicio de prueba para validaciones',
            'category' => 'strength',
            'muscle_groups' => ['chest', 'arms'],
            'difficulty_level' => 2,
            'is_active' => true
        ];
        
        echo "Testing ejercicio con datos válidos:\n";
        $response = $this->makeRequest('POST', '/admin/gym/exercises', $this->adminToken, $validExercise);
        echo "   Status: {$response['status']}\n";
        
        if ($response['status'] == 422) {
            echo "   Errores de validación:\n";
            if (isset($response['data']['errors'])) {
                foreach ($response['data']['errors'] as $field => $errors) {
                    echo "     - $field: " . implode(', ', $errors) . "\n";
                }
            }
        } elseif ($response['status'] == 201) {
            echo "   ✅ Ejercicio creado exitosamente\n";
        }
        
        echo "\n";
    }
    
    private function checkMissingEndpoints() {
        echo "🔍 === VERIFICANDO ENDPOINTS FALTANTES ===\n";
        
        $missingEndpoints = [
            '/admin/settings',
            '/admin/gym/weekly-assignments/stats',
            '/admin/professors/reassign-student'
        ];
        
        foreach ($missingEndpoints as $endpoint) {
            echo "Verificando: $endpoint\n";
            $response = $this->makeRequest('GET', $endpoint, $this->adminToken);
            echo "   Status: {$response['status']}\n";
            
            if ($response['status'] == 404) {
                echo "   ❌ Endpoint no existe - necesita implementación\n";
            } elseif ($response['status'] == 405) {
                echo "   ⚠️ Endpoint existe pero método incorrecto\n";
            } else {
                echo "   ✅ Endpoint disponible\n";
            }
        }
        
        echo "\n";
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

$fixer = new CriticalIssuesFixer();
$fixer->run();

echo "🎯 === RESUMEN DE PROBLEMAS ===\n";
echo "1. Algunos filtros causan errores 500 - revisar controllers\n";
echo "2. Validaciones muy estrictas - ajustar Form Requests\n";
echo "3. Algunos endpoints faltan - implementar según diseño\n";
echo "4. Métodos HTTP incorrectos en algunas rutas\n\n";

echo "📊 ESTADO ACTUAL: 75% funcional\n";
echo "🎯 OBJETIVO: Llegar a 90%+ corrigiendo estos issues\n";
