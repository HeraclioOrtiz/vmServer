<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Ruta de test simple para login
Route::post('/test/login', function (Request $request) {
    $dni = $request->input('dni');
    $password = $request->input('password');
    
    // Buscar usuario
    $user = User::where('dni', $dni)->first();
    
    if (!$user) {
        return response()->json([
            'error' => 'Usuario no encontrado',
            'dni_buscado' => $dni
        ], 404);
    }
    
    // Verificar password
    if (!Hash::check($password, $user->password)) {
        return response()->json([
            'error' => 'Password incorrecto'
        ], 401);
    }
    
    // Crear token
    $token = $user->createToken('test-login')->plainTextToken;
    
    return response()->json([
        'message' => 'Login exitoso',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'dni' => $user->dni,
            'is_admin' => $user->is_admin,
            'is_professor' => $user->is_professor,
        ],
        'token' => $token
    ]);
});

// Ruta de test para verificar token
Route::middleware('auth:sanctum')->get('/test/me', function (Request $request) {
    return response()->json([
        'message' => 'Token válido',
        'user' => $request->user()
    ]);
});

// Ruta temporal para stats (mientras debuggeamos la original)
Route::middleware(['auth:sanctum', 'professor'])->get('/test/weekly-assignments-stats', function (Request $request) {
    return response()->json([
        'message' => 'Stats endpoint funcionando',
        'total_assignments' => \App\Models\Gym\WeeklyAssignment::count(),
        'active_assignments' => 0,
        'completed_assignments' => 0,
        'timestamp' => now()->toISOString()
    ]);
});
