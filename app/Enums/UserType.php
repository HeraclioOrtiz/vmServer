<?php

namespace App\Enums;

enum UserType: string
{
    case LOCAL = 'local';
    case API = 'api';

    public function label(): string
    {
        return match($this) {
            self::LOCAL => 'Usuario Local',
            self::API => 'Usuario API',
        };
    }

    public function isLocal(): bool
    {
        return $this === self::LOCAL;
    }

    public function isApi(): bool
    {
        return $this === self::API;
    }
}
