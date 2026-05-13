<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendantRequest;
use App\Http\Requests\UpdateAttendantRequest;
use App\Models\ParkingAttendant;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParkingAttendantController extends Controller
{
    protected AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Display a listing of all parking attendants.
     * 
     * Requirements: 7.6
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        $attendants = ParkingAttendant::query()
            ->withCount([
                'transactions as transaction_count',
                'transactions as success_transaction_count' => fn ($query) => $query->where('payment_status', 'success'),
                'transactions as pending_transaction_count' => fn ($query) => $query->where('payment_status', 'pending'),
                'transactions as expired_transaction_count' => fn ($query) => $query->where('payment_status', 'expired'),
            ])
            ->withSum([
                'transactions as total_revenue' => fn ($query) => $query->where('payment_status', 'success'),
            ], 'amount')
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $search = strtolower($search);

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(registration_number) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($validated['location'] ?? null, fn ($query, string $location) => $query->where('street_section', $location))
            ->when(($validated['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($validated['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('registration_number')
            ->get()
            ->map(function (ParkingAttendant $attendant) {
                $attendant->transaction_count = (int) ($attendant->transaction_count ?? 0);
                $attendant->success_transaction_count = (int) ($attendant->success_transaction_count ?? 0);
                $attendant->pending_transaction_count = (int) ($attendant->pending_transaction_count ?? 0);
                $attendant->expired_transaction_count = (int) ($attendant->expired_transaction_count ?? 0);
                $attendant->total_revenue = (float) ($attendant->total_revenue ?? 0);

                return $attendant;
            });

        $locations = ParkingAttendant::query()
            ->whereNotNull('street_section')
            ->distinct()
            ->orderBy('street_section')
            ->pluck('street_section')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $attendants,
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created parking attendant in storage.
     * 
     * Requirements: 7.1, 7.2
     */
    public function store(StoreAttendantRequest $request): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');
        $user = User::find($userId);

        $attendant = ParkingAttendant::create($request->validated());

        // Log the creation
        $this->auditLogger->log(
            'attendant_created',
            [
                'entity_type' => 'parking_attendant',
                'entity_id' => $attendant->id,
                'new_values' => $attendant->toArray(),
            ],
            $user,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Juru parkir berhasil dibuat.',
            'data' => $attendant,
        ], 201);
    }

    /**
     * Display the specified parking attendant.
     * 
     * Requirements: 7.6
     */
    public function show(int $id): JsonResponse
    {
        $attendant = ParkingAttendant::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $attendant,
        ]);
    }

    /**
     * Update the specified parking attendant in storage.
     * 
     * Requirements: 7.3
     */
    public function update(UpdateAttendantRequest $request, int $id): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');
        $user = User::find($userId);
        $attendant = ParkingAttendant::findOrFail($id);

        // Store old values for audit log
        $oldValues = $attendant->toArray();

        $attendant->update($request->validated());

        // Log the update with old and new values
        $this->auditLogger->log(
            'attendant_updated',
            [
                'entity_type' => 'parking_attendant',
                'entity_id' => $attendant->id,
                'old_values' => $oldValues,
                'new_values' => $attendant->toArray(),
            ],
            $user,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Juru parkir berhasil diperbarui.',
            'data' => $attendant,
        ]);
    }

    /**
     * Activate the specified parking attendant.
     * 
     * Requirements: 7.4
     */
    public function activate(Request $request, int $id): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');
        $user = User::find($userId);
        $attendant = ParkingAttendant::findOrFail($id);

        $oldValues = $attendant->toArray();
        $attendant->update(['is_active' => true]);

        // Log the activation
        $this->auditLogger->log(
            'attendant_activated',
            [
                'entity_type' => 'parking_attendant',
                'entity_id' => $attendant->id,
                'old_values' => $oldValues,
                'new_values' => $attendant->toArray(),
            ],
            $user,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Juru parkir berhasil diaktifkan.',
            'data' => $attendant,
        ]);
    }

    /**
     * Deactivate the specified parking attendant.
     * 
     * Requirements: 7.4, 7.5
     */
    public function deactivate(Request $request, int $id): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');
        $user = User::find($userId);
        $attendant = ParkingAttendant::findOrFail($id);

        $oldValues = $attendant->toArray();
        $attendant->update(['is_active' => false]);

        // Log the deactivation
        $this->auditLogger->log(
            'attendant_deactivated',
            [
                'entity_type' => 'parking_attendant',
                'entity_id' => $attendant->id,
                'old_values' => $oldValues,
                'new_values' => $attendant->toArray(),
            ],
            $user,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Juru parkir berhasil dinonaktifkan.',
            'data' => $attendant,
        ]);
    }
}
