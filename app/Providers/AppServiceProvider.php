<?php

namespace App\Providers;

use App\Models\AccessToken;
use App\Models\Address;
use App\Models\Audit;
use App\Models\DOrder;
use App\Models\ImportProduct;
use App\Models\Produce;
use App\Models\Voucher;
use App\Observes\AddressObserve;
use App\Observes\AuditObserve;
use App\Observes\DOrderObserve;
use App\Observes\ImportProductObserve;
use App\Observes\VoucherObserve;
use Validator;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductSeri;
use App\Models\Transaction;
use App\Models\Bank;
use App\Models\CODOrder;
use App\Models\Customer;
use App\Models\Group;
use App\Models\SmsSent;
use App\Models\TransportOrder;
use App\Models\WarrantyOrder;
use App\Models\WarrantyOrderProductSeri;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\StoreProduct;
use App\Observes\AccessTokenObserve;
use App\Observes\OrderObserve;
use App\Observes\OrderProductObserve;
use App\Observes\ProductObserve;
use App\Observes\TransactionObserve;
use App\Observes\BankObserve;
use App\Observes\CODOrderObserve;
use App\Observes\CustomerObserve;
use App\Observes\GroupObserve;
use App\Observes\TransportOrderObserve;
use App\Observes\ProduceObserve;
use App\Observes\ProductSeriObserve;
use App\Observes\StoreProductGroupAttributeExtraObserve;
use App\Observes\StoreProductObserve;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('series_equal', function ($attribute, $value, $parameters, $validator) {
            $i = explode('.', $attribute);
            $i = $i[1];
            $input = $validator->getData();
            $quantity = $input['sub_type'] == 1 && isset($input['products'][$i]['n_quantity'])
                ? @$input['products'][$i]['n_quantity']
                : @$input['products'][$i]['w_quantity'];
            return intval(count($value)) === intval($quantity);
        });
        
        $this->bootObserves();
        $this->bootMorphMap();
        //        \DB::listen(function ($query) {
        //            $file=storage_path().'/logs/query_'.date('Y-m-d').'.log';
        //            if (!file_exists($file)) {
        //                $ft=fopen($file,
        //                    'w+');
        //                fclose($ft);
        //            }
        //            file_put_contents($file,
        //                json_encode([
        //                    $query->time,
        //                    $query->sql,
        //                    $query->bindings,
        //                ])."\n",
        //                FILE_APPEND);
        //        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    protected function bootObserves()
    {
        Product::observe(ProductObserve::class);
        OrderProduct::observe(OrderProductObserve::class);
        Transaction::observe(TransactionObserve::class);
        Order::observe(OrderObserve::class);
        Bank::observe(BankObserve::class);
        Customer::observe(CustomerObserve::class);
        Group::observe(GroupObserve::class);
        Address::observe(AddressObserve::class);
        TransportOrder::observe(TransportOrderObserve::class);
        CODOrder::observe(CODOrderObserve::class);
        AccessToken::observe(AccessTokenObserve::class);
        Audit::observe(AuditObserve::class);
        DOrder::observe(DOrderObserve::class);
        Produce::observe(ProduceObserve::class);
        StoreProductGroupAttributeExtra::observe(StoreProductGroupAttributeExtraObserve::class);
        StoreProduct::observe(StoreProductObserve::class);
        ProductSeri::observe(ProductSeriObserve::class);
        ImportProduct::observe(ImportProductObserve::class);
        Voucher::observe(VoucherObserve::class);
    }

    protected function bootMorphMap()
    {
        Relation::morphMap([
            'WarrantyOrder' => WarrantyOrder::class,
            'Order' => Order::class,
            'WarrantyOrderProductSeri' => WarrantyOrderProductSeri::class,
            'DOrder' => DOrder::class,
        ]);
    }
}
