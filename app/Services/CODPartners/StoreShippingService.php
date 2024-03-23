<?php


namespace App\Services\CODPartners;


use App\Models\CODOrder;

class StoreShippingService
{
    /**
     * @return Shipping
     */
    public static function getProvider($key)
    {
        switch ($key) {
            case CODOrder::PARTNER_VTP:
                $provider = app(VTPService::class);
                break;
            case CODOrder::PARTNER_GHN:
                $provider = app(GHNService::class);
                break;
            case CODOrder::PARTNER_GHN_5:
                $provider = app(GHN5Service::class);
                break;
            case CODOrder::PARTNER_GHTK:
                $provider = app(GHTKService::class);
                break;
            case CODOrder::PARTNER_VNPOST:
                $provider = app(VNPostService::class);
                break;
            default:
                $provider = null;
                break;
        }

        return $provider;
    }
}
