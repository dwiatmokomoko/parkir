<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Services\StatisticsService;
use App\Services\ChartDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Eris\Generator;
use Eris\TestTrait;
use Carbon\Carbon;

class StatisticsPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    private StatisticsService $statisticsService;
    private ChartDataService $chartDataService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statisticsService = app(StatisticsService::class);
        $this->chartDataService = app(ChartDataService::class);
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
     * Property 13: Daily Revenue Calculation Accuracy
     * 
     * **Validates: Requirements 5.3**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 13: Daily revenue calculation accuracy
     */
    public function testDailyRevenueCalculationAccuracy()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(2000, 50000),
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            $today = Carbon::now();
            $expectedRevenue = 0;
            
            foreach ($transactionData as $data) {
                $transaction = Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => 'Jl. Sudirman',
                    'vehicle_type' => $data['vehicle_type'],
                    'amount' => $data['amount'],
                    'payment_status' => 'success',
                    'created_at' => $today,
                ]);
                
                $expectedRevenue += $data['amount'];
                usleep(100);
            }
            
            // Get daily revenue
            $dailyRevenue = $this->statisticsService->getDailyRevenue();
            $todayKey = $today->format('Y-m-d');
            
            // Assert calculation is accurate
            $this->assertArrayHasKey($todayKey, $dailyRevenue, 'Daily revenue should include today');
            $this->assertEquals($expectedRevenue, $dailyRevenue[$todayKey], 'Daily revenue calculation must be accurate');
        });
    }

    /**
     * Property 14: Monthly Revenue Calculation Accuracy
     * 
     * **Validates: Requirements 5.4**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 14: Monthly revenue calculation accuracy
     */
    public function testMonthlyRevenueCalculationAccuracy()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(2000, 50000),
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            $now = Carbon::now();
            $expectedRevenue = 0;
            
            foreach ($transactionData as $data) {
                $transaction = Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => 'Jl. Sudirman',
                    'vehicle_type' => $data['vehicle_type'],
                    'amount' => $data['amount'],
                    'payment_status' => 'success',
                    'created_at' => $now,
                ]);
                
                $expectedRevenue += $data['amount'];
                usleep(100);
            }
            
            // Get monthly revenue
            $monthlyRevenue = $this->statisticsService->getMonthlyRevenue();
            $monthKey = $now->format('Y-m');
            
            // Assert calculation is accurate
            $this->assertArrayHasKey($monthKey, $monthlyRevenue, 'Monthly revenue should include current month');
            $this->assertEquals($expectedRevenue, $monthlyRevenue[$monthKey], 'Monthly revenue calculation must be accurate');
        });
    }

    /**
     * Property 15: Transaction Count by Location
     * 
     * **Validates: Requirements 5.5**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 15: Transaction count by location
     */
    public function testTransactionCountByLocation()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(2000, 50000),
                'street_section' => Generator\elements(['Jl. Sudirman', 'Jl. Thamrin', 'Jl. Gatot Subroto']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            $locationCounts = [];
            
            foreach ($transactionData as $data) {
                $transaction = Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => $data['street_section'],
                    'vehicle_type' => 'motorcycle',
                    'amount' => $data['amount'],
                    'payment_status' => 'success',
                ]);
                
                $locationCounts[$data['street_section']] = ($locationCounts[$data['street_section']] ?? 0) + 1;
                usleep(100);
            }
            
            // Get transaction count by location
            $counts = $this->statisticsService->getTransactionCountByLocation();
            
            // Assert counts are accurate
            foreach ($locationCounts as $location => $expectedCount) {
                $this->assertArrayHasKey($location, $counts, "Location {$location} should be in counts");
                $this->assertEquals($expectedCount, $counts[$location], "Transaction count for {$location} must be accurate");
            }
        });
    }

    /**
     * Property 16: Transaction Count by Attendant
     * 
     * **Validates: Requirements 5.6**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 16: Transaction count by attendant
     */
    public function testTransactionCountByAttendant()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(2000, 50000),
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            $expectedCount = 0;
            
            foreach ($transactionData as $data) {
                $transaction = Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => 'Jl. Sudirman',
                    'vehicle_type' => $data['vehicle_type'],
                    'amount' => $data['amount'],
                    'payment_status' => 'success',
                ]);
                
                $expectedCount++;
                usleep(100);
            }
            
            // Get transaction count by attendant
            $counts = $this->statisticsService->getTransactionCountByAttendant();
            
            // Find the attendant in the results
            $attendantCount = null;
            foreach ($counts as $item) {
                if ($item['attendant_id'] === $attendant->id) {
                    $attendantCount = $item['count'];
                    break;
                }
            }
            
            // Assert count is accurate
            $this->assertNotNull($attendantCount, 'Attendant should be in counts');
            $this->assertEquals($expectedCount, $attendantCount, 'Transaction count for attendant must be accurate');
        });
    }

    /**
     * Property 17: Payment Status Distribution
     * 
     * **Validates: Requirements 5.7**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 17: Payment status distribution
     */
    public function testPaymentStatusDistribution()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(2000, 50000),
                'status' => Generator\elements(['success', 'failed', 'pending']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            $expectedDistribution = [];
            
            foreach ($transactionData as $data) {
                $transaction = Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => 'Jl. Sudirman',
                    'vehicle_type' => 'motorcycle',
                    'amount' => $data['amount'],
                    'payment_status' => $data['status'],
                ]);
                
                $expectedDistribution[$data['status']] = ($expectedDistribution[$data['status']] ?? 0) + 1;
                usleep(100);
            }
            
            // Get payment status distribution
            $distribution = $this->statisticsService->getPaymentStatusDistribution();
            
            // Assert distribution is accurate
            foreach ($expectedDistribution as $status => $expectedCount) {
                $this->assertArrayHasKey($status, $distribution, "Status {$status} should be in distribution");
                $this->assertEquals($expectedCount, $distribution[$status], "Count for status {$status} must be accurate");
            }
        });
    }

    /**
     * Property 29: Location Distribution Calculation
     * 
     * **Validates: Requirements 9.3**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 29: Location distribution calculation
     */
    public function testLocationDistributionCalculation()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(2000, 50000),
                'street_section' => Generator\elements(['Jl. Sudirman', 'Jl. Thamrin']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            
            foreach ($transactionData as $data) {
                Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => $data['street_section'],
                    'vehicle_type' => 'motorcycle',
                    'amount' => $data['amount'],
                    'payment_status' => 'success',
                ]);
                usleep(100);
            }
            
            // Get chart data
            $chartData = $this->chartDataService->getLocationDistributionChartData();
            
            // Assert chart data structure
            $this->assertArrayHasKey('labels', $chartData, 'Chart data should have labels');
            $this->assertArrayHasKey('datasets', $chartData, 'Chart data should have datasets');
            $this->assertCount(1, $chartData['datasets'], 'Should have one dataset');
            $this->assertCount(count($chartData['labels']), $chartData['datasets'][0]['data'], 'Data count should match labels count');
        });
    }

    /**
     * Property 30: Vehicle Type Distribution Calculation
     * 
     * **Validates: Requirements 9.4**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 30: Vehicle type distribution calculation
     */
    public function testVehicleTypeDistributionCalculation()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(2000, 50000),
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            
            foreach ($transactionData as $data) {
                Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => 'Jl. Sudirman',
                    'vehicle_type' => $data['vehicle_type'],
                    'amount' => $data['amount'],
                    'payment_status' => 'success',
                ]);
                usleep(100);
            }
            
            // Get chart data
            $chartData = $this->chartDataService->getVehicleTypeDistributionChartData();
            
            // Assert chart data structure
            $this->assertArrayHasKey('labels', $chartData, 'Chart data should have labels');
            $this->assertArrayHasKey('datasets', $chartData, 'Chart data should have datasets');
            $this->assertCount(1, $chartData['datasets'], 'Should have one dataset');
            $this->assertCount(count($chartData['labels']), $chartData['datasets'][0]['data'], 'Data count should match labels count');
        });
    }

    /**
     * Property 31: Top Attendants Ranking
     * 
     * **Validates: Requirements 9.5**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 31: Top attendants ranking
     */
    public function testTopAttendantsRanking()
    {
        $this->forAll(
            Generator\choose(1, 10)
        )
        ->withMaxSize(10)
        ->then(function ($limit) {
            $attendant = $this->getTestAttendant();
            
            // Create some transactions
            for ($i = 0; $i < 5; $i++) {
                Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => 'Jl. Sudirman',
                    'vehicle_type' => 'motorcycle',
                    'amount' => 5000,
                    'payment_status' => 'success',
                ]);
                usleep(100);
            }
            
            // Get top attendants
            $topAttendants = $this->statisticsService->getTopAttendants($limit);
            
            // Assert results
            $this->assertIsArray($topAttendants, 'Top attendants should be an array');
            $this->assertLessThanOrEqual($limit, count($topAttendants), 'Should not exceed limit');
            
            // Check structure
            if (count($topAttendants) > 0) {
                $this->assertArrayHasKey('attendant_id', $topAttendants[0], 'Should have attendant_id');
                $this->assertArrayHasKey('attendant_name', $topAttendants[0], 'Should have attendant_name');
                $this->assertArrayHasKey('transaction_count', $topAttendants[0], 'Should have transaction_count');
                $this->assertArrayHasKey('total_revenue', $topAttendants[0], 'Should have total_revenue');
            }
        });
    }

    /**
     * Property 32: Success Rate Calculation
     * 
     * **Validates: Requirements 9.6**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 32: Success rate calculation
     */
    public function testSuccessRateCalculation()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(2000, 50000),
                'status' => Generator\elements(['success', 'failed']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            $successCount = 0;
            $totalCount = 0;
            
            foreach ($transactionData as $data) {
                Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => 'Jl. Sudirman',
                    'vehicle_type' => 'motorcycle',
                    'amount' => $data['amount'],
                    'payment_status' => $data['status'],
                ]);
                
                if ($data['status'] === 'success') {
                    $successCount++;
                }
                $totalCount++;
                usleep(100);
            }
            
            // Get success rate
            $successRate = $this->statisticsService->getSuccessRate();
            
            // Calculate expected rate
            $expectedRate = $totalCount > 0 ? round(($successCount / $totalCount) * 100, 2) : 0;
            
            // Assert calculation is accurate
            $this->assertEquals($expectedRate, $successRate, 'Success rate calculation must be accurate');
        });
    }

    /**
     * Property 36: Location Summary Statistics Accuracy
     * 
     * **Validates: Requirements 12.1, 12.2, 12.3, 12.4**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 36: Location summary statistics accuracy
     */
    public function testLocationSummaryStatisticsAccuracy()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'amount' => Generator\choose(2000, 50000),
                'street_section' => Generator\elements(['Jl. Sudirman', 'Jl. Thamrin']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionData) {
            $attendant = $this->getTestAttendant();
            $locationStats = [];
            
            foreach ($transactionData as $data) {
                Transaction::create([
                    'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => $data['street_section'],
                    'vehicle_type' => 'motorcycle',
                    'amount' => $data['amount'],
                    'payment_status' => 'success',
                ]);
                
                if (!isset($locationStats[$data['street_section']])) {
                    $locationStats[$data['street_section']] = [
                        'count' => 0,
                        'total' => 0,
                    ];
                }
                $locationStats[$data['street_section']]['count']++;
                $locationStats[$data['street_section']]['total'] += $data['amount'];
                usleep(100);
            }
            
            // Get location summary
            $summary = $this->statisticsService->getLocationSummary();
            
            // Assert summary is accurate
            foreach ($locationStats as $location => $stats) {
                $found = false;
                foreach ($summary as $item) {
                    if ($item['street_section'] === $location) {
                        $found = true;
                        $this->assertEquals($stats['count'], $item['transaction_count'], "Transaction count for {$location} must be accurate");
                        $this->assertEquals($stats['total'], $item['total_revenue'], "Total revenue for {$location} must be accurate");
                        break;
                    }
                }
                $this->assertTrue($found, "Location {$location} should be in summary");
            }
        });
    }
}
