<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerRevenue;
use App\Models\Store;
use App\Repositories\CustomerRepository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\RevenueStatisticRepository;

class CustomerRevenueStatistic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer-revenue:statistic {month?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rp = app(CustomerRepository::class);
        $month = $this->argument('month') ? Carbon::createFromFormat('Ym', $this->argument('month')) : Carbon::now()->subMonth();
        $customers = Customer::whereNull('customer_parent_id')
            ->get();
        foreach ($customers as $customer) {
            $report = $rp->report($customer->id, $month->format('m'), $month->format('Y'));
            CustomerRevenue::updateOrCreate([
                'customer_id' => $customer->id,
                'month' => $month->format('m-Y')
            ], [
                'data' => $report,
                'accepted_at' => '',
            ]);
        }
    }
}
