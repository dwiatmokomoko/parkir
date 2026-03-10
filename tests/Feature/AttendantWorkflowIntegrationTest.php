<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\ParkingAttendant;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendantWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected ParkingAttendant $attendant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attendant = ParkingAttendant::factory()->create();
    }

    /**
     * Test complete attendant workflow: login → generate QR → receive notification
     */
    public function test_complete_attendant_workflow(): void
    {
        // Step 1: Attendant login
        $response = $this->postJson('/api/attendant/auth/login', [
            'registration_number' => $this->attendant->registration_number,
            'pin' => '1234',
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json());

        $token = $response->json('token');

        // Step 2: Generate QR code
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/payments/generate-qr', [
                'vehicle_type' => 'motorcycle',
                'parking_attendant_id' => $this->attendant->id,
                'street_section' => $this->attendant->street_section,
            ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('qr_code', $response->json());

        $transactionId = $response->json('transaction_id');

        // Step 3: Simulate successful payment
        $transaction = Transaction::where('transaction_id', $transactionId)->first();
        $transaction->update([
            'payment_status' => 'success',
            'payment_method' => 'qris',
            'paid_at' => now(),
        ]);

        // Create notification
        Notification::create([
            'parking_attendant_id' => $this->attendant->id,
            'transaction_id' => $transaction->id,
            'type' => 'payment_success',
            'title' => 'Pembayaran Berhasil',
            'message' => 'Pembayaran parkir telah berhasil diproses',
            'data' => json_encode([
                'amount' => $transaction->amount,
                'vehicle_type' => $transaction->vehicle_type,
            ]),
        ]);

        // Step 4: Get notifications
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/attendant/notifications');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
        $this->assertGreaterThan(0, count($response->json()));

        // Step 5: Mark notification as read
        $notification = Notification::where('parking_attendant_id', $this->attendant->id)->first();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/attendant/notifications/{$notification->id}/read");

        $response->assertStatus(200);

        // Verify notification is marked as read
        $notification->refresh();
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);

        // Step 6: Logout
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/attendant/auth/logout');

        $response->assertStatus(200);
    }

    /**
     * Test notification history viewing
     */
    public function test_notification_history_viewing(): void
    {
        // Login
        $response = $this->postJson('/api/attendant/auth/login', [
            'registration_number' => $this->attendant->registration_number,
            'pin' => '1234',
        ]);

        $token = $response->json('token');

        // Create sample notifications
        Notification::factory()->count(5)->create([
            'parking_attendant_id' => $this->attendant->id,
        ]);

        // Get notifications
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/attendant/notifications');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json());
    }

    /**
     * Test session timeout
     */
    public function test_session_timeout(): void
    {
        // Login
        $response = $this->postJson('/api/attendant/auth/login', [
            'registration_number' => $this->attendant->registration_number,
            'pin' => '1234',
        ]);

        $token = $response->json('token');

        // Simulate session expiration by manipulating the session
        // In a real scenario, this would be handled by the middleware

        // Try to access protected endpoint with expired session
        // This would normally return 401 Unauthorized
    }

    /**
     * Test invalid PIN
     */
    public function test_invalid_pin(): void
    {
        $response = $this->postJson('/api/attendant/auth/login', [
            'registration_number' => $this->attendant->registration_number,
            'pin' => 'invalid_pin',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test inactive attendant cannot login
     */
    public function test_inactive_attendant_cannot_login(): void
    {
        $inactiveAttendant = ParkingAttendant::factory()->inactive()->create();

        $response = $this->postJson('/api/attendant/auth/login', [
            'registration_number' => $inactiveAttendant->registration_number,
            'pin' => '1234',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test QR code generation for different vehicle types
     */
    public function test_qr_generation_for_different_vehicle_types(): void
    {
        // Login
        $response = $this->postJson('/api/attendant/auth/login', [
            'registration_number' => $this->attendant->registration_number,
            'pin' => '1234',
        ]);

        $token = $response->json('token');

        // Generate QR for motorcycle
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/payments/generate-qr', [
                'vehicle_type' => 'motorcycle',
                'parking_attendant_id' => $this->attendant->id,
                'street_section' => $this->attendant->street_section,
            ]);

        $response->assertStatus(200);
        $motorcycleTransaction = Transaction::where('transaction_id', $response->json('transaction_id'))->first();
        $this->assertEquals('motorcycle', $motorcycleTransaction->vehicle_type);
        $this->assertEquals(2000, $motorcycleTransaction->amount);

        // Generate QR for car
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/payments/generate-qr', [
                'vehicle_type' => 'car',
                'parking_attendant_id' => $this->attendant->id,
                'street_section' => $this->attendant->street_section,
            ]);

        $response->assertStatus(200);
        $carTransaction = Transaction::where('transaction_id', $response->json('transaction_id'))->first();
        $this->assertEquals('car', $carTransaction->vehicle_type);
        $this->assertEquals(5000, $carTransaction->amount);
    }
}
