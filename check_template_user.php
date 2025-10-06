<?php

echo "👤 === VERIFICAR USUARIO DE PLANTILLAS === 👤\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $templates = \App\Models\Gym\DailyTemplate::all();
    
    echo "PLANTILLAS Y SUS USUARIOS:\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($templates as $template) {
        echo "\n📋 Plantilla: {$template->title}\n";
        echo "   ID: {$template->id}\n";
        echo "   Created By: " . ($template->created_by ?? 'NULL') . "\n";
        
        if ($template->created_by) {
            $user = \App\Models\User::find($template->created_by);
            if ($user) {
                echo "   👤 Usuario: {$user->name}\n";
                echo "      Email: {$user->email}\n";
                echo "      DNI: {$user->dni}\n";
                $userType = is_object($user->user_type) ? $user->user_type->value : $user->user_type;
                echo "      Tipo: {$userType}\n";
                echo "      Admin: " . ($user->is_admin ? 'Sí' : 'No') . "\n";
                echo "      Profesor: " . ($user->is_professor ? 'Sí' : 'No') . "\n";
            } else {
                echo "   ❌ Usuario NO encontrado (ID huérfano)\n";
            }
        } else {
            echo "   ⚠️  Sin usuario asignado (NULL)\n";
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
    
    // Mostrar usuarios disponibles
    echo "USUARIOS DISPONIBLES EN EL SISTEMA:\n";
    echo str_repeat("=", 80) . "\n";
    
    $users = \App\Models\User::all();
    
    if ($users->isEmpty()) {
        echo "❌ NO HAY USUARIOS EN LA BASE DE DATOS\n";
        echo "   Las plantillas se crearon con created_by = NULL\n";
    } else {
        foreach ($users as $user) {
            echo "\n{$user->id}. {$user->name}\n";
            echo "   Email: {$user->email}\n";
            $userType = is_object($user->user_type) ? $user->user_type->value : $user->user_type;
            echo "   Tipo: {$userType}\n";
            echo "   Admin: " . ($user->is_admin ? 'Sí' : 'No') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
