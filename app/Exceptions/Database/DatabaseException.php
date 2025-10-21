<?php

namespace App\Exceptions\Database;

use App\Exceptions\BaseException;

/**
 * Database Exception
 *
 * For database-related errors
 */
class DatabaseException extends BaseException
{
    protected int $statusCode = 500;
    protected string $errorCode = 'DATABASE_ERROR';

    public function __construct(
        string $message = 'A database error occurred',
        array $details = []
    ) {
        parent::__construct($message, $details);
    }

    /**
     * Don't expose database details in non-debug mode
     */
    public function render(): \Illuminate\Http\JsonResponse
    {
        if (!config('app.debug')) {
            // Sanitize message in production
            $this->message = 'A database error occurred. Please try again later.';
            $this->details = [];
        }

        return parent::render();
    }
}
