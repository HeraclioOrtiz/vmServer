<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class LicenseController extends Controller
{
    /**
     * Master key - cambiar antes de deployment
     */
    private const MASTER_KEY = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // bcrypt de 'change-this-key'
    
    /**
     * Verificar estado del sistema
     */
    public function status(Request $request)
    {
        $key = $request->header('X-System-Key');
        
        if (!$this->validateKey($key)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $status = Cache::get('system_license_status', true);
        
        return response()->json([
            'active' => $status,
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    /**
     * Activar sistema
     */
    public function activate(Request $request)
    {
        $key = $request->header('X-System-Key');
        
        if (!$this->validateKey($key)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        Cache::put('system_license_status', true, now()->addYears(10));
        
        return response()->json([
            'status' => 'activated',
            'message' => 'Sistema activado correctamente',
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    /**
     * Desactivar sistema
     */
    public function deactivate(Request $request)
    {
        $key = $request->header('X-System-Key');
        
        if (!$this->validateKey($key)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        Cache::put('system_license_status', false, now()->addYears(10));
        
        return response()->json([
            'status' => 'deactivated',
            'message' => 'Sistema desactivado',
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    /**
     * Validar la master key
     */
    private function validateKey(?string $key): bool
    {
        if (!$key) {
            return false;
        }
        
        return Hash::check($key, self::MASTER_KEY);
    }
}
