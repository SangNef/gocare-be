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

class UpdateCustomerCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer-code:update';

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
        $customers = Customer::whereNull('deleted_at')->get();
        foreach ($customers as $customer) {
            $customer->code = 'DL-' . str_pad($customer->id, 6, '0', STR_PAD_LEFT);
            $customer->save();
        }
    }
}

//
