<?php

namespace Omalizadeh\QueryFilter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Omalizadeh\QueryFilter\Filter;
use Omalizadeh\QueryFilter\Tests\Filters\UserFilter;
use Omalizadeh\QueryFilter\Tests\Models\User;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    public function testSumCanBeCalculatedAndReturned(): void
    {
        $limit = 2;

        $filter = new Filter();

        $filter->setPage(0, $limit);

        $filterResult = User::filter((new UserFilter($filter)));

        $this->assertCount($limit, $filterResult->data());
        $this->assertGreaterThan($limit, $filterResult->count());
    }
}
