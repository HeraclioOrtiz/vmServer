<?php

namespace App\Exceptions\External;

use App\Exceptions\BaseException;

/**
 * External Service Exception
 *
 * For errors related to external API calls
 */
class ExternalServiceException extends BaseException
{
    protected int $statusCode = 502;
    protected string $errorCode = 'EXTERNAL_SERVICE_ERROR';

    public function __construct(
        string $message = 'External service unavailable',
        array $details = []
    ) {
        parent::__construct($message, $details);
    }
}
