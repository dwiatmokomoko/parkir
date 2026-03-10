<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\Transaction;
use App\Services\PDFReportService;
use App\Services\ExcelReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Throwable;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Report $report;

    /**
     * Create a new job instance.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status to processing
            $this->report->update(['status' => 'processing']);

            // Get filters
            $filters = $this->report->filters;

            // Query transactions with filters
            $query = Transaction::query();

            // Apply date range filter
            if (!empty($filters['start_date'])) {
                $query->whereDate('created_at', '>=', $filters['start_date']);
            }

            if (!empty($filters['end_date'])) {
                $query->whereDate('created_at', '<=', $filters['end_date']);
            }

            // Apply street section filter
            if (!empty($filters['street_section'])) {
                $query->where('street_section', $filters['street_section']);
            }

            // Apply parking attendant filter
            if (!empty($filters['parking_attendant_id'])) {
                $query->where('parking_attendant_id', $filters['parking_attendant_id']);
            }

            // Eager load relationships
            $query->with(['parkingAttendant']);

            // Order by created_at
            $query->orderBy('created_at', 'desc');

            // Get transactions (limit to 10,000 for performance)
            $transactions = $query->limit(10000)->get();

            // Generate report based on type
            if ($this->report->type === 'pdf') {
                $filePath = app(PDFReportService::class)->generate($transactions, $filters);
            } else {
                $filePath = app(ExcelReportService::class)->generate($transactions, $filters);
            }

            // Update report with file path and completed status
            $this->report->update([
                'file_path' => $filePath,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        } catch (Throwable $e) {
            // Update report with error status
            $this->report->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            // Re-throw exception for queue to handle
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Update report status to failed if not already done
        if ($this->report->status !== 'failed') {
            $this->report->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
