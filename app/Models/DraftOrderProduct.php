<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftOrderProduct extends Model
{
    protected $table = 'draft_order_products';

    protected $hidden = [];

    protected $guarded = [];

    public function draftOrder()
    {
        return $this->belongsTo(DraftOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
