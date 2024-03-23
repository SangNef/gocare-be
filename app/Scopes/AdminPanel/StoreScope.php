<?php
namespace App\Scopes\AdminPanel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Zizaco\Entrust\EntrustFacade as Entrust;

class StoreScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check()) {
            if (method_exists($model, 'applyStoreOwnerScope')) {
                $model->applyStoreOwnerScope($builder);
            } else {
                if (auth()->user()->store_id) {
                    $builder->where($model->getStoreColumn(), auth()->user()->store_id);
                }
                if (Entrust::hasRole('ADMIN')) {
                    $stores = explode(',', config('app.admin_stores'));
                    if (!empty($stores)) {
                        $builder->whereIn($model->getStoreColumn(), $stores);
                    }
                }
            }
        }
    }
}