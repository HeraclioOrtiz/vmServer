<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureProfessor;
use App\Http\Middleware\EnsureAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: base_path('routes/api.php'),
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/admin.php'));
            
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/test.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias de middleware de rutas
        $middleware->alias([
            'professor' => EnsureProfessor::class,
            'admin' => EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Configurar manejo de excepciones para rutas API
        $exceptions->render(function (Throwable $e, $request) {
            // Si es una ruta API o espera JSON, devolver respuesta JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                
                // Manejar ValidationException específicamente
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $e->errors()
                    ], 422);
                }
                
                // Manejar errores de autenticación
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No autenticado'
                    ], 401);
                }
                
                // Manejar errores de autorización
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No autorizado'
                    ], 403);
                }
                
                // Manejar errores 404
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Endpoint no encontrado'
                    ], 404);
                }
                
                // Manejar errores 405 (Method Not Allowed)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Método no permitido'
                    ], 405);
                }
                
                // Para otros errores, devolver error genérico
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
                    'debug' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ] : null
                ], $statusCode);
            }
            
            // Para rutas web, usar el manejo por defecto
            return null;
        });
    })->create();
