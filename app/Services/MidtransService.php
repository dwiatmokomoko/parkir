<?php

namespace App\Services;

use App\Models\Transaction;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;
use Midtrans\Notification;

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
            'snap_token' => $snapResponse->token,
            'redirect_url' => $snapResponse->redirect_url,
        ];
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
     * Handle webhook notification from Midtrans
     *
     * @param array $notificationData
     * @return array Processed notification data
     * @throws \Exception
     */
    public function handleNotification(array $notificationData): array
    {
        $notification = new Notification();

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
