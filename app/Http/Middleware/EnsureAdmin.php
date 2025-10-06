<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = $request->user();

        // Verificar que el usuario esté autenticado
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Verificar que sea administrador
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Forbidden. Admin role required.',
                'required_role' => 'admin'
            ], 403);
        }

        // Verificar permiso específico si se proporciona
        if ($permission && !$user->hasPermission($permission)) {
            return response()->json([
                'message' => "Forbidden. Permission '{$permission}' required.",
                'required_permission' => $permission
            ], 403);
        }

        // Verificar que la cuenta esté activa
        if ($user->account_status !== 'active') {
            return response()->json([
                'message' => 'Account suspended or inactive.',
                'account_status' => $user->account_status
            ], 403);
        }

        return $next($request);
    }
}
