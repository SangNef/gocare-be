<?php
namespace App\Services;

use App\Models\Group;
use App\Models\GroupProductDiscount;
use App\Models\Product;
use App\Models\Upload as UploadModel;

class Generator
{
    public function generateOrderCode($prefix = '')
    {
        return ($prefix ? $prefix . '-' : '') . date('ymd') . $this->generate(6, true);
    }

    public function generateProductSeries($prefix)
    {
        return $prefix . $this->generate(5, true);
    }

    public function generate($length, $digitOnly = false)
    {
        $characters = '0123456789' . ( $digitOnly ? '' : 'abcdefghijklmnopqrstuvwxyz');
        $characters = str_split($characters);
        shuffle($characters);
        $charactersLength = count($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
