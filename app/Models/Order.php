<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Traits\SearchScope;
use App\Scopes\Traits\StoreOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use StoreOwner;

    const BEARER_FEE_BUYER = 1;
    const BEARER_FEE_SELLER = 2;
    const TYPE_IMPORT = 1;
    const TYPE_EXPORT = 2;
    const SUB_TYPE_NEW = 1;
    const SUB_TYPE_WARRANTY = 2;
    const PAYMENT_METHOD_PAY_LATER = 1;
    const PAYMENT_METHOD_PAY_ONLINE = 3;
    const PAYMENT_METHOD_COD = 2;
    const ORDER_FROM_ADMIN = 1;
    const ORDER_FROM_FE = 2;

    use SoftDeletes;
    use SearchScope;
    protected $authorized = false;

    protected $table = 'orders';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $searches = [
        'created_at',
    ];

    protected $casts = [];

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'orderproducts')->wherePivot('deleted_at',  NULL);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function productSeries()
    {
        return $this->hasMany(ProductSeri::class);
    }

    public function codOrder()
    {
        return $this->morphOne(CODOrder::class, 'order');
    }

    public function isImport()
    {
        return $this->type == static::TYPE_IMPORT;
    }

    public function isExport()
    {
        return $this->type == static::TYPE_EXPORT;
    }

    public function isNewProduct()
    {
        return $this->sub_type == static::SUB_TYPE_NEW;
    }

    public function isWarrantyProduct()
    {
        return $this->sub_type == static::SUB_TYPE_WARRANTY;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function azOrder()
    {
        return $this->hasOne(AzOrder::class);
    }

    public function transportOrder()
    {
        return $this->hasOne(TransportOrder::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function onlineCustomer()
    {
        return $this->hasOne(OnlineCustomer::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function getCommissionReceiver()
    {
        return $this->voucher ? $this->voucher->owner_id : $this->customer_id;
    }

    public function availableType()
    {
        return [
            static::TYPE_IMPORT => 'Nhập',
            static::TYPE_EXPORT => 'Xuất'
        ];
    }

    public function getStatus($status = null)
    {
        return [
            1 => trans('status.processing'),
            2 => trans('status.success'),
            3 => trans('status.refund'),
            4 => trans('status.cancel'),
            5 => trans('status.fixing'),
            6 => trans('status.returned'),
            7 => trans('status.pending_cancel'),
        ][$status ?: $this->status];
    }

    public function getPaymentInfo()
    {
        $payments = json_decode($this->payment, true);
        if (isset($payments['detail']) && !empty($payments['detail'])) {
            foreach ($payments['detail'] as $key => $payment) {
                if (isset($payment['bank_id'])) {
                    $bank = Bank::find($payment['bank_id']);
                    $payments['detail'][$key]['bank'] = $bank ? implode('-', [
                        $bank->name,
                        $bank->branch,
                        $bank->acc_id,
                        $bank->acc_name
                    ]) : '';
                }
            }
        } else {
            $payments['detail'] = [];
        }

        return $payments;
    }

    public function getTypeHTMLFormatted($type = null)
    {
        $type = $type ?: $this->type;

        if ($type == static::TYPE_IMPORT) {
            return '<span class="label label-warning">' . trans('order.type_' . $type) . '</span>';
        }

        return '<span class="label label-default">' . trans('order.type_' . $type) . '</span>';
    }

    public function getSubTypeHTMLFormatted($type = null)
    {
        $type = $type ?: $this->sub_type;

        if ($type == Product::WARRANTY_PRODUCT) {
            return '<span class="label label-warning">' . trans('order.sub_type_' . $type) . '</span>';
        }

        return '<span class="label label-default">' . trans('order.sub_type_' . $type) . '</span>';
    }

    public function getPaymentMethodHTMLFormatted()
    {
        if ($this->payment_method === self::PAYMENT_METHOD_COD) {
            return '<span class="label label-warning">Vận chuyển</span>';
        }

        return '<span class="label label-default">Thanh toán sau</span>';
    }

    public function getFeeBearerHTMLFormatted()
    {
        if ($this->fee_bearer === static::BEARER_FEE_SELLER) {
            return '<span class="label label-warning">' . trans('order.fee_bearer_seller') . '</span>';
        }

        return '<span class="label label-default">' . trans('order.fee_bearer_buyer') . '</span>';
    }

    public function getOldDebt()
    {
        $prevOrder = $this->where('customer_id', $this->customer_id)
            ->where('id', '<', $this->id)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->first();
        $debt = 0;
        if ($prevOrder && $prevOrder->current_debt != 0) {
            $totalDebt = $prevOrder->current_debt;
            $transactionTotal = $this->customer->transactions()
                ->doesntHave('order')
                ->whereBetween('created_at', [$prevOrder->created_at, $this->created_at])
                ->selectRaw('(CASE WHEN type = 1 THEN received_amount ELSE -transfered_amount END) as amount')
                ->get()
                ->sum('amount');
            $debt = $transactionTotal ? $totalDebt - $transactionTotal : $totalDebt;
        }
        return $debt;
    }

    public function isCODOrder()
    {
        return $this->payment_method == static::PAYMENT_METHOD_COD || $this->cod_partner;
    }

    public function isCODOrderChargeDebt()
    {
        return $this->isCODOrder() && $this->codOrder && $this->codOrder->isChargeToCustomerDebt();
    }

    public function isCreateNewSeries()
    {
        return $this->order_series_type && $this->order_series_type == 2;
    }

    public function isUpdateSeriesLater()
    {
        return $this->order_series_type && $this->order_series_type == 3;
    }

    public function isUseAvailableSeries()
    {
        return $this->order_series_type && $this->order_series_type == 1;
    }

    public function isBuyerBearsTheFee()
    {
        return $this->fee_bearer === static::BEARER_FEE_BUYER;
    }

    public function isSellerBearsTheFee()
    {
        return $this->fee_bearer === static::BEARER_FEE_SELLER;
    }

    public static function getOrderSeriesType()
    {
        return [
            1 => 'Seri có sẵn',
            2 => 'Tạo seri mới',
            3 => 'Cập nhật seri sau'
        ];
    }

    public function isFromFE()
    {
        return $this->order_from == static::ORDER_FROM_FE;
    }

    public function isFromAdmin()
    {
        return $this->order_from == static::ORDER_FROM_ADMIN;
    }

    public function getCTVPriceForOrderFromFE()
    {
        $price = 0;
        if ($this->isFromFE()) {
            $price = $this->getOrderFeProductsPrice(true);
            $codOrder = $this->codOrder;
            // check if order is cod-order && cod order exists
            // if not charge fee to receiver => add fee amount to total price
            if ($codOrder && !$codOrder->charge_fee) {
                $price += $codOrder->fee_amount;
            }
        }
        return $price;
    }

    public function getOrderFeProductsPrice($getByDiscount = false)
    {
        // get order from FE products price.
        return $this->orderProducts->reduce(function ($total, $op) use ($getByDiscount) {
            $product = $op->product;
            $price = 0;
            if ($product) {
                $discount = $product->getLastestPriceForCustomer($this->customer_id, true);
                $price = $getByDiscount && $discount ? $discount : $product->getPriceForCustomerGroup('khách_hàng_Điện_tử', true);
            }
            return $total + ($price * $op->quantity);
        });
    }

    public function getCommission()
    {
        if ($this->voucher) {
            return $this->getCommissionForCustomer($this->voucher->owner);
        }
        $commission = 0;
        foreach ($this->orderProducts as $orderProduct) {
            $commission += ($orderProduct->retail_price - $orderProduct->price) * $orderProduct->quantity;
        }

        $fee = $this->fee_bearer == Order::BEARER_FEE_SELLER ? $this->fee : 0;

        return $commission - $this->discount - $fee;
    }

    public function getCommissionForCustomer(Customer $customer)
    {
        $commission = 0;
        foreach ($this->orderProducts as $orderProduct) {
            $product = $orderProduct->product;
            $price = $product->getLastestPriceForCustomer($customer->id, true);
            $commission += ($orderProduct->retail_price - $price) * $orderProduct->quantity;
        }

        $fee = $this->fee_bearer == Order::BEARER_FEE_SELLER ? $this->fee : 0;

        return $commission - $this->discount - $fee;
    }

    public function needToPayCommission()
    {
        return $this->store->neededToPayCommission($this->customer->group_id);
    }

    public function isPayingCommission()
    {
        // da duyet
        return $this->approve == 1
            // thanh cong
            && $this->status == 2
            //hang moi
            && $this->sub_type == 1
            // xuat
            && $this->type == 2;
    }

    public function updateDebtForCurrentAndNextOrders($changed = 0)
    {
        return $this->where('id', '>=', $this->id)
            ->where('customer_id', $this->customer_id)
            ->update([
                'current_debt' => \DB::raw('current_debt +' . $changed),
            ]);
    }
}
