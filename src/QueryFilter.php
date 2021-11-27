<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Database\Eloquent\Builder;

class QueryFilter
{
    protected Builder $builder;
    protected ModelFilter $modelFilter;

    public function __construct(Builder $builder, ModelFilter $modelFilter)
    {
        $this->builder = $builder;
        $this->modelFilter = $modelFilter;
    }

    /**
     * @return QueryFilterResult
     */
    public function applyFilter(): QueryFilterResult
    {
        if ($this->getFilter()->hasSelectedAttribute()) {
            $this->select();
        }

        if ($this->getFilter()->hasFilterGroup()) {
            $this->applyFilterGroups();
        }

        $totalCount = $this->getBuilder()->count();

        if ($this->getFilter()->hasSum()) {
            $sums = $this->sum();
        }

        if ($this->getFilter()->hasSort()) {
            $this->sort();
        }

        if ($this->getFilter()->hasRelations()) {
            $this->load();
        }

        $this->applyPagination();

        return new QueryFilterResult($this->getBuilder(), $totalCount, $sums ?? []);
    }

    protected function select(): void
    {
        $validAttributes = [];

        foreach ($this->getFilter()->getSelectedAttributes() as $attribute) {
            if ($this->getModelFilter()->hasSelectableAttribute($attribute)) {
                $validAttributes[] = $attribute;
            }
        }

        if (!empty($validAttributes)) {
            $this->getBuilder()->select($validAttributes);
        }
    }

    protected function applyFilterGroups(): void
    {
        foreach ($this->getFilter()->getFilterGroups() as $filterGroup) {
            $this->applyFilters($filterGroup);
        }
    }

    protected function applyFilters(array $filterGroup): void
    {
        $this->getBuilder()->where(function (Builder $query) use ($filterGroup) {
            foreach ($filterGroup as $filterKey => $filter) {
                if (
                    !$this->filterRelations($query, $filter, $filterKey === 0) &&
                    $this->getModelFilter()->hasFilterableAttribute($filter['field'])
                ) {
                    $filterKey === 0 ? $this->where($query, $filter) : $this->orWhere($query, $filter);
                }
            }
        });
    }

    protected function where(Builder $query, array $filter): Builder
    {
        if ($this->isWhereNull($filter)) {
            return $this->whereNull($query, $filter['field']);
        }
        if ($this->isWhereNotNull($filter)) {
            return $this->whereNotNull($query, $filter['field']);
        }
        if ($this->isWhereIn($filter)) {
            return $this->whereIn($query, $filter);
        }
        if ($this->isWhereNotIn($filter)) {
            return $this->whereNotIn($query, $filter);
        }

        return $query->where($filter['field'], $filter['op'], $filter['value']);
    }

    /**
     * @param  Builder  $query
     * @param  array  $filter
     * @return Builder
     */
    protected function orWhere(Builder $query, array $filter): Builder
    {
        if ($this->isWhereNull($filter)) {
            return $this->whereNull($query, $filter['field'], true);
        }
        if ($this->isWhereNotNull($filter)) {
            return $this->whereNotNull($query, $filter['field'], true);
        }
        if ($this->isWhereIn($filter)) {
            return $this->whereIn($query, $filter, true);
        }
        if ($this->isWhereNotIn($filter)) {
            return $this->whereNotIn($query, $filter, true);
        }

        return $query->orWhere($filter['field'], $filter['op'], $filter['value']);
    }

    /**
     * @param  Builder  $query
     * @param  array  $filter
     * @param  bool  $orCondition
     * @return Builder
     */
    protected function whereIn(Builder $query, array $filter, bool $orCondition = false): Builder
    {
        if ($orCondition) {
            return $query->orWhereIn($filter['field'], $filter['value']);
        }

        return $query->whereIn($filter['field'], $filter['value']);
    }

    /**
     * @param  Builder  $query
     * @param  array  $filter
     * @param  bool  $orCondition
     * @return Builder
     */
    protected function whereNotIn(Builder $query, array $filter, bool $orCondition = false): Builder
    {
        if ($orCondition) {
            return $query->orWhereNotIn($filter['field'], $filter['value']);
        }

        return $query->whereNotIn($filter['field'], $filter['value']);
    }

    /**
     * @param  Builder  $query
     * @param  string  $column
     * @param  bool  $orCondition
     * @return Builder
     */
    protected function whereNull(Builder $query, string $column, bool $orCondition = false): Builder
    {
        if ($orCondition) {
            return $query->orWhereNull($column);
        }

        return $query->whereNull($column);
    }

    /**
     * @param  Builder  $query
     * @param  string  $column
     * @param  bool  $orCondition
     * @return Builder
     */
    protected function whereNotNull(Builder $query, string $column, bool $orCondition = false): Builder
    {
        if ($orCondition) {
            return $query->orWhereNotNull($column);
        }

        return $query->whereNotNull($column);
    }

    protected function filterRelations(Builder $query, array $filter, bool $firstKey = true): bool
    {
        if (($relationInfo = $this->getModelFilter()->hasFilterableRelation($filter['field'])) !== false) {
            [$relationName, $relationAttribute] = $relationInfo;
            $filter['field'] = $relationAttribute;

            $this->filterRelation($query, $filter, $relationName, !$firstKey);

            return true;
        }

        return false;
    }

    /**
     * @param  Builder  $query
     * @param  array  $filter
     * @param  string  $relationName
     * @param  bool  $orCondition
     *
     * @return Builder
     */
    protected function filterRelation(
        Builder $query,
        array $filter,
        string $relationName,
        bool $orCondition = false
    ): Builder {
        if (isset($filter['has']) && $filter['has'] === false) {
            if ($orCondition) {
                return $query->orWhereDoesntHave($relationName, function ($query) use ($filter) {
                    $this->where($query, $filter);
                });
            }

            return $query->whereDoesntHave($relationName, function ($query) use ($filter) {
                $this->where($query, $filter);
            });
        }

        if ($orCondition) {
            return $query->orWhereHas($relationName, function ($query) use ($filter) {
                $this->where($query, $filter);
            });
        }

        return $query->whereHas($relationName, function ($query) use ($filter) {
            $this->where($query, $filter);
        });
    }

    protected function applyPagination(): Builder
    {
        if (!$this->getFilter()->hasLimit() || $this->getFilter()->getLimit() > $this->getModelFilter()->getMaxPaginationLimit()) {
            $this->getFilter()->setLimit($this->getModelFilter()->getMaxPaginationLimit());
        }

        if (!$this->getFilter()->hasOffset()) {
            $this->getFilter()->setOffset(0);
        }

        return $this->paginate();
    }

    protected function paginate(): Builder
    {
        return $this->getBuilder()->limit($this->getFilter()->getLimit())->offset($this->getFilter()->getOffset());
    }

    protected function load(): void
    {
        foreach ($this->getFilter()->getRelations() as $relation) {
            if ($this->getModelFilter()->hasLoadableRelation($relation)) {
                $this->getBuilder()->with($relation);
            }
        }
    }

    protected function sort(): void
    {
        foreach ($this->getFilter()->getSorts() as $sort) {
            if ($this->getModelFilter()->hasSortableAttribute($sort['field'])) {
                $this->getBuilder()->orderBy($sort['field'], $sort['dir']);
            }
        }
    }

    protected function sum(): array
    {
        $sum = array();

        foreach ($this->getFilter()->getSums() as $sumField) {
            if ($this->getModelFilter()->hasSummableAttribute($sumField)) {
                $sum[$sumField] = $this->getBuilder()->sum($sumField);
            }
        }

        return $sum;
    }

    /**
     * @param $filter
     *
     * @return bool
     */
    protected function isWhereIn($filter): bool
    {
        return ($filter['op'] === 'in' and is_array($filter['value']));
    }

    /**
     * @param $filter
     *
     * @return bool
     */
    protected function isWhereNotIn($filter): bool
    {
        return ($filter['op'] === 'not' and is_array($filter['value']));
    }

    /**
     * @param $filter
     *
     * @return bool
     */
    protected function isWhereNull($filter): bool
    {
        return ($filter['op'] === 'is' and $filter['value'] === null);
    }

    /**
     * @param $filter
     *
     * @return bool
     */
    protected function isWhereNotNull($filter): bool
    {
        return ($filter['op'] === 'not' and $filter['value'] === null);
    }

    protected function getFilter(): Filter
    {
        return $this->getModelFilter()->getFilter();
    }

    protected function getModelFilter(): ModelFilter
    {
        return $this->modelFilter;
    }

    protected function getBuilder(): Builder
    {
        return $this->builder;
    }
}
