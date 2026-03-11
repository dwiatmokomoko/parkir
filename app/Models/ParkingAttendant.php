<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ParkingAttendant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_number',
        'name',
        'street_section',
        'location_side',
        'bank_account_number',
        'bank_name',
        'pin',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pin',
        'bank_account_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Encrypt sensitive attributes before saving.
     */
    public function setAttribute($key, $value)
    {
        // Only encrypt bank_account_number, not PIN (PIN should be hashed)
        if ($key === 'bank_account_number' && $value !== null) {
            $value = Crypt::encryptString($value);
        }
        
        // Hash PIN if it's being set and not already hashed
        if ($key === 'pin' && $value !== null && !str_starts_with($value, '$2y$')) {
            $value = \Illuminate\Support\Facades\Hash::make($value);
        }
        
        return parent::setAttribute($key, $value);
    }

    /**
     * Decrypt sensitive attributes when retrieving.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        // Only decrypt bank_account_number, not PIN
        if ($key === 'bank_account_number' && $value !== null) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                // If decryption fails, return the original value
            }
        }
        
        return $value;
    }

    /**
     * Get the transactions for the parking attendant.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the notifications for the parking attendant.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
