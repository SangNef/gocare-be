<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;
    use EntrustUserTrait;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', "role", "context_id", "type",
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // protected $dates = ['deleted_at'];

    /**
     * @return mixed
     */
    public function uploads()
    {
        return $this->hasMany('App\Upload');
    }

    public function customers()
    {
        return $this->hasMany(\App\Models\Customer::class, 'parent_id', 'id');
    }

    public function roles()
    {
        return $this->belongsToMany(\App\Role::class);
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class);
    }

    public function isRole($roleName)
    {
        foreach ($this->roles as $role) {
            if ($role->name === $roleName) {
                return true;
            }
        }

        return false;
    }

    public function isSupperAdminRole()
    {
        return $this->isRole('SUPER_ADMIN');
    }

    public function haveRoleMustBeExcludeFromRoutes()
    {
        return $this->isRole('STORE_OWNER') || $this->isRole('STORE')  || $this->isRole('NV_BAO_HANH');
    }

    public function isChairmanUser()
    {
        return $this->email == config('app.chairman');
    }

    public static function createNewUser($data)
    {
        $user = self::create([
            'email' => $data['email'],
            'name' => $data['name'],
            'password' => Hash::make($data['password']),
        ]);

        if (isset($data['role']) && $data['role']) {
            $user->roles()->sync([$data['role']]);
        }

        return $user;
    }
}
