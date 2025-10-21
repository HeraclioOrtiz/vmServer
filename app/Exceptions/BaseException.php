<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Base Exception Class
 *
 * All custom exceptions should extend this class.
 * Provides consistent error formatting and HTTP status codes.
 */
abstract class BaseException extends Exception
{
    /**
     * HTTP status code
     */
    protected int $statusCode = 500;

    /**
     * Error code (for API clients)
     */
    protected string $errorCode = 'INTERNAL_ERROR';

    /**
     * Additional details
     */
    protected array $details = [];

    /**
     * Constructor
     *
     * @param string $message Human-readable error message
     * @param array $details Additional error details
     * @param int|null $statusCode HTTP status code override
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        array $details = [],
        ?int $statusCode = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->details = $details;

        if ($statusCode !== null) {
            $this->statusCode = $statusCode;
        }
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get additional details
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Render exception as JSON response
     */
    public function render(): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $this->errorCode,
            'message' => $this->getMessage(),
        ];

        if (!empty($this->details)) {
            $response['details'] = $this->details;
        }

        // Include exception trace in debug mode
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($this),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => $this->getTraceAsString(),
            ];
        }

        return response()->json($response, $this->statusCode);
    }

    /**
     * Report exception to logging system
     */
    public function report(): bool
    {
        // Return true to report to logs
        // Override in child classes to customize reporting
        return true;
    }
}
