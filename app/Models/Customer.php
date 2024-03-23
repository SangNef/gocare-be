<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Repositories\GroupRepository;
use App\Scopes\Traits\StoreOwner;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use SoftDeletes, Authenticatable, Authorizable, StoreOwner;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'username',
        "phone",
        "parent_id",
        "debt_in_advance",
        "debt_total",
        "note",
        "province",
        "district",
        "ward",
        "address",
        "group_id",
        "customer_currency",
        "password",
        "accesstoken_id",
        'store_id',
        'customer_parent_id',
        'can_create_sub',
        'bank_name',
        'bank_acc',
        'bank_acc_name',
        'cccd',
        'cv_id'
    ];

    protected $hidden = [
        'password'
    ];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'vtp_account' => 'array'
    ];

    public function accesstoken()
    {
        return $this->belongsTo(AccessToken::class);
    }

    public function backlogs()
    {
        return $this->hasMany(\App\Models\CustomerBacklog::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function shippingSetups()
    {
        return $this->hasMany(CustomerShippingSetup::class);
    }

    public function getShippingSetupByPartner($partner)
    {
        return $this->shippingSetups()
            ->where('partner', $partner)
            ->where('is_active', 1)
            ->first();
    }

    public function getFullAddress()
    {
        $data = array_filter([$this->address, $this->ward, $this->district, $this->province]);

        return $data && !empty($data) ? implode(', ', $data) : '';
    }

    public function getProvinceId()
    {
        $province = $this->getProvince();
        return $province ? $province->id : '';
    }

    public function getDistrictId()
    {
        $district = $this->getDistrict();
        return $district ? $district->id : '';
    }

    public function getWardId()
    {
        $ward = $this->getWard();
        return $ward ? $ward->id : '';
    }

    public function getProvince()
    {
        return Province::select('name', 'id')
            ->where('name', $this->province)
            ->first();
    }

    public function getDistrict()
    {
        return District::select('name', 'id')
            ->where('name', $this->district)
            ->where('province_id', $this->getProvinceId())
            ->first();
    }

    public function getWard()
    {
        return Ward::select('name', 'id')
            ->where('name', $this->ward)
            ->where('district_id', $this->getDistrictId())
            ->first();
    }


    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function setProvinceAttribute($provinceId)
    {
        $province = Province::find($provinceId);
        if ($province) {
            $this->attributes['province'] = $province->name;
        }
    }

    public function setDistrictAttribute($districtId)
    {
        $district = District::find($districtId);
        if ($district) {
            $this->attributes['district'] = $district->name;
        }
    }

    public function setWardAttribute($wardId)
    {
        $ward = Ward::find($wardId);
        if ($ward) {
            $this->attributes['ward'] = $ward->name;
        }
    }

    public function getJWTIdentifier()
    {
        return $this->getKey(); // Eloquent model method
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function getDefaultAddressId()
    {
        return $this->addresses()->where('default', 1)->first();
    }

    public function ownedStore()
    {
        return $this->hasOne(Store::class, 'owner_id');
    }

    public function canDiscountFromFE()
    {
        $configs = Config::getFEDiscountGroupsConfig();
        return in_array($this->group_id, $configs);
    }

    public function getDataForFE()
    {
        $orders = $this->orders
            ->where('order_from', Order::ORDER_FROM_FE)
            ->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30));
        $total = $orders->sum('total');
        $customerPrice = 0;
        foreach ($orders as $order) {
            $customerPrice += $order->getCTVPriceForOrderFromFE();
        }
        $profit = $total - $customerPrice;
        $balance = Commission::where('customer_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();
        $lockedBalacne = LockCommission::where('customer_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();
        $storeShippings = $this->store->shippings()
            ->where('status', true)
            ->pluck('provider')
            ->toArray();

        $shippingSetupResults = [];
        $availableProviders = app(\App\Repositories\CODOrderRepository::class)->getAvailableProvider();
        $shippingSetups = $this->shippingSetups()->where('is_active', true)->pluck('partner')->toArray();
        foreach ($storeShippings as $key) {
            if (count($shippingSetups) > 0 && array_search($key, $shippingSetups) === false) {
                continue;
            }
            $shippingSetupResults[] = [
                'key' => $key,
                'name' => $availableProviders[$key]
            ];
        }

        $discountByCate = array_values(app(GroupRepository::class)->getDiscounts($this->group_id));
        $discountByCate = !empty($discountByCate) ? $discountByCate[0]['discount'] : [];

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->phone,
            'debt' => (int) $this->debt_total,
            'total_order' => $orders->count(),
            'total_sold_amount' => (int) $total,
            'profit' => (int) $profit,
            'address' => $this->address,
            'province' => $this->getProvinceId(),
            'district' => $this->getDistrictId(),
            'ward' => $this->getWardId(),
            'group' => $this->group->name,
            'can_discount' => $this->canDiscountFromFE(),
            'balance' => $balance ? ($this->hasOwnedShippingService() ? -$balance->balance : $balance->balance) : 0,
            'locked_balance' => $lockedBalacne ? $lockedBalacne->balance : 0,
            'available_shipping_setups' => $shippingSetupResults,
            'hasOwnedShippingService' => count($shippingSetups) > 0,
            'can_create_sub' => $this->can_create_sub,
            'az_point' => $this->getAZPoint(),
            'has_parent' => $this->customer_parent_id ? true : false,
            'require_payment' => $this->group->require_payment,
            'discount_by_cate' => $discountByCate,
        ];
    }

    public function isBelongsToElectronicGroup()
    {
        $electronicGroupIds = Group::getElectronicGroup();
        return static::where('id', $this->id)
            ->whereIn('group_id', $electronicGroupIds)
            ->exists();
    }

    public function applyStoreOwnerScope(Builder $builder)
    {
        if (auth()->check() && auth()->user()->store_id) {
            $builder->where(function ($q) {
                $store = Store::find(auth()->user()->store_id);
                $q->where($this->getStoreColumn(), auth()->user()->store_id)
                    ->orWhere('id', $store->owner_id);
            });
        }
    }

    public static function getInternalCustomer()
    {
        return static::where('username', 'noibo@fqs.vn')->first();
    }

    public function hasOwnedTransportation()
    {
        $shippingSetups = $this->shippingSetups()->where('is_active', true)->pluck('partner')->toArray();
        
        return count($shippingSetups) > 0;
    }

    public function getAvailableOwnedTransportation()
    {
        $shippingSetupResults = [];
        $availableProviders = app(\App\Repositories\CODOrderRepository::class)->getAvailableProvider();
        $shippingSetups = $this->shippingSetups()->where('is_active', true)->pluck('partner')->toArray();
        foreach ($availableProviders as $key => $name) {
            if (count($shippingSetups) > 0 && (array_search($key, $shippingSetups)) === false) {
                unset($availableProviders[$key]);
                continue;
            }
            $shippingSetupResults[] = [
                'provider' => $key,
                'name' => $name
            ];
        }
        
        return $shippingSetupResults;
    }
    
    public function hasOwnedShippingService()
    {
        return $this->shippingSetups()->count() > 0;
    }

    public function getAZPoint()
    {
        $azPoint = AZPoint::where('customer_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        
        return $azPoint ? $azPoint->balance : 0;
    }

    public function getParrentIds()
    {
        $result = [];
        $parent = $this->parent;
        if ($parent) {
            $result[] = $parent->id;
            if ($parent->parent_id) {
                return $result + $parent->getParrentIds();
            }
        }

        return $result;
    }
}
