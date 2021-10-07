<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class QueryFilterResult
{
    protected Builder $builder;
    protected int $count;
    protected array $sums;

    public function __construct(Builder $builder, int $count, array $sums = [])
    {
        $this->builder = $builder;
        $this->count = $count;
        $this->sums = $sums;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getSums(): array
    {
        return $this->sums;
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    public function getData(): Collection
    {
        return $this->builder->get();
    }
}
