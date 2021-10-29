<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use JsonException;
use Omalizadeh\QueryFilter\Exceptions\InvalidFilterException;
use Omalizadeh\QueryFilter\Exceptions\InvalidRelationException;
use Omalizadeh\QueryFilter\Exceptions\InvalidSelectedAttributeException;
use Omalizadeh\QueryFilter\Exceptions\InvalidSortException;
use Omalizadeh\QueryFilter\Exceptions\InvalidSumException;

class Filter implements Jsonable
{
    protected array $selectedAttributes = [];
    protected array $filterGroups = [];
    protected array $sorts = [];
    protected array $sums = [];
    protected array $relations = [];
    protected ?int $offset = null;
    protected ?int $limit = null;

    /**
     * @throws InvalidSortException
     * @throws InvalidRelationException
     * @throws InvalidSumException
     * @throws InvalidSelectedAttributeException
     * @throws InvalidFilterException
     */
    public function __construct(
        array $selectedAttributes = [],
        array $filterGroups = [],
        array $sorts = [],
        array $sums = [],
        array $relations = [],
        ?int $offset = null,
        ?int $limit = null
    ) {
        $this->setSelectedAttributes($selectedAttributes);
        $this->setFilterGroups($filterGroups);
        $this->setSorts($sorts);
        $this->setSums($sums);
        $this->setRelations($relations);
        $this->setOffset($offset);
        $this->setLimit($limit);
    }

    /**
     * @throws InvalidSelectedAttributeException
     */
    public function setSelectedAttributes(array $attributes): Filter
    {
        foreach ($attributes as $attribute) {
            if (!is_string($attribute)) {
                throw new InvalidSelectedAttributeException('Selected attribute names must be string.');
            }

            $this->selectAttribute($attribute);
        }

        return $this;
    }

    public function selectAttribute(string $attribute): Filter
    {
        $this->selectedAttributes[] = $attribute;

        return $this;
    }

    /**
     * @throws InvalidFilterException
     */
    public function setFilterGroups(array $filterGroups): Filter
    {
        foreach ($filterGroups as $filterGroup) {
            foreach ($filterGroup as &$filter) {
                $filter = $this->validateFilter($filter);
            }

            $this->filterGroups[] = $filterGroup;
        }

        return $this;
    }

    /**
     * @throws InvalidFilterException
     */
    public function addFilterGroup(array $filterGroup): Filter
    {
        foreach ($filterGroup as &$filter) {
            $filter = $this->validateFilter($filter);
        }

        $this->filterGroups[] = $filterGroup;

        return $this;
    }

    /**
     * @throws InvalidFilterException
     */
    public function addFilter(string $attribute, $op = null, $value = null, bool $has = true): Filter
    {
        if (func_num_args() === 2) {
            $value = $op;
            $op = '=';
        } elseif (is_null($op) && func_num_args() === 1) {
            $op = '=';
        } else {
            $this->validateOperator($op);
        }

        $filter = $this->validateFilter([
            'field' => $attribute,
            'op' => $op,
            'value' => $value,
            'has' => $has
        ]);

        $this->filterGroups[] = [$filter];

        return $this;
    }

    public function setSorts(array $sorts): Filter
    {
        foreach ($sorts as $sort) {
            if (!is_array($sort)) {
                throw new InvalidSortException('Sort must be an array containing field and dir.');
            }

            if (!isset($sort['field'], $sort['dir'])) {
                throw new InvalidSortException('Field or dir key not found in sort array.');
            }

            $this->addSort($sort['field'], $sort['dir']);
        }

        return $this;
    }

    public function setPage(int $offset, int $limit): Filter
    {
        $this->setOffset($offset);
        $this->setLimit($limit);

        return $this;
    }

    public function setSums(array $sums): Filter
    {
        foreach ($sums as $sum) {
            if (!is_string($sum) || empty($sum)) {
                throw new InvalidSumException('Sums array must contain only non-empty strings of field names.');
            }

            $this->addSum($sum);
        }

        return $this;
    }

    public function setRelations(array $relations): Filter
    {
        foreach ($relations as $relation) {
            if (!is_string($relation) || empty($relation)) {
                throw new InvalidRelationException('Relations array must contain only non-empty strings.');
            }

            $this->addRelation($relation);
        }

        return $this;
    }

    public function addSort(string $attribute, string $dir = 'asc'): Filter
    {
        $this->sorts[] = [
            'field' => $attribute,
            'dir' => strtolower($dir) === 'desc' ? 'desc' : 'asc'
        ];

        return $this;
    }

    public function addSum(string $attribute): Filter
    {
        $this->sums[] = $attribute;

        return $this;
    }

    public function addRelation(string $relation): Filter
    {
        $this->relations[] = $relation;

        return $this;
    }

    public function setLimit(?int $limit): Filter
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(?int $offset): Filter
    {
        $this->offset = $offset;

        return $this;
    }

    public function getFilteredAttributes(): array
    {
        $filterGroups = $this->getFilterGroups();
        $filteredAttributes = [];

        foreach ($filterGroups as $filter) {
            $filteredAttributes[] = collect($filter)->pluck('field')->toArray();
        }

        return array_unique(Arr::flatten($filteredAttributes));
    }

    public function getSelectedAttributes(): array
    {
        return $this->selectedAttributes;
    }

    /**
     * @return array
     */
    public function getFilterGroups(): array
    {
        return $this->filterGroups;
    }

    /**
     * @return array
     */
    public function getSums(): array
    {
        return $this->sums;
    }

    public function getPage(): array
    {
        return [
            'limit' => $this->getLimit(),
            'offset' => $this->getOffset()
        ];
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getSorts(): array
    {
        return $this->sorts;
    }

    public function hasSelectedAttribute(): bool
    {
        return !empty($this->getSelectedAttributes());
    }

    /**
     * @return bool
     */
    public function hasFilterGroup(): bool
    {
        return !empty($this->getFilterGroups());
    }

    /**
     * @return bool
     */
    public function hasRelations(): bool
    {
        return !empty($this->getRelations());
    }

    /**
     * @return bool
     */
    public function hasPage(): bool
    {
        return $this->hasOffset() && $this->hasLimit();
    }

    /**
     * @return bool
     */
    public function hasSort(): bool
    {
        return !empty($this->getSorts());
    }

    /**
     * @return bool
     */
    public function hasSum(): bool
    {
        return !empty($this->getSums());
    }

    /**
     * @return bool
     */
    public function hasLimit(): bool
    {
        return !is_null($this->getLimit());
    }

    /**
     * @return bool
     */
    public function hasOffset(): bool
    {
        return !is_null($this->getOffset());
    }

    /**
     * Convert filter to JSON string.
     *
     * @param  int  $options
     * @return string
     * @throws JsonException
     */
    public function toJson($options = 0): string
    {
        $data = [];

        if (!empty($this->getSelectedAttributes())) {
            $data['fields'] = $this->getSelectedAttributes();
        }

        if (!empty($this->getFilterGroups())) {
            $data['filters'] = $this->getFilterGroups();
        }

        if (!empty($this->getSorts())) {
            $data['sorts'] = $this->getSorts();
        }

        if (!empty($this->getRelations())) {
            $data['withs'] = $this->getRelations();
        }

        if (!empty($this->getSums())) {
            $data['sums'] = $this->getSums();
        }

        if (!empty($this->getPage())) {
            $data['page'] = $this->getPage();
        }

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | $options);
    }

    /**
     * @throws InvalidFilterException
     */
    protected function validateFilter(array $filter): array
    {
        if (!isset($filter['field']) || !is_string($filter['field'])) {
            throw new InvalidFilterException('Filter field key not found or is invalid.');
        }

        if (!isset($filter['op']) || !is_string($filter['op'])) {
            throw new InvalidFilterException('Filter op key not found or is invalid.');
        }

        if (!array_key_exists('value', $filter)) {
            throw new InvalidFilterException('Value not found in filter.');
        }

        $this->validateOperator($filter['op']);

        return $this->sanitizeFilter($filter);
    }

    protected function sanitizeFilter(array $filter): array
    {
        if (is_array($filter['value']) && !in_array($filter['op'], ['in', 'not'], true)) {
            $filter['op'] = ($filter['op'] === '=') ? 'in' : 'not';
        } elseif ($filter['op'] === 'like' || $filter['op'] === 'not like') {
            $filter['value'] = '%'.$filter['value'].'%';
        } elseif ($filter['value'] === null) {
            $filter['op'] = ($filter['op'] === '!=' || $filter['op'] === '<>' || $filter['op'] === 'not') ? 'not' : 'is';
        }

        return $filter;
    }

    /**
     * @throws InvalidFilterException
     */
    protected function validateOperator(string $op): void
    {
        if (!in_array($op, $this->getValidOperators(), true)) {
            throw new InvalidFilterException("Invalid operator in filter: $op");
        }
    }

    protected function getValidOperators(): array
    {
        return [
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
    }
}
