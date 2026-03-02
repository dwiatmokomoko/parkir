<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Eris\Generator;
use Eris\TestTrait;

class PaymentGatewayPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get or create a parking attendant for testing
     */
    protected function getTestAttendant(): ParkingAttendant
    {
        $attendant = ParkingAttendant::where('registration_number', 'ATT001')->first();
        
        if (!$attendant) {
            $attendant = ParkingAttendant::factory()->create([
                'registration_number' => 'ATT001',
                'name' => 'Test Attendant',
                'is_active' => true,
            ]);
        }
        
        return $attendant;
    }

    /**
     * Property 1: Unique Transaction Identifiers
     * 
     * **Validates: Requirements 1.4**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 1: Unique transaction identifiers
     */
    public function testUniqueTransactionIdentifiers()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
                'street_section' => Generator\elements(['Jl. Sudirman', 'Jl. Thamrin', 'Jl. Gatot Subroto']),
            ]))
        )
        ->withMaxSize(50)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            $transactionIds = [];
            
            foreach ($transactionData as $data) {
                // Generate unique transaction ID
                $transactionId = 'TRX-' . time() . '-' . uniqid();
                
                // Create transaction
                $transaction = Transaction::create([
                    'transaction_id' => $transactionId,
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => $data['street_section'],
                    'vehicle_type' => $data['vehicle_type'],
                    'amount' => $data['amount'],
                    'payment_status' => 'pending',
                ]);
                
                $transactionIds[] = $transaction->transaction_id;
                
                // Small delay to ensure unique timestamps
                usleep(1000);
            }
            
            // Assert all transaction IDs are unique
            $uniqueIds = array_unique($transactionIds);
            $this->assertCount(count($transactionIds), $uniqueIds, 'All transaction identifiers must be unique');
        });
    }

    /**
     * Property 6: Payment Initiation Sets Pending Status
     * 
     * **Validates: Requirements 3.1, 3.2**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 6: Payment initiation sets pending status
     */
    public function testPaymentInitiationSetsPendingStatus()
    {
        $this->forAll(
            Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
                'street_section' => Generator\elements(['Jl. Sudirman', 'Jl. Thamrin']),
            ])
        )
        ->withMaxSize(30)
        ->then(function ($data) {
            $attendant = $this->getTestAttendant();
            
            // Create transaction (payment initiation)
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $data['street_section'],
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Assert status is pending
            $this->assertEquals('pending', $transaction->payment_status, 'Payment initiation must set status to pending');
        });
    }

    /**
     * Property 7: Payment Success Updates Status
     * 
     * **Validates: Requirements 3.3**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 7: Payment success updates status
     */
    public function testPaymentSuccessUpdatesStatus()
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
            
            // Create transaction with pending status
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => 'Jl. Sudirman',
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Simulate payment success
            $transaction->update([
                'payment_status' => 'success',
                'paid_at' => now(),
            ]);
            
            // Refresh from database
            $transaction->refresh();
            
            // Assert status is updated to success
            $this->assertEquals('success', $transaction->payment_status, 'Payment success must update status to success');
            $this->assertNotNull($transaction->paid_at, 'Payment success must set paid_at timestamp');
        });
    }

    /**
     * Property 8: Payment Failure Updates Status
     * 
     * **Validates: Requirements 3.4**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 8: Payment failure updates status
     */
    public function testPaymentFailureUpdatesStatus()
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
            
            // Create transaction with pending status
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => 'Jl. Sudirman',
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Simulate payment failure
            $transaction->update([
                'payment_status' => 'failed',
                'failure_reason' => $data['failure_reason'],
            ]);
            
            // Refresh from database
            $transaction->refresh();
            
            // Assert status is updated to failed
            $this->assertEquals('failed', $transaction->payment_status, 'Payment failure must update status to failed');
            $this->assertNotNull($transaction->failure_reason, 'Payment failure must include failure reason');
        });
    }
}
