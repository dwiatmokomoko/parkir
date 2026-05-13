<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $perPage = (int) $request->query('per_page', $request->query('limit', 15));
        $perPage = max(1, min($perPage, 100));
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        // Validate sort parameters
        if (!in_array($sortBy, ['created_at', 'action', 'user_id', 'entity_type'])) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query = $this->filteredQuery($request)->with('user');

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $auditLogs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $auditLogs->items(),
            'total' => $auditLogs->total(),
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

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        $query = $this->filteredQuery($request)->with('user');

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $auditLogs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $auditLogs->items(),
            'total' => $auditLogs->total(),
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
     * Export audit logs as CSV.
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.csv';
        $query = $this->filteredQuery($request)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5000);

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Waktu',
                'Pengguna',
                'Email',
                'Aksi',
                'Entitas',
                'ID Entitas',
                'IP Address',
                'User Agent',
                'Nilai Lama',
                'Nilai Baru',
            ]);

            foreach ($query->get() as $log) {
                fputcsv($handle, [
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'System',
                    $log->user?->email ?? '',
                    $log->action,
                    $log->entity_type,
                    $log->entity_id,
                    $log->ip_address,
                    $log->user_agent,
                    json_encode($log->old_values ?? [], JSON_UNESCAPED_UNICODE),
                    json_encode($log->new_values ?? [], JSON_UNESCAPED_UNICODE),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Users available for audit filtering.
     *
     * @return JsonResponse
     */
    public function users(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Build the shared audit log query with compatible filter names.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filteredQuery(Request $request)
    {
        $dateFrom = $request->query('date_from', $request->query('dateFrom'));
        $dateTo = $request->query('date_to', $request->query('dateTo'));
        $userId = $request->query('user_id', $request->query('user'));
        $action = $request->query('action');
        $entityType = $request->query('entity_type');
        $search = $request->query('search');

        return AuditLog::query()
            ->when($dateFrom, function ($query, $value) {
                $query->where('created_at', '>=', Carbon::parse($value)->startOfDay());
            })
            ->when($dateTo, function ($query, $value) {
                $query->where('created_at', '<=', Carbon::parse($value)->endOfDay());
            })
            ->when($userId, function ($query, $value) {
                $query->where('user_id', $value);
            })
            ->when($action, function ($query, $value) {
                $query->where('action', 'like', '%' . $value . '%');
            })
            ->when($entityType, function ($query, $value) {
                $query->where('entity_type', $value);
            })
            ->when($search, function ($query, $value) {
                $query->where(function ($searchQuery) use ($value) {
                    $searchQuery->where('action', 'like', '%' . $value . '%')
                        ->orWhere('entity_type', 'like', '%' . $value . '%')
                        ->orWhere('ip_address', 'like', '%' . $value . '%')
                        ->orWhereHas('user', function ($userQuery) use ($value) {
                            $userQuery->where('name', 'like', '%' . $value . '%')
                                ->orWhere('email', 'like', '%' . $value . '%');
                        });

                    if (is_numeric($value)) {
                        $searchQuery->orWhere('entity_id', (int) $value);
                    }
                });
            });
    }
}
