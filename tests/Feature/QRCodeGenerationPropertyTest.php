<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Services\QRCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Eris\Generator;
use Eris\TestTrait;
use Carbon\Carbon;

class QRCodeGenerationPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    protected QRCodeService $qrCodeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->qrCodeService = new QRCodeService();
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
     * Property 3: Unique QR Code Generation
     * 
     * **Validates: Requirements 2.2**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 3: Unique QR code generation
     */
    public function testUniqueQRCodeGeneration()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
                'street_section' => Generator\elements(['Jl. Sudirman', 'Jl. Thamrin']),
            ]))
        )
        ->withMaxSize(30)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            $qrCodes = [];
            
            foreach ($transactionData as $data) {
                // Create transaction
                $transaction = Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => $data['street_section'],
                    'vehicle_type' => $data['vehicle_type'],
                    'amount' => $data['amount'],
                    'payment_status' => 'pending',
                ]);
                
                // Generate QR code
                $qrCode = $this->qrCodeService->generate($transaction);
                $qrCodes[] = $qrCode;
                
                // Small delay to ensure unique timestamps
                usleep(1000);
            }
            
            // Assert all QR codes are unique
            $uniqueQRCodes = array_unique($qrCodes);
            $this->assertCount(count($qrCodes), $uniqueQRCodes, 'All QR codes must be unique');
        });
    }

    /**
     * Property 4: QR Code Contains Required Data
     * 
     * **Validates: Requirements 2.3**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 4: QR code contains required data
     */
    public function testQRCodeContainsRequiredData()
    {
        $this->forAll(
            Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
                'street_section' => Generator\elements(['Jl. Sudirman', 'Jl. Thamrin', 'Jl. Gatot Subroto']),
            ])
        )
        ->withMaxSize(50)
        ->then(function ($data) {
            $attendant = $this->getTestAttendant();
            
            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => $data['street_section'],
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Generate QR code
            $qrCode = $this->qrCodeService->generate($transaction);
            
            // Parse QR code data
            $qrData = $this->qrCodeService->parseQRCode($qrCode);
            
            // Assert required fields are present
            $this->assertNotNull($qrData, 'QR code data must be parseable');
            $this->assertArrayHasKey('transaction_id', $qrData, 'QR code must contain transaction_id');
            $this->assertArrayHasKey('parking_rate', $qrData, 'QR code must contain parking_rate');
            $this->assertArrayHasKey('attendant_id', $qrData, 'QR code must contain attendant_id');
            
            // Assert values match transaction
            $this->assertEquals($transaction->transaction_id, $qrData['transaction_id']);
            $this->assertEquals((float) $transaction->amount, $qrData['parking_rate']);
            $this->assertEquals($transaction->parking_attendant_id, $qrData['attendant_id']);
        });
    }

    /**
     * Property 5: QR Code Validity Period
     * 
     * **Validates: Requirements 2.5**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 5: QR code validity period
     */
    public function testQRCodeValidityPeriod()
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
                'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                'parking_attendant_id' => $attendant->id,
                'street_section' => 'Jl. Sudirman',
                'vehicle_type' => $data['vehicle_type'],
                'amount' => $data['amount'],
                'payment_status' => 'pending',
            ]);
            
            // Generate QR code
            $qrCode = $this->qrCodeService->generate($transaction);
            
            // Validate immediately (should be valid)
            $isValidNow = $this->qrCodeService->validate($qrCode);
            $this->assertTrue($isValidNow, 'QR code must be valid immediately after generation');
            
            // Check expiration time is set to 15 minutes
            $transaction->refresh();
            $this->assertNotNull($transaction->qr_code_expires_at, 'QR code must have expiration time');
            
            $generatedAt = $transaction->qr_code_generated_at;
            $expiresAt = $transaction->qr_code_expires_at;
            
            $diffInMinutes = $generatedAt->diffInMinutes($expiresAt);
            $this->assertEquals(15, $diffInMinutes, 'QR code must expire in 15 minutes');
            
            // Simulate time passing beyond 15 minutes
            Carbon::setTestNow($expiresAt->addMinute());
            
            // Validate after expiration (should be invalid)
            $isValidAfterExpiry = $this->qrCodeService->validate($qrCode);
            $this->assertFalse($isValidAfterExpiry, 'QR code must be invalid after 15 minutes');
            
            // Reset time
            Carbon::setTestNow();
        });
    }
}
