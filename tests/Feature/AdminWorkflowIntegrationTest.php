<?php

namespace Tests\Feature;

use App\Models\ParkingAttendant;
use App\Models\ParkingRate;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    /**
     * Test complete admin workflow: login → view dashboard → generate report → download → logout
     */
    public function test_complete_admin_workflow(): void
    {
        // Step 1: Admin login
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json());

        $token = $response->json('token');

        // Step 2: View dashboard
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/dashboard');

        $response->assertStatus(200);
        $this->assertArrayHasKey('daily_revenue', $response->json());
        $this->assertArrayHasKey('monthly_revenue', $response->json());
        $this->assertArrayHasKey('total_transactions', $response->json());

        // Step 3: Generate report
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/reports/generate', [
                'type' => 'pdf',
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('report_id', $response->json());

        $reportId = $response->json('report_id');

        // Step 4: Check report status
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson("/api/reports/$reportId/status");

        $response->assertStatus(200);
        $this->assertIn($response->json('status'), ['pending', 'processing', 'completed']);

        // Step 5: Logout
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }

    /**
     * Test attendant management workflow
     */
    public function test_attendant_management_workflow(): void
    {
        // Login
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $token = $response->json('token');

        // Create new attendant
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/attendants', [
                'registration_number' => 'JP999',
                'name' => 'Test Attendant',
                'street_section' => 'Jl. Test',
                'location_side' => 'Utara',
                'bank_account_number' => '1234567890',
                'bank_name' => 'BCA',
                'pin' => '1234',
            ]);

        $response->assertStatus(201);
        $attendantId = $response->json('id');

        // Get attendant details
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson("/api/attendants/$attendantId");

        $response->assertStatus(200);
        $this->assertEquals('JP999', $response->json('registration_number'));

        // Update attendant
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->putJson("/api/attendants/$attendantId", [
                'name' => 'Updated Attendant',
                'bank_account_number' => '0987654321',
            ]);

        $response->assertStatus(200);

        // Deactivate attendant
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/attendants/$attendantId/deactivate");

        $response->assertStatus(200);

        // Verify attendant is inactive
        $attendant = ParkingAttendant::find($attendantId);
        $this->assertFalse($attendant->is_active);

        // Activate attendant
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/attendants/$attendantId/activate");

        $response->assertStatus(200);

        // Verify attendant is active
        $attendant->refresh();
        $this->assertTrue($attendant->is_active);
    }

    /**
     * Test rate configuration workflow
     */
    public function test_rate_configuration_workflow(): void
    {
        // Login
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $token = $response->json('token');

        // Get current rates
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/rates');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());

        // Update rates
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->putJson('/api/rates', [
                'rates' => [
                    [
                        'vehicle_type' => 'motorcycle',
                        'street_section' => null,
                        'rate' => 3000,
                    ],
                    [
                        'vehicle_type' => 'car',
                        'street_section' => null,
                        'rate' => 7000,
                    ],
                ],
            ]);

        $response->assertStatus(200);

        // Verify rates were updated
        $motorcycleRate = ParkingRate::where('vehicle_type', 'motorcycle')
            ->whereNull('street_section')
            ->latest('effective_from')
            ->first();

        $this->assertEquals(3000, $motorcycleRate->rate);
    }

    /**
     * Test audit log viewing
     */
    public function test_audit_log_viewing(): void
    {
        // Login
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $token = $response->json('token');

        // Get audit logs
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/audit-logs');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));

        // Search audit logs
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/audit-logs/search', [
                'action' => 'login',
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test unauthorized access
     */
    public function test_unauthorized_access(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }

    /**
     * Test invalid credentials
     */
    public function test_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'invalid_password',
        ]);

        $response->assertStatus(401);
    }
}
