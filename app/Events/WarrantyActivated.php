<?php

namespace App\Events;

use App\Events\Event;
use App\Models\ProductSeri;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WarrantyActivated extends Event
{
    use SerializesModels;
    protected $seri;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ProductSeri $seri)
    {
        $this->seri = $seri;
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

    public function getSeri()
    {
        return $this->seri;
    }
}
