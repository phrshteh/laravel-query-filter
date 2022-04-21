<?php

namespace Omalizadeh\QueryFilter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Omalizadeh\QueryFilter\Filter;
use Omalizadeh\QueryFilter\Tests\Filters\UserFilter;
use Omalizadeh\QueryFilter\Tests\Models\User;

class RelationFilterTest extends TestCase
{
    use RefreshDatabase;

    public function testHasRelationFilter(): void
    {
        $filter = new Filter();

        $filter->setFilterGroups([
            [
                [
                    'field' => 'gender',
                    'op' => '=',
                    'value' => false,
                ],
            ],
        ]);

        $filterResult = User::filter(new UserFilter($filter));

        foreach ($filterResult->data() as $user) {
            $this->assertTrue($user->isFemale());
        }
    }

    public function testDoesntHaveRelationFilter(): void
    {
        $filter = new Filter();

        $filter->setFilterGroups([
            [
                [
                    'field' => 'post_body',
                    'op' => 'like',
                    'value' => 'hello',
                    'has' => false,
                ],
            ],
        ]);

        $filterResult = User::filter(new UserFilter($filter));

        foreach ($filterResult->data() as $user) {
            $this->assertFalse($user->posts()->where('body', 'like', '%hello%')->exists());
        }
    }
}
