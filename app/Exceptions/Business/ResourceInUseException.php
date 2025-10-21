<?php

namespace App\Exceptions\Business;

/**
 * Resource In Use Exception
 *
 * Thrown when attempting to delete a resource that has dependencies
 */
class ResourceInUseException extends BusinessException
{
    protected int $statusCode = 422;
    protected string $errorCode = 'RESOURCE_IN_USE';

    public function __construct(
        string $message = 'Cannot delete resource because it is being used',
        array $details = []
    ) {
        parent::__construct($message, $details);
    }
}
