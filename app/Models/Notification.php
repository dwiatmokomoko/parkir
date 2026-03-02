<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parking_attendant_id',
        'transaction_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the parking attendant that owns the notification.
     */
    public function parkingAttendant()
    {
        return $this->belongsTo(ParkingAttendant::class);
    }

    /**
     * Get the transaction associated with the notification.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
