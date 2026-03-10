<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    protected TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Get paginated list of transactions with filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate query parameters
            $validated = $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
                'status' => 'nullable|in:pending,success,failed,expired',
                'street_section' => 'nullable|string',
                'attendant_id' => 'nullable|integer|exists:parking_attendants,id',
                'vehicle_type' => 'nullable|in:motorcycle,car',
                'start_date' => 'nullable|date_format:Y-m-d',
                'end_date' => 'nullable|date_format:Y-m-d',
            ]);

            $perPage = $validated['per_page'] ?? 15;

            // Build filters array
            $filters = array_filter([
                'status' => $validated['status'] ?? null,
                'street_section' => $validated['street_section'] ?? null,
                'attendant_id' => $validated['attendant_id'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]);

            // Get paginated transactions
            $transactions = $this->transactionRepository->getPaginated($perPage, $filters);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diambil',
                'data' => $transactions->items(),
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching transactions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi',
            ], 500);
        }
    }

    /**
     * Get transaction details by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $transaction = $this->transactionRepository->getById($id);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail transaksi berhasil diambil',
                'data' => $transaction,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transaction details', [
                'error' => $e->getMessage(),
                'transaction_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail transaksi',
            ], 500);
        }
    }

    /**
     * Get transactions by location
     *
     * @param string $streetSection
     * @return JsonResponse
     */
    public function getByLocation(string $streetSection): JsonResponse
    {
        try {
            $transactions = $this->transactionRepository->getByLocation($streetSection);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berdasarkan lokasi berhasil diambil',
                'data' => $transactions,
                'count' => count($transactions),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transactions by location', [
                'error' => $e->getMessage(),
                'street_section' => $streetSection,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil transaksi berdasarkan lokasi',
            ], 500);
        }
    }

    /**
     * Get transactions by attendant
     *
     * @param int $attendantId
     * @return JsonResponse
     */
    public function getByAttendant(int $attendantId): JsonResponse
    {
        try {
            $transactions = $this->transactionRepository->getByAttendant($attendantId);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berdasarkan juru parkir berhasil diambil',
                'data' => $transactions,
                'count' => count($transactions),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transactions by attendant', [
                'error' => $e->getMessage(),
                'attendant_id' => $attendantId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil transaksi berdasarkan juru parkir',
            ], 500);
        }
    }
}
