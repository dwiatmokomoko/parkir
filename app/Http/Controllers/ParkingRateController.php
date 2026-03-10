<?php

namespace App\Http\Controllers;

use App\Models\ParkingRate;
use App\Http\Requests\UpdateParkingRateRequest;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParkingRateController extends Controller
{
    protected AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Get all parking rates
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $rates = ParkingRate::with('creator')
            ->orderBy('vehicle_type')
            ->orderBy('street_section')
            ->orderBy('effective_from', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rates,
        ]);
    }

    /**
     * Update parking rates
     *
     * @param UpdateParkingRateRequest $request
     * @return JsonResponse
     */
    public function update(UpdateParkingRateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        // Get user from request (set by AdminMiddleware)
        $user = $request->authenticated_user ?? auth()->user();

        // Get existing rate for comparison (if updating existing rate)
        $existingRate = null;
        if (isset($validated['id'])) {
            $existingRate = ParkingRate::find($validated['id']);
        }

        // Create new rate record
        $rate = ParkingRate::create([
            'vehicle_type' => $validated['vehicle_type'],
            'street_section' => $validated['street_section'] ?? null,
            'rate' => $validated['rate'],
            'effective_from' => $validated['effective_from'] ?? now(),
            'created_by' => $user->id,
        ]);

        // Log the rate change
        $this->auditLogger->log(
            'parking_rate_updated',
            [
                'entity_type' => 'parking_rate',
                'entity_id' => $rate->id,
                'old_values' => $existingRate ? [
                    'rate' => $existingRate->rate,
                    'effective_from' => $existingRate->effective_from,
                ] : null,
                'new_values' => [
                    'vehicle_type' => $rate->vehicle_type,
                    'street_section' => $rate->street_section,
                    'rate' => $rate->rate,
                    'effective_from' => $rate->effective_from,
                ],
            ],
            $user,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Parking rate updated successfully',
            'data' => $rate->load('creator'),
        ], 201);
    }

    /**
     * Get rates by location (street section)
     *
     * @param string $streetSection
     * @return JsonResponse
     */
    public function getByLocation(string $streetSection): JsonResponse
    {
        $rates = ParkingRate::where('street_section', $streetSection)
            ->orWhereNull('street_section')
            ->where('effective_from', '<=', now())
            ->orderBy('street_section', 'DESC')
            ->orderBy('effective_from', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rates,
        ]);
    }
}
