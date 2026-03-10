<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Notification;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    protected AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Handle the Transaction "created" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function created(Transaction $transaction): void
    {
        try {
            // Log transaction creation for audit trail
            $this->auditLogger->log(
                'transaction_created',
                [
                    'entity_type' => 'transaction',
                    'entity_id' => $transaction->id,
                    'new_values' => [
                        'transaction_id' => $transaction->transaction_id,
                        'parking_attendant_id' => $transaction->parking_attendant_id,
                        'street_section' => $transaction->street_section,
                        'vehicle_type' => $transaction->vehicle_type,
                        'amount' => $transaction->amount,
                        'payment_status' => $transaction->payment_status,
                    ],
                ],
                null,
                request()
            );

            Log::info('Transaction created', [
                'transaction_id' => $transaction->transaction_id,
                'amount' => $transaction->amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TransactionObserver created event', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->transaction_id,
            ]);
        }
    }

    /**
     * Handle the Transaction "updated" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function updated(Transaction $transaction): void
    {
        try {
            $changes = $transaction->getChanges();

            // Log transaction update for audit trail
            $this->auditLogger->log(
                'transaction_updated',
                [
                    'entity_type' => 'transaction',
                    'entity_id' => $transaction->id,
                    'old_values' => $transaction->getOriginal(),
                    'new_values' => $changes,
                ],
                null,
                request()
            );

            // Trigger notifications on status change to success
            if (isset($changes['payment_status']) && $changes['payment_status'] === 'success') {
                $this->handleSuccessfulPayment($transaction);
            }

            Log::info('Transaction updated', [
                'transaction_id' => $transaction->transaction_id,
                'changes' => $changes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TransactionObserver updated event', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->transaction_id,
            ]);
        }
    }

    /**
     * Handle successful payment status change
     *
     * @param Transaction $transaction
     * @return void
     */
    protected function handleSuccessfulPayment(Transaction $transaction): void
    {
        try {
            // Create notification for attendant
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

            Log::info('Success notification created', [
                'attendant_id' => $transaction->parking_attendant_id,
                'transaction_id' => $transaction->transaction_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating success notification', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->transaction_id,
            ]);
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function deleted(Transaction $transaction): void
    {
        try {
            // Log transaction deletion for audit trail
            $this->auditLogger->log(
                'transaction_deleted',
                [
                    'entity_type' => 'transaction',
                    'entity_id' => $transaction->id,
                    'old_values' => [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount,
                    ],
                ],
                null,
                request()
            );

            Log::info('Transaction deleted', [
                'transaction_id' => $transaction->transaction_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TransactionObserver deleted event', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->transaction_id,
            ]);
        }
    }
}
