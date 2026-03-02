<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'parking_attendant_id',
        'street_section',
        'vehicle_type',
        'amount',
        'payment_method',
        'payment_status',
        'qr_code_data',
        'qr_code_generated_at',
        'qr_code_expires_at',
        'paid_at',
        'failure_reason',
        'retry_count',
        'midtrans_transaction_id',
        'midtrans_response',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'qr_code_generated_at' => 'datetime',
        'qr_code_expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'midtrans_response' => 'array',
    ];

    /**
     * Get the parking attendant that owns the transaction.
     */
    public function parkingAttendant()
    {
        return $this->belongsTo(ParkingAttendant::class);
    }

    /**
     * Get the notifications for the transaction.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if the QR code has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->qr_code_expires_at && 
               $this->qr_code_expires_at->isPast();
    }
}
