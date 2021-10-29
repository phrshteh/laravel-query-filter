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

    /** @test */
    public function getDataWithoutFilterTest(): void
    {
        $filterResult = User::filter((new UserFilter()));

        $users = $filterResult->getData();

        $this->assertCount($filterResult->getCount(), $users);
    }

    /** @test */
    public function fieldEqualsFilterTest(): void
    {
        $filter = new Filter();
        $filter->setFilterGroups([
            [
                [
                    'field' => 'is_active',
                    'op' => '=',
                    'value' => true
                ]
            ]
        ]);

        $modelFilter = new UserFilter($filter);

        $filterResult = User::filter($modelFilter);

        $users = $filterResult->getData();

        foreach ($users as $user) {
            $this->assertTrue($user->isActive());
        }
    }

    /** @test */
    public function selectSpecificFieldsTest(): void
    {
        $filter = new Filter();
        $filter->setSelectedAttributes(['phone']);

        $modelFilter = new UserFilter($filter);

        $filterResult = User::filter($modelFilter);

        $users = $filterResult->getData()->toArray();

        foreach ($users as $user) {
            $this->assertEmpty(Arr::except($user, ['phone']));
        }
    }
}
