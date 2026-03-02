<?php

namespace App\Http\Middleware;

use App\Models\ParkingAttendant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttendantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if session has attendant user ID
        $attendantId = $request->session()->get('attendant_user_id');
        $lastActivity = $request->session()->get('attendant_last_activity');

        if (!$attendantId || !$lastActivity) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid. Silakan login kembali.',
            ], 401);
        }

        // Check 15-minute session timeout
        $timeout = 15 * 60; // 15 minutes in seconds
        if (now()->timestamp - $lastActivity > $timeout) {
            $request->session()->forget(['attendant_user_id', 'attendant_last_activity']);
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan login kembali.',
            ], 401);
        }

        // Verify attendant exists and is active
        $attendant = ParkingAttendant::find($attendantId);

        if (!$attendant || !$attendant->is_active) {
            $request->session()->forget(['attendant_user_id', 'attendant_last_activity']);
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif atau tidak ditemukan.',
            ], 401);
        }

        // Update last activity timestamp
        $request->session()->put('attendant_last_activity', now()->timestamp);

        // Attach attendant to request for use in controllers
        $request->merge(['authenticated_attendant' => $attendant]);

        return $next($request);
    }
}
