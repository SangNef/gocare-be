<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchScope;
use App\Models\WarrantyOrderProductSeri;

class CODOrder extends Model
{
    use SearchScope;

    const PARTNER_GHN = 'ghn';
    const PARTNER_GHN_5 = 'ghn_5';
    const PARTNER_VTP = 'vtp';
    const PARTNER_GHTK = 'ghtk';
    const PARTNER_VNPOST = 'vnpost';
    const PARTNER_OTHER = 'other';
    const CHARGE_METHOD_COD = 1;
    const CHARGE_METHOD_DEBT = 2;

    protected $table = 'cod_orders';

    protected $authorized = false;

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = ['created_at'];

    protected $casts = [
        'additional_data' => 'array',
    ];

    protected $searches = [
        'created_at'
    ];

    public function order()
    {
        return $this->morphTo();
    }

    public function warrantyOrderProductSeries()
    {
        return $this->hasMany(WarrantyOrderProductSeri::class, 'cod_order_id');
    }

    public function isChargeToCustomerDebt()
    {
        return $this->charge_method && $this->charge_method == static::CHARGE_METHOD_DEBT;
    }

    public function getTotalAmountOfColumnByOrderCode($partner, $codes = [], $column)
    {
        if (in_array($partner, [static::PARTNER_GHN, static::PARTNER_VTP])) {
            return $this->where('partner', $partner)
                ->whereIn('order_code', $codes)
                ->sum($column);
        }
        return $this->where('partner', $partner)
            ->where(function ($query) use ($codes) {
                foreach ($codes as $key => $code) {
                    $clause = $key == 0 ? 'where' : 'orWhere';
                    $code = explode('.', $code);
                    $query->{$clause}('order_code', 'LIKE', '%' . end($code) . '%');
                }
            })
            ->excludeCancel()
            ->sum($column);
    }

    public function getPartnerLabelHTML()
    {
        $labelColor = [
            'ghn' => 'success',
            'ghn_5' => 'success',
            'vtp' => 'warning',
            'ghtk' => 'primary'
        ];
        return '<span class="label label-' . $labelColor[$this->partner] . '">' . trans('cod_order.' . $this->partner) . '</span>';
    }

    public function getChargeMethodLabelHTML()
    {
        $labelColor = [
            static::CHARGE_METHOD_COD => 'default',
            static::CHARGE_METHOD_DEBT => 'success'
        ];
        $labelText = [
            static::CHARGE_METHOD_COD => 'Thu tiền COD',
            static::CHARGE_METHOD_DEBT => 'Tính vào công nợ'
        ];
        return '<span class="label label-' . $labelColor[$this->charge_method] . '">' . $labelText[$this->charge_method] . '</span>';
    }

    public function getCompareStatusLabelHTML()
    {
        $labelColor = [
            0 => 'default',
            1 => 'success'
        ];
        $labelText = [
            0 => 'Chưa đối soát',
            1 => 'Đã đối soát'
        ];
        return '<span class="label label-' . $labelColor[$this->compare_status] . '">' . $labelText[$this->compare_status] . '</span>';
    }

    public function getStatusMessages($status = null)
    {
        $text = "Đang cập nhật";
        switch ($this->partner) {
            case static::PARTNER_VTP:
                $statusList = app(\App\Services\CODPartners\VTPService::class)->statusList();
                break;
            case static::PARTNER_GHTK:
                $statusList = app(\App\Services\CODPartners\GHTKService::class)->statusList();
                break;
            default:
                $statusList = [];
        }
        if (!empty($statusList) && in_array($this->status, array_keys($statusList))) {
            $text = $statusList[$status ?: $this->status];
        }
        return $text;
    }

    public function vtpIsSuccessfulDelivery()
    {
        return $this->partner == static::PARTNER_VTP && $this->status == '501';
    }

    public function vtpIsSuccessfulReturn()
    {
        return $this->partner == static::PARTNER_VTP && $this->status == '504';
    }

    public function ghtkIsSuccessfulDelivery()
    {
        return $this->partner == static::PARTNER_GHTK && $this->status == '5';
    }

    public function ghtkIsSuccessfulReturn()
    {
        return $this->partner == static::PARTNER_GHTK && $this->status == '21';
    }

    public function scopeExcludeCancel($query)
    {
        return $query->whereNotIn('status', ['107', '201', '-1', 'cancel'])
            ->orWhereNull('status');
    }

    public function getNumberOfProducts()
    {
        return array_reduce($this->orderProducts(), function ($total, $orderProduct) {
            return $orderProduct['quantity'] + $total;
        }, 0);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warrantyOrderProductSeriActualOrder()
    {
        $wops = $this->warrantyOrderProductSeries->first();
        return @$wops->warrantyOrderProduct->warrantyOrder;
    }

    public function orderProducts()
    {
        $order = $this->order;
        $results = [];
        if ($this->order_type === 'WarrantyOrderProductSeri') {
            foreach ($this->warrantyOrderProductSeries as $wops) {
                $product = $wops->warrantyOrderProduct->product;
                if (@$results[$product->sku]) {
                    $results[$product->sku]['quantity']++;
                } else {
                    $results[$product->sku] = [
                        'name' => $product->name,
                        'quantity' => 1
                    ];
                }
            }
        } else {
            $orderProducts = $order instanceof WarrantyOrder ? $order->warrantyOrderProducts : $order->orderProducts;
            foreach ($orderProducts as $op) {
                $results[] = [
                    'name' => $op->product->name,
                    'quantity' => $op->quantity
                ];
            }
        }
        return array_values($results);
    }
}
