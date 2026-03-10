<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add encrypted columns for sensitive data
        Schema::table('parking_attendants', function (Blueprint $table) {
            // Add new encrypted columns if they don't exist
            if (!Schema::hasColumn('parking_attendants', 'bank_account_number_encrypted')) {
                $table->text('bank_account_number_encrypted')->nullable();
            }
            if (!Schema::hasColumn('parking_attendants', 'pin_encrypted')) {
                $table->text('pin_encrypted')->nullable();
            }
        });

        // Update database connection to use SSL
        // This is configured in config/database.php with sslmode = 'require'
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_attendants', function (Blueprint $table) {
            // Revert to regular columns
            $table->string('bank_account_number')->nullable()->change();
            $table->string('pin')->change();
        });
    }
};
