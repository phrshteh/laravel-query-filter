<?php

namespace Omalizadeh\QueryFilter\Tests;

use Omalizadeh\QueryFilter\Providers\QueryFilterServiceProvider;
use Omalizadeh\QueryFilter\Tests\Database\Migrations\CreateTestingPostsTable;
use Omalizadeh\QueryFilter\Tests\Database\Migrations\CreateTestingProfilesTable;
use Omalizadeh\QueryFilter\Tests\Database\Migrations\CreateTestingUsersTable;
use Omalizadeh\QueryFilter\Tests\Database\Seeders\TestingDataSeeder;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateAndSeed();
    }

    protected function getPackageProviders($app): array
    {
        return [
            QueryFilterServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'test_db');
        $app['config']->set('database.connections.test_db', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function migrateAndSeed(): void
    {
        $this->migrate();
        $this->seedTestData();
    }

    protected function migrate(): void
    {
        (new CreateTestingUsersTable)->up();
        (new CreateTestingProfilesTable)->up();
        (new CreateTestingPostsTable)->up();
    }

    protected function seedTestData(): void
    {
        (new TestingDataSeeder)->run();
    }

    protected function getFilterPath(?string $filterFileName = null): string
    {
        return app_path("Filters"."/$filterFileName");
    }
}
