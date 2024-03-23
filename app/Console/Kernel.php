<?php

namespace App\Console;

use App\Console\Commands\AddVoucherHistory;
use App\Console\Commands\CustomerRevenueStatistic;
use App\Console\Commands\OrderCancel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Psy\Command\Command;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
         Commands\Inspire::class,
         Commands\SendLowStockProductNotification::class,
         Commands\SendingSms::class,
         Commands\UpdateProductAttribute::class,
         Commands\OrderNotification::class,
         Commands\WarrrantyOrderNotification::class,
         Commands\RevenueStatistic::class   ,
         Commands\UpdateCustomerCode::class,
         AddVoucherHistory::class,
         OrderCancel::class,
         CustomerRevenueStatistic::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }
}
