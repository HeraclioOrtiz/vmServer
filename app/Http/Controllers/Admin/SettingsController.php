<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Lista todas las configuraciones del sistema
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->get('category');
        $search = $request->get('search');
        
        $query = SystemSetting::query();
        
        if ($category) {
            $query->where('category', $category);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $settings = $query->orderBy('category')->orderBy('key')->get();
        
        // Agrupar por categoría
        $grouped = $settings->groupBy('category');
        
        return response()->json([
            'data' => $grouped,
            'categories' => $settings->pluck('category')->unique()->values(),
            'total' => $settings->count()
        ]);
    }
    
    /**
     * Obtener una configuración específica
     */
    public function show(string $key): JsonResponse
    {
        $setting = SystemSetting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found'
            ], 404);
        }
        
        return response()->json($setting);
    }
    
    /**
     * Crear o actualizar una configuración
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:system_settings,key',
            'value' => 'required',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean'
        ]);
        
        $setting = SystemSetting::create($validated);
        
        return response()->json($setting, 201);
    }
    
    /**
     * Actualizar una configuración existente
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $setting = SystemSetting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found'
            ], 404);
        }
        
        $validated = $request->validate([
            'value' => 'required',
            'category' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean'
        ]);
        
        $setting->update($validated);
        
        return response()->json($setting);
    }
    
    /**
     * Eliminar una configuración
     */
    public function destroy(string $key): JsonResponse
    {
        $setting = SystemSetting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found'
            ], 404);
        }
        
        $setting->delete();
        
        return response()->noContent();
    }
    
    /**
     * Obtener configuraciones públicas (para frontend)
     */
    public function public(): JsonResponse
    {
        $settings = SystemSetting::where('is_public', true)
            ->get()
            ->pluck('value', 'key');
            
        return response()->json($settings);
    }
    
    /**
     * Actualizar múltiples configuraciones
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required'
        ]);
        
        $updated = [];
        
        foreach ($validated['settings'] as $settingData) {
            $setting = SystemSetting::where('key', $settingData['key'])->first();
            
            if ($setting) {
                $setting->update(['value' => $settingData['value']]);
                $updated[] = $setting;
            }
        }
        
        return response()->json([
            'message' => 'Settings updated successfully',
            'updated' => $updated
        ]);
    }
}
