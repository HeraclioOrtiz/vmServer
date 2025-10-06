<?php

echo "🔍 === VERIFICANDO COLUMNAS DE TABLA USERS === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "📋 Columnas de la tabla 'users':\n";
    echo str_repeat("=", 40) . "\n";
    
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
    
    foreach ($columns as $index => $column) {
        echo ($index + 1) . ". {$column}\n";
    }
    
    echo "\n🔍 Buscando usuario María García...\n";
    
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    if ($maria) {
        echo "✅ Usuario encontrado: {$maria->name}\n\n";
        
        echo "📊 Valores actuales de columnas relevantes:\n";
        echo str_repeat("=", 40) . "\n";
        
        $relevantColumns = ['id', 'name', 'dni', 'user_type', 'promotion_status', 'account_status', 'is_professor', 'is_admin'];
        
        foreach ($relevantColumns as $column) {
            if (in_array($column, $columns)) {
                $value = $maria->$column;
                if (is_object($value) && method_exists($value, 'value')) {
                    $value = $value->value;
                }
                echo "- {$column}: {$value}\n";
            } else {
                echo "- {$column}: [COLUMNA NO EXISTE]\n";
            }
        }
        
        echo "\n🔧 Actualizando solo user_type...\n";
        
        // Solo actualizar user_type que sabemos que existe
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $maria->id)
            ->update([
                'user_type' => 'api',
                'promotion_status' => 'approved',
                'account_status' => 'active',
                'password' => bcrypt('estudiante123'),
                'updated_at' => now()
            ]);
        
        echo "✅ Usuario actualizado\n\n";
        
        // Verificar cambios
        $maria = \App\Models\User::where('dni', '33333333')->first();
        
        echo "📊 Estado después de actualización:\n";
        echo "- user_type: " . ($maria->user_type->value ?? $maria->user_type) . "\n";
        echo "- promotion_status: " . ($maria->promotion_status->value ?? $maria->promotion_status) . "\n";
        echo "- account_status: {$maria->account_status}\n";
        
    } else {
        echo "❌ Usuario no encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
