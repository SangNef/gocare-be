<?php
namespace App\Repositories;

use App\Models\Transaction as Model;
use Illuminate\Support\Facades\DB;

class TransactionRepository
{
	public function updateBankHistoryForNextTransactions($begin, $bank, $changed)
	{
		Model::where('id', '>=', $begin)
            ->where('bank_id', $bank)
            ->update([
                'bank_history' => DB::raw('bank_history + ' . $changed)
            ]);
	}

}