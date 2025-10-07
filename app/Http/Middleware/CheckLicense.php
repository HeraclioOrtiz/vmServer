<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el sistema está activo
        $licenseStatus = Cache::get('system_license_status', true);
        
        if (!$licenseStatus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sistema temporalmente no disponible. Contacte al administrador.',
                'code' => 'SERVICE_UNAVAILABLE'
            ], 503);
        }

        return $next($request);
    }
}
