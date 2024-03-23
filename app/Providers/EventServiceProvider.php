<?php

namespace App\Providers;

use App\Events\OrderSaved;
use App\Events\WarrantyOrderSaved;
use App\Events\WarrantyActivated;
use App\Listeners\SendingSms;
use App\Listeners\SyncStoreProductQuantity;
use App\Listeners\UpdateStatusWarrantyOrder;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderSaved::class => [
            SyncStoreProductQuantity::class
        ],
        WarrantyActivated::class => [
            SendingSms::class
        ],
        WarrantyOrderSaved::class => [
            UpdateStatusWarrantyOrder::class
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
