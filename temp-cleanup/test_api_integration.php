<?php

require_once 'vendor/autoload.php';

use App\Services\SociosApi;
use App\Services\AuthService;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

// Configurar entorno básico para testing
$_ENV['APP_ENV'] = 'testing';

// Simular configuración de servicios
config(['services.socios' => [
    'base' => 'https://clubvillamitre.com/api_back_socios',
    'login' => 'surtek',
    'token' => '4fd8fa5840fc5e71d27e46f858f18b4c0cafe220',
    'img_base' => 'https://clubvillamitre.com/images/socios',
    'timeout' => 15,
    'verify' => true
]]);

echo "🧪 Testing API Integration with New Structure\n";
echo "============================================\n\n";

try {
    // Crear instancia del servicio
    $sociosApi = new SociosApi();
    
    // Test DNI: 59964604 (MUNAFO, JUSTINA) - Usuario de testing oficial
    $testDni = '59964604';
    echo "📡 Testing API call for DNI: {$testDni}\n";
    
    $socioData = $sociosApi->getSocioPorDni($testDni);
    
    if ($socioData) {
        echo "✅ API Response received successfully!\n\n";
        
        echo "📋 Socio Data Structure:\n";
        echo "------------------------\n";
        
        // Campos críticos
        echo "🆔 ID: " . ($socioData['Id'] ?? 'N/A') . "\n";
        echo "👤 Nombre: " . ($socioData['nombre'] ?? 'N/A') . "\n";
        echo "👤 Apellido: " . ($socioData['apellido'] ?? 'N/A') . "\n";
        echo "📧 Email: " . ($socioData['mail'] ?? 'N/A') . "\n";
        echo "🏠 DNI: " . ($socioData['dni'] ?? 'N/A') . "\n";
        
        // Nuevos campos críticos
        echo "\n💳 Campos Financieros:\n";
        echo "📊 Barcode: " . ($socioData['barcode'] ?? 'N/A') . "\n";
        echo "💰 Saldo: " . ($socioData['saldo'] ?? 'N/A') . "\n";
        echo "🚦 Semáforo: " . ($socioData['semaforo'] ?? 'N/A') . "\n";
        
        // Interpretación del semáforo
        $semaforo = (int)($socioData['semaforo'] ?? 1);
        $semaforoText = match($semaforo) {
            1 => '✅ Al día',
            99 => '⚠️ Con deuda exigible',
            10 => '🔶 Con deuda no exigible',
            default => '❓ Estado desconocido'
        };
        echo "🚦 Estado: {$semaforoText}\n";
        
        // Información adicional
        echo "\n📝 Información Adicional:\n";
        echo "🏢 Socio N°: " . ($socioData['socio_n'] ?? 'N/A') . "\n";
        echo "📅 Nacimiento: " . ($socioData['nacimiento'] ?? 'N/A') . "\n";
        echo "🏠 Domicilio: " . ($socioData['domicilio'] ?? 'N/A') . "\n";
        echo "📞 Teléfono: " . ($socioData['telefono'] ?? 'N/A') . "\n";
        echo "📱 Celular: " . ($socioData['celular'] ?? 'N/A') . "\n";
        echo "📅 Alta: " . ($socioData['alta'] ?? 'N/A') . "\n";
        echo "🏷️ Categoría: " . ($socioData['categoria'] ?? 'N/A') . "\n";
        
        // Test de descarga de imagen
        echo "\n🖼️ Testing Image Download:\n";
        echo "-------------------------\n";
        
        $socioId = $socioData['Id'] ?? '';
        if ($socioId) {
            echo "📡 Attempting to download image for socio ID: {$socioId}\n";
            
            $imageData = $sociosApi->fetchFotoSocio($socioId);
            
            if ($imageData) {
                $imageSize = strlen($imageData);
                echo "✅ Image downloaded successfully! Size: {$imageSize} bytes\n";
                
                // Verificar que es una imagen válida
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);
                
                echo "🖼️ Image type: {$mimeType}\n";
                
                if (str_starts_with($mimeType, 'image/')) {
                    echo "✅ Valid image format detected\n";
                } else {
                    echo "⚠️ Warning: Unexpected file type\n";
                }
            } else {
                echo "❌ No image data received\n";
            }
        } else {
            echo "❌ No socio ID available for image download\n";
        }
        
        // Mostrar estructura completa para debugging
        echo "\n🔍 Complete API Response Structure:\n";
        echo "-----------------------------------\n";
        echo json_encode($socioData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
    } else {
        echo "❌ No data received from API\n";
        echo "This could mean:\n";
        echo "- DNI not found in the system\n";
        echo "- API connection issues\n";
        echo "- Authentication problems\n";
    }
    
} catch (Exception $e) {
    echo "💥 Error during API testing:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🏁 API Integration Test Complete\n";
