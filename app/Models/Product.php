<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Traits\SearchScope;
use App\Services\Discount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    const TYPE_SIMPLE_PRODUCT = 1;
    const TYPE_GROUP_PRODUCT = 2;
    const TYPE_ACCESSORIES_PRODUCT = 3;
    const NEW_PRODUCT = 1;
    const WARRANTY_PRODUCT = 2;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    use SoftDeletes;
    use SearchScope;

    protected $table = 'products';
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = ['deleted_at'];
    protected $searches = [
        'category_ids',
        'name',
        'sku',
        'id',
        'status'
    ];

    public $authorized = false;

    public function products()
    {
        return $this->hasMany(ProductRelated::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_products')->withPivot('n_quantity', 'w_quantity');
    }

    public function getFullFeaturedImage($width)
    {
        $uploadSv = app(\App\Services\Upload::class);

        return $uploadSv->getThumbnail($this->featured_image, $width);
    }

    public function getLastestPriceForCustomer($customerId, $applyGroupDiscountPercent = false)
    {
        // Priority: Customer discount > Group discount.
        $discountSv = app(Discount::class);

        return $discountSv->getDiscountForCustomer($customerId, $this->id, $applyGroupDiscountPercent);
    }

    public function getPriceForCustomerGroup($groupName, $appyDiscountPercent = false)
    {
        $price = $this->retail_price;
        $group = Group::where('name', $groupName)->first();
        if ($group) {
            $id = $this->id;
            $groupDiscount = app(Discount::class)->getDiscountForGroup($group->id, $id);
            if (isset($groupDiscount[$id])) {
                $price = $groupDiscount[$id]['price'];
                if ($appyDiscountPercent) {
                    $price *= ((100 - $groupDiscount[$id]['percent']) / 100);
                }
            }
        }
        return $price;
    }

    public function series()
    {
        return $this->hasMany(\App\Models\ProductSeri::class);
    }

    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'products_product_category')->withPivot('position');
    }

    public function getFeaturedImagePath($groupAttrId = '')
    {
        $uploadSv = app(\App\Services\Upload::class);

        if ($groupAttrId && $groupAttr = ProductGroupAttributeMedia::find($groupAttrId)) {
            $media = explode(',', $groupAttr->media_ids);
            if (!empty($media)) {
                $path = $uploadSv->getImagePath($media[0]);
                if ($path) {
                    return $path;
                }
            }
        }

        return $this->featured_image ? $uploadSv->getImagePath($this->featured_image) : '';
    }

    public function getFeaturedImagePathByAttrValues($values)
    {
        $uploadSv = app(\App\Services\Upload::class);
        $groupAttr = ProductGroupAttributeMedia::where('product_id', $this->id)
            ->where('attribute_value_ids', $values)
            ->first();
        if ($groupAttr) {
            $media = explode(',', $groupAttr->media_ids);
            if (!empty($media)) {
                $path = $uploadSv->getImagePath($media[0]);
                if ($path) {
                    return $path;
                }
            }
        }

        return $this->featured_image ? $uploadSv->getImagePath($this->featured_image) : '';
    }

    public function getProductGallery()
    {
        $uploadSv = app(\App\Services\Upload::class);
        $gallery = json_decode($this->product_gallery);

        return array_map(function ($image) use ($uploadSv) {
            return $uploadSv->getImagePath($image);
        }, $gallery);
    }

    public function isUseSeries()
    {
        return $this->has_series;
    }

    public function getCategoriesName()
    {
        return $this->categories()->exists()
            ? $this->categories->implode('name', ', ')
            : '';
    }

    public function attrs()
    {
        return $this->hasMany(ProductAttribute::class, 'product_id');
    }

    public function attrValues()
    {
        return $this->hasMany(ProductGroupAttributeMedia::class, 'product_id');
    }

    public function combos()
    {
        return $this->hasMany(ProductCombo::class);
    }

    public function getFirstCategoryId()
    {
        $first = $this->categories()->first();
        if ($first) {
            return $first->pivot->product_category_id;
        }

        return null;
    }
}
