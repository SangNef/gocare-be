<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Repositories\CODOrderRepository;
use App\Models\Customer;

class Store extends Model
{
    use SoftDeletes;

    protected $table = 'stores';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $casts = ['setting' => 'array'];

    public function owner()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(StoreProduct::class, 'store_id');
    }

    public function shippings()
    {
        return $this->hasMany(StoreShipping::class);
    }

    public function observes()
    {
        return $this->hasMany(StoreObserve::class);
    }

    public function neededToPayCommission($groupId)
    {
        $setting = $this->setting;
        $commissionGroups = @$setting['commission_groups'] ?: [];

        return in_array($groupId, $commissionGroups);
    }

    public function getAvailableTransportation()
    {
        return $this->shippings
            ->where('status', 1)
            ->map(function ($item) {
                return [
                    'provider' => $item->provider,
                    'name' => trans('cod_order.' . ($item->provider != 'ghn_5' ? $item->provider : 'ghn')),
                ];
            });
    }

    public function getAvailableSharingTransportation($storeId)
    {
        return $this->shippings
            ->filter(function ($item) {
                $apiConnection = json_decode($item->api_connection, true);
                return @$apiConnection['storeIdForSharing'];
            })
            ->map(function ($item) use($storeId) {
                $provider = $item->provider;
                $codOrder = app(CODOrderRepository::class);
                $customer = Customer::where('store_id', $storeId)->first();
                $codOrder->loadCodServiceByStore($customer, true);
                $apiConnection = json_decode($item->api_connection, true);
                $partnerStoreIDs = explode(',', @$apiConnection['storeIdForSharing']);
                $partnerStores = [];
                try {
                    $partnerStores = $codOrder->getPartnerStores($provider)->toArray();
                    $partnerStoreIDs = array_filter(array_map(function($partnerStoreID) use ($partnerStores) {
                        return [
                            $partnerStoreID,
                            $partnerStores[$partnerStoreID]
                        ];
                    }, $partnerStoreIDs), function($item) {
                        return $item[1];
                    });
                } catch (\Exception $exception) {
                    \Log::error($exception->getMessage());
                }
                return [
                    'provider' => $item->provider,
                    'name' => trans('cod_order.' . $item->provider),
                    'stores' => $partnerStoreIDs
                ];
            });
    }
    public static function getDefaultStore()
    {
        $storeOwner = Customer::where('username', 'khoazpro')->first();
        return $storeOwner ? $storeOwner->ownedStore : collect();
    }
}
