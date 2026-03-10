<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ChartDataService
{
    private StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Format daily revenue data for Chart.js line chart
     *
     * @return array
     */
    public function getDailyRevenueChartData(): array
    {
        $cacheKey = 'chart_daily_revenue';

        return Cache::remember($cacheKey, 300, function () {
            $dailyRevenue = $this->statisticsService->getDailyRevenue();
            
            return [
                'labels' => array_keys($dailyRevenue),
                'datasets' => [
                    [
                        'label' => 'Daily Revenue',
                        'data' => array_values($dailyRevenue),
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true,
                    ]
                ]
            ];
        });
    }

    /**
     * Format monthly revenue data for Chart.js bar chart
     *
     * @return array
     */
    public function getMonthlyRevenueChartData(): array
    {
        $cacheKey = 'chart_monthly_revenue';

        return Cache::remember($cacheKey, 3600, function () {
            $monthlyRevenue = $this->statisticsService->getMonthlyRevenue();
            
            return [
                'labels' => array_keys($monthlyRevenue),
                'datasets' => [
                    [
                        'label' => 'Monthly Revenue',
                        'data' => array_values($monthlyRevenue),
                        'backgroundColor' => '#10b981',
                        'borderColor' => '#059669',
                        'borderWidth' => 1,
                    ]
                ]
            ];
        });
    }

    /**
     * Format location distribution data for Chart.js pie chart
     *
     * @return array
     */
    public function getLocationDistributionChartData(): array
    {
        $cacheKey = 'chart_location_distribution';

        return Cache::remember($cacheKey, 300, function () {
            $locationData = $this->statisticsService->getTransactionCountByLocation();
            
            $colors = [
                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'
            ];
            
            return [
                'labels' => array_keys($locationData),
                'datasets' => [
                    [
                        'label' => 'Transactions by Location',
                        'data' => array_values($locationData),
                        'backgroundColor' => array_slice($colors, 0, count($locationData)),
                        'borderColor' => '#ffffff',
                        'borderWidth' => 2,
                    ]
                ]
            ];
        });
    }

    /**
     * Format vehicle type distribution data for Chart.js bar chart
     *
     * @return array
     */
    public function getVehicleTypeDistributionChartData(): array
    {
        $cacheKey = 'chart_vehicle_type_distribution';

        return Cache::remember($cacheKey, 300, function () {
            $vehicleData = $this->statisticsService->getTransactionCountByVehicleType();
            
            return [
                'labels' => array_keys($vehicleData),
                'datasets' => [
                    [
                        'label' => 'Transactions by Vehicle Type',
                        'data' => array_values($vehicleData),
                        'backgroundColor' => ['#3b82f6', '#10b981'],
                        'borderColor' => ['#1e40af', '#047857'],
                        'borderWidth' => 1,
                    ]
                ]
            ];
        });
    }

    /**
     * Format top attendants data for Chart.js bar chart
     *
     * @param int $limit
     * @return array
     */
    public function getTopAttendantsChartData(int $limit = 10): array
    {
        $cacheKey = 'chart_top_attendants_' . $limit;

        return Cache::remember($cacheKey, 300, function () use ($limit) {
            $topAttendants = $this->statisticsService->getTopAttendants($limit);
            
            $names = array_map(fn($item) => $item['attendant_name'], $topAttendants);
            $counts = array_map(fn($item) => $item['transaction_count'], $topAttendants);
            
            return [
                'labels' => $names,
                'datasets' => [
                    [
                        'label' => 'Transaction Count',
                        'data' => $counts,
                        'backgroundColor' => '#8b5cf6',
                        'borderColor' => '#6d28d9',
                        'borderWidth' => 1,
                    ]
                ]
            ];
        });
    }

    /**
     * Invalidate all chart cache
     *
     * @return void
     */
    public function invalidateAllCache(): void
    {
        Cache::forget('chart_daily_revenue');
        Cache::forget('chart_monthly_revenue');
        Cache::forget('chart_location_distribution');
        Cache::forget('chart_vehicle_type_distribution');
        
        // Invalidate top attendants chart cache for all limits
        for ($i = 5; $i <= 20; $i += 5) {
            Cache::forget('chart_top_attendants_' . $i);
        }
    }
}
