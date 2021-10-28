<?php

namespace Omalizadeh\QueryFilter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Omalizadeh\QueryFilter\Filter;
use Omalizadeh\QueryFilter\Tests\Filters\UserFilter;
use Omalizadeh\QueryFilter\Tests\Models\User;

class RelationFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function hasRelationFilterTest(): void
    {
        $filter = new Filter();
        $filter->setFilterGroups([
            [
                [
                    'field' => 'gender',
                    'op' => '=',
                    'value' => false
                ]
            ]
        ]);
        $request = new Request([
            'q' => $filter->toJson()
        ]);
        $modelFilter = new UserFilter($request);
        $filterResult = User::filter($modelFilter);
        $users = $filterResult->getData();
        foreach ($users as $user) {
            $this->assertTrue($user->isFemale());
        }
    }

    /** @test */
    public function doesntHaveRelationFilterTest(): void
    {
        $filter = new Filter();
        $filter->setFilterGroups([
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
            'q' => $filter->toJson()
        ]);
        $modelFilter = new UserFilter($request);
        $filterResult = User::filter($modelFilter);
        $users = $filterResult->getData();
        foreach ($users as $user) {
            $this->assertFalse($user->posts()->where('body', 'like', '%hello%')->exists());
        }
    }
}
