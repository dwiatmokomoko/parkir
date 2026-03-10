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
        Schema::create('parking_attendants', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 50)->unique();
            $table->string('name');
            $table->string('street_section');
            $table->string('location_side', 50)->nullable();
            $table->text('bank_account_number')->nullable(); // Changed to text for encrypted data
            $table->string('bank_name', 100)->nullable();
            $table->text('pin'); // Changed to text for encrypted data
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('registration_number');
            $table->index('street_section');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_attendants');
    }
};
