<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use JsonException;
use Omalizadeh\QueryFilter\Exceptions\InvalidFilterException;

class ModelFilter
{
    protected Filter $filter;

    protected int $maxPaginationLimit = 200;

    public function __construct($filter = null)
    {
        if (!$filter instanceof Filter) {
            $this->filter = $this->createFilterFromRequest(request());
        } else {
            $this->setFilter($filter);
        }
    }

    /**
     * Model selectable attributes. these attributes can be selected alone.
     *
     * @return array
     */
    protected function selectableAttributes(): array
    {
        return [];
    }

    /**
     * Model sortable attributes.
     *
     * @return array
     */
    protected function sortableAttributes(): array
    {
        return [];
    }

    /**
     * Model summable attributes.
     *
     * @return array
     */
    protected function summableAttributes(): array
    {
        return [];
    }

    /**
     * Model filterable attributes.
     *
     * @return array
     */
    protected function filterableAttributes(): array
    {
        return [];
    }

    /**
     * Attributes on relations that can be filtered.
     *
     * 'relation_name' => [
     *      'filter_key' => 'db_column_name',
     *  ],
     * 'relation_name' => [
     *      'filter_key_and_db_column_name',
     *  ],
     *
     * @return array
     */
    protected function filterableRelations(): array
    {
        return [];
    }

    /**
     * Relations data that can be requested with model objects.
     *
     * @return array
     */
    protected function loadableRelations(): array
    {
        return [];
    }

    public function getMaxPaginationLimit(): int
    {
        return $this->maxPaginationLimit;
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
        return in_array($attribute, $this->selectableAttributes(), true);
    }

    public function hasFilterableAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->filterableAttributes(), true);
    }

    public function hasFilterableRelation(string $relationAttribute)
    {
        foreach ($this->filterableRelations() as $relationName => $filterableRelationAttributes) {
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
        return in_array($attribute, $this->sortableAttributes(), true);
    }

    public function hasSummableAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->summableAttributes(), true);
    }

    public function hasLoadableRelation(string $relation): bool
    {
        return in_array($relation, $this->loadableRelations(), true);
    }

    /**
     * @throws Exceptions\InvalidSortException
     * @throws Exceptions\InvalidRelationException
     * @throws InvalidFilterException
     * @throws Exceptions\InvalidSelectedAttributeException
     * @throws Exceptions\InvalidSumException
     */
    private function createFilterFromRequest(Request $request): Filter
    {
        try {
            $requestData = json_decode(
                $request->input('q', '{}'),
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

        $relations = Arr::get($requestData, 'withs');
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
