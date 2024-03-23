<?php

namespace App\Repositories;

use App\Models\Bank;

class BankRepository
{
    public function create($attributes = [])
    {
        $attributes = array_filter($attributes);
        $data = array_replace($attributes, [
            'name' => $attributes['name'],
            'branch' => $attributes['branch'],
            'acc_name' => $attributes['acc_name'],
            'acc_id' => $attributes['acc_id'],
            'first_balance' => isset($attributes['first_balance']) ? $attributes['first_balance'] : 0,
            'currency_type' => Bank::CURRENCY_VND,
            'printing' => 1
        ]);
        $bank = Bank::create($data);
        return $bank;
    }
}
