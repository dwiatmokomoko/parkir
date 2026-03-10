<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\ParkingAttendant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class StatisticsService
{
    /**
     * Calculate daily revenue for the last 30 days
     *
     * @return array
     */
    public function getDailyRevenue(): array
    {
        $cacheKey = 'daily_revenue_30days';

        return Cache::remember($cacheKey, 300, function () {
            $data = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $revenue = (float) Transaction::whereDate('created_at', $date)
                    ->where('payment_status', 'success')
                    ->sum('amount');
                $data[$date->format('Y-m-d')] = $revenue;
            }
            return $data;
        });
    }

    /**
     * Calculate monthly revenue for the last 12 months
     *
     * @return array
     */
    public function getMonthlyRevenue(): array
    {
        $cacheKey = 'monthly_revenue_12months';

        return Cache::remember($cacheKey, 3600, function () {
            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $revenue = (float) Transaction::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('payment_status', 'success')
                    ->sum('amount');
                $data[$date->format('Y-m')] = $revenue;
            }
            return $data;
        });
    }

    /**
     * Get transaction count by location
     *
     * @return array
     */
    public function getTransactionCountByLocation(): array
    {
        $cacheKey = 'transaction_count_by_location';

        return Cache::remember($cacheKey, 300, function () {
            return Transaction::where('payment_status', 'success')
                ->groupBy('street_section')
                ->selectRaw('street_section, COUNT(*) as count')
                ->pluck('count', 'street_section')
                ->toArray();
        });
    }

    /**
     * Get transaction count by attendant
     *
     * @return array
     */
    public function getTransactionCountByAttendant(): array
    {
        $cacheKey = 'transaction_count_by_attendant';

        return Cache::remember($cacheKey, 300, function () {
            return Transaction::where('payment_status', 'success')
                ->with('parkingAttendant')
                ->groupBy('parking_attendant_id')
                ->selectRaw('parking_attendant_id, COUNT(*) as count')
                ->get()
                ->map(function ($item) {
                    return [
                        'attendant_id' => $item->parking_attendant_id,
                        'attendant_name' => $item->parkingAttendant->name ?? 'Unknown',
                        'count' => $item->count,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get payment status distribution
     *
     * @return array
     */
    public function getPaymentStatusDistribution(): array
    {
        $cacheKey = 'payment_status_distribution';

        return Cache::remember($cacheKey, 300, function () {
            return Transaction::groupBy('payment_status')
                ->selectRaw('payment_status, COUNT(*) as count')
                ->pluck('count', 'payment_status')
                ->toArray();
        });
    }

    /**
     * Calculate success rate percentage
     *
     * @return float
     */
    public function getSuccessRate(): float
    {
        $cacheKey = 'success_rate';

        return Cache::remember($cacheKey, 300, function () {
            $total = Transaction::count();
            if ($total === 0) {
                return 0.0;
            }
            $successful = Transaction::where('payment_status', 'success')->count();
            return round(($successful / $total) * 100, 2);
        });
    }

    /**
     * Get transaction count by vehicle type
     *
     * @return array
     */
    public function getTransactionCountByVehicleType(): array
    {
        $cacheKey = 'transaction_count_by_vehicle_type';

        return Cache::remember($cacheKey, 300, function () {
            return Transaction::where('payment_status', 'success')
                ->groupBy('vehicle_type')
                ->selectRaw('vehicle_type, COUNT(*) as count')
                ->pluck('count', 'vehicle_type')
                ->toArray();
        });
    }

    /**
     * Get top 10 attendants by transaction count
     *
     * @return array
     */
    public function getTopAttendants(int $limit = 10): array
    {
        $cacheKey = 'top_attendants_' . $limit;

        return Cache::remember($cacheKey, 300, function () use ($limit) {
            return Transaction::where('payment_status', 'success')
                ->with('parkingAttendant')
                ->groupBy('parking_attendant_id')
                ->selectRaw('parking_attendant_id, COUNT(*) as count, SUM(amount) as total_revenue')
                ->orderByRaw('count DESC')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'attendant_id' => $item->parking_attendant_id,
                        'attendant_name' => $item->parkingAttendant->name ?? 'Unknown',
                        'transaction_count' => $item->count,
                        'total_revenue' => (float) $item->total_revenue,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get location summary statistics
     *
     * @return array
     */
    public function getLocationSummary(): array
    {
        $cacheKey = 'location_summary';

        return Cache::remember($cacheKey, 300, function () {
            return Transaction::where('payment_status', 'success')
                ->groupBy('street_section')
                ->selectRaw('street_section, COUNT(*) as transaction_count, SUM(amount) as total_revenue, AVG(amount) as avg_transaction')
                ->get()
                ->map(function ($item) {
                    return [
                        'street_section' => $item->street_section,
                        'transaction_count' => $item->transaction_count,
                        'total_revenue' => (float) $item->total_revenue,
                        'average_transaction' => (float) $item->avg_transaction,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Invalidate all statistics cache
     *
     * @return void
     */
    public function invalidateAllCache(): void
    {
        Cache::forget('daily_revenue_30days');
        Cache::forget('monthly_revenue_12months');
        Cache::forget('transaction_count_by_location');
        Cache::forget('transaction_count_by_attendant');
        Cache::forget('payment_status_distribution');
        Cache::forget('success_rate');
        Cache::forget('transaction_count_by_vehicle_type');
        Cache::forget('location_summary');
        
        // Invalidate top attendants cache for all limits
        for ($i = 5; $i <= 20; $i += 5) {
            Cache::forget('top_attendants_' . $i);
        }
    }
}
