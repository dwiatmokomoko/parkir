<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Jobs\GenerateReportJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Generate a new report (async job dispatch)
     *
     * @return JsonResponse
     */
    public function generate(): JsonResponse
    {
        // Get user ID from session
        $userId = request()->session()->get('admin_user_id');
        
        // Validate request
        $validated = request()->validate([
            'type' => 'required|in:pdf,excel',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'street_section' => 'nullable|string',
            'parking_attendant_id' => 'nullable|integer|exists:parking_attendants,id',
        ]);

        // Validate date range (max 90 days)
        $startDate = Carbon::createFromFormat('Y-m-d', $validated['start_date']);
        $endDate = Carbon::createFromFormat('Y-m-d', $validated['end_date']);
        
        if ($endDate->diffInDays($startDate) > 90) {
            return response()->json([
                'success' => false,
                'message' => 'Rentang tanggal maksimal 90 hari',
            ], 422);
        }

        // Create report record with pending status
        $report = Report::create([
            'user_id' => $userId,
            'type' => $validated['type'],
            'filters' => [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'street_section' => $validated['street_section'] ?? null,
                'parking_attendant_id' => $validated['parking_attendant_id'] ?? null,
            ],
            'status' => 'pending',
            'created_at' => now(),
        ]);

        // Dispatch async job
        GenerateReportJob::dispatch($report);

        return response()->json([
            'success' => true,
            'message' => 'Laporan sedang diproses',
            'report_id' => $report->id,
            'status' => 'pending',
        ], 202);
    }

    /**
     * Download generated report file
     *
     * @param int $reportId
     * @return Response|JsonResponse
     */
    public function download(int $reportId): Response|JsonResponse
    {
        $report = Report::findOrFail($reportId);
        
        // Get user ID from session
        $userId = request()->session()->get('admin_user_id');

        // Check authorization
        if ($report->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengunduh laporan ini',
            ], 403);
        }

        // Check if report is completed
        if ($report->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Laporan belum selesai diproses',
                'status' => $report->status,
            ], 400);
        }

        // Check if file exists
        if (!$report->file_path || !Storage::disk('local')->exists($report->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File laporan tidak ditemukan',
            ], 404);
        }

        // Determine file extension
        $extension = $report->type === 'pdf' ? 'pdf' : 'xlsx';
        $filename = "laporan_parkir_{$report->id}.{$extension}";

        return Storage::disk('local')->download($report->file_path, $filename);
    }

    /**
     * Check report generation status
     *
     * @param int $reportId
     * @return JsonResponse
     */
    public function status(int $reportId): JsonResponse
    {
        $report = Report::findOrFail($reportId);
        
        // Get user ID from session
        $userId = request()->session()->get('admin_user_id');

        // Check authorization
        if ($report->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat laporan ini',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'report_id' => $report->id,
            'status' => $report->status,
            'type' => $report->type,
            'created_at' => $report->created_at,
            'completed_at' => $report->completed_at,
            'error_message' => $report->error_message,
        ]);
    }
}
