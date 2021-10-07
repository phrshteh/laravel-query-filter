<?php

namespace Omalizadeh\QueryFilter\Tests;

use Omalizadeh\QueryFilter\QueryFilterServiceProvider;
use Omalizadeh\QueryFilter\Tests\Database\Migrations\CreateTestUsersTable;
use Omalizadeh\QueryFilter\Tests\Database\Seeders\TestUserSeeder;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            QueryFilterServiceProvider::class
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
        (new CreateTestUsersTable)->up();
    }

    protected function seedTestData(): void
    {
        (new TestUserSeeder)->run();
    }

    protected function getFilterPath(?string $filterFileName = null): string
    {
        return app_path("Filters" . "\\{$filterFileName}");
    }
}
