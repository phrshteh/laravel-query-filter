<?php

namespace Omalizadeh\QueryFilter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Omalizadeh\QueryFilter\Filter;
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
    public function getDataWithoutFilterTest(): void
    {
        $filterResult = User::filter((new UserFilter(new Request())));
        $users = $filterResult->getData();
        $this->assertCount($filterResult->getCount(), $users);
    }

    /** @test */
    public function fieldEqualsFilterTest(): void
    {
        $filters = new Filter([
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
        $filterResult = User::filter($filters);
        $users = $filterResult->getData();
        foreach ($users as $user) {
            $this->assertTrue($user->isActive());
        }
    }

    /** @test */
    public function hasRelationFilterTest(): void
    {
        $filters = new Filter([
            [
                [
                    'field' => 'gender',
                    'op' => '=',
                    'value' => false
                ]
            ]
        ]);
        $request = new Request([
            'filter' => $filters->toJson()
        ]);
        $filters = new UserFilter($request);
        $filterResult = User::filter($filters);
        $users = $filterResult->getData();
        foreach ($users as $user) {
            $this->assertTrue($user->isFemale());
        }
    }

    /** @test */
    public function doesntHaveRelationFilterTest(): void
    {
        $filters = new Filter([
            [
                [
                    'field' => 'post_body',
                    'op' => 'like',
                    'value' => 'hello',
                    'has' => false
                ]
            ]
        ]);
        $request = new Request([
            'filter' => $filters->toJson()
        ]);
        $filters = new UserFilter($request);
        $filterResult = User::filter($filters);
        $users = $filterResult->getData();
        foreach ($users as $user) {
            $this->assertFalse($user->posts()->where('body', 'like', '%hello%')->exists());
        }
    }
}
