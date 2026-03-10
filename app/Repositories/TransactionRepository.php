<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TransactionRepository
{
    /**
     * Get paginated transactions with filters
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Transaction::query();

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('payment_status', $filters['status']);
        }

        if (!empty($filters['street_section'])) {
            $query->where('street_section', $filters['street_section']);
        }

        if (!empty($filters['attendant_id'])) {
            $query->where('parking_attendant_id', $filters['attendant_id']);
        }

        if (!empty($filters['vehicle_type'])) {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        // Date range filtering
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Eager load relationships for performance
        $query->with(['parkingAttendant', 'notifications']);

        // Order by created_at descending
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get transaction by ID with relationships
     *
     * @param int $id
     * @return Transaction|null
     */
    public function getById(int $id): ?Transaction
    {
        return Transaction::with(['parkingAttendant', 'notifications'])
            ->find($id);
    }

    /**
     * Get transactions by location with caching
     *
     * @param string $streetSection
     * @param int $limit
     * @return array
     */
    public function getByLocation(string $streetSection, int $limit = 50): array
    {
        $cacheKey = "transactions_location_{$streetSection}";

        return Cache::remember($cacheKey, 300, function () use ($streetSection, $limit) {
            return Transaction::where('street_section', $streetSection)
                ->with('parkingAttendant')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get transactions by attendant with caching
     *
     * @param int $attendantId
     * @param int $limit
     * @return array
     */
    public function getByAttendant(int $attendantId, int $limit = 50): array
    {
        $cacheKey = "transactions_attendant_{$attendantId}";

        return Cache::remember($cacheKey, 300, function () use ($attendantId, $limit) {
            return Transaction::where('parking_attendant_id', $attendantId)
                ->with('parkingAttendant')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get daily revenue with caching
     *
     * @param Carbon $date
     * @return float
     */
    public function getDailyRevenue(Carbon $date): float
    {
        $cacheKey = "daily_revenue_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, 3600, function () use ($date) {
            return (float) Transaction::whereDate('created_at', $date)
                ->where('payment_status', 'success')
                ->sum('amount');
        });
    }

    /**
     * Get monthly revenue with caching
     *
     * @param int $month
     * @param int $year
     * @return float
     */
    public function getMonthlyRevenue(int $month, int $year): float
    {
        $cacheKey = "monthly_revenue_{$year}_{$month}";

        return Cache::remember($cacheKey, 3600, function () use ($month, $year) {
            return (float) Transaction::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('payment_status', 'success')
                ->sum('amount');
        });
    }

    /**
     * Get transaction count by location
     *
     * @param string $streetSection
     * @return int
     */
    public function getCountByLocation(string $streetSection): int
    {
        $cacheKey = "transaction_count_location_{$streetSection}";

        return Cache::remember($cacheKey, 300, function () use ($streetSection) {
            return Transaction::where('street_section', $streetSection)
                ->where('payment_status', 'success')
                ->count();
        });
    }

    /**
     * Get transaction count by attendant
     *
     * @param int $attendantId
     * @return int
     */
    public function getCountByAttendant(int $attendantId): int
    {
        $cacheKey = "transaction_count_attendant_{$attendantId}";

        return Cache::remember($cacheKey, 300, function () use ($attendantId) {
            return Transaction::where('parking_attendant_id', $attendantId)
                ->where('payment_status', 'success')
                ->count();
        });
    }

    /**
     * Invalidate cache for a location
     *
     * @param string $streetSection
     * @return void
     */
    public function invalidateLocationCache(string $streetSection): void
    {
        Cache::forget("transactions_location_{$streetSection}");
        Cache::forget("transaction_count_location_{$streetSection}");
    }

    /**
     * Invalidate cache for an attendant
     *
     * @param int $attendantId
     * @return void
     */
    public function invalidateAttendantCache(int $attendantId): void
    {
        Cache::forget("transactions_attendant_{$attendantId}");
        Cache::forget("transaction_count_attendant_{$attendantId}");
    }

    /**
     * Invalidate daily revenue cache
     *
     * @param Carbon $date
     * @return void
     */
    public function invalidateDailyRevenueCache(Carbon $date): void
    {
        Cache::forget("daily_revenue_{$date->format('Y-m-d')}");
    }

    /**
     * Invalidate monthly revenue cache
     *
     * @param int $month
     * @param int $year
     * @return void
     */
    public function invalidateMonthlyRevenueCache(int $month, int $year): void
    {
        Cache::forget("monthly_revenue_{$year}_{$month}");
    }
}
