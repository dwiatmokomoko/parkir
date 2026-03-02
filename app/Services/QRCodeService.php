<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;

class QRCodeService
{
    /**
     * Generate QR code data with HMAC signature
     *
     * @param Transaction $transaction
     * @return string Base64 encoded QR code image
     */
    public function generate(Transaction $transaction): string
    {
        // Set expiration time (15 minutes from now)
        $expiresAt = Carbon::now()->addMinutes(15);
        
        // Update transaction with QR code timestamps
        $transaction->update([
            'qr_code_generated_at' => Carbon::now(),
            'qr_code_expires_at' => $expiresAt,
        ]);

        // Prepare QR data
        $qrData = [
            'transaction_id' => $transaction->transaction_id,
            'parking_rate' => (float) $transaction->amount,
            'attendant_id' => $transaction->parking_attendant_id,
            'vehicle_type' => $transaction->vehicle_type,
            'street_section' => $transaction->street_section,
            'expires_at' => $expiresAt->toIso8601String(),
        ];

        // Generate HMAC signature for security
        $signature = $this->generateHmacSignature($qrData);
        $qrData['signature'] = $signature;

        // Encode QR data as JSON
        $qrDataJson = json_encode($qrData);

        // Store QR data in transaction
        $transaction->update([
            'qr_code_data' => $qrDataJson,
        ]);

        // Generate QR code image (using a simple base64 encoding for now)
        // In production, you would use a QR code library like SimpleSoftwareIO/simple-qrcode
        return base64_encode($qrDataJson);
    }

    /**
     * Validate QR code
     *
     * @param string $qrCode Base64 encoded QR code data
     * @return bool
     */
    public function validate(string $qrCode): bool
    {
        try {
            // Decode QR code
            $qrDataJson = base64_decode($qrCode);
            $qrData = json_decode($qrDataJson, true);

            if (!$qrData) {
                return false;
            }

            // Check required fields
            $requiredFields = ['transaction_id', 'parking_rate', 'attendant_id', 'expires_at', 'signature'];
            foreach ($requiredFields as $field) {
                if (!isset($qrData[$field])) {
                    return false;
                }
            }

            // Verify HMAC signature
            $signature = $qrData['signature'];
            unset($qrData['signature']);
            
            if (!$this->verifyHmacSignature($qrData, $signature)) {
                return false;
            }

            // Check expiration
            $expiresAt = Carbon::parse($qrData['expires_at']);
            if ($expiresAt->isPast()) {
                return false;
            }

            // Check if transaction exists and is in valid state
            $transaction = Transaction::where('transaction_id', $qrData['transaction_id'])->first();
            if (!$transaction || $transaction->payment_status === 'success') {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate HMAC signature for QR data
     *
     * @param array $data
     * @return string
     */
    protected function generateHmacSignature(array $data): string
    {
        $secret = config('app.key');
        $dataString = json_encode($data);
        return hash_hmac('sha256', $dataString, $secret);
    }

    /**
     * Verify HMAC signature
     *
     * @param array $data
     * @param string $signature
     * @return bool
     */
    protected function verifyHmacSignature(array $data, string $signature): bool
    {
        $expectedSignature = $this->generateHmacSignature($data);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse QR code data
     *
     * @param string $qrCode Base64 encoded QR code data
     * @return array|null
     */
    public function parseQRCode(string $qrCode): ?array
    {
        try {
            $qrDataJson = base64_decode($qrCode);
            $qrData = json_decode($qrDataJson, true);
            return $qrData ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
