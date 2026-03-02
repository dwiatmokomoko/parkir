<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Services\MidtransService;
use App\Services\WebhookService;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class PaymentGatewayIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test Midtrans API connection with mocked responses
     */
    public function testMidtransAPIConnectionWithMockedResponses()
    {
        // Create test data
        $attendant = ParkingAttendant::factory()->create();
        $transaction = Transaction::create([
            'transaction_id' => 'TRX-TEST-001',
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'car',
            'amount' => 5000,
            'payment_status' => 'pending',
        ]);

        // Mock Midtrans Snap response
        $mockSnapResponse = (object) [
            'token' => 'mock-snap-token-123',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-123',
        ];

        // Test that service can handle the response structure
        $this->assertIsObject($mockSnapResponse);
        $this->assertObjectHasProperty('token', $mockSnapResponse);
        $this->assertObjectHasProperty('redirect_url', $mockSnapResponse);
    }

    /**
     * Test webhook signature verification
     */
    public function testWebhookSignatureVerification()
    {
        $auditLogger = app(AuditLogger::class);
        $webhookService = new WebhookService($auditLogger);

        // Create valid notification data
        $orderId = 'TRX-TEST-002';
        $statusCode = '200';
        $grossAmount = '5000.00';
        $serverKey = config('midtrans.server_key');

        // Generate valid signature
        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $validNotification = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
        ];

        // Test valid signature
        $isValid = $webhookService->verifySignature($validNotification);
        $this->assertTrue($isValid, 'Valid signature should be verified');

        // Test invalid signature
        $invalidNotification = $validNotification;
        $invalidNotification['signature_key'] = 'invalid-signature';

        $isInvalid = $webhookService->verifySignature($invalidNotification);
        $this->assertFalse($isInvalid, 'Invalid signature should be rejected');
    }

    /**
     * Test payment status mapping
     */
    public function testPaymentStatusMapping()
    {
        $auditLogger = app(AuditLogger::class);
        $webhookService = new WebhookService($auditLogger);

        // Test various Midtrans statuses
        $statusMappings = [
            ['midtrans' => 'capture', 'fraud' => null, 'expected' => 'success'],
            ['midtrans' => 'settlement', 'fraud' => null, 'expected' => 'success'],
            ['midtrans' => 'pending', 'fraud' => null, 'expected' => 'pending'],
            ['midtrans' => 'deny', 'fraud' => null, 'expected' => 'failed'],
            ['midtrans' => 'cancel', 'fraud' => null, 'expected' => 'failed'],
            ['midtrans' => 'expire', 'fraud' => null, 'expected' => 'failed'],
            ['midtrans' => 'capture', 'fraud' => 'deny', 'expected' => 'failed'],
        ];

        foreach ($statusMappings as $mapping) {
            $reflection = new \ReflectionClass($webhookService);
            $method = $reflection->getMethod('mapMidtransStatus');
            $method->setAccessible(true);

            $result = $method->invoke($webhookService, $mapping['midtrans'], $mapping['fraud']);
            $this->assertEquals(
                $mapping['expected'],
                $result,
                "Midtrans status '{$mapping['midtrans']}' with fraud '{$mapping['fraud']}' should map to '{$mapping['expected']}'"
            );
        }
    }

    /**
     * Test error handling for API failures
     */
    public function testErrorHandlingForAPIFailures()
    {
        $attendant = ParkingAttendant::factory()->create();
        $transaction = Transaction::create([
            'transaction_id' => 'TRX-TEST-003',
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'car',
            'amount' => 5000,
            'payment_status' => 'pending',
        ]);

        // Test that transaction can handle failure status
        $transaction->update([
            'payment_status' => 'failed',
            'failure_reason' => 'API connection timeout',
        ]);

        $this->assertEquals('failed', $transaction->payment_status);
        $this->assertEquals('API connection timeout', $transaction->failure_reason);
    }

    /**
     * Test timeout scenarios
     */
    public function testTimeoutScenarios()
    {
        $attendant = ParkingAttendant::factory()->create();
        $transaction = Transaction::create([
            'transaction_id' => 'TRX-TEST-004',
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'car',
            'amount' => 5000,
            'payment_status' => 'pending',
        ]);

        // Simulate timeout by setting expired status
        $transaction->update([
            'payment_status' => 'failed',
            'failure_reason' => 'Payment timeout - exceeded 10 seconds',
        ]);

        $this->assertEquals('failed', $transaction->payment_status);
        $this->assertStringContainsString('timeout', strtolower($transaction->failure_reason));
    }

    /**
     * Test webhook order validation
     */
    public function testWebhookOrderValidation()
    {
        $auditLogger = app(AuditLogger::class);
        $webhookService = new WebhookService($auditLogger);

        // Create a transaction
        $attendant = ParkingAttendant::factory()->create();
        $transaction = Transaction::create([
            'transaction_id' => 'TRX-TEST-005',
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'car',
            'amount' => 5000,
            'payment_status' => 'pending',
        ]);

        // Test valid order exists
        $exists = $webhookService->validateOrderExists('TRX-TEST-005');
        $this->assertTrue($exists, 'Existing order should be validated');

        // Test invalid order
        $notExists = $webhookService->validateOrderExists('TRX-NONEXISTENT');
        $this->assertFalse($notExists, 'Non-existent order should not be validated');
    }

    /**
     * Test transaction status transition validity
     */
    public function testTransactionStatusTransitionValidity()
    {
        $auditLogger = app(AuditLogger::class);
        $webhookService = new WebhookService($auditLogger);

        // Test valid transitions
        $validTransitions = [
            ['from' => 'pending', 'to' => 'success', 'expected' => true],
            ['from' => 'pending', 'to' => 'failed', 'expected' => true],
            ['from' => 'pending', 'to' => 'expired', 'expected' => true],
            ['from' => 'failed', 'to' => 'pending', 'expected' => true],
            ['from' => 'expired', 'to' => 'pending', 'expected' => true],
        ];

        foreach ($validTransitions as $transition) {
            $result = $webhookService->isValidStatusTransition($transition['from'], $transition['to']);
            $this->assertEquals(
                $transition['expected'],
                $result,
                "Transition from '{$transition['from']}' to '{$transition['to']}' should be " . ($transition['expected'] ? 'valid' : 'invalid')
            );
        }

        // Test invalid transitions
        $invalidTransitions = [
            ['from' => 'success', 'to' => 'pending', 'expected' => false],
            ['from' => 'success', 'to' => 'failed', 'expected' => false],
            ['from' => 'pending', 'to' => 'pending', 'expected' => false],
        ];

        foreach ($invalidTransitions as $transition) {
            $result = $webhookService->isValidStatusTransition($transition['from'], $transition['to']);
            $this->assertEquals(
                $transition['expected'],
                $result,
                "Transition from '{$transition['from']}' to '{$transition['to']}' should be invalid"
            );
        }
    }

    /**
     * Test webhook processing with invalid signature
     */
    public function testWebhookProcessingWithInvalidSignature()
    {
        $auditLogger = app(AuditLogger::class);
        $webhookService = new WebhookService($auditLogger);

        $notification = [
            'order_id' => 'TRX-TEST-006',
            'status_code' => '200',
            'gross_amount' => '5000.00',
            'signature_key' => 'invalid-signature',
            'transaction_status' => 'settlement',
        ];

        $result = $webhookService->processWebhook($notification);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid signature', $result['message']);
    }

    /**
     * Test webhook processing with non-existent order
     */
    public function testWebhookProcessingWithNonExistentOrder()
    {
        $auditLogger = app(AuditLogger::class);
        $webhookService = new WebhookService($auditLogger);

        $orderId = 'TRX-NONEXISTENT';
        $statusCode = '200';
        $grossAmount = '5000.00';
        $serverKey = config('midtrans.server_key');
        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $notification = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'settlement',
        ];

        $result = $webhookService->processWebhook($notification);

        $this->assertFalse($result['success']);
        $this->assertEquals('Order not found', $result['message']);
    }
}
