<?php

namespace App\Exceptions;

use Exception;

class SocioNotFoundException extends Exception
{
    public function __construct(string $dni)
    {
        parent::__construct("No se encontró el socio con DNI: {$dni}");
    }
}
