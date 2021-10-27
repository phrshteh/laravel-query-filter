<?php

namespace Omalizadeh\QueryFilter\Tests\Filters;

use Omalizadeh\QueryFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    protected function getSortableAttributes(): array
    {
        return [
            'id',
            'created_at',
            'updated_at'
        ];
    }

    protected function getSummableAttributes(): array
    {
        return [];
    }

    protected function getFilterableAttributes(): array
    {
        return [
            'id',
            'phone',
            'is_active'
        ];
    }

    protected function getFilterableRelations(): array
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

    protected function getLoadableRelations(): array
    {
        return [];
    }
}
