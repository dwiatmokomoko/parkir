<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected AuditLogger $auditLogger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditLogger = app(AuditLogger::class);
    }

    /**
     * Test audit log creation for transaction action type
     */
    public function testAuditLogCreationForTransactionActionType()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $attendant = ParkingAttendant::factory()->create([
            'registration_number' => 'ATT001',
        ]);

        $transaction = Transaction::create([
            'transaction_id' => 'TRX-001',
            'parking_attendant_id' => $attendant->id,
            'street_section' => 'Jl. Sudirman',
            'vehicle_type' => 'motorcycle',
            'amount' => 5000,
            'payment_status' => 'success',
        ]);

        // Log the transaction
        $auditLog = $this->auditLogger->log(
            'transaction_created',
            [
                'entity_type' => 'transaction',
                'entity_id' => $transaction->id,
                'new_values' => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                ],
            ],
            $admin
        );

        $this->assertNotNull($auditLog);
        $this->assertEquals('transaction_created', $auditLog->action);
        $this->assertEquals('transaction', $auditLog->entity_type);
        $this->assertEquals($transaction->id, $auditLog->entity_id);
        $this->assertEquals($admin->id, $auditLog->user_id);
        $this->assertEquals('admin', $auditLog->user_type);
    }

    /**
     * Test audit log with null user (system actions)
     */
    public function testAuditLogWithNullUser()
    {
        $auditLog = $this->auditLogger->log(
            'system_action',
            [
                'entity_type' => 'system',
                'new_values' => [
                    'action' => 'automated_cleanup',
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
            null
        );

        $this->assertNotNull($auditLog);
        $this->assertEquals('system_action', $auditLog->action);
        $this->assertNull($auditLog->user_id);
        $this->assertEquals('system', $auditLog->entity_type);
    }

    /**
     * Test audit log with very long field values
     */
    public function testAuditLogWithVeryLongFieldValues()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $longValue = str_repeat('A', 5000);

        $auditLog = $this->auditLogger->log(
            'long_value_test',
            [
                'entity_type' => 'test',
                'new_values' => [
                    'long_field' => $longValue,
                    'description' => 'Testing with very long field values',
                ],
            ],
            $admin
        );

        $this->assertNotNull($auditLog);
        $this->assertEquals('long_value_test', $auditLog->action);
        $this->assertIsArray($auditLog->new_values);
        $this->assertEquals($longValue, $auditLog->new_values['long_field']);
    }

    /**
     * Test audit log query performance
     */
    public function testAuditLogQueryPerformance()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        // Create 100 audit logs
        for ($i = 0; $i < 100; $i++) {
            $this->auditLogger->log(
                'test_action_' . ($i % 5),
                [
                    'entity_type' => 'test_entity_' . ($i % 10),
                    'entity_id' => $i,
                    'new_values' => [
                        'index' => $i,
                        'timestamp' => now()->toIso8601String(),
                    ],
                ],
                $admin
            );
        }

        // Test query performance - should complete quickly
        $startTime = microtime(true);

        // Query with pagination
        $logs = AuditLog::where('user_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Should complete in less than 1 second
        $this->assertLessThan(1, $duration);
        $this->assertGreaterThanOrEqual(15, $logs->count());
    }

    /**
     * Test audit log creation for each action type
     */
    public function testAuditLogCreationForEachActionType()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $actionTypes = ['create', 'update', 'delete', 'activate', 'deactivate'];

        foreach ($actionTypes as $action) {
            $auditLog = $this->auditLogger->log(
                $action,
                [
                    'entity_type' => 'attendant',
                    'entity_id' => 1,
                    'new_values' => [
                        'action' => $action,
                    ],
                ],
                $admin
            );

            $this->assertNotNull($auditLog);
            $this->assertEquals($action, $auditLog->action);
            $this->assertEquals('attendant', $auditLog->entity_type);
        }

        // Verify all action types were logged
        $logs = AuditLog::where('user_id', $admin->id)->get();
        $this->assertCount(count($actionTypes), $logs);
    }

    /**
     * Test audit log with old and new values
     */
    public function testAuditLogWithOldAndNewValues()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $oldValues = [
            'name' => 'Old Name',
            'rate' => 5000,
        ];

        $newValues = [
            'name' => 'New Name',
            'rate' => 6000,
        ];

        $auditLog = $this->auditLogger->log(
            'update',
            [
                'entity_type' => 'parking_rate',
                'entity_id' => 1,
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ],
            $admin
        );

        $this->assertNotNull($auditLog);
        $this->assertEquals($oldValues, $auditLog->old_values);
        $this->assertEquals($newValues, $auditLog->new_values);
    }

    /**
     * Test audit log with IP address and user agent
     */
    public function testAuditLogWithIpAddressAndUserAgent()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $request = \Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('ip')->andReturn('192.168.1.1');
        $request->shouldReceive('userAgent')->andReturn('Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

        $auditLog = $this->auditLogger->log(
            'test_action',
            [
                'entity_type' => 'test',
            ],
            $admin,
            $request
        );

        $this->assertNotNull($auditLog);
        $this->assertEquals('192.168.1.1', $auditLog->ip_address);
        $this->assertEquals('Mozilla/5.0 (Windows NT 10.0; Win64; x64)', $auditLog->user_agent);
    }

    /**
     * Test audit log filtering by date range
     */
    public function testAuditLogFilteringByDateRange()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        // Create logs with different dates
        $yesterday = now()->subDay();
        $today = now();
        $tomorrow = now()->addDay();

        AuditLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'test_yesterday',
            'entity_type' => 'test',
            'created_at' => $yesterday,
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'test_today',
            'entity_type' => 'test',
            'created_at' => $today,
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'test_tomorrow',
            'entity_type' => 'test',
            'created_at' => $tomorrow,
        ]);

        // Filter by date range
        $logs = AuditLog::whereDate('created_at', '>=', $today->toDateString())
            ->whereDate('created_at', '<=', $today->toDateString())
            ->get();

        $this->assertGreaterThanOrEqual(1, $logs->count());
        foreach ($logs as $log) {
            $this->assertTrue($log->created_at->isToday());
        }
    }

    /**
     * Test audit log search by action
     */
    public function testAuditLogSearchByAction()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        // Create logs with different actions
        $this->auditLogger->log('create_attendant', ['entity_type' => 'attendant'], $admin);
        $this->auditLogger->log('update_attendant', ['entity_type' => 'attendant'], $admin);
        $this->auditLogger->log('delete_attendant', ['entity_type' => 'attendant'], $admin);

        // Search for specific action
        $logs = AuditLog::where('action', 'like', '%create%')->get();

        $this->assertGreaterThanOrEqual(1, $logs->count());
        foreach ($logs as $log) {
            $this->assertStringContainsString('create', $log->action);
        }
    }
}
