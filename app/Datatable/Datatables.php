<?php

namespace App\Datatable;

use App\Datatable\Engines\EloquentEngine;
use App\Datatable\Engines\QueryBuilderEngine;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Yajra\Datatables\Datatables as BaseDatatables;

class Datatables extends BaseDatatables
{
    public function usingQueryBuilder(QueryBuilder $builder)
    {
        return new QueryBuilderEngine($builder, $this->request);
    }

    /**
     * Datatables using Eloquent.
     *
     * @param  mixed $builder
     * @return EloquentEngine
     */
    public function usingEloquent($builder)
    {
        return new EloquentEngine($builder, $this->request);
    }
}