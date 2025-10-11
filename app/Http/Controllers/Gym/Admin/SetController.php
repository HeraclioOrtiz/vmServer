<?php

namespace App\Http\Controllers\Gym\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gym\DailyTemplateSet;
use App\Services\Gym\SetService;
use Illuminate\Http\Request;

class SetController extends Controller
{
    public function __construct(
        private SetService $setService
    ) {}

    /**
     * Actualizar un set individual
     * PUT /admin/gym/sets/{set}
     */
    public function update(Request $request, DailyTemplateSet $set)
    {
        $data = $request->validate([
            'set_number' => 'sometimes|integer|min:1',
            'reps_min' => 'nullable|integer|min:1',
            'reps_max' => 'nullable|integer|min:1',
            'rest_seconds' => 'nullable|integer|min:0',
            'rpe_target' => 'nullable|numeric|min:0|max:10',
            'weight_min' => 'nullable|numeric|min:0|max:1000',
            'weight_max' => 'nullable|numeric|min:0|max:1000',
            'weight_target' => 'nullable|numeric|min:0|max:1000',
            'notes' => 'nullable|string',
        ]);

        $updated = $this->setService->updateSet($set, $data, $request->user());
        
        return response()->json($updated);
    }

    /**
     * Eliminar un set individual
     * DELETE /admin/gym/sets/{set}
     */
    public function destroy(DailyTemplateSet $set)
    {
        $result = $this->setService->deleteSet($set, auth()->user());
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'] ?? null
            ], $result['status_code']);
        }
        
        return response()->json([
            'message' => $result['message']
        ], $result['status_code']);
    }
}
