<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TransactionRepository();
    }

    /**
     * Test pagination with large datasets
     */
    public function test_pagination_with_large_datasets(): void
    {
        // Create attendant
        $attendant = ParkingAttendant::factory()->create();

        // Create 100 transactions
        Transaction::factory(100)->create([
            'parking_attendant_id' => $attendant->id,
            'payment_status' => 'success',
        ]);

        // Get first page
        $result = $this->repository->getPaginated(15, []);

        $this->assertCount(15, $result->items());
        $this->assertEquals(100, $result->total());
        $this->assertEquals(1, $result->currentPage());
        $this->assertEquals(7, $result->lastPage());
    }

    /**
     * Test filtering by multiple criteria
     */
    public function test_filtering_by_multiple_criteria(): void
    {
        $attendant = ParkingAttendant::factory()->create(['street_section' => 'Jalan Sudirman']);

        // Create transactions with different statuses
        Transaction::factory(5)->create([
            'parking_attendant_id' => $attendant->id,
            'payment_status' => 'success',
            'street_section' => 'Jalan Sudirman',
            'vehicle_type' => 'motorcycle',
        ]);

        Transaction::factory(3)->create([
            'parking_attendant_id' => $attendant->id,
            'payment_status' => 'failed',
            'street_section' => 'Jalan Sudirman',
            'vehicle_type' => 'motorcycle',
        ]);

        Transaction::factory(2)->create([
            'parking_attendant_id' => $attendant->id,
            'payment_status' => 'success',
            'street_section' => 'Jalan Sudirman',
            'vehicle_type' => 'car',
        ]);

        // Filter by status and vehicle type
        $result = $this->repository->getPaginated(15, [
            'status' => 'success',
            'vehicle_type' => 'motorcycle',
        ]);

        $this->assertCount(5, $result->items());
    }

    /**
     * Test transaction with missing optional fields
     */
    public function test_transaction_with_missing_optional_fields(): void
    {
        $attendant = ParkingAttendant::factory()->create();

        $transaction = Transaction::create([
            'transaction_id' => 'TRX-123',
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jalan Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 2000,
            'payment_status' => 'pending',
            // Optional fields not provided
        ]);

        $retrieved = $this->repository->getById($transaction->id);

        $this->assertNotNull($retrieved);
        $this->assertNull($retrieved->payment_method);
        $this->assertNull($retrieved->paid_at);
        $this->assertNull($retrieved->failure_reason);
    }

    /**
     * Test query performance with eager loading
     */
    public function test_query_performance_with_eager_loading(): void
    {
        $attendant = ParkingAttendant::factory()->create();

        Transaction::factory(50)->create([
            'parking_attendant_id' => $attendant->id,
        ]);

        // This should use eager loading to avoid N+1 queries
        $result = $this->repository->getPaginated(50, []);

        // Verify relationships are loaded
        foreach ($result->items() as $transaction) {
            $this->assertNotNull($transaction->parkingAttendant);
        }
    }

    /**
     * Test date range filtering
     */
    public function test_date_range_filtering(): void
    {
        $attendant = ParkingAttendant::factory()->create();

        // Create transactions on different dates
        $today = Carbon::now();
        $yesterday = $today->copy()->subDay();
        $tomorrowDate = $today->copy()->addDay();

        Transaction::factory(5)->create([
            'parking_attendant_id' => $attendant->id,
            'created_at' => $yesterday,
        ]);

        Transaction::factory(10)->create([
            'parking_attendant_id' => $attendant->id,
            'created_at' => $today,
        ]);

        Transaction::factory(3)->create([
            'parking_attendant_id' => $attendant->id,
            'created_at' => $tomorrowDate,
        ]);

        // Filter by date range
        $result = $this->repository->getPaginated(50, [
            'start_date' => $today->format('Y-m-d'),
            'end_date' => $today->format('Y-m-d'),
        ]);

        $this->assertCount(10, $result->items());
    }

    /**
     * Test get by location
     */
    public function test_get_by_location(): void
    {
        $attendant1 = ParkingAttendant::factory()->create(['street_section' => 'Jalan Sudirman']);
        $attendant2 = ParkingAttendant::factory()->create(['street_section' => 'Jalan Gatot Subroto']);

        Transaction::factory(10)->create([
            'parking_attendant_id' => $attendant1->id,
            'street_section' => 'Jalan Sudirman',
        ]);

        Transaction::factory(5)->create([
            'parking_attendant_id' => $attendant2->id,
            'street_section' => 'Jalan Gatot Subroto',
        ]);

        $result = $this->repository->getByLocation('Jalan Sudirman');

        $this->assertCount(10, $result);
    }

    /**
     * Test get by attendant
     */
    public function test_get_by_attendant(): void
    {
        $attendant1 = ParkingAttendant::factory()->create();
        $attendant2 = ParkingAttendant::factory()->create();

        Transaction::factory(8)->create(['parking_attendant_id' => $attendant1->id]);
        Transaction::factory(5)->create(['parking_attendant_id' => $attendant2->id]);

        $result = $this->repository->getByAttendant($attendant1->id);

        $this->assertCount(8, $result);
    }

    /**
     * Test daily revenue calculation
     */
    public function test_daily_revenue_calculation(): void
    {
        $attendant = ParkingAttendant::factory()->create();
        $today = Carbon::now();

        // Create successful transactions
        Transaction::factory(5)->create([
            'parking_attendant_id' => $attendant->id,
            'amount' => 2000,
            'payment_status' => 'success',
            'created_at' => $today,
        ]);

        // Create failed transactions (should not be counted)
        Transaction::factory(3)->create([
            'parking_attendant_id' => $attendant->id,
            'amount' => 2000,
            'payment_status' => 'failed',
            'created_at' => $today,
        ]);

        $revenue = $this->repository->getDailyRevenue($today);

        $this->assertEquals(10000, $revenue);
    }

    /**
     * Test monthly revenue calculation
     */
    public function test_monthly_revenue_calculation(): void
    {
        $attendant = ParkingAttendant::factory()->create();

        // Create transactions for current month
        Transaction::factory(10)->create([
            'parking_attendant_id' => $attendant->id,
            'amount' => 5000,
            'payment_status' => 'success',
            'created_at' => Carbon::now(),
        ]);

        $revenue = $this->repository->getMonthlyRevenue(
            Carbon::now()->month,
            Carbon::now()->year
        );

        $this->assertEquals(50000, $revenue);
    }

    /**
     * Test cache invalidation for location
     */
    public function test_cache_invalidation_for_location(): void
    {
        $attendant = ParkingAttendant::factory()->create(['street_section' => 'Jalan Sudirman']);

        Transaction::factory(5)->create([
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jalan Sudirman',
        ]);

        // Get from cache
        $result1 = $this->repository->getByLocation('Jalan Sudirman');
        $this->assertCount(5, $result1);

        // Invalidate cache
        $this->repository->invalidateLocationCache('Jalan Sudirman');

        // Create new transaction
        Transaction::factory(1)->create([
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jalan Sudirman',
        ]);

        // Get fresh data
        $result2 = $this->repository->getByLocation('Jalan Sudirman');
        $this->assertCount(6, $result2);
    }
}
