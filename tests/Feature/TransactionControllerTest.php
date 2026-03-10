<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    /**
     * Test index endpoint returns paginated transactions
     */
    public function test_index_returns_paginated_transactions(): void
    {
        $attendant = ParkingAttendant::factory()->create();
        Transaction::factory(20)->create(['parking_attendant_id' => $attendant->id]);

        $response = $this->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'transaction_id',
                        'parking_attendant_id',
                        'street_section',
                        'vehicle_type',
                        'amount',
                        'payment_status',
                    ],
                ],
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to',
                ],
            ])
            ->assertJsonPath('pagination.total', 20)
            ->assertJsonPath('pagination.per_page', 15);
    }

    /**
     * Test index with status filter
     */
    public function test_index_with_status_filter(): void
    {
        $attendant = ParkingAttendant::factory()->create();

        Transaction::factory(10)->create([
            'parking_attendant_id' => $attendant->id,
            'payment_status' => 'success',
        ]);

        Transaction::factory(5)->create([
            'parking_attendant_id' => $attendant->id,
            'payment_status' => 'failed',
        ]);

        $response = $this->getJson('/api/transactions?status=success');

        $response->assertStatus(200)
            ->assertJsonPath('pagination.total', 10);
    }

    /**
     * Test index with date range filter
     */
    public function test_index_with_date_range_filter(): void
    {
        $attendant = ParkingAttendant::factory()->create();
        $today = Carbon::now();
        $yesterday = $today->copy()->subDay();

        Transaction::factory(5)->create([
            'parking_attendant_id' => $attendant->id,
            'created_at' => $yesterday,
        ]);

        Transaction::factory(10)->create([
            'parking_attendant_id' => $attendant->id,
            'created_at' => $today,
        ]);

        $response = $this->getJson("/api/transactions?start_date={$today->format('Y-m-d')}&end_date={$today->format('Y-m-d')}");

        $response->assertStatus(200)
            ->assertJsonPath('pagination.total', 10);
    }

    /**
     * Test show endpoint returns transaction details
     */
    public function test_show_returns_transaction_details(): void
    {
        $attendant = ParkingAttendant::factory()->create();
        $transaction = Transaction::factory()->create(['parking_attendant_id' => $attendant->id]);

        $response = $this->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'transaction_id',
                    'parking_attendant_id',
                    'street_section',
                    'vehicle_type',
                    'amount',
                    'payment_status',
                ],
            ])
            ->assertJsonPath('data.id', $transaction->id);
    }

    /**
     * Test show with non-existent transaction
     */
    public function test_show_with_non_existent_transaction(): void
    {
        $response = $this->getJson('/api/transactions/99999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Transaksi tidak ditemukan');
    }

    /**
     * Test get by location endpoint
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

        $response = $this->getJson('/api/transactions/location/Jalan%20Sudirman');

        $response->assertStatus(200)
            ->assertJsonPath('count', 10)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'transaction_id',
                        'street_section',
                    ],
                ],
            ]);
    }

    /**
     * Test get by attendant endpoint
     */
    public function test_get_by_attendant(): void
    {
        $attendant1 = ParkingAttendant::factory()->create();
        $attendant2 = ParkingAttendant::factory()->create();

        Transaction::factory(8)->create(['parking_attendant_id' => $attendant1->id]);
        Transaction::factory(5)->create(['parking_attendant_id' => $attendant2->id]);

        $response = $this->getJson("/api/transactions/attendant/{$attendant1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('count', 8)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'transaction_id',
                        'parking_attendant_id',
                    ],
                ],
            ]);
    }

    /**
     * Test index with invalid per_page parameter
     */
    public function test_index_with_invalid_per_page(): void
    {
        $response = $this->getJson('/api/transactions?per_page=1000');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Test index with invalid status filter
     */
    public function test_index_with_invalid_status_filter(): void
    {
        $response = $this->getJson('/api/transactions?status=invalid_status');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Test index with invalid date format
     */
    public function test_index_with_invalid_date_format(): void
    {
        $response = $this->getJson('/api/transactions?start_date=invalid-date');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Test index with multiple filters combined
     */
    public function test_index_with_multiple_filters_combined(): void
    {
        $attendant = ParkingAttendant::factory()->create(['street_section' => 'Jalan Sudirman']);
        $today = Carbon::now();

        Transaction::factory(5)->create([
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jalan Sudirman',
            'vehicle_type' => 'motorcycle',
            'payment_status' => 'success',
            'created_at' => $today,
        ]);

        Transaction::factory(3)->create([
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jalan Sudirman',
            'vehicle_type' => 'car',
            'payment_status' => 'success',
            'created_at' => $today,
        ]);

        $response = $this->getJson(
            "/api/transactions?status=success&street_section=Jalan%20Sudirman&vehicle_type=motorcycle&start_date={$today->format('Y-m-d')}"
        );

        $response->assertStatus(200)
            ->assertJsonPath('pagination.total', 5);
    }

    /**
     * Test pagination navigation
     */
    public function test_pagination_navigation(): void
    {
        $attendant = ParkingAttendant::factory()->create();
        Transaction::factory(50)->create(['parking_attendant_id' => $attendant->id]);

        // Get first page
        $response1 = $this->getJson('/api/transactions?per_page=15&page=1');
        $response1->assertStatus(200)
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.last_page', 4);

        // Get second page
        $response2 = $this->getJson('/api/transactions?per_page=15&page=2');
        $response2->assertStatus(200)
            ->assertJsonPath('pagination.current_page', 2);
    }

    /**
     * Test transaction with all optional fields populated
     */
    public function test_transaction_with_all_optional_fields(): void
    {
        $attendant = ParkingAttendant::factory()->create();
        $transaction = Transaction::factory()->create([
            'parking_attendant_id' => $attendant->id,
            'payment_status' => 'success',
            'payment_method' => 'qris',
            'paid_at' => Carbon::now(),
        ]);

        $response = $this->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.payment_method', 'qris')
            ->assertJsonPath('data.payment_status', 'success');
    }
}
