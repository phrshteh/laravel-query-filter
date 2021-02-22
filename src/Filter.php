<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use JsonException;
use Omalizadeh\QueryFilter\Exceptions\InvalidFilterException;

class Filter extends QueryFilter
{
    protected $request;
    protected $filterableAttributes = [];
    protected $sortableAttributes = [];
    protected $filterableRelations = [];
    protected $summableAttributes = [];
    protected $maxPaginationLimit = 500;
    protected $hasFiltersWithoutPagination = true;

    /**
     * PostFilter constructor.
     *
     * @param  Request  $request
     *
     * @throws \JsonException
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setParameters();
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @throws \JsonException
     */
    protected function setParameters(): void
    {
        try {
            $requestData = json_decode(
                $this->request->get('filter', '{}'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $ex) {
            throw new InvalidFilterException('cannot parse json filter. check json filter structure.');
        }
        $sortData = Arr::get($requestData, 'sort', []);
        if (!empty($sortData)) {
            $this->setSortData($sortData);
        }
        $page = Arr::get($requestData, 'page', []);
        if (!empty($page)) {
            $this->setPage($page);
        }
        $filters = Arr::get($requestData, 'filters', []);
        if (!empty($filters)) {
            $this->setFilters($filters);
        }
        $sums = Arr::get($requestData, 'sum', []);
        if (!empty($sums)) {
            $this->setSumFields($sums);
        }
    }

    /**
     * @param $builder
     *
     * @return array
     */
    public function apply($builder): array
    {
        $entries = $builder;
        if ($this->hasFilter()) {
            $entries = $this->applyFilters($builder);
        }
        if ($this->hasSum()) {
            $sum = $this->sum($entries);
        }
        if ($this->hasSort()) {
            $entries = $this->sort($entries);
        }
        $count = $entries->count();
        if ($this->hasPage()) {
            if ($this->getLimit() > $this->getMaxPaginationLimit()) {
                throw new InvalidFilterException('pagination limit value is out of range. max valid value: ' . $this->getMaxPaginationLimit());
            }
            $entries = $entries->limit($this->getLimit());
            $entries = $entries->offset($this->getOffset());
        } elseif (!$this->canFilterWithoutPagination()) {
            throw new InvalidFilterException('cannot filter without pagination.');
        }
        return array($entries, $count, $sum ?? []);
    }

    /**
     * @param  Builder  $entries
     *
     * @return Builder
     */
    protected function applyFilters(Builder $entries): Builder
    {
        foreach ($this->getFilters() as $filters) {
            $entries = $this->applyFilter($filters, $entries);
        }
        return $entries;
    }

    /**
     * @param $filters
     * @param $entries
     *
     * @return Builder
     */
    protected function applyFilter($filters, $entries): Builder
    {
        return $entries->where(function ($query) use ($filters) {
            foreach ($filters as $filterKey => $item) {
                $item = $this->sanitizeFilter($item);
                if (!$this->filterRelations($query, $item, $filterKey === 0)) {
                    if ($this->hasFilterableAttribute($item->field)) {
                        if ($filterKey === 0) {
                            $this->where($query, $item);
                        } else {
                            $this->orWhere($query, $item);
                        }
                    } else {
                        continue;
                    }
                }
            }
        });
    }

    /**
     * @param $query
     * @param $item
     *
     * @return Builder
     */
    protected function where($query, $item): Builder
    {
        if ($this->isWhereNull($item)) {
            return $this->whereNull($query, $item->field);
        }
        if ($this->isWhereNotNull($item)) {
            return $this->whereNotNull($query, $item->field);
        }
        if ($this->isWhereIn($item)) {
            return $this->whereIn($query, $item);
        }
        if ($this->isWhereNotIn($item)) {
            return $this->whereNotIn($query, $item);
        }
        return $query->where($item->field, $item->op, $item->value);
    }

    /**
     * @param $query
     * @param $item
     *
     * @return mixed
     */
    protected function orWhere($query, $item)
    {
        if ($this->isWhereNull($item)) {
            return $this->whereNull($query, $item->field, true);
        }
        if ($this->isWhereNotNull($item)) {
            return $this->whereNotNull($query, $item->field, true);
        }
        if ($this->isWhereIn($item)) {
            return $this->whereIn($query, $item, true);
        }
        if ($this->isWhereNotIn($item)) {
            return $this->whereNotIn($query, $item, true);
        }
        return $query->orWhere($item->field, $item->op, $item->value);
    }

    /**
     * @param $query
     * @param $item
     *
     * @return mixed
     */
    protected function whereIn($query, $item, bool $orCondition = false)
    {
        if ($orCondition) {
            return $query->orWhereIn($item->field, $item->value);
        } else {
            return $query->whereIn($item->field, $item->value);
        }
    }

    /**
     * @param $query
     * @param $item
     *
     * @return mixed
     */
    protected function whereNotIn($query, $item, bool $orCondition = false)
    {
        if ($orCondition) {
            return $query->orWhereNotIn($item->field, $item->value);
        } else {
            return $query->whereNotIn($item->field, $item->value);
        }
    }

    /**
     * @param $query
     * @param $columns
     * @param  bool  $orCondition
     *
     * @return mixed
     */
    protected function whereNull($query, $columns, bool $orCondition = false)
    {
        if ($orCondition) {
            return $query->orWhereNull($columns);
        } else {
            return $query->whereNull($columns);
        }
    }

    /**
     * @param $query
     * @param $columns
     * @param  bool  $orCondition
     * @param  false  $not
     *
     * @return mixed
     */
    protected function whereNotNull($query, $columns, bool $orCondition = false)
    {
        if ($orCondition) {
            return $query->orWhereNotNull($columns);
        } else {
            return $query->whereNotNull($columns);
        }
    }

    /**
     * relations = [
     *  'relation_name' => [
     *      'destination_key' => 'origin_key'
     *   ]
     * ]
     *
     * @param $query
     * @param $item
     * @param  bool  $isWhere
     *
     * @return mixed
     */
    protected function filterRelations(&$query, $item, bool $firstKey = true)
    {
        if (!$this->hasAnyFilterableRelation()) {
            return false;
        }
        foreach ($this->filterableRelations as $relationName => $params) {
            $relationKey = $this->hasFilterableRelation($params, $item);
            if ($relationKey !== false) {
                $item = $this->setFilterRelationKey($item, $relationKey);
                $query = $this->filterRelation(
                    $query,
                    $item,
                    $relationName,
                    !$firstKey
                );
                return true;
            }
        }
        return false;
    }

    /**
     * @param $relationProperties
     * @param $filter
     *
     * @return false|int|string
     */
    protected function hasFilterableRelation($relationProperties, $filter)
    {
        if (empty($relationProperties)) {
            return false;
        }
        if (!is_array($relationProperties)) {
            $relationProperties = [$relationProperties];
        }
        return $relationProperties[$filter->field] ??
            array_search($filter->field, $relationProperties, true);
    }

    /**
     * @param $item  'filterObject'
     * @param $keyName
     *
     * @return object $filter
     */
    protected function setFilterRelationKey($item, $keyName)
    {
        if (!empty($keyName) and is_string($keyName)) {
            $item->field = $keyName;
        }
        return $item;
    }

    /**
     * @param $entries
     * @param $filter
     * @param $relation
     * @param $orCondition
     *
     * @return Builder
     */
    protected function filterRelation($entries, $filter, $relation, bool $orCondition = false): Builder
    {
        if ($orCondition) {
            return $entries->orWhereHas($relation, function ($query) use ($filter) {
                $this->where($query, $filter);
            });
        } else {
            return $entries->whereHas($relation, function ($query) use ($filter) {
                $this->where($query, $filter);
            });
        }
    }

    /**
     * @param $entries
     *
     * @return Builder
     */
    protected function sort($entries): Builder
    {
        foreach ($this->getSortData() as $sort) {
            $field = $sort->field;
            $dir = $sort->dir;
            if ($this->hasSortableAttribute($field)) {
                $entries = $entries->orderBy($field, $dir);
            }
        }
        return $entries;
    }

    protected function sum($entries): array
    {
        $sum = array();
        foreach ($this->getSumFields() as $sumField) {
            if ($this->hasSummableAttribute($sumField)) {
                $sum[$sumField] = $entries->sum($sumField);
            }
        }
        return $sum;
    }

    /**
     * @param  object  $filter
     *
     * @return object $filter
     */
    protected function sanitizeFilter(object $filter)
    {
        if ($this->isValidOperator($filter->op)) {
            if (is_array($filter->value) and !in_array($filter->op, ['in', 'not'], true)) {
                if ($filter->op === '=') {
                    $filter->op = 'in';
                } else {
                    $filter->op = 'not';
                }
            } elseif ($filter->op === 'like' or $filter->op === 'not like') {
                $filter->value = '%' . $filter->value . '%';
            } elseif ($filter->op === 'is' and $filter->value !== null) {
                $filter->op = '=';
            } elseif ($filter->op === '=' and $filter->value === null) {
                $filter->op = 'is';
            } elseif (($filter->op === '!=' or $filter->op === '<>') and $filter->value === null) {
                $filter->op = 'not';
            }
            return $filter;
        } else {
            throw new InvalidFilterException('filter op is invalid. unknown op: ' . $filter->op);
        }
    }

    protected function getMaxPaginationLimit()
    {
        return $this->maxPaginationLimit;
    }

    protected function canFilterWithoutPagination()
    {
        return (bool) $this->hasFiltersWithoutPagination;
    }

    /**
     * @param $item
     *
     * @return bool
     */
    protected function isWhereIn($item): bool
    {
        return ($item->op === 'in' and is_array($item->value));
    }

    /**
     * @param $item
     *
     * @return bool
     */
    protected function isWhereNotIn($item): bool
    {
        return ($item->op === 'not' and is_array($item->value));
    }

    /**
     * @param $item
     *
     * @return bool
     */
    protected function isWhereNull($item): bool
    {
        return ($item->op === 'is' and $item->value === null);
    }

    /**
     * @param $item
     *
     * @return bool
     */
    protected function isWhereNotNull($item): bool
    {
        return ($item->op === 'not' and $item->value === null);
    }

    /**
     * @return bool
     */
    public function hasFilter(): bool
    {
        return !empty($this->filters);
    }

    /**
     * @return bool
     */
    public function hasPage(): bool
    {
        return $this->hasOffset() and $this->hasLimit();
    }

    /**
     * @return bool
     */
    public function hasSort(): bool
    {
        return !empty($this->sortData);
    }

    /**
     * @return bool
     */
    public function hasSum(): bool
    {
        return !empty($this->sumFields);
    }

    /**
     * @return bool
     */
    protected function hasLimit(): bool
    {
        return !empty($this->limit);
    }

    /**
     * @return bool
     */
    protected function hasOffset(): bool
    {
        return isset($this->offset);
    }

    /**
     * @return bool
     */
    protected function hasAnyFilterableRelation(): bool
    {
        return !empty($this->filterableRelations);
    }

    /**
     * @return bool
     */
    protected function hasFilterableAttribute($fieldName): bool
    {
        return !empty($this->filterableAttributes)
            and in_array($fieldName, $this->filterableAttributes, true);
    }

    /**
     * @return bool
     */
    protected function hasSortableAttribute($fieldName): bool
    {
        return !empty($this->sortableAttributes)
            and in_array($fieldName, $this->sortableAttributes, true);
    }

    /**
     * @return bool
     */
    protected function hasSummableAttribute($fieldName): bool
    {
        return !empty($this->summableAttributes)
            and in_array($fieldName, $this->summableAttributes, true);
    }

    /**
     * @return bool
     */
    protected function isValidOperator($op): bool
    {
        $operators = [
            '=',
            '>',
            '>=',
            '<',
            '<=',
            '!=',
            '<>',
            'like',
            'not like',
            'is',
            'not',
            'in'
        ];
        return in_array($op, $operators, true);
    }
}
