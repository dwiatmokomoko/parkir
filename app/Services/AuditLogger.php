<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\ParkingAttendant;
use Illuminate\Http\Request;

class AuditLogger
{
    /**
     * Log an action to the audit log.
     *
     * @param string $action The action being performed
     * @param array $data Additional data to log
     * @param User|ParkingAttendant|null $user The user performing the action
     * @param Request|null $request The HTTP request
     * @return AuditLog
     */
    public function log(
        string $action,
        array $data = [],
        $user = null,
        ?Request $request = null
    ): AuditLog {
        $logData = [
            'action' => $action,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'created_at' => now(),
        ];

        // Determine user type and ID
        if ($user instanceof User) {
            $logData['user_id'] = $user->id;
            // Only set user_type if not already provided in data
            if (!isset($data['user_type'])) {
                $logData['user_type'] = 'admin';
            }
        } elseif ($user instanceof ParkingAttendant) {
            $logData['user_id'] = $user->id;
            // Only set user_type if not already provided in data
            if (!isset($data['user_type'])) {
                $logData['user_type'] = 'attendant';
            }
        }

        // Preserve user_type from data if provided (for failed login attempts)
        if (isset($data['user_type'])) {
            $logData['user_type'] = $data['user_type'];
        }

        // Add entity information if provided
        if (isset($data['entity_type'])) {
            $logData['entity_type'] = $data['entity_type'];
        }
        if (isset($data['entity_id'])) {
            $logData['entity_id'] = $data['entity_id'];
        }
        if (isset($data['old_values'])) {
            $logData['old_values'] = $data['old_values'];
        }
        if (isset($data['new_values'])) {
            $logData['new_values'] = $data['new_values'];
        }

        return AuditLog::create($logData);
    }

    /**
     * Log a login attempt.
     *
     * @param string $userType 'admin' or 'attendant'
     * @param string $identifier Email or registration number
     * @param bool $success Whether the login was successful
     * @param Request $request The HTTP request
     * @param User|ParkingAttendant|null $user The authenticated user (if successful)
     * @return AuditLog
     */
    public function logLoginAttempt(
        string $userType,
        string $identifier,
        bool $success,
        Request $request,
        $user = null
    ): AuditLog {
        $action = $success ? "{$userType}_login_success" : "{$userType}_login_failed";
        
        $data = [
            'entity_type' => $userType,
            'user_type' => $userType, // Always set user_type for login attempts
            'new_values' => [
                'identifier' => $identifier,
                'success' => $success,
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        return $this->log($action, $data, $user, $request);
    }

    /**
     * Log a logout action.
     *
     * @param string $userType 'admin' or 'attendant'
     * @param User|ParkingAttendant $user The user logging out
     * @param Request $request The HTTP request
     * @return AuditLog
     */
    public function logLogout(
        string $userType,
        $user,
        Request $request
    ): AuditLog {
        $action = "{$userType}_logout";
        
        $data = [
            'entity_type' => $userType,
            'entity_id' => $user->id,
        ];

        return $this->log($action, $data, $user, $request);
    }
}
