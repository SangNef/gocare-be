<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class RequestWarrantyHistory extends Model
{
    protected $table = 'request_warranty_histories';

    protected $hidden = [];

    protected $guarded = [];

    public function handler()
    {
        return $this->belongsTo(User::class, 'handler_id');
    }
}
