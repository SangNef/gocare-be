<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces';

    protected $hidden = [
        
    ];

    protected $guarded = [];
    
    public function districts()
    {
        return $this->hasMany(\App\Models\District::class);
    }
}
