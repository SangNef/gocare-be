<?php

namespace App\Events;

use App\Events\Event;
use App\Models\WarrantyOrder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WarrantyOrderSaved extends Event
{
    use SerializesModels;
    protected $wOrder;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(WarrantyOrder $wOrder)
    {
        $this->wOrder = $wOrder;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }

    public function getWarrantyOrder()
    {
        return $this->wOrder;
    }
}
