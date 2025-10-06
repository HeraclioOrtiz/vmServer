<?php

require_once 'vendor/autoload.php';

echo "=== VERIFICACIÓN DE RUTAS ADMIN PANEL ===\n\n";

try {
    // Inicializar Laravel
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    echo "✅ Laravel inicializado\n";
    
    // Obtener todas las rutas
    $router = $app['router'];
    $routes = $router->getRoutes();
    
    echo "\n=== RUTAS ADMIN ENCONTRADAS ===\n";
    
    $adminRoutes = [];
    $gymRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        $name = $route->getName();
        $methods = implode('|', $route->methods());
        
        if (strpos($uri, 'admin') !== false) {
            if (strpos($uri, 'admin/gym') !== false) {
                $gymRoutes[] = "[$methods] $uri" . ($name ? " -> $name" : "");
            } else {
                $adminRoutes[] = "[$methods] $uri" . ($name ? " -> $name" : "");
            }
        }
    }
    
    echo "\n📋 RUTAS PANEL ADMIN (" . count($adminRoutes) . "):\n";
    foreach ($adminRoutes as $route) {
        echo "  ✅ $route\n";
    }
    
    echo "\n🏋️ RUTAS PANEL GIMNASIO (" . count($gymRoutes) . "):\n";
    foreach ($gymRoutes as $route) {
        echo "  ✅ $route\n";
    }
    
    echo "\n=== VERIFICACIÓN DE CONTROLLERS ===\n";
    
    $controllers = [
        'App\Http\Controllers\Admin\AdminUserController',
        'App\Http\Controllers\Admin\AdminProfessorController',
        'App\Http\Controllers\Admin\AuditLogController',
        'App\Http\Controllers\Gym\Admin\ExerciseController',
        'App\Http\Controllers\Gym\Admin\DailyTemplateController',
        'App\Http\Controllers\Gym\Admin\WeeklyTemplateController',
        'App\Http\Controllers\Gym\Admin\WeeklyAssignmentController',
    ];
    
    foreach ($controllers as $controller) {
        if (class_exists($controller)) {
            $reflection = new ReflectionClass($controller);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            $publicMethods = array_filter($methods, function($method) {
                return !in_array($method->getName(), ['__construct', '__call', '__callStatic']);
            });
            
            echo "✅ $controller (" . count($publicMethods) . " métodos)\n";
        } else {
            echo "❌ $controller - NO EXISTE\n";
        }
    }
    
    echo "\n=== VERIFICACIÓN DE MIDDLEWARE ===\n";
    
    $middlewares = [
        'App\Http\Middleware\EnsureAdmin',
        'App\Http\Middleware\EnsureProfessor',
    ];
    
    foreach ($middlewares as $middleware) {
        if (class_exists($middleware)) {
        } else {
            echo "❌ $middleware - NO EXISTE\n";
        }
    }
    
    echo "👥 === ANÁLISIS USUARIOS: BD vs API === 👥\n\n";
    echo "🎯 Total rutas admin: " . (count($adminRoutes) + count($gymRoutes)) . "\n";
    echo "🎯 Controllers verificados: " . count($controllers) . "\n";
    echo "🎯 Middleware verificados: " . count($middlewares) . "\n";
    
    echo "\n✅ VERIFICACIÓN DE RUTAS COMPLETADA\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
