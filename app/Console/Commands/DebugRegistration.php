<?php

namespace App\Console\Commands;

use App\Services\AuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DebugRegistration extends Command
{
    protected $signature = 'debug:registration {dni}';
    protected $description = 'Debug registration flow with detailed logging';

    public function handle()
    {
        $dni = $this->argument('dni');
        
        $this->info("🔍 Debugging registration flow for DNI: {$dni}");
        
        // Simular datos de registro
        $userData = [
            'dni' => $dni,
            'name' => 'Debug User',
            'email' => 'debug@example.com',
            'password' => 'password123',
            'phone' => '+54911000000'
        ];
        
        $this->info("📋 Simulating registration with data:");
        $this->table(['Field', 'Value'], [
            ['DNI', $userData['dni']],
            ['Name', $userData['name']],
            ['Email', $userData['email']],
        ]);
        
        try {
            $authService = app(AuthService::class);
            
            $this->info("🚀 Calling AuthService::registerLocal()...");
            
            // Capturar logs en tiempo real
            $this->info("📝 Check logs with: tail -f storage/logs/laravel.log");
            
            $user = $authService->registerLocal($userData);
            
            $this->info("✅ Registration completed!");
            $this->table(['Field', 'Value'], [
                ['ID', $user->id],
                ['DNI', $user->dni],
                ['Type', $user->user_type->value],
                ['Name', $user->name],
                ['Email', $user->email],
            ]);
            
            if ($user->user_type->value === 'api') {
                $this->info("🎉 SUCCESS: User was promoted to API type!");
            } else {
                $this->warn("⚠️  User remained as LOCAL type - check logs for details");
            }
            
        } catch (\Exception $e) {
            $this->error("💥 Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
        }
        
        return 0;
    }
}
