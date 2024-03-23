<?php

namespace App\Observes;

use App\Models\Address;
use App\Models\District;
use App\Models\Province;
use App\Models\Voucher;
use App\Models\Voucherhistory;
use App\Models\Ward;
use Illuminate\Support\Facades\DB;

class VoucherObserve
{
    public function saved(Voucher $voucher)
    {
        if ($voucher->isUsableMultile()) {
            $diff = $voucher->quantity - $voucher->histories()->count();
            if ($diff < 100) {
                $this->syncHistoriesQuantity($voucher);
            } else {
                $path = base_path();
                exec("nohup php {$path}/artisan voucher:histories {$voucher->id} > /dev/null &");
            }
        }
    }

    public function syncHistoriesQuantity(Voucher $voucher)
    {
        if ($voucher->isUsableMultile()) {
            $diff = $voucher->quantity - $voucher->histories()->count();
            if ($diff) {
                $diff > 0
                    ? $this->addMoreHistories($voucher->id, $voucher->code, abs($diff))
                    : $this->remoteHistories($voucher->id, abs($diff));
            }
        }
    }

    protected function addMoreHistories($voucherId, $voucherCode, $quantity)
    {
        for ($i = 1; $i <= $quantity; $i++) {
            $voucher = Voucherhistory::create([
                'voucher_id' => $voucherId,
                'code' => $voucherCode,
            ]);
            $voucher->code = $voucher->code . $this->generateCode();
            $voucher->save();
        }
    }

    protected function remoteHistories($voucherId, $quantity)
    {
        $unUsed = Voucherhistory::where('voucher_id', $voucherId)
            ->where('customer_id', '<>', 0);
        if ($unUsed->count() > $quantity) {
            while ($quantity) {
                Voucherhistory::where('voucher_id', $voucherId)->first()->delete();
                $quantity--;
            }
        }
    }

    protected function generateCode($length = 7)
    {
        $seed = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, $length) as $k) $rand .= $seed[$k];

        return $rand;
    }
}
