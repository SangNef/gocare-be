<?php

namespace App\Policies;

use App\User;
use App\Models\Transaction;
use Dwij\Laraadmin\Models\Module;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function approve(User $user, Transaction $transaction)
    {
        return Module::hasAccess("Transactions", "edit") && $user->isSupperAdminRole();
    }
}
