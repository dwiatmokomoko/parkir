<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use App\Services\ChartDataService;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private StatisticsService $statisticsService;
    private ChartDataService $chartDataService;

    public function __construct(
        StatisticsService $statisticsService,
        ChartDataService $chartDataService
    ) {
        $this->statisticsService = $statisticsService;
        $this->chartDataService = $chartDataService;
    }

    /**
     * Get dashboard summary
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $today = Carbon::now();
        
        // Calculate today's revenue
        $todayRevenue = (float) Transaction::whereDate('created_at', $today)
            ->where('payment_status', 'success')
            ->sum('amount');
        
        // Calculate this month's revenue
        $monthRevenue = (float) Transaction::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->where('payment_status', 'success')
            ->sum('amount');
        
        // Get total transactions today
        $todayTransactions = Transaction::whereDate('created_at', $today)->count();
        
        // Get total transactions this month
        $monthTransactions = Transaction::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->count();
        
        // Get success rate
        $successRate = $this->statisticsService->getSuccessRate();
        
        // Get payment status distribution
        $statusDistribution = $this->statisticsService->getPaymentStatusDistribution();
        
        return response()->json([
            'today_revenue' => $todayRevenue,
            'month_revenue' => $monthRevenue,
            'today_transactions' => $todayTransactions,
            'month_transactions' => $monthTransactions,
            'success_rate' => $successRate,
            'status_distribution' => $statusDistribution,
        ]);
    }

    /**
     * Get daily revenue for last 30 days
     *
     * @return JsonResponse
     */
    public function getDailyRevenue(): JsonResponse
    {
        $dailyRevenue = $this->statisticsService->getDailyRevenue();
        $chartData = $this->chartDataService->getDailyRevenueChartData();
        
        return response()->json([
            'data' => $dailyRevenue,
            'chart' => $chartData,
        ]);
    }

    /**
     * Get monthly revenue for last 12 months
     *
     * @return JsonResponse
     */
    public function getMonthlyRevenue(): JsonResponse
    {
        $monthlyRevenue = $this->statisticsService->getMonthlyRevenue();
        $chartData = $this->chartDataService->getMonthlyRevenueChartData();
        
        return response()->json([
            'data' => $monthlyRevenue,
            'chart' => $chartData,
        ]);
    }

    /**
     * Get location statistics
     *
     * @return JsonResponse
     */
    public function getLocationStats(): JsonResponse
    {
        $locationCounts = $this->statisticsService->getTransactionCountByLocation();
        $locationSummary = $this->statisticsService->getLocationSummary();
        $chartData = $this->chartDataService->getLocationDistributionChartData();
        
        return response()->json([
            'counts' => $locationCounts,
            'summary' => $locationSummary,
            'chart' => $chartData,
        ]);
    }

    /**
     * Get attendant statistics
     *
     * @return JsonResponse
     */
    public function getAttendantStats(): JsonResponse
    {
        $attendantCounts = $this->statisticsService->getTransactionCountByAttendant();
        $topAttendants = $this->statisticsService->getTopAttendants(10);
        $chartData = $this->chartDataService->getTopAttendantsChartData(10);
        
        return response()->json([
            'counts' => $attendantCounts,
            'top_attendants' => $topAttendants,
            'chart' => $chartData,
        ]);
    }

    /**
     * Get vehicle type statistics
     *
     * @return JsonResponse
     */
    public function getVehicleStats(): JsonResponse
    {
        $vehicleCounts = $this->statisticsService->getTransactionCountByVehicleType();
        $chartData = $this->chartDataService->getVehicleTypeDistributionChartData();
        
        return response()->json([
            'counts' => $vehicleCounts,
            'chart' => $chartData,
        ]);
    }
}
