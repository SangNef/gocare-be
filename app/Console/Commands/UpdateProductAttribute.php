<?php

namespace App\Console\Commands;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Customer;
use App\Models\CustomerProductDiscount;
use App\Models\Group;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductGroupAttributeMedia;
use App\Models\ProductSeri;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\Transaction;
use App\Models\Transactionhistory;
use FontLib\Table\Type\maxp;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\DB;

class UpdateProductAttribute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product-attribute:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $attribute = Attribute::updateOrCreate([
            'name' => 'Màu sắc'
        ]);

        $black = AttributeValue::updateOrCreate([
            'attribute_id' => $attribute->id,
            'value' => 'Đen'
        ]);
        $yeallow = AttributeValue::updateOrCreate([
            'attribute_id' => $attribute->id,
            'value' => 'Vàng'
        ]);

        $products = DB::table('products')
            ->select(['products.id', 'product_gallery'])
            ->where('category_ids', 'like', '%"2"%')
            ->orWhere('category_ids', 'like', '%"10"%')
            ->get();
        $stores = Store::whereNull('deleted_at')->get();
        ProductGroupAttributeMedia::unsetEventDispatcher();
        StoreProductGroupAttributeExtra::unsetEventDispatcher();
        foreach ($products as $product) {
            $media = json_decode($product->product_gallery, true);
            ProductAttribute::updateOrCreate([
                'product_id' => $product->id,
                'attribute_id' => $attribute->id,
            ],[
                'attribute_value_id' => $black->id . ',' . $yeallow->id,
            ]);
            $default = ProductGroupAttributeMedia::updateOrCreate([
                'product_id' => $product->id,
                'attribute_value_ids' => $black->id,
            ], [
                'attribute_value_texts' => $black->value,
                'media_ids' => implode(',', $media),
            ]);
            ProductGroupAttributeMedia::updateOrCreate([
                'product_id' => $product->id,
                'attribute_value_ids' => $yeallow->id,
            ],[
                'attribute_value_texts' => $yeallow->value,
                'media_ids' => implode(',', $media),
            ]);
            foreach ($stores as $store) {
                $quantity = StoreProduct::where('store_id', $store->id)
                    ->where('product_id', $product->id)
                    ->first();
                StoreProductGroupAttributeExtra::updateOrCreate([
                    'product_id' => $product->id,
                    'store_id' => $store->id,
                    'attribute_value_ids' => $black->id,
                ],[
                    'attribute_value_texts' => $black->value,
                    'n_quantity' => $quantity ? $quantity->n_quantity : 0,
                    'w_quantity' => $quantity ? $quantity->w_quantity : 0,
                ]);
                StoreProductGroupAttributeExtra::updateOrCreate([
                    'product_id' => $product->id,
                    'store_id' => $store->id,
                    'attribute_value_ids' => $yeallow->id,
                ],[
                    'attribute_value_texts' => $yeallow->value,
                    'n_quantity' => 0,
                    'w_quantity' => 0,
                ]);
            }

            ProductSeri::where('product_id', $product->id)
                ->update([
                    'group_attribute_id' => $default->id
                ]);
        }

    }
}

//
