<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBacklog extends Model
{
    const BEGINING = 1;
    const IN_MONTH = 2;
    
    protected $table = 'customer_backlogs';
    
    protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];
	
	/**
	 * Get user
	 * 
     * @return Collection
	 */
	public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
    
    public static function debtTypeName()
    {
        return [
            self::BEGINING=> 'Tồn đầu kỳ',
            self::IN_MONTH => 'Tồn trong kỳ'
        ];
    }
}