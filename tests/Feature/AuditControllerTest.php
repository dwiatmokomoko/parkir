<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test audit logs index endpoint
     */
    public function testAuditLogsIndexEndpoint()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create some audit logs
        for ($i = 0; $i < 20; $i++) {
            AuditLog::create([
                'user_id' => $admin->id,
                'user_type' => 'admin',
                'action' => 'test_action_' . ($i % 5),
                'entity_type' => 'test_entity',
                'entity_id' => $i,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'created_at' => now(),
            ]);
        }

        // Test the endpoint with session
        $response = $this->withSession([
            'admin_user_id' => $admin->id,
            'admin_last_activity' => now()->timestamp,
        ])->getJson('/api/audit-logs');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'user_type',
                    'action',
                    'entity_type',
                    'entity_id',
                    'ip_address',
                    'user_agent',
                    'created_at',
                ]
            ],
            'pagination' => [
                'total',
                'per_page',
                'current_page',
                'last_page',
                'from',
                'to',
            ]
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    /**
     * Test audit logs search endpoint with filters
     */
    public function testAuditLogsSearchEndpointWithFilters()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create audit logs with different actions
        AuditLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'create_attendant',
            'entity_type' => 'attendant',
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'update_attendant',
            'entity_type' => 'attendant',
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'create_rate',
            'entity_type' => 'rate',
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        // Test search by action
        $response = $this->withSession([
            'admin_user_id' => $admin->id,
            'admin_last_activity' => now()->timestamp,
        ])->getJson('/api/audit-logs/search?action=create');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        // Test search by entity type
        $response = $this->withSession([
            'admin_user_id' => $admin->id,
            'admin_last_activity' => now()->timestamp,
        ])->getJson('/api/audit-logs/search?entity_type=attendant');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    /**
     * Test audit logs pagination
     */
    public function testAuditLogsPagination()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create 50 audit logs
        for ($i = 0; $i < 50; $i++) {
            AuditLog::create([
                'user_id' => $admin->id,
                'user_type' => 'admin',
                'action' => 'test_action',
                'entity_type' => 'test',
                'entity_id' => $i,
                'created_at' => now(),
            ]);
        }

        // Test first page
        $response = $this->withSession([
            'admin_user_id' => $admin->id,
            'admin_last_activity' => now()->timestamp,
        ])->getJson('/api/audit-logs?per_page=15&page=1');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('pagination.current_page'));
        $this->assertLessThanOrEqual(15, count($response->json('data')));

        // Test second page
        $response = $this->withSession([
            'admin_user_id' => $admin->id,
            'admin_last_activity' => now()->timestamp,
        ])->getJson('/api/audit-logs?per_page=15&page=2');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('pagination.current_page'));
    }

    /**
     * Test audit logs sorting
     */
    public function testAuditLogsSorting()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create audit logs with different actions
        AuditLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'action_a',
            'entity_type' => 'test',
            'created_at' => now()->subHours(2),
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'action_b',
            'entity_type' => 'test',
            'created_at' => now()->subHours(1),
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'action_c',
            'entity_type' => 'test',
            'created_at' => now(),
        ]);

        // Test sorting by created_at descending (default)
        $response = $this->withSession([
            'admin_user_id' => $admin->id,
            'admin_last_activity' => now()->timestamp,
        ])->getJson('/api/audit-logs?sort_by=created_at&sort_order=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));

        // Test sorting by action ascending
        $response = $this->withSession([
            'admin_user_id' => $admin->id,
            'admin_last_activity' => now()->timestamp,
        ])->getJson('/api/audit-logs?sort_by=action&sort_order=asc');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }
}
