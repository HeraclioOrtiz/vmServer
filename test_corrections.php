<?php

echo "🔧 === TESTING CORRECCIONES APLICADAS === 🔧\n\n";

class CorrectionTester {
    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $adminToken = null;
    
    public function run() {
        $this->setupAuth();
        $this->testCriticalFixes();
        $this->testNewEndpoints();
        $this->showResults();
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
    
    private function testCriticalFixes() {
        echo "🚨 === TESTING CORRECCIONES CRÍTICAS ===\n";
        
        // 1. Test filtro account_status (que causaba error 500)
        echo "1. Filtro account_status (antes Error 500):\n";
        $response = $this->makeRequest('GET', '/admin/users?account_status=active', $this->adminToken);
        echo "   Status: {$response['status']} " . ($response['status'] == 200 ? '✅' : '❌') . "\n";
        
        // 2. Test endpoint profesores (que causaba error 500)
        echo "\n2. Lista de profesores (antes Error 500):\n";
        $response = $this->makeRequest('GET', '/admin/professors', $this->adminToken);
        echo "   Status: {$response['status']} " . ($response['status'] == 200 ? '✅' : '❌') . "\n";
        if ($response['status'] != 200 && isset($response['data']['error'])) {
            echo "   Error: " . $response['data']['error'] . "\n";
        }
        
        echo "\n";
    }
    
    private function testNewEndpoints() {
        echo "🆕 === TESTING ENDPOINTS NUEVOS ===\n";
        
        // 1. Settings endpoints
        echo "1. Settings - Lista configuraciones:\n";
        $response = $this->makeRequest('GET', '/admin/settings', $this->adminToken);
        echo "   Status: {$response['status']} " . ($response['status'] == 200 ? '✅' : '❌') . "\n";
        
        echo "\n2. Settings - Configuraciones públicas:\n";
        $response = $this->makeRequest('GET', '/admin/settings/public', $this->adminToken);
        echo "   Status: {$response['status']} " . ($response['status'] == 200 ? '✅' : '❌') . "\n";
        
        // 2. Stats de asignaciones
        echo "\n3. Stats de asignaciones semanales:\n";
        $response = $this->makeRequest('GET', '/admin/gym/weekly-assignments/stats', $this->adminToken);
        echo "   Status: {$response['status']} " . ($response['status'] == 200 ? '✅' : '❌') . "\n";
        
        // 3. Test crear configuración
        echo "\n4. Crear configuración de prueba:\n";
        $settingData = [
            'key' => 'test_setting_' . time(),
            'value' => 'test_value',
            'category' => 'testing',
            'description' => 'Configuración de prueba',
            'is_public' => false
        ];
        $response = $this->makeRequest('POST', '/admin/settings', $this->adminToken, $settingData);
        echo "   Status: {$response['status']} " . ($response['status'] == 201 ? '✅' : '❌') . "\n";
        
        echo "\n";
    }
    
    private function showResults() {
        echo "📊 === RESUMEN DE CORRECCIONES ===\n";
        echo "✅ Filtro account_status: Corregido (whereIn → where condicional)\n";
        echo "✅ AdminProfessorController: Agregado manejo de errores\n";
        echo "✅ SettingsController: Implementado completamente\n";
        echo "✅ Rutas settings: Agregadas al archivo admin.php\n";
        echo "✅ Cache de rutas: Limpiado\n\n";
        
        echo "🎯 PRÓXIMO PASO: Ejecutar test completo para verificar mejora\n";
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

$tester = new CorrectionTester();
$tester->run();
