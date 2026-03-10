<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * List of endpoints to skip logging (sensitive endpoints).
     *
     * @var array
     */
    protected $skipLogging = [
        'api/auth/login',
        'api/attendant/auth/login',
    ];

    /**
     * Create a new middleware instance.
     *
     * @param AuditLogger $auditLogger
     */
    public function __construct(protected AuditLogger $auditLogger)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log state-changing requests (POST, PUT, DELETE)
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
            // Skip logging for sensitive endpoints
            if (!$this->shouldSkipLogging($request)) {
                $this->logRequest($request, $response);
            }
        }

        return $response;
    }

    /**
     * Check if the request should skip logging.
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        $path = $request->path();

        foreach ($this->skipLogging as $skipPath) {
            if (str_contains($path, $skipPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the request.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    protected function logRequest(Request $request, Response $response): void
    {
        $user = $request->user();
        $method = $request->method();
        $path = $request->path();

        // Determine action based on HTTP method
        $action = match ($method) {
            'POST' => 'create',
            'PUT' => 'update',
            'DELETE' => 'delete',
            default => 'unknown',
        };

        // Extract entity type from path
        $pathSegments = explode('/', $path);
        $entityType = $pathSegments[1] ?? 'unknown';

        // Get response status
        $responseStatus = $response->getStatusCode();

        $data = [
            'entity_type' => $entityType,
            'new_values' => [
                'method' => $method,
                'path' => $path,
                'status' => $responseStatus,
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        // Log the action
        $this->auditLogger->log($action, $data, $user, $request);
    }
}
