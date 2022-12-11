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
            $this->setFilter($this->createFilterFromRequest(request()));
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
     * Relations count that can be filtered.
     *
     * @return array
     */
    protected function filterableRelationsCount(): array
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
            if (isset($filterableRelationAttributes[$relationAttribute])) {
                return [$relationName, $filterableRelationAttributes[$relationAttribute]];
            }

            if (
                is_array($filterableRelationAttributes)
                && array_is_list($filterableRelationAttributes)
                && in_array($relationAttribute, $filterableRelationAttributes, true) !== false
            ) {
                return [$relationName, $relationAttribute];
            }

            if (!is_array($filterableRelationAttributes) && $filterableRelationAttributes === $relationAttribute) {
                return [$relationName, $relationAttribute];
            }
        }

        return false;
    }

    public function hasFilterableRelationCount(string $relationCountAttribute)
    {
        foreach ($this->filterableRelationsCount() as $relationName => $filterableRelationAttributes) {
            if (isset($filterableRelationAttributes[$relationCountAttribute]) && is_string($filterableRelationAttributes[$relationCountAttribute])) {
                return $filterableRelationAttributes[$relationCountAttribute];
            }

            if (
                is_array($filterableRelationAttributes)
                && array_is_list($filterableRelationAttributes)
                && in_array($relationCountAttribute, $filterableRelationAttributes, true) !== false
            ) {
                return $relationCountAttribute;
            }

            if (!is_array($filterableRelationAttributes) && $filterableRelationAttributes === $relationCountAttribute) {
                return $relationCountAttribute;
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
        $filterArray = $this->parseJsonFilterFromRequest($request);

        $filter = new Filter();

        $selectedFields = Arr::get($filterArray, 'fields');

        if (!empty($selectedFields)) {
            $filter->setSelectedAttributes($selectedFields);
        }

        $sorts = Arr::has($filterArray, 'sort') ? Arr::get($filterArray, 'sort') : Arr::get($filterArray, 'sorts');

        if (!empty($sorts)) {
            $filter->setSorts($sorts);
        }

        $limit = Arr::get($filterArray, 'page.limit');

        if (!empty($limit)) {
            $filter->setLimit($limit);
        }

        $offset = Arr::get($filterArray, 'page.offset');

        if (!is_null($offset)) {
            $filter->setOffset($offset);
        }

        $filterGroups = Arr::get($filterArray, 'filters');

        if (!empty($filterGroups)) {
            $filter->setFilterGroups($filterGroups);
        }

        $relations = Arr::has($filterArray, 'with') ? Arr::get($filterArray, 'with') : Arr::get($filterArray, 'withs');

        if (!empty($relations) && is_array($relations)) {
            $filter->setRelations($relations);
        }

        $sums = Arr::has($filterArray, 'sum') ? Arr::get($filterArray, 'sum') : Arr::get($filterArray, 'sums');

        if (!empty($sums) && is_array($sums)) {
            $filter->setSums($sums);
        }

        return $filter;
    }

    private function parseJsonFilterFromRequest(Request $request): ?array
    {
        try {
            if ($request->filled('filter')) {
                $json = $request->input('filter');
            } else {
                $json = $request->input('q', '{}');
            }

            return json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $ex) {
            throw new InvalidFilterException('Cannot parse json filter. check json structure.');
        }
    }
}
