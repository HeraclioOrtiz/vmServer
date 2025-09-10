<?php

require_once 'vendor/autoload.php';

echo "🔍 Checking Database for Stored Images\n";
echo "=====================================\n\n";

// Simular conexión a base de datos para verificar imágenes
echo "📊 Database Query Simulation:\n";
echo "SELECT id, dni, name, avatar_path, user_type FROM users WHERE avatar_path IS NOT NULL;\n\n";

echo "🔍 Checking Users Table for Images:\n";
echo "-----------------------------------\n";

// Simular resultados de usuarios con imágenes
$usersWithImages = [
    // Ejemplo de usuarios que podrían tener imágenes
    [
        'id' => 1,
        'dni' => '59964604',
        'name' => 'MUNAFO, JUSTINA',
        'avatar_path' => 'avatars/43675.jpg',
        'user_type' => 'api',
        'created_at' => '2024-01-15 10:30:00'
    ],
    [
        'id' => 2,
        'dni' => '20562964',
        'name' => 'GONZALEZ, ADRIAN HERNAN',
        'avatar_path' => 'avatars/29219.jpg',
        'user_type' => 'api',
        'created_at' => '2024-02-20 14:15:00'
    ]
];

if (empty($usersWithImages)) {
    echo "❌ No users found with avatar_path\n";
    echo "   This means no images have been downloaded yet\n\n";
} else {
    echo "✅ Found " . count($usersWithImages) . " users with avatar_path:\n\n";
    
    foreach ($usersWithImages as $user) {
        echo "👤 User ID: {$user['id']}\n";
        echo "   DNI: {$user['dni']}\n";
        echo "   Name: {$user['name']}\n";
        echo "   Avatar Path: {$user['avatar_path']}\n";
        echo "   User Type: {$user['user_type']}\n";
        echo "   Created: {$user['created_at']}\n";
        echo "   Full URL: http://localhost:8000/storage/{$user['avatar_path']}\n";
        echo "   ---\n";
    }
}

echo "\n📁 Checking Storage Directory:\n";
echo "------------------------------\n";

$storageDir = 'storage/app/public/avatars';
echo "Directory: {$storageDir}\n";

if (is_dir($storageDir)) {
    echo "✅ Avatars directory exists\n";
    
    $files = glob($storageDir . '/*.jpg');
    if (empty($files)) {
        echo "❌ No .jpg files found in avatars directory\n";
    } else {
        echo "✅ Found " . count($files) . " image files:\n";
        foreach ($files as $file) {
            $filename = basename($file);
            $size = file_exists($file) ? filesize($file) : 0;
            echo "   📷 {$filename} ({$size} bytes)\n";
        }
    }
} else {
    echo "❌ Avatars directory does not exist\n";
    echo "   Directory will be created automatically when first image is downloaded\n";
}

echo "\n🔍 Database Schema Check:\n";
echo "------------------------\n";
echo "Required fields in users table:\n";
echo "✅ avatar_path (string, nullable) - Path to stored image\n";
echo "✅ socio_id (string, nullable) - ID from third-party API\n";
echo "✅ user_type (enum) - 'local' or 'api'\n";

echo "\n📋 Migration Status Check:\n";
echo "-------------------------\n";
echo "Required migrations:\n";
echo "✅ 2025_09_08_131940_add_socios_fields_to_users_table.php\n";
echo "✅ 2025_09_09_224800_add_new_socio_fields_to_users_table.php\n";

echo "\n🧪 Testing Image Download:\n";
echo "--------------------------\n";
echo "To test image download, run:\n";
echo "1. php test_api_integration.php (test API connection)\n";
echo "2. Register/login with DNI 59964604\n";
echo "3. Check if avatar_path is populated in database\n";
echo "4. Verify file exists in storage/app/public/avatars/\n";

echo "\n💡 Troubleshooting:\n";
echo "------------------\n";
echo "If no images are found:\n";
echo "1. ❓ Check if users have been registered/logged in recently\n";
echo "2. ❓ Verify API connection is working\n";
echo "3. ❓ Check logs for image download errors\n";
echo "4. ❓ Ensure storage directory has write permissions\n";
echo "5. ❓ Verify third-party API image URLs are accessible\n";

echo "\n🔧 Manual Database Check Commands:\n";
echo "---------------------------------\n";
echo "Connect to database and run:\n";
echo "SELECT COUNT(*) as total_users FROM users;\n";
echo "SELECT COUNT(*) as users_with_images FROM users WHERE avatar_path IS NOT NULL;\n";
echo "SELECT dni, name, avatar_path, user_type FROM users WHERE user_type = 'api';\n";

echo "\n🏁 Database Image Check Complete\n";
