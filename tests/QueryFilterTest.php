<?php

namespace Omalizadeh\QueryFilter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Omalizadeh\QueryFilter\Filter;
use Omalizadeh\QueryFilter\Tests\Filters\UserFilter;
use Omalizadeh\QueryFilter\Tests\Models\User;

class QueryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function testResultWithoutFilter(): void
    {
        $filterResult = User::filter((new UserFilter()));

        $this->assertCount($filterResult->count(), $filterResult->data());
    }

    public function testFieldIsEqualFilter(): void
    {
        $filter = new Filter();

        $filter->setFilterGroups([
            [
                [
                    'field' => 'is_active',
                    'op' => '=',
                    'value' => true,
                ],
            ],
        ]);

        $filterResult = User::filter(new UserFilter($filter));

        foreach ($filterResult->data() as $user) {
            $this->assertTrue($user->isActive());
        }
    }

    public function testSelectingSpecificFieldsInFilters(): void
    {
        $filter = new Filter();

        $filter->setSelectedAttributes(['phone']);

        $filterResult = User::filter(new UserFilter($filter));

        foreach ($filterResult->data()->toArray() as $user) {
            $this->assertEmpty(Arr::except($user, ['phone']));
        }
    }
}
