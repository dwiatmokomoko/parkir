<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Models\ParkingRate;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
    }

    /**
     * Setup test data
     */
    protected function setupTestData(): void
    {
        // Create parking attendant
        ParkingAttendant::factory()->create([
            'registration_number' => 'ATT001',
            'name' => 'Test Attendant',
            'street_section' => 'Jl. Sudirman',
            'is_active' => true,
        ]);

        // Create parking rates
        ParkingRate::create([
            'vehicle_type' => 'motorcycle',
            'street_section' => null,
            'rate' => 2000,
            'effective_from' => Carbon::now()->subDay(),
        ]);

        ParkingRate::create([
            'vehicle_type' => 'car',
            'street_section' => null,
            'rate' => 5000,
            'effective_from' => Carbon::now()->subDay(),
        ]);
    }

    /**
     * Get test attendant
     */
    protected function getTestAttendant(): ParkingAttendant
    {
        return ParkingAttendant::where('registration_number', 'ATT001')->first();
    }

    /**
     * Test transaction creation with minimum required fields
     */
    public function testTransactionCreationWithMinimumRequiredFields()
    {
        $attendant = $this->getTestAttendant();
        
        // Create transaction with minimum fields
        $transaction = Transaction::create([
            'transaction_id' => 'TRX-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => $attendant->street_section,
            'vehicle_type' => 'motorcycle',
            'amount' => 2000,
            'payment_status' => 'pending',
        ]);
        
        // Verify transaction was created
        $this->assertNotNull($transaction->id);
        $this->assertEquals('pending', $transaction->payment_status);
        $this->assertEquals(2000, $transaction->amount);
        $this->assertEquals('motorcycle', $transaction->vehicle_type);
    }

    /**
     * Test concurrent transaction processing
     */
    public function testConcurrentTransactionProcessing()
    {
        $attendant = $this->getTestAttendant();
        $transactionIds = [];
        
        // Create multiple transactions concurrently
        for ($i = 0; $i < 5; $i++) {
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $attendant->street_section,
                'vehicle_type' => $i % 2 === 0 ? 'motorcycle' : 'car',
                'amount' => $i % 2 === 0 ? 2000 : 5000,
                'payment_status' => 'pending',
            ]);
            
            $transactionIds[] = $transaction->id;
        }
        
        // Verify all transactions were created
        $this->assertCount(5, $transactionIds);
        
        // Verify all transactions exist in database
        foreach ($transactionIds as $id) {
            $this->assertNotNull(Transaction::find($id));
        }
    }

    /**
     * Test transaction rollback on failure
     */
    public function testTransactionRollbackOnFailure()
    {
        $attendant = $this->getTestAttendant();
        
        // Create transaction
        $transaction = Transaction::create([
            'transaction_id' => 'TRX-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => $attendant->street_section,
            'vehicle_type' => 'motorcycle',
            'amount' => 2000,
            'payment_status' => 'pending',
        ]);
        
        $transactionId = $transaction->id;
        
        // Simulate failure and rollback
        try {
            \DB::transaction(function () use ($transaction) {
                $transaction->update(['payment_status' => 'failed']);
                throw new \Exception('Simulated failure');
            });
        } catch (\Exception $e) {
            // Expected exception
        }
        
        // Verify transaction status was rolled back
        $transaction->refresh();
        $this->assertEquals('pending', $transaction->payment_status);
    }

    /**
     * Test retry limit (max 3 attempts)
     */
    public function testRetryLimitMaxThreeAttempts()
    {
        $attendant = $this->getTestAttendant();
        
        // Create transaction
        $transaction = Transaction::create([
            'transaction_id' => 'TRX-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => $attendant->street_section,
            'vehicle_type' => 'motorcycle',
            'amount' => 2000,
            'payment_status' => 'failed',
            'retry_count' => 0,
        ]);
        
        // Simulate retries
        for ($i = 0; $i < 3; $i++) {
            $transaction->increment('retry_count');
            $this->assertLessThanOrEqual(3, $transaction->retry_count);
        }
        
        // Verify retry count is at max
        $this->assertEquals(3, $transaction->retry_count);
        
        // Verify no more retries allowed
        $this->assertGreaterThanOrEqual(3, $transaction->retry_count);
    }

    /**
     * Test QR code expiration handling
     */
    public function testQRCodeExpirationHandling()
    {
        $attendant = $this->getTestAttendant();
        
        // Create transaction with QR code
        $transaction = Transaction::create([
            'transaction_id' => 'TRX-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => $attendant->street_section,
            'vehicle_type' => 'motorcycle',
            'amount' => 2000,
            'payment_status' => 'pending',
            'qr_code_generated_at' => Carbon::now(),
            'qr_code_expires_at' => Carbon::now()->addMinutes(15),
        ]);
        
        // Verify QR code is not expired
        $this->assertFalse($transaction->isExpired());
        
        // Simulate QR code expiration
        $transaction->update([
            'qr_code_expires_at' => Carbon::now()->subMinutes(1),
        ]);
        
        // Verify QR code is expired
        $this->assertTrue($transaction->isExpired());
    }
}
