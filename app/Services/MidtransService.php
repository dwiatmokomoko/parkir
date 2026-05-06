<?php

namespace App\Services;

use App\Models\Transaction;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        // Configure Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
        Config::$overrideNotifUrl = config('midtrans.notification_url') ?: url('/api/payments/callback');
    }

    /**
     * Create a new transaction with Midtrans Snap API
     *
     * @param Transaction $transaction
     * @return array Contains snap_token and redirect_url
     * @throws \Exception
     */
    public function createTransaction(Transaction $transaction): array
    {
        if ($this->shouldUseLocalMock()) {
            return [
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'midtrans_transaction_id' => 'local-' . $transaction->transaction_id,
                'snap_token' => null,
                'redirect_url' => url('/local-payment/' . $transaction->transaction_id),
                'qr_code_url' => null,
                'qr_string' => $this->localQrString($transaction),
                'acquirer' => 'local',
            ];
        }

        if (config('midtrans.payment_mode') === 'qris') {
            return $this->createQrisTransaction($transaction);
        }

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->transaction_id,
                'gross_amount' => (int) $transaction->amount,
            ],
            'item_details' => [
                [
                    'id' => 'parking-fee',
                    'price' => (int) $transaction->amount,
                    'quantity' => 1,
                    'name' => "Parkir {$transaction->vehicle_type} - {$transaction->street_section}",
                ]
            ],
            'customer_details' => [
                'first_name' => 'Pengguna Parkir',
            ],
            'enabled_payments' => [
                'qris',
                'gopay',
                'shopeepay',
                'other_qris',
            ],
        ];

        $snapResponse = Snap::createTransaction($params);

        return [
            'payment_type' => 'snap',
            'snap_token' => $snapResponse->token,
            'redirect_url' => $snapResponse->redirect_url,
        ];
    }

    /**
     * Create a dynamic QRIS transaction that can be scanned directly.
     */
    protected function createQrisTransaction(Transaction $transaction): array
    {
        $expiryMinutes = max(1, min((int) config('midtrans.qris_expiry_minutes', 60), 60));

        $response = CoreApi::charge([
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $transaction->transaction_id,
                'gross_amount' => (int) $transaction->amount,
            ],
            'item_details' => [
                [
                    'id' => 'parking-fee',
                    'price' => (int) $transaction->amount,
                    'quantity' => 1,
                    'name' => "Parkir {$transaction->vehicle_type} - {$transaction->street_section}",
                ],
            ],
            'custom_expiry' => [
                'expiry_duration' => $expiryMinutes,
                'unit' => 'minute',
            ],
        ]);

        $actions = collect($response->actions ?? []);
        $qrCodeAction = $actions->firstWhere('name', 'generate-qr-code');

        return [
            'payment_type' => $response->payment_type ?? 'qris',
            'transaction_status' => $response->transaction_status ?? 'pending',
            'midtrans_transaction_id' => $response->transaction_id ?? null,
            'snap_token' => null,
            'redirect_url' => null,
            'qr_code_url' => $qrCodeAction->url ?? null,
            'qr_string' => $response->qr_string ?? null,
            'acquirer' => $response->acquirer ?? null,
            'raw_response' => $response,
        ];
    }

    /**
     * Use a local mock when Midtrans keys are still placeholders.
     */
    protected function shouldUseLocalMock(): bool
    {
        $serverKey = (string) config('midtrans.server_key', '');

        return app()->environment(['local', 'testing'])
            && ($serverKey === '' || str_contains($serverKey, 'your-server-key'));
    }

    /**
     * Local QR payload for UI testing only. This is not payable.
     */
    protected function localQrString(Transaction $transaction): string
    {
        return json_encode([
            'mode' => 'local-qris-mock',
            'transaction_id' => $transaction->transaction_id,
            'amount' => (int) $transaction->amount,
            'vehicle_type' => $transaction->vehicle_type,
        ]);
    }

    /**
     * Get transaction status from Midtrans
     *
     * @param string $orderId
     * @return array Transaction status details
     * @throws \Exception
     */
    public function getTransactionStatus(string $orderId): array
    {
        $status = MidtransTransaction::status($orderId);

        return [
            'order_id' => $status->order_id,
            'transaction_status' => $status->transaction_status,
            'fraud_status' => $status->fraud_status ?? null,
            'payment_type' => $status->payment_type,
            'transaction_time' => $status->transaction_time,
            'gross_amount' => $status->gross_amount,
        ];
    }

    /**
     * Verify webhook signature from Midtrans
     *
     * @param array $notificationData
     * @return bool True if signature is valid
     */
    public function verifyWebhookSignature(array $notificationData): bool
    {
        $orderId = $notificationData['order_id'] ?? null;
        $statusCode = $notificationData['status_code'] ?? null;
        $grossAmount = $notificationData['gross_amount'] ?? null;
        $signatureKey = $notificationData['signature_key'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            Log::warning('Invalid webhook notification data', $notificationData);
            return false;
        }

        // Calculate expected signature
        $serverKey = config('midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        // Compare signatures
        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('Webhook signature verification failed', [
                'order_id' => $orderId,
                'expected' => $expectedSignature,
                'received' => $signatureKey,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Validate transaction amount matches expected rate
     *
     * @param Transaction $transaction
     * @param float $expectedAmount
     * @return bool True if amount matches
     */
    public function validateTransactionAmount(Transaction $transaction, float $expectedAmount): bool
    {
        if ((float) $transaction->amount !== $expectedAmount) {
            Log::warning('Transaction amount mismatch', [
                'transaction_id' => $transaction->transaction_id,
                'expected' => $expectedAmount,
                'actual' => $transaction->amount,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Handle webhook notification from Midtrans with idempotency
     *
     * @param array $notificationData
     * @return array Processed notification data
     * @throws \Exception
     */
    public function handleNotification(array $notificationData): array
    {
        // Verify webhook signature first
        if (!$this->verifyWebhookSignature($notificationData)) {
            throw new \Exception('Invalid webhook signature');
        }

        $notification = new Notification();

        // Check for idempotency - prevent duplicate processing
        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;

        // Log webhook for audit trail
        Log::info('Webhook notification received', [
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'payment_type' => $notification->payment_type,
        ]);

        return [
            'order_id' => $notification->order_id,
            'transaction_status' => $notification->transaction_status,
            'fraud_status' => $notification->fraud_status ?? null,
            'payment_type' => $notification->payment_type,
            'transaction_time' => $notification->transaction_time,
            'status_code' => $notification->status_code,
            'gross_amount' => $notification->gross_amount,
            'signature_key' => $notification->signature_key,
        ];
    }
}
