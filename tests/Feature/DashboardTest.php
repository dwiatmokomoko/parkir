<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

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
     * Test dashboard with zero transactions
     */
    public function testDashboardWithZeroTransactions()
    {
        $response = $this->getJson('/api/dashboard');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'today_revenue',
            'month_revenue',
            'today_transactions',
            'month_transactions',
            'success_rate',
            'status_distribution',
        ]);
        
        $response->assertJson([
            'today_revenue' => 0,
            'month_revenue' => 0,
            'today_transactions' => 0,
            'month_transactions' => 0,
            'success_rate' => 0,
        ]);
    }

    /**
     * Test dashboard with single transaction
     */
    public function testDashboardWithSingleTransaction()
    {
        $attendant = $this->getTestAttendant();
        $today = Carbon::now();
        
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 5000,
            'payment_status' => 'success',
            'created_at' => $today,
        ]);
        
        $response = $this->getJson('/api/dashboard');
        
        $response->assertStatus(200);
        $response->assertJson([
            'today_revenue' => 5000,
            'month_revenue' => 5000,
            'today_transactions' => 1,
            'month_transactions' => 1,
            'success_rate' => 100.0,
        ]);
    }

    /**
     * Test dashboard with transactions spanning multiple months
     */
    public function testDashboardWithTransactionsSpanningMultipleMonths()
    {
        $attendant = $this->getTestAttendant();
        
        // Create transaction in previous month
        $lastMonth = Carbon::now()->subMonth();
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 5000,
            'payment_status' => 'success',
            'created_at' => $lastMonth,
        ]);
        
        // Create transaction in current month
        $today = Carbon::now();
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'car',
            'amount' => 10000,
            'payment_status' => 'success',
            'created_at' => $today,
        ]);
        
        $response = $this->getJson('/api/dashboard');
        
        $response->assertStatus(200);
        $response->assertJson([
            'today_revenue' => 10000,
            'month_revenue' => 10000,
            'today_transactions' => 1,
            'month_transactions' => 1,
        ]);
    }

    /**
     * Test filtering by date range boundaries
     */
    public function testFilteringByDateRangeBoundaries()
    {
        $attendant = $this->getTestAttendant();
        
        // Create transaction at start of month
        $startOfMonth = Carbon::now()->startOfMonth();
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 5000,
            'payment_status' => 'success',
            'created_at' => $startOfMonth,
        ]);
        
        // Create transaction at end of month
        $endOfMonth = Carbon::now()->endOfMonth();
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'car',
            'amount' => 10000,
            'payment_status' => 'success',
            'created_at' => $endOfMonth,
        ]);
        
        $response = $this->getJson('/api/dashboard');
        
        $response->assertStatus(200);
        $response->assertJson([
            'month_revenue' => 15000,
            'month_transactions' => 2,
        ]);
    }

    /**
     * Test daily revenue endpoint
     */
    public function testDailyRevenueEndpoint()
    {
        $attendant = $this->getTestAttendant();
        $today = Carbon::now();
        
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 5000,
            'payment_status' => 'success',
            'created_at' => $today,
        ]);
        
        $response = $this->getJson('/api/dashboard/daily-revenue');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'chart' => [
                'labels',
                'datasets',
            ],
        ]);
        
        $data = $response->json('data');
        $this->assertArrayHasKey($today->format('Y-m-d'), $data);
        $this->assertEquals(5000, $data[$today->format('Y-m-d')]);
    }

    /**
     * Test monthly revenue endpoint
     */
    public function testMonthlyRevenueEndpoint()
    {
        $attendant = $this->getTestAttendant();
        $now = Carbon::now();
        
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 5000,
            'payment_status' => 'success',
            'created_at' => $now,
        ]);
        
        $response = $this->getJson('/api/dashboard/monthly-revenue');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'chart' => [
                'labels',
                'datasets',
            ],
        ]);
        
        $data = $response->json('data');
        $this->assertArrayHasKey($now->format('Y-m'), $data);
        $this->assertEquals(5000, $data[$now->format('Y-m')]);
    }

    /**
     * Test location stats endpoint
     */
    public function testLocationStatsEndpoint()
    {
        $attendant = $this->getTestAttendant();
        
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 5000,
            'payment_status' => 'success',
        ]);
        
        $response = $this->getJson('/api/dashboard/location-stats');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'counts',
            'summary',
            'chart' => [
                'labels',
                'datasets',
            ],
        ]);
        
        $counts = $response->json('counts');
        $this->assertArrayHasKey('Jl. Sudirman', $counts);
        $this->assertEquals(1, $counts['Jl. Sudirman']);
    }

    /**
     * Test attendant stats endpoint
     */
    public function testAttendantStatsEndpoint()
    {
        $attendant = $this->getTestAttendant();
        
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 5000,
            'payment_status' => 'success',
        ]);
        
        $response = $this->getJson('/api/dashboard/attendant-stats');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'counts',
            'top_attendants',
            'chart' => [
                'labels',
                'datasets',
            ],
        ]);
        
        $topAttendants = $response->json('top_attendants');
        $this->assertGreaterThan(0, count($topAttendants));
        $this->assertArrayHasKey('attendant_id', $topAttendants[0]);
        $this->assertArrayHasKey('attendant_name', $topAttendants[0]);
        $this->assertArrayHasKey('transaction_count', $topAttendants[0]);
    }

    /**
     * Test vehicle stats endpoint
     */
    public function testVehicleStatsEndpoint()
    {
        $attendant = $this->getTestAttendant();
        
        Transaction::create([
            'transaction_id' => 'TRX-' . time() . '-' . uniqid(),
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 5000,
            'payment_status' => 'success',
        ]);
        
        $response = $this->getJson('/api/dashboard/vehicle-stats');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'counts',
            'chart' => [
                'labels',
                'datasets',
            ],
        ]);
        
        $counts = $response->json('counts');
        $this->assertArrayHasKey('motorcycle', $counts);
        $this->assertEquals(1, $counts['motorcycle']);
    }
}
