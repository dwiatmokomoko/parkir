<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingRate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_type',
        'street_section',
        'rate',
        'effective_from',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'decimal:2',
        'effective_from' => 'datetime',
    ];

    /**
     * Get the user who created the parking rate.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the current rate for a vehicle type and optional street section.
     *
     * @param string $vehicleType
     * @param string|null $streetSection
     * @return float|null
     */
    public static function getCurrentRate(string $vehicleType, ?string $streetSection = null): ?float
    {
        return self::where('vehicle_type', $vehicleType)
            ->where(function ($query) use ($streetSection) {
                $query->where('street_section', $streetSection)
                      ->orWhereNull('street_section');
            })
            ->where('effective_from', '<=', now())
            ->orderBy('street_section', 'DESC')
            ->orderBy('effective_from', 'DESC')
            ->value('rate');
    }
}
