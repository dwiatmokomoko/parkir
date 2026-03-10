<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ParkingRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParkingRateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Helper method to set up admin session
     */
    protected function actingAsAdmin()
    {
        $this->session([
            'admin_user_id' => $this->admin->id,
            'admin_last_activity' => now()->timestamp,
        ]);
        return $this;
    }

    /**
     * Test rate configuration with negative values should fail
     */
    public function testRateConfigurationWithNegativeValuesFails()
    {
        $this->actingAsAdmin();

        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'motorcycle',
            'street_section' => 'Jl. Test',
            'rate' => -1000,
            'effective_from' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('rate');
    }

    /**
     * Test rate configuration with zero value should fail
     */
    public function testRateConfigurationWithZeroValueFails()
    {
        $this->actingAsAdmin();

        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'motorcycle',
            'street_section' => 'Jl. Test',
            'rate' => 0,
            'effective_from' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('rate');
    }

    /**
     * Test rate effective date in the past should fail
     */
    public function testRateEffectiveDateInPastFails()
    {
        $this->actingAsAdmin();

        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'motorcycle',
            'street_section' => 'Jl. Test',
            'rate' => 2000,
            'effective_from' => now()->subHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('effective_from');
    }

    /**
     * Test rate effective date in the future succeeds
     */
    public function testRateEffectiveDateInFutureSucceeds()
    {
        $this->actingAsAdmin();

        $futureDate = now()->addHour()->format('Y-m-d H:i:s');

        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'motorcycle',
            'street_section' => 'Jl. Test',
            'rate' => 2000,
            'effective_from' => $futureDate,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertEquals('2000.00', $response->json('data.rate'));
    }

    /**
     * Test location-specific rate override
     */
    public function testLocationSpecificRateOverride()
    {
        // Create default rate (no street section)
        ParkingRate::create([
            'vehicle_type' => 'motorcycle',
            'street_section' => null,
            'rate' => 2000,
            'effective_from' => now()->subHour(),
            'created_by' => $this->admin->id,
        ]);

        // Create location-specific rate
        ParkingRate::create([
            'vehicle_type' => 'motorcycle',
            'street_section' => 'Jl. Sudirman',
            'rate' => 3000,
            'effective_from' => now()->subHour(),
            'created_by' => $this->admin->id,
        ]);

        // Get rate for specific location - should return location-specific rate
        $rate = ParkingRate::getCurrentRate('motorcycle', 'Jl. Sudirman');
        $this->assertEquals(3000, $rate);

        // Get rate for different location - should return default rate
        $rate = ParkingRate::getCurrentRate('motorcycle', 'Jl. Thamrin');
        $this->assertEquals(2000, $rate);

        // Get rate without location - should return default rate
        $rate = ParkingRate::getCurrentRate('motorcycle');
        $this->assertEquals(2000, $rate);
    }

    /**
     * Test rate configuration with positive value succeeds
     */
    public function testRateConfigurationWithPositiveValueSucceeds()
    {
        $this->actingAsAdmin();

        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'car',
            'street_section' => 'Jl. Test',
            'rate' => 5000,
            'effective_from' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.vehicle_type', 'car');
        $this->assertEquals('5000.00', $response->json('data.rate'));
    }

    /**
     * Test rate configuration without street section (default rate)
     */
    public function testRateConfigurationWithoutStreetSection()
    {
        $this->actingAsAdmin();

        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'motorcycle',
            'rate' => 2000,
            'effective_from' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.street_section', null);
    }

    /**
     * Test get all rates endpoint
     */
    public function testGetAllRates()
    {
        $this->actingAsAdmin();

        // Create some rates
        ParkingRate::create([
            'vehicle_type' => 'motorcycle',
            'street_section' => 'Jl. Sudirman-test-' . uniqid(),
            'rate' => 2000,
            'effective_from' => now(),
            'created_by' => $this->admin->id,
        ]);

        ParkingRate::create([
            'vehicle_type' => 'car',
            'street_section' => 'Jl. Thamrin-test-' . uniqid(),
            'rate' => 5000,
            'effective_from' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson('/api/rates');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        // Should have at least 2 rates (may have more from other tests)
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    /**
     * Test get rates by location endpoint
     */
    public function testGetRatesByLocation()
    {
        $this->actingAsAdmin();

        $uniqueSection = 'Jl. Sudirman-' . uniqid();

        // Create default rate
        ParkingRate::create([
            'vehicle_type' => 'motorcycle',
            'street_section' => null,
            'rate' => 2000,
            'effective_from' => now(),
            'created_by' => $this->admin->id,
        ]);

        // Create location-specific rate
        ParkingRate::create([
            'vehicle_type' => 'motorcycle',
            'street_section' => $uniqueSection,
            'rate' => 3000,
            'effective_from' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson('/api/rates/location/' . urlencode($uniqueSection));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        // Should return both location-specific and default rates
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    /**
     * Test rate created_by is set to admin user_id
     */
    public function testRateCreatedByIsSetToAdminUserId()
    {
        $this->actingAsAdmin();

        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'motorcycle',
            'rate' => 2000,
            'effective_from' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.created_by', $this->admin->id);
    }

    /**
     * Test rate effective_from timestamp is set
     */
    public function testRateEffectiveFromTimestampIsSet()
    {
        $this->actingAsAdmin();

        $futureDate = now()->addHour();

        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'motorcycle',
            'rate' => 2000,
            'effective_from' => $futureDate->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('data.effective_from'));
    }

    /**
     * Test rate values must be positive (minimum 0.01)
     */
    public function testRateMinimumValue()
    {
        $this->actingAsAdmin();

        // Test with 0.01 - should succeed
        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'motorcycle',
            'rate' => 0.01,
            'effective_from' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(201);

        // Test with 0.001 - should fail
        $response = $this->putJson('/api/rates', [
            'vehicle_type' => 'motorcycle',
            'rate' => 0.001,
            'effective_from' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('rate');
    }
}
