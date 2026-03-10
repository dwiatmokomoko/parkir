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
        if (in_array($key, ['bank_account_number', 'pin']) && $value !== null) {
            $value = Crypt::encryptString($value);
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Decrypt sensitive attributes when retrieving.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        if (in_array($key, ['bank_account_number', 'pin']) && $value !== null) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                // If decryption fails, return the original value
                // This handles cases where data wasn't encrypted
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
