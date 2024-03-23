<?php

namespace App\Observes;

use App\Models\TransportOrder;
use App\Repositories\CustomerBacklogRepository;

class TransportOrderObserve
{
    protected $customerBacklogRp;

    public function __construct(CustomerBacklogRepository $customerBacklogRp)
    {
        $this->customerBacklogRp = $customerBacklogRp;
    }

    public function created(TransportOrder $transportOrder)
    {
        $this->customerBacklogRp->update($transportOrder->customer_id, -$transportOrder->transport_price);
    }

    public function updated(TransportOrder $transportOrder)
    {
        if (!empty($transportOrder->getOriginal())) {
            if ($transportOrder->isDirty(['transport_price']) && !$transportOrder->isDirty(['customer_id'])) {
                $price = $transportOrder->transport_price - $transportOrder->getOriginal('transport_price');
                $this->customerBacklogRp->update($transportOrder->customer_id, -$price);
            }
            if (
                $transportOrder->isDirty(['customer_id'])
                || ($transportOrder->isDirty(['customer_id']) && $transportOrder->isDirty(['transport_price']))
            ) {
                // refund old customer backlog
                $oldCustomer = $transportOrder->getOriginal('customer_id');
                if ($oldCustomer) {
                    $this->customerBacklogRp->update($oldCustomer, $transportOrder->getOriginal('transport_price'));
                }
                $this->customerBacklogRp->update($transportOrder->customer_id, -$transportOrder->transport_price);
            }
        }
    }

    public function deleted(TransportOrder $transportOrder)
    {
        $this->customerBacklogRp->update($transportOrder->customer_id, $transportOrder->transport_price);
    }
}
