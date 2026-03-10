<?php

namespace Tests\Feature;

use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * @group Feature: parking-payment-monitoring-system
 */
class AdminAuthenticationPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 18: Valid credentials grant access
     * 
     * For any admin user with correct username and password, 
     * the authentication system should grant access and create a valid session.
     * 
     * **Validates: Requirements 6.1, 6.2**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 18: Valid credentials grant access
     */
    public function test_property_18_valid_credentials_grant_access()
    {
        // Create user with hashed password
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Attempt login with correct credentials
        $response = $this->postJson('/api/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        // Assert successful authentication
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Login berhasil.',
        ]);
        $response->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'role'],
            ],
        ]);

        // Assert session was created
        $this->assertNotNull(session('admin_user_id'));
        $this->assertEquals($user->id, session('admin_user_id'));
        $this->assertNotNull(session('admin_last_activity'));

        // Assert last login info was updated
        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_login_ip);
    }

    /**
     * Property 19: Invalid credentials deny access
     * 
     * For any login attempt with incorrect username or password, 
     * the authentication system should deny access and not create a session.
     * 
     * **Validates: Requirements 6.3**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 19: Invalid credentials deny access
     */
    public function test_property_19_invalid_credentials_deny_access()
    {
        // Create user with correct password
        User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Attempt login with wrong password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert authentication failed
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Email/password salah.',
        ]);

        // Assert no session was created
        $this->assertNull(session('admin_user_id'));
        $this->assertNull(session('admin_last_activity'));
    }

    /**
     * Test invalid credentials with non-existent email
     */
    public function test_property_19_nonexistent_email_denies_access()
    {
        // Attempt login without creating user
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        // Assert authentication failed
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Email/password salah.',
        ]);

        // Assert no session was created
        $this->assertNull(session('admin_user_id'));
    }

    /**
     * Test that inactive users cannot login
     */
    public function test_inactive_users_cannot_login()
    {
        // Create inactive user
        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => false,
        ]);

        // Attempt login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        // Assert authentication failed
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Akun tidak aktif. Hubungi administrator.',
        ]);

        // Assert no session was created
        $this->assertNull(session('admin_user_id'));
    }
}
