<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class MaskSensitiveData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log request with sensitive data masked
        if ($this->shouldLogRequest($request)) {
            $this->logRequestWithMasking($request);
        }

        return $next($request);
    }

    /**
     * Determine if the request should be logged
     */
    protected function shouldLogRequest(Request $request): bool
    {
        // Log all state-changing requests (POST, PUT, DELETE)
        return in_array($request->getMethod(), ['POST', 'PUT', 'DELETE']);
    }

    /**
     * Log request with sensitive data masked
     */
    protected function logRequestWithMasking(Request $request): void
    {
        $data = [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // Mask sensitive input data
        $input = $request->all();
        $input = $this->maskSensitiveData($input);
        $data['input'] = $input;

        Log::info('Request received', $data);
    }

    /**
     * Mask sensitive data in arrays
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
            'password_confirmation',
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
