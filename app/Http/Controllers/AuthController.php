<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Handle admin login request.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Log failed login attempt
            $this->auditLogger->logLoginAttempt(
                'admin',
                $request->email,
                false,
                $request
            );

            return response()->json([
                'success' => false,
                'message' => 'Email/password salah.',
            ], 401);
        }

        if (!$user->is_active) {
            // Log failed login attempt (inactive account)
            $this->auditLogger->logLoginAttempt(
                'admin',
                $request->email,
                false,
                $request,
                $user
            );

            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif. Hubungi administrator.',
            ], 403);
        }

        // Update last login information
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Create session
        $request->session()->put('admin_user_id', $user->id);
        $request->session()->put('admin_last_activity', now()->timestamp);

        // Log successful login attempt
        $this->auditLogger->logLoginAttempt(
            'admin',
            $request->email,
            true,
            $request,
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
        ]);
    }

    /**
     * Handle admin logout request.
     */
    public function logout(Request $request): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                // Log logout
                $this->auditLogger->logLogout('admin', $user, $request);
            }
        }

        $request->session()->forget(['admin_user_id', 'admin_last_activity']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Check if admin session is valid.
     */
    public function checkSession(Request $request): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');
        $lastActivity = $request->session()->get('admin_last_activity');

        if (!$userId || !$lastActivity) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid.',
            ], 401);
        }

        // Check 30-minute timeout
        $timeout = 30 * 60; // 30 minutes in seconds
        if (now()->timestamp - $lastActivity > $timeout) {
            $request->session()->forget(['admin_user_id', 'admin_last_activity']);
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan login kembali.',
            ], 401);
        }

        $user = User::find($userId);

        if (!$user || !$user->is_active) {
            $request->session()->forget(['admin_user_id', 'admin_last_activity']);
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid.',
            ], 401);
        }

        // Update last activity
        $request->session()->put('admin_last_activity', now()->timestamp);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
        ]);
    }
}
