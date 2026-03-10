<?php

namespace Tests;

use Eris\TestTrait;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base class for property-based tests using Eris
 * 
 * This class extends the Laravel TestCase and includes Eris TestTrait
 * for property-based testing capabilities.
 */
abstract class PropertyTestCase extends BaseTestCase
{
    use CreatesApplication;
    use TestTrait;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Begin database transaction for test isolation
        $this->beginDatabaseTransaction();
    }

    /**
     * Teardown the test environment.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Rollback database transaction
        $this->rollbackDatabaseTransaction();
        
        parent::tearDown();
    }

    /**
     * Begin a database transaction for test isolation.
     *
     * @return void
     */
    protected function beginDatabaseTransaction(): void
    {
        $this->app['db']->connection()->beginTransaction();
    }

    /**
     * Rollback the database transaction.
     *
     * @return void
     */
    protected function rollbackDatabaseTransaction(): void
    {
        $this->app['db']->connection()->rollBack();
    }
}
