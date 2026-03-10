<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\ParkingAttendant;
use App\Models\Transaction;
use App\Services\NotificationService;
use Eris\Generator;
use Eris\TestTrait;
use Tests\TestCase;

class NotificationPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * @group Feature: parking-payment-monitoring-system, Property 41: Success notifications are sent
     */
    public function testSuccessNotificationsAreSent()
    {
        $this->forAll(
            Generator\associative([
                'amount' => Generator\choose(1000, 50000),
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
            ])
        )
        ->withMaxSize(10)
        ->then(function ($data) {
            // Create attendant with unique registration number
            $attendant = ParkingAttendant::factory()->create([
                'is_active' => true,
                'registration_number' => 'ATT' . uniqid(),
            ]);

            // Create transaction with success status
            $transaction = Transaction::factory()->create([
                'parking_attendant_id' => $attendant->id,
                'payment_status' => 'success',
                'amount' => $data['amount'],
                'vehicle_type' => $data['vehicle_type'],
                'paid_at' => now(),
            ]);

            // Verify notification was created
            $notification = Notification::where('transaction_id', $transaction->id)
                ->where('parking_attendant_id', $attendant->id)
                ->where('type', 'payment_success')
                ->first();

            $this->assertNotNull($notification, 'Success notification should be created');
            $this->assertEquals('payment_success', $notification->type);
            $this->assertEquals($attendant->id, $notification->parking_attendant_id);
        });
    }

    /**
     * @group Feature: parking-payment-monitoring-system, Property 42: Notifications contain required data
     */
    public function testNotificationsContainRequiredData()
    {
        $this->forAll(
            Generator\associative([
                'amount' => Generator\choose(1000, 50000),
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
            ])
        )
        ->withMaxSize(10)
        ->then(function ($data) {
            // Create attendant with unique registration number
            $attendant = ParkingAttendant::factory()->create([
                'is_active' => true,
                'registration_number' => 'ATT' . uniqid(),
            ]);

            // Create transaction
            $transaction = Transaction::factory()->create([
                'parking_attendant_id' => $attendant->id,
                'payment_status' => 'success',
                'amount' => $data['amount'],
                'vehicle_type' => $data['vehicle_type'],
                'paid_at' => now(),
            ]);

            // Get notification
            $notification = Notification::where('transaction_id', $transaction->id)->first();

            // Verify required data is present
            $this->assertNotNull($notification);
            $this->assertNotNull($notification->data);
            $this->assertArrayHasKey('amount', $notification->data);
            $this->assertArrayHasKey('vehicle_type', $notification->data);
            $this->assertArrayHasKey('transaction_id', $notification->data);
            $this->assertEquals($data['amount'], $notification->data['amount']);
            $this->assertEquals($data['vehicle_type'], $notification->data['vehicle_type']);
        });
    }

    /**
     * @group Feature: parking-payment-monitoring-system, Property 43: Notification history retrieval
     */
    public function testNotificationHistoryRetrieval()
    {
        $this->forAll(
            Generator\seq(
                Generator\associative([
                    'amount' => Generator\choose(1000, 50000),
                    'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                ])
            )
        )
        ->withMaxSize(10)
        ->then(function ($transactionData) {
            // Create attendant with unique registration number
            $attendant = ParkingAttendant::factory()->create([
                'is_active' => true,
                'registration_number' => 'ATT' . uniqid(),
            ]);

            // Create multiple transactions
            $transactions = collect($transactionData)->map(function ($data) use ($attendant) {
                return Transaction::factory()->create([
                    'parking_attendant_id' => $attendant->id,
                    'payment_status' => 'success',
                    'amount' => $data['amount'],
                    'vehicle_type' => $data['vehicle_type'],
                    'paid_at' => now(),
                ]);
            });

            // Get notification service
            $service = app(NotificationService::class);

            // Retrieve notifications
            $notifications = $service->getAttendantNotifications($attendant->id);

            // Verify all notifications are retrieved
            $this->assertGreaterThanOrEqual(count($transactionData), $notifications->count());

            // Verify notifications are ordered by created_at DESC
            $createdAts = $notifications->pluck('created_at')->toArray();
            $sortedCreatedAts = collect($createdAts)->sort()->reverse()->toArray();
            $this->assertEquals($sortedCreatedAts, $createdAts);
        });
    }
}
