<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\ParkingAttendant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification to a parking attendant
     *
     * @param int $attendantId
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @param int|null $transactionId
     * @return Notification|null
     */
    public function notifyAttendant(
        int $attendantId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?int $transactionId = null
    ): ?Notification {
        try {
            // Verify attendant exists and is active
            $attendant = ParkingAttendant::find($attendantId);
            if (!$attendant || !$attendant->is_active) {
                Log::warning('Notification not sent: attendant not found or inactive', [
                    'attendant_id' => $attendantId,
                ]);
                return null;
            }

            // Create notification record
            $notification = Notification::create([
                'parking_attendant_id' => $attendantId,
                'transaction_id' => $transactionId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'is_read' => false,
                'created_at' => now(),
            ]);

            Log::info('Notification created', [
                'notification_id' => $notification->id,
                'attendant_id' => $attendantId,
                'type' => $type,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Error creating notification', [
                'error' => $e->getMessage(),
                'attendant_id' => $attendantId,
            ]);
            return null;
        }
    }

    /**
     * Get all notifications for an attendant
     *
     * @param int $attendantId
     * @param int $limit
     * @return Collection
     */
    public function getAttendantNotifications(int $attendantId, int $limit = 50): Collection
    {
        return Notification::where('parking_attendant_id', $attendantId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread notifications for an attendant
     *
     * @param int $attendantId
     * @return Collection
     */
    public function getUnreadNotifications(int $attendantId): Collection
    {
        return Notification::where('parking_attendant_id', $attendantId)
            ->where('is_read', false)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Mark a notification as read
     *
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead(int $notificationId): bool
    {
        try {
            $notification = Notification::find($notificationId);
            if (!$notification) {
                return false;
            }

            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            Log::info('Notification marked as read', [
                'notification_id' => $notificationId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error marking notification as read', [
                'error' => $e->getMessage(),
                'notification_id' => $notificationId,
            ]);
            return false;
        }
    }

    /**
     * Mark all notifications as read for an attendant
     *
     * @param int $attendantId
     * @return int
     */
    public function markAllAsRead(int $attendantId): int
    {
        try {
            $count = Notification::where('parking_attendant_id', $attendantId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            Log::info('All notifications marked as read', [
                'attendant_id' => $attendantId,
                'count' => $count,
            ]);

            return $count;
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read', [
                'error' => $e->getMessage(),
                'attendant_id' => $attendantId,
            ]);
            return 0;
        }
    }

    /**
     * Get notifications for an attendant filtered by date
     *
     * @param int $attendantId
     * @param string $date (Y-m-d format)
     * @return Collection
     */
    public function getNotificationsByDate(int $attendantId, string $date): Collection
    {
        return Notification::where('parking_attendant_id', $attendantId)
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
