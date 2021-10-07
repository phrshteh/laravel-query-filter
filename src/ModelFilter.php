<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use JsonException;
use Omalizadeh\QueryFilter\Exceptions\InvalidFilterException;

abstract class ModelFilter
{
    public int $maxPaginationLimit = 1000;

    protected Request $request;
    protected Filter $filter;

    /**
     * @throws Exceptions\InvalidRelationException
     * @throws Exceptions\InvalidSortException
     * @throws InvalidFilterException
     * @throws Exceptions\InvalidSumException
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->filter = $this->createFilterFromRequest();
    }

    abstract public function getSortableAttributes(): array;

    abstract public function getSummableAttributes(): array;

    abstract public function getFilterableAttributes(): array;

    abstract public function getFilterableRelations(): array;

    abstract public function getLoadableRelations(): array;

    public function getMaxPaginationLimit(): int
    {
        return $this->maxPaginationLimit;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getFilter(): Filter
    {
        return $this->filter;
    }

    public function setFilter(Filter $filter): ModelFilter
    {
        $this->filter = $filter;
        return $this;
    }

    public function hasFilterableAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->getFilterableAttributes(), true);
    }

    public function hasFilterableRelation(string $relationAttribute)
    {
        foreach ($this->getFilterableRelations() as $relationName => $filterableRelationAttributes) {
            if (!is_array($filterableRelationAttributes) && $filterableRelationAttributes === $relationAttribute) {
                return [$relationName, $relationAttribute];
            }
            if (isset($filterableRelationAttributes[$relationAttribute])) {
                return [$relationName, $filterableRelationAttributes[$relationAttribute]];
            }
            if (in_array($relationAttribute, $filterableRelationAttributes, true) !== false) {
                return [$relationName, $relationAttribute];
            }
        }

        return false;
    }

    public function hasSortableAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->getSortableAttributes(), true);
    }

    public function hasSummableAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->getSummableAttributes(), true);
    }

    public function hasLoadableRelation(string $relation): bool
    {
        return in_array($relation, $this->getLoadableRelations(), true);
    }

    /**
     * @throws Exceptions\InvalidSortException
     * @throws Exceptions\InvalidRelationException
     * @throws InvalidFilterException
     * @throws Exceptions\InvalidSumException
     */
    protected function createFilterFromRequest(): Filter
    {
        try {
            $requestData = json_decode(
                $this->getRequest()->input('filter', '{}'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $ex) {
            throw new InvalidFilterException('Cannot parse json filter. check json structure.');
        }

        $filter = new Filter();

        $sortData = Arr::get($requestData, 'sorts');
        if (!empty($sortData)) {
            $filter->setSorts($sortData);
        }

        $limit = Arr::get($requestData, 'page.limit');
        if (!empty($limit)) {
            $filter->setLimit($limit);
        }

        $offset = Arr::get($requestData, 'page.offset');
        if (!empty($offset)) {
            $filter->setLimit($offset);
        }

        $filterGroups = Arr::get($requestData, 'filters');
        if (!empty($filterGroups)) {
            $filter->setFilterGroups($filterGroups);
        }

        $relations = Arr::get($requestData, 'relations');
        if (!empty($relations)) {
            $filter->setRelations($relations);
        }

        $sums = Arr::get($requestData, 'sums');
        if (!empty($sums)) {
            $filter->setSums($sums);
        }

        return $filter;
    }
}
