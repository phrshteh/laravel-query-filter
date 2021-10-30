<?php

namespace Omalizadeh\QueryFilter\Tests\Filters;

use Omalizadeh\QueryFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    protected function selectableAttributes(): array
    {
        return [
            'id',
            'phone'
        ];
    }

    protected function sortableAttributes(): array
    {
        return [
            'id',
            'created_at',
            'updated_at'
        ];
    }

    protected function summableAttributes(): array
    {
        return [];
    }

    protected function filterableAttributes(): array
    {
        return [
            'id',
            'phone',
            'is_active'
        ];
    }

    protected function filterableRelations(): array
    {
        return [
            'profile' => [
                'gender',
                'first_name'
            ],
            'posts' => [
                'post_body' => 'body'
            ]
        ];
    }

    protected function loadableRelations(): array
    {
        return [];
    }
}
