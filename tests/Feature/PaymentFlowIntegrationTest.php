<?php

namespace Tests\Feature;

use App\Models\ParkingAttendant;
use App\Models\ParkingRate;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected ParkingAttendant $attendant;
    protected ParkingRate $rate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->attendant = ParkingAttendant::factory()->create();
        $this->rate = ParkingRate::factory()->motorcycle()->create();
    }

    /**
     * Test complete payment flow: generate QR → scan → payment → webhook → notification
     */
    public function test_complete_payment_flow(): void
    {
        // Step 1: Attendant generates QR code
        $response = $this->postJson('/api/payments/generate-qr', [
            'vehicle_type' => 'motorcycle',
            'parking_attendant_id' => $this->attendant->id,
            'street_section' => $this->attendant->street_section,
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('qr_code', $response->json());
        $this->assertArrayHasKey('transaction_id', $response->json());

        $transactionId = $response->json('transaction_id');
        $transaction = Transaction::where('transaction_id', $transactionId)->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('pending', $transaction->payment_status);
        $this->assertEquals('motorcycle', $transaction->vehicle_type);
        $this->assertEquals(2000, $transaction->amount);

        // Step 2: Simulate Midtrans webhook callback for successful payment
        $webhookPayload = [
            'order_id' => $transactionId,
            'status_code' => '200',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'gross_amount' => '2000.00',
            'signature_key' => $this->generateMidtransSignature($transactionId, '200', '2000.00'),
        ];

        $response = $this->postJson('/api/payments/callback', $webhookPayload);
        $response->assertStatus(200);

        // Step 3: Verify transaction status was updated
        $transaction->refresh();
        $this->assertEquals('success', $transaction->payment_status);
        $this->assertEquals('qris', $transaction->payment_method);
        $this->assertNotNull($transaction->paid_at);

        // Step 4: Verify notification was created
        $notification = $transaction->parkingAttendant->notifications()
            ->where('transaction_id', $transaction->id)
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('payment_success', $notification->type);
    }

    /**
     * Test payment retry flow
     */
    public function test_payment_retry_flow(): void
    {
        // Create a failed transaction
        $transaction = Transaction::factory()
            ->failed()
            ->create([
                'parking_attendant_id' => $this->attendant->id,
                'retry_count' => 1,
            ]);

        // Retry payment
        $response = $this->postJson("/api/payments/retry/{$transaction->id}");

        $response->assertStatus(200);
        $this->assertArrayHasKey('qr_code', $response->json());

        // Verify retry count was incremented
        $transaction->refresh();
        $this->assertEquals(2, $transaction->retry_count);
    }

    /**
     * Test QR code expiration handling
     */
    public function test_qr_code_expiration_handling(): void
    {
        // Create a transaction with expired QR code
        $transaction = Transaction::factory()
            ->pending()
            ->create([
                'parking_attendant_id' => $this->attendant->id,
                'qr_code_expires_at' => now()->subMinutes(20),
            ]);

        // Check if QR code is expired
        $this->assertTrue($transaction->isExpired());

        // Generate new QR code
        $response = $this->postJson('/api/payments/generate-qr', [
            'vehicle_type' => 'motorcycle',
            'parking_attendant_id' => $this->attendant->id,
            'street_section' => $this->attendant->street_section,
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test payment with invalid vehicle type
     */
    public function test_payment_with_invalid_vehicle_type(): void
    {
        $response = $this->postJson('/api/payments/generate-qr', [
            'vehicle_type' => 'invalid_type',
            'parking_attendant_id' => $this->attendant->id,
            'street_section' => $this->attendant->street_section,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test payment with inactive attendant
     */
    public function test_payment_with_inactive_attendant(): void
    {
        $inactiveAttendant = ParkingAttendant::factory()->inactive()->create();

        $response = $this->postJson('/api/payments/generate-qr', [
            'vehicle_type' => 'motorcycle',
            'parking_attendant_id' => $inactiveAttendant->id,
            'street_section' => $inactiveAttendant->street_section,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test payment status check
     */
    public function test_payment_status_check(): void
    {
        $transaction = Transaction::factory()->success()->create();

        $response = $this->getJson("/api/payments/status/{$transaction->id}");

        $response->assertStatus(200);
        $this->assertEquals('success', $response->json('payment_status'));
    }

    /**
     * Helper method to generate Midtrans signature
     */
    private function generateMidtransSignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        $serverKey = config('midtrans.server_key');
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return $signatureKey;
    }
}
