<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $table = 'role_user';
    
    protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

    /**
     * Get user
     *
     * @return collection
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    /**
     * Get role
     *
     * @return Collection
     */
    public function role()
    {
        return $this->belongsTo(\App\Role::class, 'role_id');
    }
}
