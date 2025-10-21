<?php

namespace App\Exceptions\Business;

/**
 * Invalid Operation Exception
 *
 * Thrown when an operation cannot be performed due to current state
 */
class InvalidOperationException extends BusinessException
{
    protected int $statusCode = 422;
    protected string $errorCode = 'INVALID_OPERATION';

    public function __construct(
        string $message = 'This operation cannot be performed',
        array $details = []
    ) {
        parent::__construct($message, $details);
    }
}
