<?php

echo "🗑️ === LIMPIEZA TOTAL: USUARIOS + CACHE === 🗑️\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "⚠️  ADVERTENCIA: Este script eliminará TODOS los usuarios\n";
    echo "⚠️  Incluyendo los 23 usuarios conservados anteriormente\n";
    echo "⚠️  También limpiará el cache de usuarios\n\n";
    
    echo "📊 PASO 1: VERIFICAR USUARIOS ACTUALES\n";
    echo str_repeat("=", 60) . "\n";
    
    $currentUsers = \Illuminate\Support\Facades\DB::table('users')->count();
    echo "Total usuarios actuales: {$currentUsers}\n";
    
    if ($currentUsers > 0) {
        $byType = \Illuminate\Support\Facades\DB::table('users')
            ->select('user_type', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->groupBy('user_type')
            ->get();
        
        echo "Distribución:\n";
        foreach ($byType as $type) {
            echo "  • {$type->user_type}: {$type->count} usuarios\n";
        }
    }
    
    echo "\n🗑️  PASO 2: ELIMINAR TODOS LOS USUARIOS\n";
    echo str_repeat("=", 60) . "\n";
    
    // Desactivar foreign keys temporalmente
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    // Eliminar todos los usuarios
    $deleted = \Illuminate\Support\Facades\DB::table('users')->delete();
    echo "✅ {$deleted} usuarios eliminados\n";
    
    // Eliminar tokens de acceso personal
    $tokensDeleted = \Illuminate\Support\Facades\DB::table('personal_access_tokens')->delete();
    echo "✅ {$tokensDeleted} tokens eliminados\n";
    
    // Reactivar foreign keys
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\n🧹 PASO 3: LIMPIAR CACHE\n";
    echo str_repeat("=", 60) . "\n";
    
    try {
        // Limpiar cache de aplicación
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo "✅ Cache de aplicación limpiado\n";
        
        // Limpiar cache de configuración
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        echo "✅ Cache de configuración limpiado\n";
        
        // Limpiar cache de rutas
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        echo "✅ Cache de rutas limpiado\n";
        
        // Limpiar cache de vistas
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        echo "✅ Cache de vistas limpiado\n";
        
    } catch (Exception $e) {
        echo "⚠️  Advertencia en limpieza de cache: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ PASO 4: VERIFICAR LIMPIEZA\n";
    echo str_repeat("=", 60) . "\n";
    
    $remainingUsers = \Illuminate\Support\Facades\DB::table('users')->count();
    $remainingTokens = \Illuminate\Support\Facades\DB::table('personal_access_tokens')->count();
    
    echo "Usuarios restantes: {$remainingUsers}\n";
    echo "Tokens restantes: {$remainingTokens}\n";
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎉 LIMPIEZA TOTAL COMPLETADA\n";
    echo str_repeat("=", 70) . "\n";
    
    if ($remainingUsers === 0 && $remainingTokens === 0) {
        echo "✅ Tabla de usuarios completamente vacía\n";
        echo "✅ Tokens eliminados\n";
        echo "✅ Cache limpiado\n";
        echo "✅ Base de datos lista para reestructuración completa\n\n";
        echo "🎯 SIGUIENTE PASO: Definir estructura correcta de usuarios desde cero\n";
    } else {
        echo "⚠️  Advertencia: Aún quedan registros\n";
        echo "  Usuarios: {$remainingUsers}\n";
        echo "  Tokens: {$remainingTokens}\n";
    }
    
    echo "\n📋 RESET AUTO INCREMENT\n";
    echo str_repeat("=", 60) . "\n";
    
    // Resetear auto increment para comenzar desde 1
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE personal_access_tokens AUTO_INCREMENT = 1');
    echo "✅ Contadores de ID reseteados a 1\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
