<?php

namespace App\Http\Controllers\ApiV2;

use App\Models\Voucherhistory;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Carbon\Carbon;

class VouchersController extends Controller
{
    public function index(Request $request)
    {
         $vouchers = Voucher::where('code', $request->q)
            ->where('type', Voucher::TYPE_ONE_CODE)
//            ->where('status', 1)
            ->where('started_at', '<=', Carbon::now())
            ->where('ended_at', '>=', Carbon::now())
            ->get();

         $voucherHistory = Voucherhistory::where('code', $request->q)
             ->where('customer_id', '0')
             ->first();
        $multiCodeVoucher = collect();
         if ($voucherHistory) {
             $voucher = $voucherHistory->voucher;

             $multiCodeVoucher = Voucher::where('id', $voucher->id)
                 ->where('type', Voucher::TYPE_MULTI_CODE)
//                 ->where('status', 1)
                 ->where('started_at', '<=', Carbon::now())
                 ->where('ended_at', '>=', Carbon::now())
                 ->get()
                 ->map(function ($voucher) use ($voucherHistory) {
                     $voucher->history_id = $voucherHistory->id;

                     return $voucher;
                 });
         }

         return $vouchers->merge($multiCodeVoucher);
    }
}
