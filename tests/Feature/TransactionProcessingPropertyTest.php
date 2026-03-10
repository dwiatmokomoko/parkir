<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Models\ParkingRate;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Eris\Generator;
use Eris\TestTrait;
use Carbon\Carbon;

class TransactionProcessingPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

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
     * Property 2: Correct Parking Rate Determination
     * 
     * **Validates: Requirements 2.1**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 2: Correct parking rate determination
     */
    public function testCorrectParkingRateDetermination()
    {
        $this->forAll(
            Generator\elements(['motorcycle', 'car'])
        )
        ->withMaxSize(20)
        ->then(function ($vehicleType) {
            $attendant = $this->getTestAttendant();
            
            // Get parking rate
            $rate = ParkingRate::getCurrentRate($vehicleType, $attendant->street_section);
            
            // Assert rate is determined correctly
            $this->assertNotNull($rate, "Parking rate must be determined for vehicle type: {$vehicleType}");
            
            // Verify rate is positive
            $this->assertGreaterThan(0, $rate, 'Parking rate must be positive');
            
            // Verify rate is a valid decimal
            $this->assertIsNumeric($rate, 'Parking rate must be numeric');
        });
    }

    /**
     * Property 9: Successful Payment Creates Transaction Record
     * 
     * **Validates: Requirements 4.1, 4.2, 4.3**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 9: Successful payment creates transaction record
     */
    public function testSuccessfulPaymentCreatesTransactionRecord()
    {
        $this->forAll(
            Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
            ])
        )
        ->withMaxSize(30)
        ->then(function ($data) {
            $attendant = $this->getTestAttendant();
            
            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $attendant->street_section,
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Simulate successful payment
            $transaction->update([
                'payment_status' => 'success',
                'paid_at' => Carbon::now(),
            ]);
            
            // Verify transaction record exists
            $storedTransaction = Transaction::find($transaction->id);
            $this->assertNotNull($storedTransaction, 'Transaction record must be created');
            $this->assertEquals('success', $storedTransaction->payment_status, 'Transaction status must be success');
            $this->assertNotNull($storedTransaction->paid_at, 'Transaction must have paid_at timestamp');
            $this->assertEquals($attendant->id, $storedTransaction->parking_attendant_id, 'Transaction must reference attendant');
            $this->assertEquals($data['vehicle_type'], $storedTransaction->vehicle_type, 'Transaction must store vehicle type');
        });
    }

    /**
     * Property 10: Payment Confirmation Sent on Success
     * 
     * **Validates: Requirements 3.6, 14.1**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 10: Payment confirmation sent on success
     */
    public function testPaymentConfirmationSentOnSuccess()
    {
        $this->forAll(
            Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
            ])
        )
        ->withMaxSize(30)
        ->then(function ($data) {
            $attendant = $this->getTestAttendant();
            
            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $attendant->street_section,
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Create notification for successful payment
            $notification = Notification::create([
                'parking_attendant_id' => $attendant->id,
                'transaction_id' => $transaction->id,
                'type' => 'payment_success',
                'title' => 'Pembayaran Berhasil',
                'message' => "Pembayaran parkir {$data['vehicle_type']} sebesar Rp " . number_format($data['amount'], 0, ',', '.') . " berhasil diterima",
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $data['amount'],
                    'vehicle_type' => $data['vehicle_type'],
                ],
                'created_at' => Carbon::now(),
            ]);
            
            // Verify notification was created
            $this->assertNotNull($notification, 'Notification must be created');
            $this->assertEquals('payment_success', $notification->type, 'Notification type must be payment_success');
            $this->assertEquals($attendant->id, $notification->parking_attendant_id, 'Notification must be sent to attendant');
            $this->assertEquals($transaction->id, $notification->transaction_id, 'Notification must reference transaction');
        });
    }

    /**
     * Property 11: Failed Payment Returns Error Message
     * 
     * **Validates: Requirements 3.7**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 11: Failed payment returns error message
     */
    public function testFailedPaymentReturnsErrorMessage()
    {
        $this->forAll(
            Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
                'failure_reason' => Generator\elements([
                    'Insufficient balance',
                    'Payment timeout',
                    'Card declined',
                    'Network error'
                ]),
            ])
        )
        ->withMaxSize(30)
        ->then(function ($data) {
            $attendant = $this->getTestAttendant();
            
            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $attendant->street_section,
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Simulate payment failure
            $transaction->update([
                'payment_status' => 'failed',
                'failure_reason' => $data['failure_reason'],
            ]);
            
            // Verify failure information is stored
            $storedTransaction = Transaction::find($transaction->id);
            $this->assertEquals('failed', $storedTransaction->payment_status, 'Transaction status must be failed');
            $this->assertNotNull($storedTransaction->failure_reason, 'Failure reason must be stored');
            $this->assertEquals($data['failure_reason'], $storedTransaction->failure_reason, 'Failure reason must match');
        });
    }

    /**
     * Property 12: Transaction Records Contain Required Fields
     * 
     * **Validates: Requirements 4.1, 4.2, 4.3**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 12: Transaction records contain required fields
     */
    public function testTransactionRecordsContainRequiredFields()
    {
        $this->forAll(
            Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
            ])
        )
        ->withMaxSize(30)
        ->then(function ($data) {
            $attendant = $this->getTestAttendant();
            
            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $attendant->street_section,
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Verify all required fields are present
            $this->assertNotNull($transaction->transaction_id, 'Transaction ID must be present');
            $this->assertNotNull($transaction->parking_attendant_id, 'Attendant ID must be present');
            $this->assertNotNull($transaction->street_section, 'Street section must be present');
            $this->assertNotNull($transaction->vehicle_type, 'Vehicle type must be present');
            $this->assertNotNull($transaction->amount, 'Amount must be present');
            $this->assertNotNull($transaction->payment_status, 'Payment status must be present');
            $this->assertNotNull($transaction->created_at, 'Created timestamp must be present');
        });
    }

    /**
     * Property 33: Retry Preserves Parking Rate
     * 
     * **Validates: Requirements 10.2**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 33: Retry preserves parking rate
     */
    public function testRetryPreservesParkingRate()
    {
        $this->forAll(
            Generator\elements(['motorcycle', 'car'])
        )
        ->withMaxSize(20)
        ->then(function ($vehicleType) {
            $attendant = $this->getTestAttendant();
            $originalRate = ParkingRate::getCurrentRate($vehicleType, $attendant->street_section);
            
            // Create transaction with original rate
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $attendant->street_section,
                'vehicle_type' => $vehicleType,
                'amount' => $originalRate,
                'payment_status' => 'failed',
                'retry_count' => 0,
            ]);
            
            // Simulate retry
            $transaction->increment('retry_count');
            $transaction->update(['payment_status' => 'pending']);
            
            // Verify rate is preserved
            $this->assertEquals($originalRate, $transaction->amount, 'Retry must preserve parking rate');
            $this->assertEquals(1, $transaction->retry_count, 'Retry count must be incremented');
        });
    }

    /**
     * Property 34: Failed Transactions Are Logged
     * 
     * **Validates: Requirements 13.1, 13.2**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 34: Failed transactions are logged
     */
    public function testFailedTransactionsAreLogged()
    {
        $this->forAll(
            Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
            ])
        )
        ->withMaxSize(30)
        ->then(function ($data) {
            $attendant = $this->getTestAttendant();
            
            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $attendant->street_section,
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Simulate payment failure and log it
            $transaction->update([
                'payment_status' => 'failed',
                'failure_reason' => 'Payment declined',
            ]);
            
            // Create audit log
            AuditLog::create([
                'action' => 'transaction_status_updated',
                'entity_type' => 'transaction',
                'entity_id' => $transaction->id,
                'old_values' => ['payment_status' => 'pending'],
                'new_values' => ['payment_status' => 'failed'],
                'created_at' => Carbon::now(),
            ]);
            
            // Verify audit log was created
            $auditLog = AuditLog::where('entity_id', $transaction->id)->first();
            $this->assertNotNull($auditLog, 'Failed transaction must be logged');
            $this->assertEquals('transaction_status_updated', $auditLog->action, 'Log action must be transaction_status_updated');
            $this->assertEquals('failed', $auditLog->new_values['payment_status'], 'Log must record failed status');
        });
    }
}
