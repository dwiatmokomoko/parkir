<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Models\ParkingRate;
use App\Models\Notification;
use App\Services\MidtransService;
use App\Services\QRCodeService;
use App\Services\WebhookService;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected MidtransService $midtransService;
    protected QRCodeService $qrCodeService;
    protected WebhookService $webhookService;
    protected AuditLogger $auditLogger;

    public function __construct(
        MidtransService $midtransService,
        QRCodeService $qrCodeService,
        WebhookService $webhookService,
        AuditLogger $auditLogger
    ) {
        $this->midtransService = $midtransService;
        $this->qrCodeService = $qrCodeService;
        $this->webhookService = $webhookService;
        $this->auditLogger = $auditLogger;
    }

    /**
     * Generate QR code for payment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateQRCode(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'vehicle_type' => 'required|in:motorcycle,car',
                'license_plate' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9 .-]+$/'],
                'attendant_id' => 'sometimes|integer|exists:parking_attendants,id',
                'parking_attendant_id' => 'sometimes|integer|exists:parking_attendants,id',
            ]);

            $validated['license_plate'] = strtoupper(trim($validated['license_plate']));

            // Get parking attendant
            $attendant = $request->authenticated_attendant;

            if (!$attendant) {
                $attendantId = $validated['attendant_id'] ?? $validated['parking_attendant_id'] ?? null;

                if (!$attendantId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesi juru parkir tidak valid. Silakan login kembali.',
                    ], 401);
                }

                $attendant = ParkingAttendant::findOrFail($attendantId);
            }

            // Check if attendant is active
            if (!$attendant->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Juru parkir tidak aktif',
                ], 403);
            }

            // Get current parking rate
            $rate = ParkingRate::getCurrentRate(
                $validated['vehicle_type'],
                $attendant->street_section
            );

            if ($rate === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarif parkir tidak ditemukan',
                ], 404);
            }

            // Create transaction record with status pending
            $transaction = DB::transaction(function () use ($validated, $attendant, $rate) {
                $transaction = Transaction::create([
                    'transaction_id' => 'TRX-' . Str::uuid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => $attendant->street_section,
                    'vehicle_type' => $validated['vehicle_type'],
                    'license_plate' => $validated['license_plate'],
                    'amount' => $rate,
                    'payment_status' => 'pending',
                    'retry_count' => 0,
                ]);

                return $transaction;
            });

            // Call MidtransService to create transaction
            $midtransResponse = $this->midtransService->createTransaction($transaction);

            // Store Midtrans response
            $transaction->update([
                'midtrans_transaction_id' => $midtransResponse['midtrans_transaction_id']
                    ?? $midtransResponse['snap_token']
                    ?? null,
                'midtrans_response' => $midtransResponse,
            ]);

            // Generate QR code
            $qrCode = $this->qrCodeService->generate($transaction);

            // Log audit trail
            $this->auditLogger->log(
                'qr_code_generated',
                [
                    'entity_type' => 'transaction',
                    'entity_id' => $transaction->id,
                    'new_values' => [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount,
                        'vehicle_type' => $transaction->vehicle_type,
                        'license_plate' => $transaction->license_plate,
                    ],
                ],
                $attendant,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'QR code berhasil dibuat',
                'transaction_id' => $transaction->transaction_id,
                'qr_code' => $qrCode,
                'amount' => $transaction->amount,
                'vehicle_type' => $transaction->vehicle_type,
                'license_plate' => $transaction->license_plate,
                'payment_url' => $midtransResponse['redirect_url'] ?? null,
                'qr_code_url' => $midtransResponse['qr_code_url'] ?? null,
                'qris_acquirer' => $midtransResponse['acquirer'] ?? null,
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'qr_code' => $qrCode,
                    'amount' => $transaction->amount,
                    'vehicle_type' => $transaction->vehicle_type,
                    'license_plate' => $transaction->license_plate,
                    'payment_url' => $midtransResponse['redirect_url'] ?? null,
                    'qr_code_url' => $midtransResponse['qr_code_url'] ?? null,
                    'qris_acquirer' => $midtransResponse['acquirer'] ?? null,
                    'street_section' => $transaction->street_section,
                    'attendant_name' => $attendant->name,
                    'attendant_registration' => $attendant->registration_number,
                    'expires_at' => $transaction->qr_code_expires_at,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error generating QR code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QR code',
            ], 500);
        }
    }

    /**
     * Handle Midtrans webhook callback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleCallback(Request $request): JsonResponse
    {
        try {
            $notification = $request->all();

            // Verify webhook signature using MidtransService
            if (!$this->midtransService->verifyWebhookSignature($notification)) {
                Log::warning('Invalid webhook signature', ['notification' => $notification]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook signature',
                ], 401);
            }

            // Process webhook
            $result = $this->webhookService->processWebhook($notification);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            $transaction = $result['transaction'];
            $newStatus = $result['new_status'];

            // Idempotency check - prevent duplicate processing
            if ($transaction->payment_status === $newStatus) {
                Log::info('Duplicate webhook notification - already processed', [
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $newStatus,
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook already processed',
                ]);
            }

            // Validate Midtrans amount matches the stored transaction amount.
            $expectedAmount = (float) ($notification['gross_amount'] ?? 0);
            
            if (!$this->midtransService->validateTransactionAmount($transaction, $expectedAmount)) {
                Log::error('Transaction amount mismatch', [
                    'transaction_id' => $transaction->transaction_id,
                    'midtrans_gross_amount' => $expectedAmount,
                    'actual' => $transaction->amount,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction amount mismatch',
                ], 400);
            }

            // Update transaction status
            DB::transaction(function () use ($transaction, $newStatus, $notification) {
                $oldStatus = $transaction->payment_status;

                $updateData = [
                    'payment_status' => $newStatus,
                ];

                if ($newStatus === 'success') {
                    $updateData['paid_at'] = Carbon::now();
                    $updateData['payment_method'] = $notification['payment_type'] ?? null;
                } elseif ($newStatus === 'failed') {
                    $updateData['failure_reason'] = $notification['transaction_status'] ?? 'Payment failed';
                }

                $transaction->update($updateData);

                // Log audit trail
                $this->auditLogger->log(
                    'transaction_status_updated',
                    [
                        'entity_type' => 'transaction',
                        'entity_id' => $transaction->id,
                        'old_values' => ['payment_status' => $oldStatus],
                        'new_values' => ['payment_status' => $newStatus],
                    ],
                    null,
                    request()
                );

                // Trigger notification to attendant if successful
                if ($newStatus === 'success') {
                    $this->notifyAttendant($transaction);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook',
            ], 500);
        }
    }

    /**
     * Retry failed payment
     *
     * @param string $transactionId
     * @param Request $request
     * @return JsonResponse
     */
    public function retryPayment(string $transactionId, Request $request): JsonResponse
    {
        try {
            // Find transaction
            $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

            // Validate transaction exists and status is failed
            if ($transaction->payment_status !== 'failed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak dalam status gagal',
                ], 400);
            }

            // Check retry_count < 3
            if ($transaction->retry_count >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas percobaan pembayaran ulang telah tercapai (maksimal 3 kali)',
                ], 400);
            }

            // Get attendant
            $attendant = $transaction->parkingAttendant;

            if (!$attendant->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Juru parkir tidak aktif',
                ], 403);
            }

            // Generate new QR code with same parking rate
            DB::transaction(function () use ($transaction) {
                // Increment retry_count
                $transaction->increment('retry_count');

                // Reset payment status to pending
                $transaction->update([
                    'payment_status' => 'pending',
                    'failure_reason' => null,
                ]);

                // Create new Midtrans transaction
                $midtransResponse = $this->midtransService->createTransaction($transaction);

                // Store new Midtrans response
                $transaction->update([
                    'midtrans_transaction_id' => $midtransResponse['snap_token'] ?? null,
                    'midtrans_response' => $midtransResponse,
                ]);

                // Generate new QR code
                $this->qrCodeService->generate($transaction);

                // Log audit trail
                $this->auditLogger->log(
                    'payment_retry',
                    [
                        'entity_type' => 'transaction',
                        'entity_id' => $transaction->id,
                        'new_values' => [
                            'retry_count' => $transaction->retry_count,
                            'amount' => $transaction->amount,
                        ],
                    ],
                    null,
                    request()
                );
            });

            // Generate QR code for response
            $qrCode = $this->qrCodeService->generate($transaction);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran ulang berhasil dibuat',
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'qr_code' => $qrCode,
                    'amount' => $transaction->amount,
                    'vehicle_type' => $transaction->vehicle_type,
                    'retry_count' => $transaction->retry_count,
                    'expires_at' => $transaction->qr_code_expires_at,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrying payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran ulang',
            ], 500);
        }
    }

    /**
     * Get payment status for the attendant page.
     *
     * @param string $transactionId
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatus(string $transactionId, Request $request): JsonResponse
    {
        $attendant = $request->authenticated_attendant;

        $transaction = Transaction::where('transaction_id', $transactionId)
            ->where('parking_attendant_id', $attendant->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        if ($transaction->payment_status === 'pending' && $transaction->isExpired()) {
            $transaction->update(['payment_status' => 'expired']);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatAttendantTransaction($transaction->fresh()),
        ]);
    }

    /**
     * List transactions owned by the authenticated attendant.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendantTransactions(Request $request): JsonResponse
    {
        $attendant = $request->authenticated_attendant;
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        Transaction::where('parking_attendant_id', $attendant->id)
            ->where('payment_status', 'pending')
            ->whereNotNull('qr_code_expires_at')
            ->where('qr_code_expires_at', '<', Carbon::now())
            ->update(['payment_status' => 'expired']);

        $query = Transaction::query()
            ->where('parking_attendant_id', $attendant->id)
            ->when($request->filled('status') && $request->query('status') !== 'all', function ($query) use ($request) {
                $query->where('payment_status', $request->query('status'));
            })
            ->when($request->filled('vehicle_type') && $request->query('vehicle_type') !== 'all', function ($query) use ($request) {
                $query->where('vehicle_type', $request->query('vehicle_type'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->where('created_at', '>=', Carbon::parse($request->query('date_from'))->startOfDay());
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->where('created_at', '<=', Carbon::parse($request->query('date_to'))->endOfDay());
            })
            ->latest();

        $transactions = $query->paginate($perPage);

        $summaryBase = Transaction::where('parking_attendant_id', $attendant->id);
        $summary = [
            'total' => (clone $summaryBase)->count(),
            'success' => (clone $summaryBase)->where('payment_status', 'success')->count(),
            'pending' => (clone $summaryBase)->where('payment_status', 'pending')->count(),
            'failed' => (clone $summaryBase)->where('payment_status', 'failed')->count(),
            'expired' => (clone $summaryBase)->where('payment_status', 'expired')->count(),
            'revenue' => (float) (clone $summaryBase)->where('payment_status', 'success')->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => collect($transactions->items())
                ->map(fn (Transaction $transaction) => $this->formatAttendantTransaction($transaction))
                ->values(),
            'summary' => $summary,
            'pagination' => [
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
        ]);
    }

    /**
     * Format a transaction for attendant-facing responses.
     *
     * @param Transaction $transaction
     * @return array<string, mixed>
     */
    protected function formatAttendantTransaction(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'transaction_id' => $transaction->transaction_id,
            'vehicle_type' => $transaction->vehicle_type,
            'vehicle_label' => $transaction->vehicle_type === 'car' ? 'Mobil' : 'Motor',
            'license_plate' => $transaction->license_plate,
            'amount' => (float) $transaction->amount,
            'payment_status' => $transaction->payment_status,
            'payment_method' => $transaction->payment_method,
            'street_section' => $transaction->street_section,
            'qr_code_expires_at' => optional($transaction->qr_code_expires_at)->toISOString(),
            'paid_at' => optional($transaction->paid_at)->toISOString(),
            'created_at' => optional($transaction->created_at)->toISOString(),
            'updated_at' => optional($transaction->updated_at)->toISOString(),
            'failure_reason' => $transaction->failure_reason,
        ];
    }

    /**
     * Notify attendant of successful payment
     *
     * @param Transaction $transaction
     * @return void
     */
    protected function notifyAttendant(Transaction $transaction): void
    {
        try {
            Notification::create([
                'parking_attendant_id' => $transaction->parking_attendant_id,
                'transaction_id' => $transaction->id,
                'type' => 'payment_success',
                'title' => 'Pembayaran Berhasil',
                'message' => "Pembayaran parkir {$transaction->vehicle_type} {$transaction->license_plate} sebesar Rp " . number_format($transaction->amount, 0, ',', '.') . " berhasil diterima",
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'vehicle_type' => $transaction->vehicle_type,
                    'license_plate' => $transaction->license_plate,
                    'paid_at' => $transaction->paid_at,
                ],
                'created_at' => Carbon::now(),
            ]);

            Log::info('Notification sent to attendant', [
                'attendant_id' => $transaction->parking_attendant_id,
                'transaction_id' => $transaction->transaction_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending notification', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->transaction_id,
            ]);
        }
    }
}
