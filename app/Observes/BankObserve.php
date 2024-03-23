<?php
namespace App\Observes;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\BankBacklog;
use App\Models\Transaction;

class BankObserve
{
    public function creating($bank)
    {
        if (auth()->check() && auth()->user()->store_id) {
            $bank->store_id = auth()->user()->store_id;
        }
    }

    public function created(Bank $bank)
    {
        $data = [
            [
                'bank_id' => $bank->id,
                'debt_type' => BankBacklog::BEGINING,
                'money_in' => $bank->first_balance,
                'money_out' => 0,
                'fee' => 0
            ],
            [
                'bank_id' => $bank->id,
                'debt_type' => BankBacklog::IN_MONTH,
                'money_in' => $bank->first_balance,
                'money_out' => 0,
                'fee' => 0
            ]
        ];
        BankBacklog::insert($data);
    }
}