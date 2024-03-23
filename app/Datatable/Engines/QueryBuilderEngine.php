<?php

namespace App\Datatable\Engines;

use Yajra\Datatables\Engines\QueryBuilderEngine as BaseQueryBuilderEngine;

/**
 * Class QueryBuilderEngine.
 *
 * @package Yajra\Datatables\Engines
 * @author  Arjay Angeles <aqangeles@gmail.com>
 */
class QueryBuilderEngine extends BaseQueryBuilderEngine
{
    protected $filterdQuery;

    /**
     * Organizes works.
     *
     * @param bool $mDataSupport
     * @param bool $orderFirst
     * @return \Illuminate\Http\JsonResponse
     */
    public function make($mDataSupport = false, $orderFirst = false)
    {
        $this->totalRecords = $this->totalCount();

        if ($this->totalRecords) {
            $this->orderRecords(! $orderFirst);
            $this->filterRecords();
            $this->orderRecords($orderFirst);
            $this->setFilteredQuery();
            $this->paginate();
        }

        return $this->render($mDataSupport);
    }

    protected function setFilteredQuery()
    {
        $this->filterdQuery = $this->query;
    }

    public function getFilteredQuery()
    {
        return $this->filterdQuery;
    }
}
