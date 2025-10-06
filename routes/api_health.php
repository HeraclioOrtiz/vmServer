<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/health', function () {
    try {
        // Test database connection
        DB::connection()->getPdo();
        $dbStatus = 'connected';
        $dbMessage = 'Database connection successful';
        
        // Test basic query
        $userCount = DB::table('users')->count();
        
    } catch (\Exception $e) {
        $dbStatus = 'failed';
        $dbMessage = $e->getMessage();
        $userCount = null;
    }
    
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'database' => [
            'status' => $dbStatus,
            'message' => $dbMessage,
            'user_count' => $userCount
        ],
        'php' => [
            'version' => PHP_VERSION,
            'extensions' => [
                'mysql' => extension_loaded('mysql'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'mysqli' => extension_loaded('mysqli')
            ]
        ]
    ]);
});
