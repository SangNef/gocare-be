<?php

namespace App\Helper;

use Dwij\Laraadmin\Models\Menu;
use Illuminate\Support\Facades\Auth;

class StoreOwnerHelper
{
    public static function excludeRoutes()
    {
        $includes = [
            'departments',
            'organizations',
            'modules',
            'request-warranties',
            'permissions',
            'roles',
            'accesstokens',
            'smssents',
            'employees',
            'cod-orders-shipping',
            'productcategories',
            'products',
            'orderproducts',
            'configs',
            'settings',
        ];

        $excludes = [];
        if (Auth::check() && Auth::user()->isRole('STORE_OWNER')) {
            $excludes = [
                'orders',
                'orders?from=1',
                'orders?from=2&cross_store=1',
                'orders?from=2',
                '#customer',
                'onlinecustomers',
                'orders?from=2&d=1',
                'addresses',
                'productquantityaudits',
            ];
        }
        if (Auth::check() && Auth::user()->isRole('STORE')) {
            $excludes = [
                'orders?from=2&d=1',
                '#cod-orders-shipping',
                'orders?from=1',
                'orders?from=2',
                'orders?from=2&cross_store=1',
                'orders?from=2&payment_method=3',
                'activatetoearns',
                'cod-orders/ghn',
                'cod-orders/vtp',
                'cod-orders/ghtk',
                'activated-warranties',
                'warrantyorders'
            ];
            $includes = array_diff($includes, ['#cod-orders-shipping']);
        }
        if (Auth::check() && Auth::user()->isRole('NV_BAO_HANH')) {
            $excludes = [
                'addresses',
            ];
            $includes = array_diff($includes, ['request-warranties']);
        }

        $urls = Menu::where('type', 'custom')
            ->whereNotIn('url', $excludes)
            ->pluck('url')
            ->toArray();

        return array_merge($urls, $includes);
    }
}
