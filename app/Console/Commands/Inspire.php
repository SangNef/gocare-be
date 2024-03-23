<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerProductDiscount;
use App\Models\Group;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSeri;
use App\Models\Transaction;
use App\Models\Transactionhistory;
use App\Services\CODPartners\GHNService;
use FontLib\Table\Type\maxp;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\DB;

class Inspire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspire';

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
        $service = app(GHNService::class);
        $partnerAddress = $service->convertAddressIdToPartnerId(79, 770, 27160);
        dd($partnerAddress);

    }
}

//
