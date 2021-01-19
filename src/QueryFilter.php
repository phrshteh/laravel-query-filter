<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use RuntimeException;

class QueryFilter implements Jsonable
{
    protected $filters = [];
    protected $sortData = [];
    protected $sumFields = [];
    protected $offset = null;
    protected $limit = null;

    /**
     * @param  array  $filters
     *
     * @return $this
     */
    public function addFilter(array $filters): QueryFilter
    {
        $filters = $this->prepareFilter($filters);
        $this->filters[] = $filters;
        return $this;
    }

    public function addMagicFilter(array $filter)
    {
        $key = key($filter);
        $filter = ['field' => $key, 'op' => '=', 'value' => $filter[$key]];
        return $this->addFilter([$filter]);
    }

    /**
     * @param $filters
     *
     * @return mixed
     */
    protected function prepareFilter($filters)
    {
        $keys = ['field', 'op', 'value'];
        $constFilters = $filters;
        foreach ($filters as &$filter) {
            $filter = Arr::only((array)$filter, $keys);
            if (count($filter) !== 3) {
                throw new RuntimeException('filter not valid.' . "\n"
                    . 'filter must have these keys: ' . join(', ', $keys) .
                    ".\n\r while " . print_r($constFilters, true));
            }
            $filter = (object)$filter;
        }
        return $filters;
    }

    /**
     * @param $field
     * @param $dir
     *
     * @return QueryFilter
     */
    public function orderBy($field, $dir)
    {
        return $this->addOrderBy([
            'field' => $field,
            'dir'   => $dir
        ]);
    }

    /**
     * @param $sortData
     *
     * @return $this
     */
    public function addOrderBy(array $sortData): QueryFilter
    {
        $sortData = $this->prepareOrderBy($sortData);
        $this->sortData[] = $sortData;
        return $this;
    }

    /**
     * @param $sortData
     *
     * @return array
     */
    protected function prepareOrderBy($sortData)
    {
        $constSortData = $sortData;
        $sortData = Arr::only($sortData, ['field', 'dir']);
        if (count($sortData) !== 2) {
            throw new RuntimeException('order data not valid.' . "\n" . print_r($constSortData, true));
        }
        return (object) $sortData;
    }

    /**
     * @return array
     */
    public function getSortData(): array
    {
        return $this->sortData;
    }

    /**
     * @param  array  $sortDataList
     *
     * @return QueryFilter
     */
    public function setSortData(array $sortDataList): QueryFilter
    {
        foreach ($sortDataList as $sortData) {
            $this->addOrderBy($sortData);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getPage(): array
    {
        return [
            'limit' => $this->limit,
            'offset' => $this->offset
        ];
    }

    /**
     * @param  array  $page
     *
     * @return QueryFilter
     */
    public function setPage(array $page): QueryFilter
    {
        if (!empty($page['limit'])) {
            $this->setLimit($page['limit']);
        }
        if (isset($page['offset'])) {
            $this->setOffset($page['offset']);
        }
        return $this;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param $offset
     *
     * @return QueryFilter
     */
    public function setOffset(int $offset): QueryFilter
    {
        $this->offset = $offset;
        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param $limit
     *
     * @return QueryFilter
     */
    public function setLimit(int $limit): QueryFilter
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param  array  $filtersList
     *
     * @return QueryFilter
     */
    public function setFilters(array $filtersList): QueryFilter
    {
        foreach ($filtersList as $filters) {
            $this->addFilter($filters);
        }
        return $this;
    }

    public function getSumFields(): array
    {
        return $this->sumFields;
    }

    public function setSumFields(array $sumFieldsList): QueryFilter
    {
        foreach ($sumFieldsList as $sumField) {
            $this->addSumField($sumField);
        }
        return $this;
    }

    public function addSumField($sumField)
    {
        if (is_string($sumField)) {
            return $this->sumFields[] = $sumField;
        } else {
            throw new RuntimeException('sum field not valid.');
        }
    }

    /**
     * Encode a value as JSON.
     *
     * @param  int  $options
     *
     * @return string
     * @throws \JsonException
     */
    public function toJson($options = 0)
    {
        $options |= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $data = [];
        if (!empty($this->filters)) {
            $data['filters'] = $this->filters;
        }
        if (!empty($page = $this->getPage())) {
            $data['page'] = $page;
        }
        if (!empty($this->sortData)) {
            $data['sort'] = $this->sortData;
        }
        return json_encode($data, JSON_THROW_ON_ERROR | $options);
    }

    /**
     * @return string
     * @throws \JsonException
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
