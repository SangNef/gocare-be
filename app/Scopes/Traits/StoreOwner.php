<?php
namespace App\Scopes\Traits;

use App\Models\Store;
use App\Scopes\AdminPanel\StoreScope;

trait StoreOwner
{
    public function store()
    {
        return $this->belongsTo(Store::class, $this->getStoreColumn());
    }

    public function storeId()
    {
        return $this->{$this->getStoreColumn()};
    }

    public function getStoreColumn()
    {
        return defined('static::STORE_ID') ? static::STORE_ID : 'store_id';
    }

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootStoreOwner()
    {
        static::addGlobalScope(new StoreScope());
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->store_id) {
                $model->{$model->getStoreColumn()} = auth()->user()->store_id;
            }
        });
    }
}