<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\OrderStatus;
use App\User;
use Dwij\Laraadmin\Models\Module;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    protected $orderStatus;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct(OrderStatus $orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    public function updateStatus(User $user, Order $order)
    {
         return Module::hasAccess("Orders", "edit") && $this->orderStatus->isEditable($order->status);
    }

    public function approveOrder(User $user, Order $order)
    {
        return Module::hasAccess("Orders", "edit") && $this->orderStatus->isApproveable($order->approve) && $user->isSupperAdminRole();
    }
}
