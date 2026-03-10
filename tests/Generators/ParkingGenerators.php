<?php

namespace Tests\Generators;

use Eris\Generator;

/**
 * Custom generators for parking system data types
 */
class ParkingGenerators
{
    /**
     * Generate vehicle types (motorcycle or car)
     *
     * @return Generator
     */
    public static function vehicleTypes(): Generator
    {
        return Generator\elements('motorcycle', 'car');
    }

    /**
     * Generate payment statuses
     *
     * @return Generator
     */
    public static function paymentStatuses(): Generator
    {
        return Generator\elements('pending', 'success', 'failed', 'expired');
    }

    /**
     * Generate parking rates (positive decimals)
     *
     * @return Generator
     */
    public static function parkingRates(): Generator
    {
        return Generator\map(
            function ($value) {
                return round($value, 2);
            },
            Generator\choose(1000, 50000)
        );
    }

    /**
     * Generate transaction amounts (1000-50000)
     *
     * @return Generator
     */
    public static function transactionAmounts(): Generator
    {
        return Generator\choose(1000, 50000);
    }

    /**
     * Generate dates within the last 12 months
     *
     * @return Generator
     */
    public static function recentDates(): Generator
    {
        return Generator\map(
            function ($daysAgo) {
                return now()->subDays($daysAgo)->toDateTimeString();
            },
            Generator\choose(0, 365)
        );
    }

    /**
     * Generate registration numbers
     *
     * @return Generator
     */
    public static function registrationNumbers(): Generator
    {
        return Generator\map(
            function ($number) {
                return 'ATT' . str_pad($number, 4, '0', STR_PAD_LEFT);
            },
            Generator\choose(1000, 9999)
        );
    }

    /**
     * Generate street sections
     *
     * @return Generator
     */
    public static function streetSections(): Generator
    {
        return Generator\elements(
            'Jalan Sudirman',
            'Jalan Gatot Subroto',
            'Jalan Thamrin',
            'Jalan Rasuna Said',
            'Jalan Kuningan',
            'Jalan Senayan',
            'Jalan Benda',
            'Jalan Blora'
        );
    }

    /**
     * Generate location sides
     *
     * @return Generator
     */
    public static function locationSides(): Generator
    {
        return Generator\elements('Utara', 'Selatan', 'Timur', 'Barat');
    }

    /**
     * Generate bank names
     *
     * @return Generator
     */
    public static function bankNames(): Generator
    {
        return Generator\elements('BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga', 'Danamon');
    }

    /**
     * Generate bank account numbers
     *
     * @return Generator
     */
    public static function bankAccountNumbers(): Generator
    {
        return Generator\map(
            function ($number) {
                return str_pad($number, 12, '0', STR_PAD_LEFT);
            },
            Generator\choose(100000000000, 999999999999)
        );
    }

    /**
     * Generate action types for audit logs
     *
     * @return Generator
     */
    public static function auditActions(): Generator
    {
        return Generator\elements('create', 'update', 'delete', 'login', 'logout', 'activate', 'deactivate');
    }

    /**
     * Generate entity types for audit logs
     *
     * @return Generator
     */
    public static function auditEntityTypes(): Generator
    {
        return Generator\elements('transaction', 'attendant', 'rate', 'user', 'notification');
    }

    /**
     * Generate notification types
     *
     * @return Generator
     */
    public static function notificationTypes(): Generator
    {
        return Generator\elements('payment_success', 'payment_failed', 'qr_expired', 'system_alert');
    }

    /**
     * Generate positive decimal numbers
     *
     * @return Generator
     */
    public static function positiveDecimals(): Generator
    {
        return Generator\map(
            function ($value) {
                return round($value / 100, 2);
            },
            Generator\choose(1, 1000000)
        );
    }

    /**
     * Generate email addresses
     *
     * @return Generator
     */
    public static function emails(): Generator
    {
        return Generator\map(
            function ($name) {
                return $name . '@example.com';
            },
            Generator\strings(
                Generator\characters('a', 'z'),
                Generator\range(5, 15)
            )
        );
    }

    /**
     * Generate names
     *
     * @return Generator
     */
    public static function names(): Generator
    {
        return Generator\strings(
            Generator\characters('a', 'z', 'A', 'Z', ' '),
            Generator\range(5, 30)
        );
    }
}
