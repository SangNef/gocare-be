<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CODOrdersShipping extends Model
{
    const TYPE_EXPORT = 1;
    const TYPE_REFUND = 2;

    protected $table = 'cod_orders_shipping';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = [];

    protected $casts = [
        'bill_data' => 'array'
    ];

    public function codOrder()
    {
        return $this->hasMany(CODOrder::class, 'so_id', 'id');
    }

    public function color()
    {
        return [
            1 => 'warning',
            2 => 'success',
            3 => 'danger'
        ];
    }

    public function availableStatus()
    {
        return [
            1 => trans('status.processing'),
            2 => trans('status.success'),
            3 => trans('status.refund'),
        ];
    }

    public function availableType()
    {
        return [
            1 => 'Hàng đi',
            2 => 'Hàng hoàn'
        ];
    }

    public function exportOrderStatus()
    {
        return [
            1 => trans('status.processing'),
            2 => trans('status.success')
        ];
    }

    public function refundOrderStatus()
    {
        return [
            1 => trans('status.processing'),
            3 => trans('status.refund')
        ];
    }

    public function getStatusList($type)
    {
        $status = [];
        if ($type == static::TYPE_EXPORT) $status = $this->exportOrderStatus();
        if ($type == static::TYPE_REFUND) $status = $this->refundOrderStatus();
        return $status;
    }

    public function canEdit()
    {
        return $this->status == 1;
    }

    public function getStatusHTMLFormatted()
    {
        $text = $this->availableStatus()[$this->status];
        $color = @$this->color()[$this->status];

        return '<span class="label label-' . $color . '">' . $text . '</span>';
    }

    public function getPartnerHTMLFormatted()
    {
        switch ($this->partner) {
            case 'ghn_5':
                $text = 'Giao Hang Nhanh dưới 5kg';
                $color = 'success';
                break;
            case 'ghn':
                $text = 'Giao Hang Nhanh';
                $color = 'success';
                break;
            case 'vtp':
                $text = 'Viettel Post';
                $color = 'danger';
                break;
            case 'ghtk':
                $text = 'Giao Hang Tiet Kiem';
                $color = 'primary';
                break;
            case 'other':
                $text = 'Vận chuyển khác';
                $color = 'warning';
                break;
            default:
                $text = '';
                $color = '';
        }
        return '<span class="label label-' . $color . '">' . $text . '</span>';
    }

    public function getTypeHTMLFormatted()
    {
        $text = $this->availableType()[$this->type];
        $color = @$this->color()[$this->type];

        return '<span class="label label-' . $color . '">' . $text . '</span>';
    }
}
