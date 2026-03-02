<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if session has admin user ID
        $userId = $request->session()->get('admin_user_id');
        $lastActivity = $request->session()->get('admin_last_activity');

        if (!$userId || !$lastActivity) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid. Silakan login kembali.',
            ], 401);
        }

        // Check 30-minute session timeout
        $timeout = 30 * 60; // 30 minutes in seconds
        if (now()->timestamp - $lastActivity > $timeout) {
            $request->session()->forget(['admin_user_id', 'admin_last_activity']);
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan login kembali.',
            ], 401);
        }

        // Verify user exists and is active
        $user = User::find($userId);

        if (!$user || !$user->is_active) {
            $request->session()->forget(['admin_user_id', 'admin_last_activity']);
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif atau tidak ditemukan.',
            ], 401);
        }

        // Update last activity timestamp
        $request->session()->put('admin_last_activity', now()->timestamp);

        // Attach user to request for use in controllers
        $request->merge(['authenticated_user' => $user]);

        return $next($request);
    }
}
