<?php

namespace Omalizadeh\QueryFilter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Omalizadeh\QueryFilter\Filter;
use Omalizadeh\QueryFilter\Tests\Filters\UserFilter;
use Omalizadeh\QueryFilter\Tests\Models\User;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    public function testPaginationWithCustomPage(): void
    {
        $limit = 1;

        $filter = new Filter();

        $filter->setPage(1, $limit);

        $filterResult = User::filter((new UserFilter($filter)));

        $this->assertCount($limit, $filterResult->data());
        $this->assertGreaterThan($limit, $filterResult->count());

        foreach ($filterResult->data() as $user) {
            $this->assertGreaterThan(1, $user->id);
        }
    }

    public function testMaxPaginationLimit(): void
    {
        $userFilter = new UserFilter();

        $count = $userFilter->getMaxPaginationLimit() + 10;

        User::factory()->count($count)->create();

        $filterResult = User::filter($userFilter);

        $this->assertCount($userFilter->getMaxPaginationLimit(), $filterResult->data());
        $this->assertGreaterThan($count, $filterResult->count());
    }
}
