<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for the authenticated attendant
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $attendantId = $request->user()->id;
            $limit = $request->query('limit', 50);

            $notifications = $this->notificationService->getAttendantNotifications($attendantId, $limit);

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'count' => $notifications->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark a notification as read
     *
     * @param Request $request
     * @param int $notificationId
     * @return JsonResponse
     */
    public function markAsRead(Request $request, int $notificationId): JsonResponse
    {
        try {
            $attendantId = $request->user()->id;

            // Verify notification belongs to attendant
            $notification = \App\Models\Notification::where('id', $notificationId)
                ->where('parking_attendant_id', $attendantId)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            $success = $this->notificationService->markAsRead($notificationId);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read',
                    'data' => $notification->refresh(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark notification as read',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as read',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for the attendant
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $attendantId = $request->user()->id;
            $count = $this->notificationService->markAllAsRead($attendantId);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking all notifications as read',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread notifications for the attendant
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUnread(Request $request): JsonResponse
    {
        try {
            $attendantId = $request->user()->id;
            $notifications = $this->notificationService->getUnreadNotifications($attendantId);

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'count' => $notifications->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve unread notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
