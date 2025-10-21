<?php

namespace App\Exceptions\Business;

use App\Exceptions\BaseException;

/**
 * Business Logic Exception
 *
 * For violations of business rules and logic
 */
class BusinessException extends BaseException
{
    protected int $statusCode = 422;
    protected string $errorCode = 'BUSINESS_RULE_VIOLATION';
}
