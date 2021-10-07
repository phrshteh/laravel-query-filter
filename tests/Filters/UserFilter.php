<?php

namespace Omalizadeh\QueryFilter\Tests\Filters;

use Omalizadeh\QueryFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    public function getSortableAttributes(): array
    {
        return [
            'id',
            'created_at',
            'updated_at'
        ];
    }

    public function getSummableAttributes(): array
    {
        return [];
    }

    public function getFilterableAttributes(): array
    {
        return [
            'id',
            'gender',
            'phone',
            'first_name',
            'is_active'
        ];
    }

    public function getFilterableRelations(): array
    {
        return [];
    }

    public function getLoadableRelations(): array
    {
        return [];
    }
}
