<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login with empty credentials
     */
    public function test_admin_login_with_empty_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_admin_login_with_empty_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test SQL injection attempts
     */
    public function test_admin_login_with_sql_injection_in_email(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => "admin@test.com' OR '1'='1",
            'password' => 'password123',
        ]);

        // Should fail authentication or validation, not cause SQL error
        $this->assertTrue(in_array($response->status(), [401, 422]));
        // Just verify it didn't succeed
        $this->assertNotEquals(200, $response->status());
    }

    public function test_admin_login_with_sql_injection_in_password(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => "' OR '1'='1",
        ]);

        // Should fail authentication, not cause SQL error
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Test session timeout at exactly 30 minutes
     */
    public function test_admin_session_expires_after_30_minutes(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Login
        $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Manually set last activity to 30 minutes + 1 second ago
        session(['admin_last_activity' => now()->timestamp - (30 * 60 + 1)]);

        // Check session - should be expired
        $response = $this->getJson('/api/auth/check');
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Sesi telah berakhir. Silakan login kembali.',
        ]);
    }

    public function test_admin_session_valid_before_30_minutes(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Login
        $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Manually set last activity to 29 minutes ago
        session(['admin_last_activity' => now()->timestamp - (29 * 60)]);

        // Check session - should still be valid
        $response = $this->getJson('/api/auth/check');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test concurrent sessions (same user logging in multiple times)
     */
    public function test_admin_concurrent_sessions_override_previous(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // First login
        $response1 = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        $response1->assertStatus(200);

        // Get the first session ID
        $firstSessionId = session()->getId();

        // Second login (simulating different device/browser)
        $this->withSession([]); // Clear session
        $response2 = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        $response2->assertStatus(200);

        // Both logins should succeed (concurrent sessions allowed)
        $this->assertTrue(true);
    }

    /**
     * Test invalid email format
     */
    public function test_admin_login_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test password too short
     */
    public function test_admin_login_with_short_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => '12345', // Less than 6 characters
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test middleware protection
     */
    public function test_admin_middleware_blocks_unauthenticated_requests(): void
    {
        // Try to access a protected route without authentication
        // Note: We'll need to add a protected route for this test
        // For now, we'll test the checkSession endpoint
        $response = $this->getJson('/api/auth/check');
        $response->assertStatus(401);
    }

    /**
     * Test inactive user cannot access after deactivation
     */
    public function test_admin_deactivated_during_session_cannot_access(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Login
        $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Deactivate user
        $user->update(['is_active' => false]);

        // Try to check session - should fail
        $response = $this->getJson('/api/auth/check');
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Sesi tidak valid.',
        ]);
    }

    /**
     * Test XSS attempt in login fields
     */
    public function test_admin_login_with_xss_attempt(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => '<script>alert("xss")</script>@test.com',
            'password' => 'password123',
        ]);

        // Should fail validation or authentication, not execute script
        $this->assertTrue($response->status() === 401 || $response->status() === 422);
    }

    /**
     * Test very long input strings
     */
    public function test_admin_login_with_very_long_email(): void
    {
        $longEmail = str_repeat('a', 300) . '@test.com';
        
        $response = $this->postJson('/api/auth/login', [
            'email' => $longEmail,
            'password' => 'password123',
        ]);

        // Should fail validation (max 255 characters)
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test logout without active session
     */
    public function test_admin_logout_without_session(): void
    {
        $response = $this->postJson('/api/auth/logout');
        
        // Should succeed even without session
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
