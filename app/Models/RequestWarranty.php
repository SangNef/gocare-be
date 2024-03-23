<?php

namespace App\Models;

use App\Traits\SearchScope;
use App\User;
use Illuminate\Database\Eloquent\Model;

class RequestWarranty extends Model
{
    use SearchScope;

    const STATUS_RECEIVED = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_FINISH = 2;
    const FROM_ADMIN = 1;
    const FROM_FE = 2;

    protected $authorized = false;

    protected $table = 'request_warranties';

    protected $hidden = [];

    protected $guarded = [];

    protected $casts = [
        'attachments' => 'array'
    ];

    protected $searches = [
        'created_at'
    ];

    const status = [
        0 => 'Đã tiếp nhận',
        1 => 'Đang xử lý',
        2 => 'Hoàn thành'
    ];

    const color = [
        0 => 'warning',
        1 => 'primary',
        2 => 'success'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function histories()
    {
        return $this->hasMany(RequestWarrantyHistory::class);
    }

    public function getFullAddress()
    {
        $province = $this->province ? Province::find($this->province)->name : "";
        $district = $this->district ? District::find($this->district)->name : "";
        $ward = $this->ward ? Ward::find($this->ward)->name : "";

        return implode(' - ', array_filter([
            $this->address,
            $ward,
            $district,
            $province
        ]));
    }

    public static function getListStatus()
    {
        return static::status;
    }

    public function getStatusHtmlFormat()
    {
        return '<span class="label label-' . self::color[$this->status] . '">' . self::status[$this->status] . '</span>';
    }

    public function getFromHtmlFormat()
    {
        return $this->isFromAdmin()
            ? '<span class="label label-warning">Admin</span>'
            : '<span class="label label-primary">Trang chủ</span>';
    }

    public function getAttachmentsPath()
    {
        $uploadSv = app(\App\Services\Upload::class);
        return array_map(function ($imageId) use ($uploadSv) {
            return $uploadSv->getImagePath($imageId);
        }, $this->attachments);
    }

    public function isFromAdmin()
    {
        return $this->from === static::FROM_ADMIN;
    }

    public function isFromFE()
    {
        return $this->from === static::FROM_FE;
    }
}
