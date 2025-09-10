<?php

namespace App\Exceptions;

use Exception;

class ApiConnectionException extends Exception
{
    public function __construct(string $message = 'Error connecting to external API', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
