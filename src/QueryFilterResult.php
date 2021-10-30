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

    /**
     * Total count of records based on filters.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Total sum of requested attributes based on filters.
     *
     * @return array
     */
    public function sums(): array
    {
        return $this->sums;
    }

    /**
     * Builder containing all filters & pagination.
     *
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this->builder;
    }

    /**
     * Execute query & return data.
     *
     * @return Collection
     */
    public function data(): Collection
    {
        return $this->builder->get();
    }
}
