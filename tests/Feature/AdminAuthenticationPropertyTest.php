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
        $this->minimumEvaluationRatio(0.1);
        
        $this->forAll(
            Generator\map(
                function ($nameNum, $emailNum, $passNum) {
                    return [
                        'name' => 'User' . $nameNum,
                        'email' => 'user' . $emailNum . '@example.com',
                        'password' => 'pass' . $passNum . 'word',
                    ];
                },
                Generator\choose(1, 10000),
                Generator\choose(1, 10000),
                Generator\choose(100000, 999999)
            )
        )
        ->withMaxSize(10)
        ->then(function ($userData) {
            // Create user with hashed password
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'admin',
                'is_active' => true,
            ]);

            // Attempt login with correct credentials
            $response = $this->postJson('/api/auth/login', [
                'email' => $userData['email'],
                'password' => $userData['password'],
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
        });
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
        $this->minimumEvaluationRatio(0.1);
        
        $this->forAll(
            Generator\map(
                function ($nameNum, $emailNum, $correctPass, $wrongPass) {
                    return [
                        'name' => 'User' . $nameNum,
                        'email' => 'user' . $emailNum . '@example.com',
                        'correct_password' => 'pass' . $correctPass . 'word',
                        'wrong_password' => 'pass' . $wrongPass . 'word',
                    ];
                },
                Generator\choose(1, 10000),
                Generator\choose(1, 10000),
                Generator\choose(100000, 999999),
                Generator\choose(100000, 999999)
            )
        )
        ->when(function ($userData) {
            // Only test when passwords are different
            return $userData['correct_password'] !== $userData['wrong_password'];
        })
        ->withMaxSize(10)
        ->then(function ($userData) {
            // Create user with correct password
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['correct_password']),
                'role' => 'admin',
                'is_active' => true,
            ]);

            // Attempt login with wrong password
            $response = $this->postJson('/api/auth/login', [
                'email' => $userData['email'],
                'password' => $userData['wrong_password'],
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
        });
    }

    /**
     * Test invalid credentials with non-existent email
     */
    public function test_property_19_nonexistent_email_denies_access()
    {
        $this->minimumEvaluationRatio(0.1);
        
        $this->forAll(
            Generator\map(
                function ($emailNum, $passNum) {
                    return [
                        'email' => 'nonexistent' . $emailNum . '@example.com',
                        'password' => 'pass' . $passNum . 'word',
                    ];
                },
                Generator\choose(1, 10000),
                Generator\choose(100000, 999999)
            )
        )
        ->withMaxSize(10)
        ->then(function ($credentials) {
            // Attempt login without creating user
            $response = $this->postJson('/api/auth/login', [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);

            // Assert authentication failed
            $response->assertStatus(401);
            $response->assertJson([
                'success' => false,
                'message' => 'Email/password salah.',
            ]);

            // Assert no session was created
            $this->assertNull(session('admin_user_id'));
        });
    }

    /**
     * Test that inactive users cannot login
     */
    public function test_inactive_users_cannot_login()
    {
        $this->minimumEvaluationRatio(0.1);
        
        $this->forAll(
            Generator\map(
                function ($nameNum, $emailNum, $passNum) {
                    return [
                        'name' => 'User' . $nameNum,
                        'email' => 'inactive' . $emailNum . '@example.com',
                        'password' => 'pass' . $passNum . 'word',
                    ];
                },
                Generator\choose(1, 10000),
                Generator\choose(1, 10000),
                Generator\choose(100000, 999999)
            )
        )
        ->withMaxSize(10)
        ->then(function ($userData) {
            // Create inactive user
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'admin',
                'is_active' => false,
            ]);

            // Attempt login
            $response = $this->postJson('/api/auth/login', [
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);

            // Assert authentication failed
            $response->assertStatus(403);
            $response->assertJson([
                'success' => false,
                'message' => 'Akun tidak aktif. Hubungi administrator.',
            ]);

            // Assert no session was created
            $this->assertNull(session('admin_user_id'));
        });
    }
}
