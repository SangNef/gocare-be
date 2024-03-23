<?php

namespace App\Observes;

use App\Events\OrderFinished;
use App\Models\Commission;
use App\Models\DOrder;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Transaction;
use App\Repositories\DOrderRepository;

class DOrderObserve
{
    protected $repository;

    public function __construct(DOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function created(DOrder $order)
    {
        $this->repository->notify($order);
    }
}
