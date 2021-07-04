<?php

namespace Omalizadeh\QueryFilter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Omalizadeh\QueryFilter\QueryFilter;
use Omalizadeh\QueryFilter\Tests\Filters\UserFilter;
use Omalizadeh\QueryFilter\Tests\Models\User;

class QueryFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateAndSeed();
    }

    /** @test */
    public function getDataWithoutFilterTest()
    {
        $filters = new QueryFilter();
        $request = new Request([
            'filter' => $filters->toJson()
        ]);
        $filters = new UserFilter($request);
        [$users, $count] = User::filter($filters);
        $users = $users->get();
        $this->assertCount($count, $users);
    }

    /** @test */
    public function fieldEqualsFilterTest()
    {
        $filters = new QueryFilter([
            [
                [
                    'field' => 'is_active',
                    'op' => '=',
                    'value' => true
                ]
            ]
        ]);
        $request = new Request([
            'filter' => $filters->toJson()
        ]);
        $filters = new UserFilter($request);
        [$users, $count] = User::filter($filters);
        $users = $users->get();
        foreach ($users as $user) {
            $this->assertTrue($user->isActive());
        }
    }
}
