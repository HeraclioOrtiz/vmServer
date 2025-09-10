<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test data
$testUsers = [
    [
        'name' => 'Usuario Local Test',
        'email' => 'local@test.com',
        'dni' => '12345678',
        'password' => 'password123',
        'expected_type' => 'local'
    ],
    [
        'name' => 'Usuario API Test', 
        'email' => 'api@test.com',
        'dni' => '59964604', // This DNI should exist in external API
        'password' => 'password123',
        'expected_type' => 'api'
    ]
];

echo "🧪 TESTING UNIFIED AUTHENTICATION FLOW\n";
echo "=====================================\n\n";

foreach ($testUsers as $index => $userData) {
    echo "Test " . ($index + 1) . ": {$userData['name']}\n";
    echo "DNI: {$userData['dni']}\n";
    echo "Expected Type: {$userData['expected_type']}\n";
    echo "---\n";
    
    try {
        // Test Registration
        echo "📝 Testing Registration...\n";
        
        $authService = app(\App\Services\AuthService::class);
        
        // Clean up any existing user with this DNI
        $existingUser = \App\Models\User::where('dni', $userData['dni'])->first();
        if ($existingUser) {
            $existingUser->delete();
            echo "🗑️ Cleaned up existing user\n";
        }
        
        // Register user
        $user = $authService->registerLocal($userData);
        
        echo "✅ Registration successful!\n";
        echo "   User ID: {$user->id}\n";
        echo "   User Type: {$user->user_type->value}\n";
        echo "   Name: {$user->name}\n";
        echo "   Email: {$user->email}\n";
        echo "   DNI: {$user->dni}\n";
        
        if ($user->foto_url) {
            echo "   Photo URL: {$user->foto_url}\n";
        }
        
        // Verify expected type
        if ($user->user_type->value === $userData['expected_type']) {
            echo "✅ User type matches expected: {$userData['expected_type']}\n";
        } else {
            echo "❌ User type mismatch! Expected: {$userData['expected_type']}, Got: {$user->user_type->value}\n";
        }
        
        echo "\n📱 Testing Login...\n";
        
        // Test Login
        $loginResult = $authService->authenticate($userData['dni'], $userData['password']);
        
        echo "✅ Login successful!\n";
        echo "   User ID: {$loginResult->user->id}\n";
        echo "   User Type: {$loginResult->user->user_type->value}\n";
        echo "   Fetched from API: " . ($loginResult->fetchedFromApi ? 'Yes' : 'No') . "\n";
        echo "   Refreshed: " . ($loginResult->refreshed ? 'Yes' : 'No') . "\n";
        
        if ($loginResult->user->foto_url) {
            echo "   Photo URL: {$loginResult->user->foto_url}\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "   Class: " . get_class($e) . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

echo "🏁 Testing completed!\n";
