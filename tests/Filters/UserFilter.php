<?php

namespace Omalizadeh\QueryFilter\Tests\Filters;

use Omalizadeh\QueryFilter\Filter;
use Illuminate\Http\Request;

class UserFilter extends Filter
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->sortableAttributes = [
            'id',
            'created_at',
            'updated_at'
        ];
        $this->filterableAttributes = [
            'id',
            'gender',
            'phone',
            'first_name',
            'is_active'
        ];
        $this->filterableRelations = [];
        $this->loadableRelations = [];
        $this->summableAttributes = [];

        // Max valid limit for pagination (records per page)
        $this->maxPaginationLimit = 1000;

        // Data can be accessed without pagination
        $this->hasFiltersWithoutPagination = true;
    }
}
