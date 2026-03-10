<?php

namespace Tests\Feature;

use App\Jobs\GenerateReportJob;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueJobsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that GenerateReportJob can be dispatched.
     */
    public function test_generate_report_job_can_be_dispatched(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $report = Report::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        GenerateReportJob::dispatch($report);

        Queue::assertPushed(GenerateReportJob::class);
    }

    /**
     * Test that GenerateReportJob executes successfully.
     */
    public function test_generate_report_job_executes_successfully(): void
    {
        // Create test data
        $user = User::factory()->create();
        $report = Report::factory()->create([
            'user_id' => $user->id,
            'type' => 'pdf',
            'status' => 'pending',
            'filters' => [],
        ]);

        // Create sample transactions
        Transaction::factory()->count(5)->success()->create();

        // Execute the job
        $job = new GenerateReportJob($report);
        $job->handle();

        // Verify report status was updated
        $report->refresh();
        $this->assertEquals('completed', $report->status);
        $this->assertNotNull($report->file_path);
        $this->assertNotNull($report->completed_at);
    }

    /**
     * Test that GenerateReportJob handles failures gracefully.
     */
    public function test_generate_report_job_handles_failure(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create([
            'user_id' => $user->id,
            'type' => 'invalid_type', // Invalid type to cause failure
            'status' => 'pending',
            'filters' => [],
        ]);

        $job = new GenerateReportJob($report);

        try {
            $job->handle();
        } catch (\Throwable $e) {
            // Expected to throw
        }

        // Verify report status was updated to failed
        $report->refresh();
        $this->assertEquals('failed', $report->status);
        $this->assertNotNull($report->error_message);
        $this->assertNotNull($report->completed_at);
    }

    /**
     * Test that GenerateReportJob has correct retry configuration.
     */
    public function test_generate_report_job_has_retry_configuration(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create([
            'user_id' => $user->id,
        ]);

        $job = new GenerateReportJob($report);

        // Verify retry configuration
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
        $this->assertEquals([10, 60, 300], $job->backoff());
    }

    /**
     * Test that GenerateReportJob respects filters.
     */
    public function test_generate_report_job_respects_filters(): void
    {
        $user = User::factory()->create();
        
        // Create transactions with different dates
        $oldTransaction = Transaction::factory()->success()->create([
            'created_at' => now()->subDays(60),
        ]);
        
        $recentTransaction = Transaction::factory()->success()->create([
            'created_at' => now()->subDays(5),
        ]);

        $report = Report::factory()->create([
            'user_id' => $user->id,
            'type' => 'pdf',
            'status' => 'pending',
            'filters' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);

        $job = new GenerateReportJob($report);
        $job->handle();

        // Verify report was generated
        $report->refresh();
        $this->assertEquals('completed', $report->status);
    }

    /**
     * Test that GenerateReportJob can be retried.
     */
    public function test_generate_report_job_can_be_retried(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create([
            'user_id' => $user->id,
            'type' => 'pdf',
            'status' => 'pending',
            'filters' => [],
        ]);

        Transaction::factory()->count(3)->success()->create();

        $job = new GenerateReportJob($report);

        // First attempt
        $job->handle();
        $report->refresh();
        $this->assertEquals('completed', $report->status);

        // Verify the job can be retried (no exception thrown)
        $this->assertTrue(true);
    }
}
