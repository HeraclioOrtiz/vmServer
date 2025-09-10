<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PromotionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    
    // CRUD de usuarios
    Route::apiResource('users', UserController::class);
    
    // Rutas adicionales de usuarios
    Route::prefix('users')->group(function () {
        Route::get('search', [UserController::class, 'search']);
        Route::get('stats', [UserController::class, 'stats']);
        Route::get('needing-refresh', [UserController::class, 'needingRefresh']);
        Route::post('{user}/change-type', [UserController::class, 'changeType']);
        // Cache y mantenimiento
        Route::delete('/users/cache/{dni}', [UserController::class, 'clearUserCache']);
        Route::delete('/users/cache', [UserController::class, 'clearAllCache']);
        Route::post('/users/{id}/restore', [UserController::class, 'restore']);
        Route::delete('/users/dni/{dni}', [UserController::class, 'deleteByDni']);
    });
    
    // Rutas de promoción
    Route::prefix('promotion')->group(function () {
        Route::post('promote', [PromotionController::class, 'promote']);
        Route::get('eligibility', [PromotionController::class, 'checkEligibility']);
        Route::post('check-dni', [PromotionController::class, 'checkDniInClub']);
        Route::post('request', [PromotionController::class, 'requestPromotion']);
        Route::get('stats', [PromotionController::class, 'stats']);
        Route::get('eligible', [PromotionController::class, 'eligible']);
        
        // Rutas administrativas
        Route::get('pending', [PromotionController::class, 'pending']);
        Route::get('history', [PromotionController::class, 'history']);
        Route::post('{user}/approve', [PromotionController::class, 'approve']);
        Route::post('{user}/reject', [PromotionController::class, 'reject']);
    });
});
