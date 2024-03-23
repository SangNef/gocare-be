<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class BankBacklogRepository
{
	public function update($bankId, $in, $out = 0, $fee = 0)
	{
        $changed = $in - $out - $fee;
        DB::table('banks')
            ->where('id', $bankId)
            ->update([
                'last_balance' => DB::raw('last_balance + ' . $changed)
            ]);
        DB::table('bank_backlogs')->where('bank_id', $bankId)->update([
            'money_in' => DB::raw('money_in + ' . (int) $in),
            'money_out' => DB::raw('money_out + ' . (int) $out),
            'fee' => DB::raw('fee + ' . (int) $fee)
        ]);
	}

}