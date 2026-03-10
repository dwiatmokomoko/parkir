<?php

namespace App\Services;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PDFReportService
{
    /**
     * Generate PDF report from transactions
     *
     * @param Collection $transactions
     * @param array $filters
     * @return string Path to generated PDF file
     */
    public function generate(Collection $transactions, array $filters): string
    {
        // Calculate summary totals
        $totalRevenue = $transactions->sum('amount');
        $transactionCount = $transactions->count();
        $successCount = $transactions->where('payment_status', 'success')->count();
        $failedCount = $transactions->where('payment_status', 'failed')->count();
        $pendingCount = $transactions->where('payment_status', 'pending')->count();

        // Prepare data for view
        $data = [
            'transactions' => $transactions,
            'filters' => $filters,
            'totalRevenue' => $totalRevenue,
            'transactionCount' => $transactionCount,
            'successCount' => $successCount,
            'failedCount' => $failedCount,
            'pendingCount' => $pendingCount,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
            'startDate' => $filters['start_date'] ?? null,
            'endDate' => $filters['end_date'] ?? null,
        ];

        // Generate PDF from view
        $pdf = Pdf::loadView('reports.pdf', $data);

        // Store PDF file
        $filename = 'reports/laporan_parkir_' . now()->timestamp . '.pdf';
        $path = storage_path('app/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return $filename;
    }
}
