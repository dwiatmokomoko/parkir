<?php

namespace Tests\Feature;

use App\Models\ParkingAttendant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * @group Feature: parking-payment-monitoring-system
 */
class AttendantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Login as admin
        $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
    }

    /**
     * Test attendant creation with minimum fields
     */
    public function test_attendant_creation_with_minimum_fields()
    {
        $attendantData = [
            'registration_number' => 'ATT-MIN-001',
            'name' => 'Minimum Attendant',
            'street_section' => 'Jalan Minimum',
            'bank_account_number' => '1234567890',
            'pin' => '123456',
        ];

        $response = $this->postJson('/api/attendants', $attendantData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Juru parkir berhasil dibuat.',
        ]);

        // Verify attendant was created with correct data
        $this->assertDatabaseHas('parking_attendants', [
            'registration_number' => 'ATT-MIN-001',
            'name' => 'Minimum Attendant',
            'street_section' => 'Jalan Minimum',
            'bank_account_number' => '1234567890',
            'is_active' => true,
        ]);

        // Verify PIN is hashed
        $attendant = ParkingAttendant::where('registration_number', 'ATT-MIN-001')->first();
        $this->assertTrue(Hash::check('123456', $attendant->pin));
    }

    /**
     * Test attendant creation with all optional fields
     */
    public function test_attendant_creation_with_all_optional_fields()
    {
        $attendantData = [
            'registration_number' => 'ATT-FULL-001',
            'name' => 'Full Attendant',
            'street_section' => 'Jalan Full',
            'location_side' => 'Utara',
            'bank_account_number' => '9876543210',
            'bank_name' => 'BCA',
            'pin' => '654321',
        ];

        $response = $this->postJson('/api/attendants', $attendantData);

        $response->assertStatus(201);

        // Verify all fields were stored
        $this->assertDatabaseHas('parking_attendants', [
            'registration_number' => 'ATT-FULL-001',
            'name' => 'Full Attendant',
            'street_section' => 'Jalan Full',
            'location_side' => 'Utara',
            'bank_account_number' => '9876543210',
            'bank_name' => 'BCA',
        ]);
    }

    /**
     * Test attendant update with partial data
     */
    public function test_attendant_update_with_partial_data()
    {
        // Create attendant
        $attendant = ParkingAttendant::create([
            'registration_number' => 'ATT-UPDATE-001',
            'name' => 'Original Name',
            'street_section' => 'Original Section',
            'bank_account_number' => '1111111111',
            'pin' => Hash::make('111111'),
            'is_active' => true,
        ]);

        // Update only name
        $updateData = [
            'name' => 'Updated Name',
        ];

        $response = $this->putJson("/api/attendants/{$attendant->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Juru parkir berhasil diperbarui.',
        ]);

        // Verify only name was updated
        $attendant->refresh();
        $this->assertEquals('Updated Name', $attendant->name);
        $this->assertEquals('Original Section', $attendant->street_section);
        $this->assertEquals('1111111111', $attendant->bank_account_number);
    }

    /**
     * Test duplicate registration number handling
     */
    public function test_duplicate_registration_number_handling()
    {
        // Create first attendant
        ParkingAttendant::create([
            'registration_number' => 'ATT-DUP-001',
            'name' => 'First Attendant',
            'street_section' => 'Jalan A',
            'bank_account_number' => '1111111111',
            'pin' => Hash::make('111111'),
            'is_active' => true,
        ]);

        // Attempt to create second attendant with same registration number
        $attendantData = [
            'registration_number' => 'ATT-DUP-001',
            'name' => 'Second Attendant',
            'street_section' => 'Jalan B',
            'bank_account_number' => '2222222222',
            'pin' => '222222',
        ];

        $response = $this->postJson('/api/attendants', $attendantData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('registration_number');

        // Verify only one attendant exists
        $count = ParkingAttendant::where('registration_number', 'ATT-DUP-001')->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test attendant activation
     */
    public function test_attendant_activation()
    {
        // Create inactive attendant
        $attendant = ParkingAttendant::create([
            'registration_number' => 'ATT-ACTIVATE-001',
            'name' => 'Inactive Attendant',
            'street_section' => 'Jalan Test',
            'bank_account_number' => '1234567890',
            'pin' => Hash::make('123456'),
            'is_active' => false,
        ]);

        // Activate attendant
        $response = $this->postJson("/api/attendants/{$attendant->id}/activate");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Juru parkir berhasil diaktifkan.',
        ]);

        // Verify attendant is now active
        $attendant->refresh();
        $this->assertTrue($attendant->is_active);
    }

    /**
     * Test attendant deactivation
     */
    public function test_attendant_deactivation()
    {
        // Create active attendant
        $attendant = ParkingAttendant::create([
            'registration_number' => 'ATT-DEACTIVATE-001',
            'name' => 'Active Attendant',
            'street_section' => 'Jalan Test',
            'bank_account_number' => '1234567890',
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);

        // Deactivate attendant
        $response = $this->postJson("/api/attendants/{$attendant->id}/deactivate");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Juru parkir berhasil dinonaktifkan.',
        ]);

        // Verify attendant is now inactive
        $attendant->refresh();
        $this->assertFalse($attendant->is_active);
    }

    /**
     * Test get attendant details
     */
    public function test_get_attendant_details()
    {
        $attendant = ParkingAttendant::create([
            'registration_number' => 'ATT-SHOW-001',
            'name' => 'Show Attendant',
            'street_section' => 'Jalan Show',
            'location_side' => 'Selatan',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Mandiri',
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/attendants/{$attendant->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $attendant->id,
                'registration_number' => 'ATT-SHOW-001',
                'name' => 'Show Attendant',
                'street_section' => 'Jalan Show',
                'location_side' => 'Selatan',
                'bank_account_number' => '1234567890',
                'bank_name' => 'Mandiri',
                'is_active' => true,
            ],
        ]);

        // Verify PIN is not exposed
        $this->assertArrayNotHasKey('pin', $response->json('data'));
    }

    /**
     * Test list all attendants
     */
    public function test_list_all_attendants()
    {
        // Create multiple attendants
        ParkingAttendant::create([
            'registration_number' => 'ATT-LIST-001',
            'name' => 'Attendant 1',
            'street_section' => 'Jalan 1',
            'bank_account_number' => '1111111111',
            'pin' => Hash::make('111111'),
            'is_active' => true,
        ]);

        ParkingAttendant::create([
            'registration_number' => 'ATT-LIST-002',
            'name' => 'Attendant 2',
            'street_section' => 'Jalan 2',
            'bank_account_number' => '2222222222',
            'pin' => Hash::make('222222'),
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/attendants');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Verify both attendants are in the list
        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertEquals('ATT-LIST-001', $data[0]['registration_number']);
        $this->assertEquals('ATT-LIST-002', $data[1]['registration_number']);
    }

    /**
     * Test PIN validation - must be 6 digits
     */
    public function test_pin_validation_must_be_6_digits()
    {
        $attendantData = [
            'registration_number' => 'ATT-PIN-001',
            'name' => 'PIN Test',
            'street_section' => 'Jalan PIN',
            'bank_account_number' => '1234567890',
            'pin' => '12345', // Only 5 digits
        ];

        $response = $this->postJson('/api/attendants', $attendantData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('pin');
    }

    /**
     * Test PIN validation - must be numeric
     */
    public function test_pin_validation_must_be_numeric()
    {
        $attendantData = [
            'registration_number' => 'ATT-PIN-002',
            'name' => 'PIN Test',
            'street_section' => 'Jalan PIN',
            'bank_account_number' => '1234567890',
            'pin' => 'ABCDEF', // Non-numeric
        ];

        $response = $this->postJson('/api/attendants', $attendantData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('pin');
    }

    /**
     * Test required fields validation
     */
    public function test_required_fields_validation()
    {
        // Missing name
        $response = $this->postJson('/api/attendants', [
            'registration_number' => 'ATT-REQ-001',
            'street_section' => 'Jalan Test',
            'bank_account_number' => '1234567890',
            'pin' => '123456',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /**
     * Test attendant update with PIN change
     */
    public function test_attendant_update_with_pin_change()
    {
        $attendant = ParkingAttendant::create([
            'registration_number' => 'ATT-PIN-UPDATE-001',
            'name' => 'PIN Update Test',
            'street_section' => 'Jalan Test',
            'bank_account_number' => '1234567890',
            'pin' => Hash::make('111111'),
            'is_active' => true,
        ]);

        // Update PIN
        $updateData = [
            'pin' => '999999',
        ];

        $response = $this->putJson("/api/attendants/{$attendant->id}", $updateData);

        $response->assertStatus(200);

        // Verify PIN was updated and hashed
        $attendant->refresh();
        $this->assertTrue(Hash::check('999999', $attendant->pin));
        $this->assertFalse(Hash::check('111111', $attendant->pin));
    }
}
