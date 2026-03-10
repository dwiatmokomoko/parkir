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
        $query = self::where('vehicle_type', $vehicleType)
            ->where('effective_from', '<=', now());

        // If street section is provided, prioritize location-specific rates
        if ($streetSection) {
            $query->where(function ($q) use ($streetSection) {
                $q->where('street_section', $streetSection)
                  ->orWhereNull('street_section');
            });
            
            // Order by: location-specific first (street_section DESC), then by effective_from DESC
            return $query
                ->orderByRaw("CASE WHEN street_section IS NOT NULL THEN 0 ELSE 1 END")
                ->orderByDesc('effective_from')
                ->value('rate');
        } else {
            // If no street section, only get default rates (where street_section is null)
            $query->whereNull('street_section');
            
            return $query
                ->orderByDesc('effective_from')
                ->value('rate');
        }
    }
}
