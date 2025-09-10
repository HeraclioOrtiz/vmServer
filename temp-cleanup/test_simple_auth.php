<?php

// Simple test script to verify unified authentication
echo "🧪 Testing Unified Authentication Flow\n";
echo "====================================\n\n";

// Test 1: Check if migration is needed
echo "1. Checking database schema...\n";

try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Check if foto_url column exists
    $hasColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'foto_url');
    
    if ($hasColumn) {
        echo "✅ foto_url column exists in users table\n";
    } else {
        echo "❌ foto_url column missing - migration needed\n";
    }
    
    // Test 2: Check AuthService methods
    echo "\n2. Testing AuthService availability...\n";
    
    $authService = app(\App\Services\AuthService::class);
    echo "✅ AuthService instantiated successfully\n";
    
    // Test 3: Check SociosApi service
    echo "\n3. Testing SociosApi service...\n";
    
    $sociosApi = app(\App\Services\SociosApi::class);
    echo "✅ SociosApi service instantiated successfully\n";
    
    // Test 4: Check User model
    echo "\n4. Testing User model...\n";
    
    $userCount = \App\Models\User::count();
    echo "✅ User model accessible - Current users: {$userCount}\n";
    
    // Test 5: Check if foto_url is in fillable
    $user = new \App\Models\User();
    $fillable = $user->getFillable();
    
    if (in_array('foto_url', $fillable)) {
        echo "✅ foto_url is in User fillable attributes\n";
    } else {
        echo "❌ foto_url not in fillable attributes\n";
    }
    
    echo "\n🏁 Basic checks completed successfully!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
