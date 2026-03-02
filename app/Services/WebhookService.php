<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    protected AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Verify webhook signature from Midtrans
     *
     * @param array $notification
     * @return bool
     */
    public function verifySignature(array $notification): bool
    {
        $serverKey = config('midtrans.server_key');
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';

        // Generate signature using SHA512
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return isset($notification['signature_key']) && 
               $signatureKey === $notification['signature_key'];
    }

    /**
     * Validate order_id exists in database
     *
     * @param string $orderId
     * @return bool
     */
    public function validateOrderExists(string $orderId): bool
    {
        return Transaction::where('transaction_id', $orderId)->exists();
    }

    /**
     * Check if transaction status transition is valid
     *
     * @param string $currentStatus
     * @param string $newStatus
     * @return bool
     */
    public function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $validTransitions = [
            'pending' => ['success', 'failed', 'expired'],
            'success' => [], // Success is final
            'failed' => ['pending'], // Can retry
            'expired' => ['pending'], // Can regenerate
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }

    /**
     * Log webhook request for audit
     *
     * @param array $notification
     * @param bool $isValid
     * @return void
     */
    public function logWebhookRequest(array $notification, bool $isValid): void
    {
        $logData = [
            'order_id' => $notification['order_id'] ?? null,
            'transaction_status' => $notification['transaction_status'] ?? null,
            'payment_type' => $notification['payment_type'] ?? null,
            'signature_valid' => $isValid,
            'notification_data' => $notification,
        ];

        Log::info('Midtrans webhook received', $logData);

        $this->auditLogger->log(
            'midtrans_webhook_received',
            $logData,
            null
        );
    }

    /**
     * Process webhook notification
     *
     * @param array $notification
     * @return array Result with success status and message
     */
    public function processWebhook(array $notification): array
    {
        // Verify signature
        if (!$this->verifySignature($notification)) {
            $this->logWebhookRequest($notification, false);
            return [
                'success' => false,
                'message' => 'Invalid signature',
            ];
        }

        // Validate order exists
        $orderId = $notification['order_id'] ?? '';
        if (!$this->validateOrderExists($orderId)) {
            $this->logWebhookRequest($notification, false);
            return [
                'success' => false,
                'message' => 'Order not found',
            ];
        }

        // Get transaction
        $transaction = Transaction::where('transaction_id', $orderId)->first();

        // Map Midtrans status to our status
        $newStatus = $this->mapMidtransStatus(
            $notification['transaction_status'] ?? '',
            $notification['fraud_status'] ?? null
        );

        // Check status transition validity
        if (!$this->isValidStatusTransition($transaction->payment_status, $newStatus)) {
            $this->logWebhookRequest($notification, false);
            return [
                'success' => false,
                'message' => 'Invalid status transition',
            ];
        }

        // Log valid webhook
        $this->logWebhookRequest($notification, true);

        return [
            'success' => true,
            'message' => 'Webhook processed successfully',
            'transaction' => $transaction,
            'new_status' => $newStatus,
        ];
    }

    /**
     * Map Midtrans transaction status to our payment status
     *
     * @param string $transactionStatus
     * @param string|null $fraudStatus
     * @return string
     */
    protected function mapMidtransStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        // Handle fraud status first
        if ($fraudStatus === 'deny') {
            return 'failed';
        }

        // Map transaction status
        return match ($transactionStatus) {
            'capture', 'settlement' => 'success',
            'pending' => 'pending',
            'deny', 'cancel', 'expire' => 'failed',
            default => 'pending',
        };
    }
}
