<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeProfessor extends Command
{
    protected $signature = 'user:make-professor {identifier : DNI o email} {--remove : Quitar rol profesor}';
    protected $description = 'Asigna o quita el rol de profesor a un usuario por DNI o email';

    public function handle(): int
    {
        $id = $this->argument('identifier');
        $remove = (bool) $this->option('remove');

        $user = User::query()
            ->where('dni', $id)
            ->orWhere('email', $id)
            ->first();

        if (!$user) {
            $this->error('Usuario no encontrado por DNI o email.');
            return self::FAILURE;
        }

        $user->is_professor = !$remove;
        $user->save();

        $this->info(($remove ? 'Removido' : 'Asignado') . ' rol profesor para: ' . ($user->email ?? $user->dni));
        return self::SUCCESS;
    }
}
