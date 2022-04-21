<?php

namespace Omalizadeh\QueryFilter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Omalizadeh\QueryFilter\Filter;
use Omalizadeh\QueryFilter\Tests\Filters\UserFilter;
use Omalizadeh\QueryFilter\Tests\Models\User;

class SumTest extends TestCase
{
    use RefreshDatabase;

    public function testSumCanBeCalculatedAndReturned(): void
    {
        $filter = new Filter();

        $filter->addFilter('paid_amount', '<=', 100);
        $filter->addSum('paid_amount');

        $filterResult = User::filter((new UserFilter($filter)));

        $this->assertEquals($filterResult->sums()['paid_amount'], 169.99);
    }
}
