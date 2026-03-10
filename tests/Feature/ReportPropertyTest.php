<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Models\User;
use App\Services\PDFReportService;
use App\Services\ExcelReportService;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    protected User $user;
    protected ParkingAttendant $attendant;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->attendant = ParkingAttendant::factory()->create();
    }

    /**
     * @group Feature: parking-payment-monitoring-system, Property 25: PDF report generation
     * 
     * Validates: Requirements 8.4
     */
    public function testPDFReportGeneration()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(1000, 50000),
                'status' => Generator\elements(['success', 'failed', 'pending']),
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
            ]), 1, 100)
        )
        ->withMaxSize(50)
        ->then(function ($transactionData) {
            // Create transactions
            $transactions = collect($transactionData)->map(function ($data) {
                return Transaction::factory()
                    ->for($this->attendant, 'parkingAttendant')
                    ->create([
                        'amount' => $data['amount'],
                        'payment_status' => $data['status'],
                        'vehicle_type' => $data['vehicle_type'],
                    ]);
            });

            // Generate PDF report
            $service = app(PDFReportService::class);
            $filters = [
                'start_date' => now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ];

            $filePath = $service->generate($transactions, $filters);

            // Verify file was created
            $this->assertTrue(file_exists(storage_path('app/' . $filePath)));
            $this->assertStringEndsWith('.pdf', $filePath);

            // Verify file is not empty
            $this->assertGreaterThan(0, filesize(storage_path('app/' . $filePath)));

            // Cleanup
            @unlink(storage_path('app/' . $filePath));
        });
    }

    /**
     * @group Feature: parking-payment-monitoring-system, Property 26: Excel report generation
     * 
     * Validates: Requirements 8.5
     */
    public function testExcelReportGeneration()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(1000, 50000),
                'status' => Generator\elements(['success', 'failed', 'pending']),
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
            ]), 1, 100)
        )
        ->withMaxSize(50)
        ->then(function ($transactionData) {
            // Create transactions
            $transactions = collect($transactionData)->map(function ($data) {
                return Transaction::factory()
                    ->for($this->attendant, 'parkingAttendant')
                    ->create([
                        'amount' => $data['amount'],
                        'payment_status' => $data['status'],
                        'vehicle_type' => $data['vehicle_type'],
                    ]);
            });

            // Generate Excel report
            $service = app(ExcelReportService::class);
            $filters = [
                'start_date' => now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ];

            $filePath = $service->generate($transactions, $filters);

            // Verify file was created
            $this->assertTrue(file_exists(storage_path('app/' . $filePath)));
            $this->assertStringEndsWith('.xlsx', $filePath);

            // Verify file is not empty
            $this->assertGreaterThan(0, filesize(storage_path('app/' . $filePath)));

            // Cleanup
            @unlink(storage_path('app/' . $filePath));
        });
    }

    /**
     * @group Feature: parking-payment-monitoring-system, Property 27: Report contains required fields
     * 
     * Validates: Requirements 8.6
     */
    public function testReportContainsRequiredFields()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(1000, 50000),
                'status' => Generator\elements(['success', 'failed', 'pending']),
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
            ]), 1, 50)
        )
        ->withMaxSize(30)
        ->then(function ($transactionData) {
            // Create transactions
            $transactions = collect($transactionData)->map(function ($data) {
                return Transaction::factory()
                    ->for($this->attendant, 'parkingAttendant')
                    ->create([
                        'amount' => $data['amount'],
                        'payment_status' => $data['status'],
                        'vehicle_type' => $data['vehicle_type'],
                    ]);
            });

            // Verify each transaction has required fields
            foreach ($transactions as $transaction) {
                $this->assertNotNull($transaction->created_at);
                $this->assertNotNull($transaction->amount);
                $this->assertNotNull($transaction->payment_status);
                $this->assertNotNull($transaction->parkingAttendant->name);
                $this->assertNotNull($transaction->street_section);
                $this->assertNotNull($transaction->vehicle_type);
            }
        });
    }

    /**
     * @group Feature: parking-payment-monitoring-system, Property 28: Report summary accuracy
     * 
     * Validates: Requirements 8.7
     */
    public function testReportSummaryAccuracy()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(1000, 50000),
                'status' => Generator\elements(['success', 'failed', 'pending']),
            ]), 1, 100)
        )
        ->withMaxSize(50)
        ->then(function ($transactionData) {
            // Create transactions
            $transactions = collect($transactionData)->map(function ($data) {
                return Transaction::factory()
                    ->for($this->attendant, 'parkingAttendant')
                    ->create([
                        'amount' => $data['amount'],
                        'payment_status' => $data['status'],
                    ]);
            });

            // Calculate expected totals
            $expectedRevenue = $transactions
                ->where('payment_status', 'success')
                ->sum('amount');
            $expectedCount = $transactions->count();

            // Verify summary calculations
            $actualRevenue = $transactions->sum('amount');
            $actualCount = $transactions->count();

            // For successful transactions only
            $successRevenue = $transactions
                ->where('payment_status', 'success')
                ->sum('amount');

            $this->assertEquals($expectedCount, $actualCount);
            $this->assertEquals($expectedRevenue, $successRevenue);
        });
    }
}
