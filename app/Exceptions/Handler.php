<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'pin',
        'bank_account_number',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log exception with sensitive data masked
            $this->logExceptionWithMasking($e);
        });
    }

    /**
     * Log exception with sensitive data masked
     *
     * @param Throwable $e
     */
    protected function logExceptionWithMasking(Throwable $e): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        // Mask sensitive data in context
        $context = $this->maskSensitiveData($context);

        Log::error('Exception occurred', $context);
    }

    /**
     * Mask sensitive data in arrays
     *
     * @param array $data
     * @return array
     */
    protected function maskSensitiveData(array $data): array
    {
        $sensitiveKeys = [
            'password',
            'pin',
            'bank_account_number',
            'credit_card',
            'cvv',
            'token',
            'secret',
            'api_key',
            'server_key',
            'client_key',
        ];

        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = $this->maskSensitiveData($value);
            } elseif (in_array(strtolower($key), $sensitiveKeys)) {
                $value = '***MASKED***';
            }
        }

        return $data;
    }
}
