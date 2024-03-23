<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\DOrder;
use App\Models\Order;
use App\Models\Store;
use App\Models\OrderProduct;
use App\Services\Discount;

class DOrderRepository
{
    public function notify(DOrder $order)
    {
        $cmd = 'php ' . base_path() . '/artisan notification:order ' . $order->id;
        shell_exec($cmd . ' > /dev/null 2>&1 &');
    }
}
