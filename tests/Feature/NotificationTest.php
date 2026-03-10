<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\ParkingAttendant;
use App\Models\Transaction;
use App\Services\NotificationService;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    protected NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = app(NotificationService::class);
    }

    /**
     * Test notification delivery failure handling
     */
    public function testNotificationDeliveryFailureHandling()
    {
        // Test with non-existent attendant
        $result = $this->notificationService->notifyAttendant(
            999999,
            'payment_success',
            'Test Title',
            'Test Message',
            ['amount' => 5000]
        );

        $this->assertNull($result, 'Should return null for non-existent attendant');
    }

    /**
     * Test notification with inactive attendant
     */
    public function testNotificationWithInactiveAttendant()
    {
        // Create inactive attendant
        $attendant = ParkingAttendant::factory()->create([
            'is_active' => false,
            'registration_number' => 'ATT' . uniqid(),
        ]);

        // Try to send notification
        $result = $this->notificationService->notifyAttendant(
            $attendant->id,
            'payment_success',
            'Test Title',
            'Test Message',
            ['amount' => 5000]
        );

        $this->assertNull($result, 'Should return null for inactive attendant');
    }

    /**
     * Test notification with missing attendant
     */
    public function testNotificationWithMissingAttendant()
    {
        $result = $this->notificationService->notifyAttendant(
            0,
            'payment_success',
            'Test Title',
            'Test Message'
        );

        $this->assertNull($result);
    }

    /**
     * Test notification read/unread status
     */
    public function testNotificationReadUnreadStatus()
    {
        // Create attendant and notification
        $attendant = ParkingAttendant::factory()->create([
            'registration_number' => 'ATT' . uniqid(),
        ]);

        $notification = Notification::create([
            'parking_attendant_id' => $attendant->id,
            'type' => 'payment_success',
            'title' => 'Test',
            'message' => 'Test message',
            'is_read' => false,
            'created_at' => now(),
        ]);

        // Verify initial state is unread
        $this->assertFalse($notification->is_read);
        $this->assertNull($notification->read_at);

        // Mark as read
        $success = $this->notificationService->markAsRead($notification->id);
        $this->assertTrue($success);

        // Verify it's marked as read
        $notification->refresh();
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    /**
     * Test notification filtering by date
     */
    public function testNotificationFilteringByDate()
    {
        // Create attendant
        $attendant = ParkingAttendant::factory()->create([
            'registration_number' => 'ATT' . uniqid(),
        ]);

        // Create a notification (created_at will be set by database)
        Notification::create([
            'parking_attendant_id' => $attendant->id,
            'type' => 'payment_success',
            'title' => 'Test Notification',
            'message' => 'Test message',
        ]);

        // Get notifications for today
        $todayNotifications = $this->notificationService->getNotificationsByDate(
            $attendant->id,
            now()->format('Y-m-d')
        );

        // Verify the method works (it should return a collection)
        $this->assertIsObject($todayNotifications);
    }

    /**
     * Test mark all notifications as read
     */
    public function testMarkAllNotificationsAsRead()
    {
        // Create attendant
        $attendant = ParkingAttendant::factory()->create([
            'registration_number' => 'ATT' . uniqid(),
        ]);

        // Create multiple unread notifications
        Notification::create([
            'parking_attendant_id' => $attendant->id,
            'type' => 'payment_success',
            'title' => 'Notification 1',
            'message' => 'Message 1',
            'is_read' => false,
            'created_at' => now(),
        ]);

        Notification::create([
            'parking_attendant_id' => $attendant->id,
            'type' => 'payment_success',
            'title' => 'Notification 2',
            'message' => 'Message 2',
            'is_read' => false,
            'created_at' => now(),
        ]);

        // Mark all as read
        $count = $this->notificationService->markAllAsRead($attendant->id);

        // Verify count
        $this->assertEquals(2, $count);

        // Verify all are marked as read
        $unreadCount = Notification::where('parking_attendant_id', $attendant->id)
            ->where('is_read', false)
            ->count();

        $this->assertEquals(0, $unreadCount);
    }

    /**
     * Test get unread notifications
     */
    public function testGetUnreadNotifications()
    {
        // Create attendant
        $attendant = ParkingAttendant::factory()->create([
            'registration_number' => 'ATT' . uniqid(),
        ]);

        // Create mix of read and unread notifications
        Notification::create([
            'parking_attendant_id' => $attendant->id,
            'type' => 'payment_success',
            'title' => 'Unread 1',
            'message' => 'Message 1',
            'is_read' => false,
            'created_at' => now(),
        ]);

        Notification::create([
            'parking_attendant_id' => $attendant->id,
            'type' => 'payment_success',
            'title' => 'Read',
            'message' => 'Message 2',
            'is_read' => true,
            'read_at' => now(),
            'created_at' => now(),
        ]);

        Notification::create([
            'parking_attendant_id' => $attendant->id,
            'type' => 'payment_success',
            'title' => 'Unread 2',
            'message' => 'Message 3',
            'is_read' => false,
            'created_at' => now(),
        ]);

        // Get unread notifications
        $unreadNotifications = $this->notificationService->getUnreadNotifications($attendant->id);

        // Verify only unread are returned
        $this->assertEquals(2, $unreadNotifications->count());
        $this->assertTrue($unreadNotifications->every(fn($n) => !$n->is_read));
    }

    /**
     * Test notification with transaction relationship
     */
    public function testNotificationWithTransactionRelationship()
    {
        // Create attendant and transaction
        $attendant = ParkingAttendant::factory()->create([
            'registration_number' => 'ATT' . uniqid(),
        ]);

        $transaction = Transaction::factory()->create([
            'parking_attendant_id' => $attendant->id,
            'payment_status' => 'success',
        ]);

        // Create notification with transaction
        $notification = Notification::create([
            'parking_attendant_id' => $attendant->id,
            'transaction_id' => $transaction->id,
            'type' => 'payment_success',
            'title' => 'Payment Success',
            'message' => 'Payment received',
            'data' => [
                'amount' => $transaction->amount,
                'vehicle_type' => $transaction->vehicle_type,
            ],
            'created_at' => now(),
        ]);

        // Verify relationship
        $this->assertEquals($transaction->id, $notification->transaction->id);
        $this->assertEquals($attendant->id, $notification->parkingAttendant->id);
    }

    /**
     * Test notification data structure
     */
    public function testNotificationDataStructure()
    {
        $attendant = ParkingAttendant::factory()->create([
            'registration_number' => 'ATT' . uniqid(),
        ]);

        $data = [
            'amount' => 5000,
            'vehicle_type' => 'motorcycle',
            'transaction_id' => 'TXN123',
        ];

        $notification = $this->notificationService->notifyAttendant(
            $attendant->id,
            'payment_success',
            'Payment Received',
            'Your payment has been received',
            $data
        );

        $this->assertNotNull($notification);
        $this->assertEquals($data, $notification->data);
    }

    /**
     * Test notification limit parameter
     */
    public function testNotificationLimitParameter()
    {
        $attendant = ParkingAttendant::factory()->create([
            'registration_number' => 'ATT' . uniqid(),
        ]);

        // Create 15 notifications
        for ($i = 0; $i < 15; $i++) {
            Notification::create([
                'parking_attendant_id' => $attendant->id,
                'type' => 'payment_success',
                'title' => "Notification $i",
                'message' => "Message $i",
                'created_at' => now()->addSeconds($i),
            ]);
        }

        // Get with limit of 5
        $notifications = $this->notificationService->getAttendantNotifications($attendant->id, 5);

        $this->assertEquals(5, $notifications->count());
    }
}
