<?php

namespace App\DTOs;

use App\Models\User;

/**
 * Resultado de autenticación
 */
class AuthResult
{
    public function __construct(
        public User $user,
        public bool $fetchedFromApi = false,
        public bool $refreshed = false
    ) {}
}
