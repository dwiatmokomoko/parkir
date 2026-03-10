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
                'attendant_id' => 'required|integer|exists:parking_attendants,id',
            ]);

            // Get parking attendant
            $attendant = ParkingAttendant::findOrFail($validated['attendant_id']);

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
                'midtrans_transaction_id' => $midtransResponse['snap_token'] ?? null,
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
                    ],
                ],
                $attendant,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'QR code berhasil dibuat',
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'qr_code' => $qrCode,
                    'amount' => $transaction->amount,
                    'vehicle_type' => $transaction->vehicle_type,
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
                'message' => "Pembayaran parkir {$transaction->vehicle_type} sebesar Rp " . number_format($transaction->amount, 0, ',', '.') . " berhasil diterima",
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'vehicle_type' => $transaction->vehicle_type,
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
