<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $table = 'social_accounts';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
