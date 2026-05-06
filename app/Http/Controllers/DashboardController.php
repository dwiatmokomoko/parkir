<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use App\Services\ChartDataService;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        try {
            $today = Carbon::now();

            $todayRevenue = (float) Transaction::whereDate('created_at', $today)
                ->where('payment_status', 'success')
                ->sum('amount');

            $monthRevenue = (float) Transaction::whereYear('created_at', $today->year)
                ->whereMonth('created_at', $today->month)
                ->where('payment_status', 'success')
                ->sum('amount');

            $todayTransactions = Transaction::whereDate('created_at', $today)->count();

            $monthTransactions = Transaction::whereYear('created_at', $today->year)
                ->whereMonth('created_at', $today->month)
                ->count();

            $totalTransactions = Transaction::count();
            $successfulTransactions = Transaction::where('payment_status', 'success')->count();
            $successRate = $totalTransactions > 0
                ? round(($successfulTransactions / $totalTransactions) * 100, 2)
                : 0.0;

            $statusDistribution = Transaction::groupBy('payment_status')
                ->selectRaw('payment_status, COUNT(*) as count')
                ->pluck('count', 'payment_status')
                ->toArray();

            $recentTransactions = Transaction::with('parkingAttendant')
                ->latest()
                ->limit(50)
                ->get();

            $dailyRevenue = collect(range(29, 0))
                ->map(function (int $daysAgo) {
                    $date = Carbon::now()->subDays($daysAgo);
                    $revenue = (float) Transaction::whereDate('created_at', $date)
                        ->where('payment_status', 'success')
                        ->sum('amount');
                    $count = Transaction::whereDate('created_at', $date)->count();

                    return [
                        'date' => $date->format('Y-m-d'),
                        'label' => $date->format('d/m'),
                        'revenue' => $revenue,
                        'count' => $count,
                        'chart_value' => $revenue > 0 ? $revenue : $count,
                        'chart_label' => $revenue > 0 ? 'Pendapatan' : 'Jumlah Transaksi',
                        'chart_type' => $revenue > 0 ? 'money' : 'count',
                    ];
                })
                ->values();

            $monthlyRevenue = collect(range(11, 0))
                ->map(function (int $monthsAgo) {
                    $date = Carbon::now()->subMonths($monthsAgo);
                    $revenue = (float) Transaction::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->where('payment_status', 'success')
                        ->sum('amount');
                    $count = Transaction::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();

                    return [
                        'month' => $date->format('Y-m'),
                        'label' => $date->format('M Y'),
                        'revenue' => $revenue,
                        'count' => $count,
                        'chart_value' => $revenue > 0 ? $revenue : $count,
                        'chart_label' => $revenue > 0 ? 'Pendapatan' : 'Jumlah Transaksi',
                        'chart_type' => $revenue > 0 ? 'money' : 'count',
                    ];
                })
                ->values();

            $locationStats = Transaction::groupBy('street_section')
                ->selectRaw('street_section, COUNT(*) as count')
                ->orderByDesc('count')
                ->get()
                ->map(fn (Transaction $transaction) => [
                    'street_section' => $transaction->street_section ?: 'Tidak diketahui',
                    'count' => (int) $transaction->count,
                ]);

            $vehicleStats = Transaction::groupBy('vehicle_type')
                ->selectRaw('vehicle_type, COUNT(*) as count')
                ->orderByDesc('count')
                ->get()
                ->map(fn (Transaction $transaction) => [
                    'vehicle_type' => $transaction->vehicle_type ?: 'Tidak diketahui',
                    'count' => (int) $transaction->count,
                ]);

            $paymentStatus = [
                'success' => (int) ($statusDistribution['success'] ?? 0),
                'pending' => (int) ($statusDistribution['pending'] ?? 0),
                'failed' => (int) ($statusDistribution['failed'] ?? 0),
                'expired' => (int) ($statusDistribution['expired'] ?? 0),
            ];

            return response()->json([
                'success' => true,
                'summary' => [
                    'dailyRevenue' => $todayRevenue,
                    'monthlyRevenue' => $monthRevenue,
                    'totalTransactions' => $monthTransactions,
                    'allTransactions' => $totalTransactions,
                    'todayTransactions' => $todayTransactions,
                    'successRate' => $successRate,
                ],
                'paymentStatus' => $paymentStatus,
                'transactions' => $recentTransactions,
                'dailyRevenue' => $dailyRevenue,
                'monthlyRevenue' => $monthlyRevenue,
                'locationStats' => $locationStats,
                'vehicleStats' => $vehicleStats,
                'today_revenue' => $todayRevenue,
                'month_revenue' => $monthRevenue,
                'today_transactions' => $todayTransactions,
                'month_transactions' => $monthTransactions,
                'success_rate' => $successRate,
                'status_distribution' => $statusDistribution,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error loading dashboard data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
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
