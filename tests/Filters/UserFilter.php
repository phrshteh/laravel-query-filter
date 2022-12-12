<?php

namespace Omalizadeh\QueryFilter\Tests\Filters;

use Illuminate\Database\Eloquent\Builder;
use Omalizadeh\QueryFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    protected int $maxPaginationLimit = 10;

    protected function selectableAttributes(): array
    {
        return [
            'id',
            'phone',
        ];
    }

    protected function sortableAttributes(): array
    {
        return [
            'id',
            'created_at',
            'updated_at',
        ];
    }

    protected function summableAttributes(): array
    {
        return [
            'paid_amount',
        ];
    }

    protected function filterableAttributes(): array
    {
        return [
            'id',
            'phone',
            'paid_amount',
            'is_active',
        ];
    }

    protected function filterableRelations(): array
    {
        return [
            'profile' => [
                'gender',
                'first_name',
            ],
            'posts' => [
                'post_body' => 'body',
            ],
        ];
    }

    protected function filterableRelationsCount(): array
    {
        return [
            'posts' => [
                'posts_count',
                'bye_posts_count' => function (Builder $query) {
                    return $query->where('body', 'like', '%bye%');
                },
            ],
        ];
    }

    protected function loadableRelations(): array
    {
        return [];
    }
}
