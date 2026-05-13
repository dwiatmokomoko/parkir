<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Jobs\GenerateReportJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');

        $reports = Report::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get()
            ->map(function (Report $report) {
                return [
                    'id' => $report->id,
                    'type' => $report->type,
                    'filters' => $report->filters ?? [],
                    'status' => $report->status,
                    'error_message' => $report->error_message,
                    'created_at' => $report->created_at,
                    'completed_at' => $report->completed_at,
                    'is_downloadable' => $report->status === 'completed' && !empty($report->file_path),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Generate a new report (async job dispatch)
     *
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        // Get user ID from session
        $userId = $request->session()->get('admin_user_id');

        $request->merge([
            'type' => $request->input('type', $request->input('format')),
            'start_date' => $request->input('start_date', $request->input('dateFrom')),
            'end_date' => $request->input('end_date', $request->input('dateTo')),
            'street_sections' => $request->input('street_sections', $request->input('locations', [])),
            'parking_attendant_ids' => $request->input('parking_attendant_ids', $request->input('attendants', [])),
            'statuses' => $request->input('statuses', $request->input('status') ? [$request->input('status')] : []),
        ]);
        
        // Validate request
        $validated = $request->validate([
            'type' => 'required|in:pdf,excel',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'street_sections' => 'nullable|array',
            'street_sections.*' => 'string|max:255',
            'parking_attendant_ids' => 'nullable|array',
            'parking_attendant_ids.*' => 'integer|exists:parking_attendants,id',
            'statuses' => 'nullable|array',
            'statuses.*' => 'in:success,pending,failed,expired',
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
                'street_sections' => array_values(array_filter($validated['street_sections'] ?? [])),
                'parking_attendant_ids' => array_values(array_filter($validated['parking_attendant_ids'] ?? [])),
                'statuses' => array_values(array_filter($validated['statuses'] ?? [])),
            ],
            'status' => 'pending',
            'created_at' => now(),
        ]);

        try {
            (new GenerateReportJob($report))->handle();
            $report->refresh();
        } catch (\Throwable $e) {
            $report->refresh();
        }

        return response()->json([
            'success' => $report->status === 'completed',
            'message' => $report->status === 'completed'
                ? 'Laporan berhasil dibuat'
                : 'Laporan gagal dibuat',
            'report_id' => $report->id,
            'status' => $report->status,
            'data' => $report,
        ], $report->status === 'completed' ? 201 : 500);
    }

    /**
     * Download generated report file
     *
     * @param int $reportId
     * @return mixed
     */
    public function download(int $reportId)
    {
        $report = Report::findOrFail($reportId);
        
        // Get user ID from session
        $userId = request()->session()->get('admin_user_id');

        // Check authorization
        if ((int) $report->user_id !== (int) $userId) {
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
        if ((int) $report->user_id !== (int) $userId) {
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
