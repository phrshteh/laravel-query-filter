<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use JsonException;
use Omalizadeh\QueryFilter\Exceptions\InvalidFilterException;

abstract class ModelFilter
{
    protected int $maxPaginationLimit = 1000;
    protected Request $request;
    protected Filter $filter;

    /**
     * @throws Exceptions\InvalidRelationException
     * @throws Exceptions\InvalidSortException
     * @throws InvalidFilterException
     * @throws Exceptions\InvalidSumException
     * @throws Exceptions\InvalidSelectedAttributeException
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->filter = $this->createFilterFromRequest();
    }

    abstract protected function getSelectableAttributes(): array;

    abstract protected function getSortableAttributes(): array;

    abstract protected function getSummableAttributes(): array;

    abstract protected function getFilterableAttributes(): array;

    abstract protected function getFilterableRelations(): array;

    abstract protected function getLoadableRelations(): array;

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

    public function hasSelectableAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->getSelectableAttributes(), true);
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
     * @throws Exceptions\InvalidSelectedAttributeException
     * @throws Exceptions\InvalidSumException
     */
    private function createFilterFromRequest(): Filter
    {
        try {
            $requestData = json_decode(
                $this->getRequest()->input('q', '{}'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $ex) {
            throw new InvalidFilterException('Cannot parse json filter. check json structure.');
        }

        $filter = new Filter();

        $selectedFields = Arr::get($requestData, 'fields');
        if (!empty($selectedFields)) {
            $filter->setSelectedAttributes($selectedFields);
        }

        $sorts = Arr::get($requestData, 'sorts');
        if (!empty($sorts)) {
            $filter->setSorts($sorts);
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

        $relations = Arr::get($requestData, 'with');
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
