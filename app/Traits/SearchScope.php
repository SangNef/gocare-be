<?php

namespace App\Traits;

trait SearchScope
{
    protected $operation = [
        'eq'    => '=',
        'like'  => 'LIKE',
        'lt'    => '<',
        'gt'    => '>',
        'neq'   => '<>',
        'lte'   => '<=',
        'gte'   => '>=',
        'in'    => 'IN',
        'nin'    => 'NOT IN',
        'null'  => 'IS NULL',
        'nnull' => 'IS NOT NULL',
        'sw'    => 'LIKE',
        'ew'    => 'LIKE'
    ];

    public function scopeSearch($query, $params)
    {
        $this->processFilter($query, $params);
        $this->processOrder($query, $params);
        $this->processPagination($query, $params);
    }

    protected function processPagination($query, $params)
    {
        if (isset($params['per_page'])) {
            $this->perPage = (int) $params['per_page'] > 0
                ? (int) $params['per_page']
                : $this->perPage;
        }
    }

    protected function processOrder($query, $params)
    {
        if (isset($params['order']) && is_array($params['order'])) {
            foreach ($params['order'] as $order) {
                if ($this->isValidOrderBy($order)) {
                    $query->orderBy($order['field'], $order['by']);
                }
            }
        } else {
            $query->orderBy($this->primaryKey, 'desc');
        }
    }

    protected function processFilter($query, $params)
    {
        if (isset($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $filter) {
                if ($this->isValidFilter($filter)) {
                    $this->addFilter($query, $filter);
                }
            }
        }

        $this->defaultFilter($query);
    }

    protected function addFilter($query, $filter)
    {
        $dateFilters = @$this->dateFiltes ?? $this->searches;
        switch (@$filter['operation']) {
            case 'lt':
            case 'gt':
            case 'lte':
            case 'gte':
            case 'neq':
                if (in_array($filter['field'], $dateFilters) && $filter['operation'] == 'lte') {
                    $filter['value'] = $filter['value'] . ' 23:59:59';
                }
                if (in_array($filter['field'], $dateFilters) && $filter['operation'] == 'gte') {
                    $filter['value'] = $filter['value'] . ' 00:00:00';
                }
                $query->where($filter['field'], $this->operation[$filter['operation']], $filter['value']);
                break;
            case 'in':
                $value = is_array($filter['value']) ? $filter['value'] : explode(',', $filter['value']);
                $query->whereIn($filter['field'], $value);
                break;
            case 'nin':
                $value = is_array($filter['value']) ? $filter['value'] : explode(',', $filter['value']);
                $query->whereNotIn($filter['field'], $value);
                break;
            case 'null':
                $query->whereNull($filter['field']);
                break;
            case 'nnull':
                $query->whereNotNull($filter['field']);
                break;
            case 'like':
                $query->where($filter['field'], 'LIKE', '%' . $filter['value'] . '%');
                break;
            case 'sw':
                $query->where($filter['field'], 'LIKE', $filter['value'] . '%');
                break;
            case 'ew':
                $query->where($filter['field'], 'LIKE', '%' . $filter['value']);
                break;
            case 'eq':
            default:
                $query->where($filter['field'], $filter['value']);
        }
    }

    protected function isValidFilter($filter = [])
    {
        return is_array($filter) &&
            isset($filter['field']) &&
            @$filter['value'] &&
            (!isset($filter['operation']) ||
                array_key_exists($filter['operation'], $this->operation)) &&
            in_array($filter['field'], !empty($this->searches) ? $this->searches : $this->fillable);
    }

    protected function isValidOrderBy($order = [])
    {
        return is_array($order) &&
            isset($order['field']) &&
            isset($order['by']) &&
            in_array(strtolower($order['by']), [
                'asc',
                'desc'
            ]) &&
            in_array($order['field'], !empty($this->orders) ? $this->orders : $this->fillable);
    }

    protected function defaultFilter($query)
    {
        if ($this->authorized) {
            $query->where('user_id', auth()->check() ? auth()->user()->id : 0);
        }
    }

    public function authorized($bool = true)
    {
        $this->authorized = $bool;

        return $this;
    }
}
