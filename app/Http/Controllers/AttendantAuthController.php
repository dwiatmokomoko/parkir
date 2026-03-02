<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendantLoginRequest;
use App\Models\ParkingAttendant;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AttendantAuthController extends Controller
{
    protected AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Handle attendant login request.
     */
    public function login(AttendantLoginRequest $request): JsonResponse
    {
        $attendant = ParkingAttendant::where('registration_number', $request->registration_number)->first();

        if (!$attendant || !Hash::check($request->pin, $attendant->pin)) {
            // Log failed login attempt
            $this->auditLogger->logLoginAttempt(
                'attendant',
                $request->registration_number,
                false,
                $request
            );

            return response()->json([
                'success' => false,
                'message' => 'Nomor registrasi/PIN salah.',
            ], 401);
        }

        if (!$attendant->is_active) {
            // Log failed login attempt (inactive account)
            $this->auditLogger->logLoginAttempt(
                'attendant',
                $request->registration_number,
                false,
                $request,
                $attendant
            );

            return response()->json([
                'success' => false,
                'message' => 'Akun juru parkir tidak aktif. Hubungi admin.',
            ], 403);
        }

        // Create session with 15-minute timeout
        $request->session()->put('attendant_user_id', $attendant->id);
        $request->session()->put('attendant_last_activity', now()->timestamp);

        // Log successful login attempt
        $this->auditLogger->logLoginAttempt(
            'attendant',
            $request->registration_number,
            true,
            $request,
            $attendant
        );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'attendant' => [
                    'id' => $attendant->id,
                    'registration_number' => $attendant->registration_number,
                    'name' => $attendant->name,
                    'street_section' => $attendant->street_section,
                    'location_side' => $attendant->location_side,
                ],
            ],
        ]);
    }

    /**
     * Handle attendant logout request.
     */
    public function logout(Request $request): JsonResponse
    {
        $attendantId = $request->session()->get('attendant_user_id');
        
        if ($attendantId) {
            $attendant = ParkingAttendant::find($attendantId);
            if ($attendant) {
                // Log logout
                $this->auditLogger->logLogout('attendant', $attendant, $request);
            }
        }

        $request->session()->forget(['attendant_user_id', 'attendant_last_activity']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Check if attendant session is valid.
     */
    public function checkSession(Request $request): JsonResponse
    {
        $attendantId = $request->session()->get('attendant_user_id');
        $lastActivity = $request->session()->get('attendant_last_activity');

        if (!$attendantId || !$lastActivity) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid.',
            ], 401);
        }

        // Check 15-minute timeout
        $timeout = 15 * 60; // 15 minutes in seconds
        if (now()->timestamp - $lastActivity > $timeout) {
            $request->session()->forget(['attendant_user_id', 'attendant_last_activity']);
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan login kembali.',
            ], 401);
        }

        $attendant = ParkingAttendant::find($attendantId);

        if (!$attendant || !$attendant->is_active) {
            $request->session()->forget(['attendant_user_id', 'attendant_last_activity']);
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid.',
            ], 401);
        }

        // Update last activity
        $request->session()->put('attendant_last_activity', now()->timestamp);

        return response()->json([
            'success' => true,
            'data' => [
                'attendant' => [
                    'id' => $attendant->id,
                    'registration_number' => $attendant->registration_number,
                    'name' => $attendant->name,
                    'street_section' => $attendant->street_section,
                    'location_side' => $attendant->location_side,
                ],
            ],
        ]);
    }
}
