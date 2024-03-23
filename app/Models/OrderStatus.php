<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderStatus
{
    const PROCESSING = 1;
    const SUCCESS = 2;
    const REFUND = 3;
    const PENDING_CANCEL = 7;
    const APPROVEABLE = 0;
    const APPROVED = 1;

    protected $status = [];
    protected $warrantyStatus = [];
    protected $color = [];
    protected $warrantyColor = [];
    protected $style = [];
    protected $approve = [];

    public function __construct()
    {
        $this->status = [
            1 => trans('status.processing'),
            2 => trans('status.success'),
            3 => trans('status.refund'),
            4 => trans('status.cancel'),
            7 => trans('status.pending_cancel')
        ];

        $this->warrantyStatus = [
            1 => trans('status.received'),
            2 => trans('status.processing'),
            3 => trans('status.success'),
        ];

        $this->warrantyColor = [
            1 => 'warning',
            2 => 'primary',
            3 => 'success',
        ];

        $this->approve = [
            0 => 'Chưa duyệt',
            1 => 'Đã duyệt'
        ];

        $this->color = [
            1 => 'warning',
            2 => 'success',
            3 => 'danger',
            4 => 'danger',
            7 => 'danger',
        ];

        $this->style = [
            6 => [
                'background' => '#AB47BC !important'
            ],
        ];
    }

    public function isProcessing($status)
    {
        return $status == static::PROCESSING;
    }

    public function isSuccess($status)
    {
        return $status == static::SUCCESS;
    }

    public function isCancel($status)
    {
        return in_array($status, [3, 4]);
    }

    public function getStatusLabel($status)
    {
        return @$this->status[$status];
    }

    public function get()
    {
        return $this->status;
    }

    public function getStatusHTMLFormatted($status)
    {
        $text = $this->getStatusLabel($status);
        $color = @$this->color[$status];
        $style = $this->getStatusStyle($status);

        return '<span class="label label-' . $color . '" style="' . $style . '">' . $text . '</span>';
    }

    public function isEditable($status)
    {
        return in_array($status, [1, 5, 6]);
    }

    public function isApproveable($approve)
    {
        return $approve === self::APPROVEABLE;
    }

    public function getStatusStyle($status)
    {
        $style = '';
        if (@$this->style[$status]) {
            foreach ($this->style[$status] as $prop => $val) {
                $style .= $prop . ': ' . $val . '; ';
            }
        }
        return $style;
    }

    public function getApproveHTMLFormatted($approve)
    {
        $color = ['default', 'success'];
        return '<span class="label label-' . $color[$approve] . '">' . $this->approve[$approve] . '</span>';
    }

    public function getApprove()
    {
        return $this->approve;
    }

    public function isApproved($status)
    {
        return $status === static::APPROVED;
    }

    public function getWarrantyStatusHTMLFormatted($status)
    {
        $text = $this->warrantyStatus[$status];
        $color = $this->warrantyColor[$status];
        return '<span class="label label-' . $color . '">' . $text . '</span>';
    }
}
