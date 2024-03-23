<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankBacklog extends Model
{
    const BEGINING = 1;
    const IN_MONTH = 2;
    
    protected $table = 'bank_backlogs';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['created_at'];
	
	public static function debtTypeName()
    {
        return [
            self::BEGINING => 'Tồn đầu kỳ',
            self::IN_MONTH => 'Tồn trong kỳ'
        ];
    }
}
