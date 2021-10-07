<?php

namespace Omalizadeh\QueryFilter\Traits;

use Omalizadeh\QueryFilter\ModelFilter;
use Omalizadeh\QueryFilter\QueryFilter;
use Omalizadeh\QueryFilter\QueryFilterResult;

trait HasFilter
{
    public static function filter(ModelFilter $modelFilter): QueryFilterResult
    {
        return (new QueryFilter(static::query(), $modelFilter))->applyFilter();
    }
}
