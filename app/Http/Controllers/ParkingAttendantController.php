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
    public function index(): JsonResponse
    {
        $attendants = ParkingAttendant::all();

        return response()->json([
            'success' => true,
            'data' => $attendants,
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
