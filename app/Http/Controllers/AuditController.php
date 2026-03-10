<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * Get paginated list of audit logs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        // Validate sort parameters
        if (!in_array($sortBy, ['created_at', 'action', 'user_id', 'entity_type'])) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query = AuditLog::query();

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $auditLogs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $auditLogs->items(),
            'pagination' => [
                'total' => $auditLogs->total(),
                'per_page' => $auditLogs->perPage(),
                'current_page' => $auditLogs->currentPage(),
                'last_page' => $auditLogs->lastPage(),
                'from' => $auditLogs->firstItem(),
                'to' => $auditLogs->lastItem(),
            ],
        ]);
    }

    /**
     * Search audit logs with filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'user_id' => 'nullable|integer',
            'action' => 'nullable|string',
            'entity_type' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:created_at,action,user_id,entity_type',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        $perPage = $request->query('per_page', 15);
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        $query = AuditLog::query();

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->query('action') . '%');
        }

        // Filter by entity type
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->query('entity_type'));
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $auditLogs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $auditLogs->items(),
            'pagination' => [
                'total' => $auditLogs->total(),
                'per_page' => $auditLogs->perPage(),
                'current_page' => $auditLogs->currentPage(),
                'last_page' => $auditLogs->lastPage(),
                'from' => $auditLogs->firstItem(),
                'to' => $auditLogs->lastItem(),
            ],
        ]);
    }
}
