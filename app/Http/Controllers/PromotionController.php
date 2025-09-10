<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PromotionService;
use App\Http\Requests\PromoteRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct(
        private PromotionService $promotionService
    ) {}

    /**
     * Promociona un usuario local a usuario API
     */
    public function promote(PromoteRequest $request)
    {
        $user = $request->user();
        
        $result = $this->promotionService->promoteUser(
            $user, 
            $request->club_password
        );

        return response()->json([
            'success' => $result->success,
            'message' => $result->message,
            'user' => UserResource::make($result->user)
        ]);
    }

    /**
     * Verifica la elegibilidad de promoción del usuario actual
     */
    public function checkEligibility(Request $request)
    {
        $user = $request->user();
        $eligibility = $this->promotionService->checkEligibility($user);

        return response()->json([
            'eligible' => $eligibility->eligible,
            'reason' => $eligibility->reason
        ]);
    }

    /**
     * Verifica si un DNI existe en el sistema del club
     */
    public function checkDniInClub(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|size:8'
        ]);

        $result = $this->promotionService->checkDniInClub($request->dni);

        return response()->json([
            'exists' => $result->exists,
            'socio_info' => $result->socioInfo,
            'message' => $result->message
        ]);
    }

    /**
     * Solicita promoción (para flujos con aprobación manual)
     */
    public function requestPromotion(Request $request)
    {
        $request->validate([
            'club_password' => 'required|string',
            'notes' => 'nullable|string|max:500'
        ]);

        $user = $request->user();
        
        $result = $this->promotionService->requestPromotion(
            $user,
            $request->club_password,
            $request->notes
        );

        return response()->json([
            'success' => $result->success,
            'message' => $result->message,
            'user' => UserResource::make($result->user)
        ]);
    }

    /**
     * Aprueba una promoción pendiente (solo administradores)
     */
    public function approve(User $user)
    {
        $result = $this->promotionService->approvePromotion($user);

        return response()->json([
            'success' => $result->success,
            'message' => $result->message,
            'user' => UserResource::make($result->user)
        ]);
    }

    /**
     * Rechaza una promoción pendiente (solo administradores)
     */
    public function reject(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $result = $this->promotionService->rejectPromotion($user, $request->reason);

        return response()->json([
            'success' => $result->success,
            'message' => $result->message,
            'user' => UserResource::make($result->user)
        ]);
    }

    /**
     * Obtiene estadísticas de promociones
     */
    public function stats()
    {
        $stats = $this->promotionService->getPromotionStats();

        return response()->json($stats);
    }

    /**
     * Lista usuarios elegibles para promoción
     */
    public function eligible(Request $request)
    {
        $perPage = min($request->integer('per_page', 15), 50);
        
        $users = User::eligibleForPromotion()
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Lista promociones pendientes (solo administradores)
     */
    public function pending(Request $request)
    {
        $perPage = min($request->integer('per_page', 15), 50);
        
        $users = User::where('promotion_status', \App\Enums\PromotionStatus::PENDING)
                    ->orderBy('promoted_at', 'desc')
                    ->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Historial de promociones (solo administradores)
     */
    public function history(Request $request)
    {
        $perPage = min($request->integer('per_page', 15), 50);
        
        $users = User::whereIn('promotion_status', [
                        \App\Enums\PromotionStatus::APPROVED,
                        \App\Enums\PromotionStatus::REJECTED
                    ])
                    ->orderBy('promoted_at', 'desc')
                    ->paginate($perPage);

        return UserResource::collection($users);
    }
}
