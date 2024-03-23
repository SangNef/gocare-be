<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App;

use App\Scopes\Traits\StoreOwner;
use Illuminate\Database\Eloquent\Builder;
use Zizaco\Entrust\EntrustRole;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends EntrustRole
{
    use SoftDeletes,StoreOwner;
	
	protected $table = 'roles';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];
	
	public function users() {
        return $this->hasMany(\App\User::class);
    }
    
    public static function availableRoles()
    {
        return self::whereNull('deleted_at')->get();
    }

    public function applyStoreOwnerScope(Builder $builder)
    {
        if (auth()->check() && auth()->user()->store_id) {
            $builder->where(function ($q) {
                $q->where($this->getStoreColumn(), auth()->user()->store_id)
                    ->orWhere('name', 'STORE_OWNER');
            });
        }
    }
}
