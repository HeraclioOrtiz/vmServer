<?php

namespace App\Exceptions\Business;

/**
 * Insufficient Permissions Exception
 *
 * Thrown when user lacks required permissions for an action
 */
class InsufficientPermissionsException extends BusinessException
{
    protected int $statusCode = 403;
    protected string $errorCode = 'INSUFFICIENT_PERMISSIONS';

    public function __construct(
        string $message = 'You do not have permission to perform this action',
        array $details = []
    ) {
        parent::__construct($message, $details);
    }
}
