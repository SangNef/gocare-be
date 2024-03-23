<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchScope;

class ProductSeri extends Model
{
    use SearchScope;
    protected $authorized = false;

    const STOCK_NOT_SOLD = 0;
    const STOCK_PROCESSING = 1;
    const STOCK_SOLD = 2;

    protected $table = 'product_series';

    protected $hidden = [];

    protected $guarded = [];

    protected $appends = [
        'warranty_full_address',
        'qr_code'
    ];

    protected $searches = [
        'activated_at'
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function groupAttribute()
    {
        return $this->belongsTo(ProductGroupAttributeMedia::class);
    }

    public static function getAvailableStockStatus()
    {
        return [
            self::STOCK_NOT_SOLD => 'Chưa bán',
            self::STOCK_PROCESSING => 'Đang xử lý',
            self::STOCK_SOLD => 'Đã bán',
        ];
    }

    public static function getImportStatus()
    {
        return [
            'Chưa thanh toán',
            'Đã thanh toán',
            'Đã sử dụng',
            'Bị khoá'
        ];
    }

    public static function getQrCodeStatus()
    {
        return [
            0 => 'Chưa in',
            1 => 'Đã in',
        ];
    }

    public static function getQrCodeStatusColor()
    {
        return [
            0 => 'warning',
            1 => 'success',
            2 => 'danger'
        ];
    }

    public static function defaultPaginatorLength()
    {
        return [10, 20, 50, 100, 200];
    }

    public function getWarrantyFullAddressAttribute()
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

    public function getQrCodeAttribute()
    {
        $url = config('services.azprodotnet.url') . 'bh/' . $this->seri_number;
        $content = app(\App\Services\QrCodeService::class)->generateQrCode($url, true);
        return base64_encode($content);
    }

    public function getActivationCode()
    {
        if (!$this->activation_code) {
            $this->activation_code = $this->generateNumericActivationCode();
            $this->save();
        }

        return $this->activation_code;
    }

    protected function generateNumericActivationCode($n = 8)
    {  
        $generator = "1357902468ABCDEFGHIJKLMNOPQRXYST";
        $result = "";
      
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }
    
        return $result;
    }
}
