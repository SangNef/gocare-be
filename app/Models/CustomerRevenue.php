<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CustomerRevenue extends Model
{
    protected $table = 'customer_revenues';

    protected $hidden = [

    ];

    protected $guarded = [];

    protected $casts = [
        'data' => 'array'
    ];

    public function getOrders()
    {
        $from = Carbon::createFromFormat('m-Y', $this->month)->firstOfMonth();
        $to = Carbon::createFromFormat('m-Y', $this->month)->endOfMonth();

        return Order::whereBetween('created_at', [
            $from,
            $to
        ])
            ->with('orderProducts')
            ->where('customer_id', $this->customer_id)
            ->where('payment', '<>', 'online')
            ->where('status', OrderStatus::SUCCESS);
    }

    public function getOnlineOrders()
    {
        $from = Carbon::createFromFormat('m-Y', $this->month)->firstOfMonth();
        $to = Carbon::createFromFormat('m-Y', $this->month)->endOfMonth();

        return Order::whereBetween('created_at', [
            $from,
            $to
        ])
            ->with('orderProducts')
            ->where('customer_id', $this->customer_id)
            ->where('payment', 'online')
            ->where('status', OrderStatus::SUCCESS);
    }

    public function getAffiliateOrders()
    {
        $from = Carbon::createFromFormat('m-Y', $this->month)->firstOfMonth();
        $to = Carbon::createFromFormat('m-Y', $this->month)->endOfMonth();
        $subCustomers = Customer::where('customer_parent_id', $this->customer_id)
            ->pluck('id');

        return Order::whereBetween('created_at', [
            $from,
            $to
        ])
            ->with('orderProducts')
            ->whereIn('customer_id', $subCustomers)
            ->where('payment', 'online')
            ->where('status', OrderStatus::SUCCESS);
    }

    public function getActivations()
    {
        $from = Carbon::createFromFormat('m-Y', $this->month)->firstOfMonth();
        $to = Carbon::createFromFormat('m-Y', $this->month)->endOfMonth();
        $customerId = $this->customer_id;

        return ProductSeri::select(DB::raw('product_series.*, products.retail_price'))
            ->where('product_series.status', 1)
            ->whereExists(function ($q) use ($customerId) {
                $q->select('orders.id')
                    ->from('orders')
                    ->whereRaw('orders.id = product_series.order_id')
                    ->where('orders.customer_id', $customerId);
            })
            ->whereBetween('purchased_date', [
                $from,
                $to
            ])
            ->join('products', 'products.id', '=', 'product_series.product_id');
    }

    public function getAffiliateActivations()
    {
        $from = Carbon::createFromFormat('m-Y', $this->month)->firstOfMonth();
        $to = Carbon::createFromFormat('m-Y', $this->month)->endOfMonth();
        $subCustomers = Customer::where('customer_parent_id', $this->customer_id)
            ->pluck('id');

        return ProductSeri::select(DB::raw('product_series.*, products.retail_price'))
            ->where('product_series.status', 1)
            ->whereExists(function ($q) use ($subCustomers) {
                $q->select('orders.id')
                    ->from('orders')
                    ->whereRaw('orders.id = product_series.order_id')
                    ->whereIn('orders.customer_id', $subCustomers);
            })
            ->whereBetween('purchased_date', [
                $from,
                $to
            ])
            ->join('products', 'products.id', '=', 'product_series.product_id');
    }
}
