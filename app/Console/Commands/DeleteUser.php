<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CacheService;
use Illuminate\Console\Command;

class DeleteUser extends Command
{
    protected $signature = 'user:delete {dni} {--force : Skip confirmation}';
    protected $description = 'Delete user by DNI and clear cache';

    public function handle()
    {
        $dni = $this->argument('dni');
        $force = $this->option('force');
        
        $user = User::where('dni', $dni)->first();
        
        if (!$user) {
            $this->error("❌ User with DNI {$dni} not found");
            return 1;
        }
        
        $this->info("👤 User found:");
        $this->table(['Field', 'Value'], [
            ['ID', $user->id],
            ['DNI', $user->dni],
            ['Name', $user->name],
            ['Email', $user->email],
            ['Type', $user->user_type->value],
            ['Created', $user->created_at->format('Y-m-d H:i:s')],
        ]);
        
        if (!$force && !$this->confirm('Are you sure you want to delete this user?')) {
            $this->info('Operation cancelled');
            return 0;
        }
        
        // Delete user tokens
        $user->tokens()->delete();
        
        // Clear cache
        $cacheService = app(CacheService::class);
        $cacheService->forgetUser($dni);
        $cacheService->clearNegativeResult($dni);
        
        // Delete user
        $user->delete();
        
        $this->info("✅ User deleted successfully");
        $this->info("🧹 Cache cleared for DNI: {$dni}");
        $this->info("🔄 Ready for fresh registration");
        
        return 0;
    }
}
