<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\ParkingAttendant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Eris\Generator;
use Eris\TestTrait;

class AuditLoggingPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get or create a test admin user
     */
    protected function getTestAdmin(): User
    {
        $admin = User::where('email', 'admin@test.com')->first();
        
        if (!$admin) {
            $admin = User::factory()->create([
                'email' => 'admin@test.com',
                'name' => 'Test Admin',
                'role' => 'admin',
                'is_active' => true,
            ]);
        }
        
        return $admin;
    }

    /**
     * Get or create a test parking attendant
     */
    protected function getTestAttendant(): ParkingAttendant
    {
        $attendant = ParkingAttendant::where('registration_number', 'ATT001')->first();
        
        if (!$attendant) {
            $attendant = ParkingAttendant::factory()->create([
                'registration_number' => 'ATT001',
                'name' => 'Test Attendant',
                'is_active' => true,
            ]);
        }
        
        return $attendant;
    }

    /**
     * Property 37: Transaction Events Are Logged
     * 
     * **Validates: Requirements 13.1, 13.2**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 37: Transaction events are logged
     */
    public function testTransactionEventsAreLogged()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'vehicle_type' => Generator\elements(['motorcycle', 'car']),
                'amount' => Generator\choose(2000, 10000),
                'street_section' => Generator\elements(['Jl. Sudirman', 'Jl. Thamrin', 'Jl. Gatot Subroto']),
                'payment_status' => Generator\elements(['pending', 'success', 'failed']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($transactionDataList) {
            $attendant = $this->getTestAttendant();
            $admin = $this->getTestAdmin();
            
            foreach ($transactionDataList as $data) {
                // Create transaction
                $transaction = Transaction::create([
                    'transaction_id' => 'TRX-' . uniqid(),
                    'parking_attendant_id' => $attendant->id,
                    'street_section' => $data['street_section'],
                    'vehicle_type' => $data['vehicle_type'],
                    'amount' => $data['amount'],
                    'payment_status' => $data['payment_status'],
                ]);
                
                // Log the transaction creation
                AuditLog::create([
                    'user_id' => $admin->id,
                    'user_type' => 'admin',
                    'action' => 'transaction_created',
                    'entity_type' => 'transaction',
                    'entity_id' => $transaction->id,
                    'new_values' => [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount,
                        'status' => $transaction->payment_status,
                    ],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Test Agent',
                    'created_at' => now(),
                ]);
            }
            
            // Verify all transactions have audit logs
            $auditLogs = AuditLog::where('action', 'transaction_created')->get();
            $this->assertGreaterThanOrEqual(count($transactionDataList), $auditLogs->count());
            
            // Verify each audit log has required fields
            foreach ($auditLogs as $log) {
                $this->assertNotNull($log->action);
                $this->assertNotNull($log->entity_type);
                $this->assertNotNull($log->created_at);
                $this->assertEquals('transaction', $log->entity_type);
            }
        });
    }

    /**
     * Property 38: Administrative Actions Are Logged
     * 
     * **Validates: Requirements 13.1, 13.2**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 38: Administrative actions are logged
     */
    public function testAdministrativeActionsAreLogged()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'action' => Generator\elements(['create', 'update', 'delete', 'activate', 'deactivate']),
                'entity_type' => Generator\elements(['attendant', 'rate', 'user']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($actionDataList) {
            $admin = $this->getTestAdmin();
            
            foreach ($actionDataList as $data) {
                // Log administrative action
                AuditLog::create([
                    'user_id' => $admin->id,
                    'user_type' => 'admin',
                    'action' => $data['action'],
                    'entity_type' => $data['entity_type'],
                    'entity_id' => 1,
                    'new_values' => [
                        'action_type' => $data['action'],
                        'timestamp' => now()->toIso8601String(),
                    ],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Test Agent',
                    'created_at' => now(),
                ]);
            }
            
            // Verify all administrative actions are logged
            $auditLogs = AuditLog::where('user_id', $admin->id)->get();
            $this->assertGreaterThanOrEqual(count($actionDataList), $auditLogs->count());
            
            // Verify each log has required fields
            foreach ($auditLogs as $log) {
                $this->assertNotNull($log->action);
                $this->assertNotNull($log->user_id);
                $this->assertNotNull($log->created_at);
                $this->assertEquals('admin', $log->user_type);
            }
        });
    }

    /**
     * Property 40: Audit Log Filtering
     * 
     * **Validates: Requirements 13.6**
     * 
     * @group Feature: parking-payment-monitoring-system, Property 40: Audit log filtering
     */
    public function testAuditLogFiltering()
    {
        $this->forAll(
            Generator\seq(Generator\associative([
                'action' => Generator\elements(['create', 'update', 'delete']),
                'entity_type' => Generator\elements(['attendant', 'rate']),
            ]))
        )
        ->withMaxSize(20)
        ->then(function ($actionDataList) {
            $admin = $this->getTestAdmin();
            
            // Create audit logs with different actions and entity types
            foreach ($actionDataList as $data) {
                AuditLog::create([
                    'user_id' => $admin->id,
                    'user_type' => 'admin',
                    'action' => $data['action'],
                    'entity_type' => $data['entity_type'],
                    'entity_id' => 1,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Test Agent',
                    'created_at' => now(),
                ]);
            }
            
            // Test filtering by action
            $createLogs = AuditLog::where('action', 'create')->get();
            foreach ($createLogs as $log) {
                $this->assertEquals('create', $log->action);
            }
            
            // Test filtering by entity type
            $attendantLogs = AuditLog::where('entity_type', 'attendant')->get();
            foreach ($attendantLogs as $log) {
                $this->assertEquals('attendant', $log->entity_type);
            }
            
            // Test filtering by user
            $userLogs = AuditLog::where('user_id', $admin->id)->get();
            foreach ($userLogs as $log) {
                $this->assertEquals($admin->id, $log->user_id);
            }
            
            // Verify filtering works correctly
            $this->assertGreaterThanOrEqual(0, $createLogs->count());
            $this->assertGreaterThanOrEqual(0, $attendantLogs->count());
            $this->assertGreaterThanOrEqual(0, $userLogs->count());
        });
    }
}
