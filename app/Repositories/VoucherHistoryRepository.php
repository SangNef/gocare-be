<?php

namespace App\Repositories;

use App\Models\Bank;
use App\Models\Voucherhistory;
use Maatwebsite\Excel\Facades\Excel;

class VoucherHistoryRepository
{
    public function exportByIds($ids)
    {
        $histories = Voucherhistory::whereIn('id', $ids)->get();
        $fileName = 'Voucher ' . date('d/m/Y');
        $data = [];
        $voucher = '';
        foreach ($histories as $history) {
            if ($history) {
                if (!$voucher) {
                    $voucher = $history->voucher;
                }
                $data[] = [
                    'ID' => $history->id,
                    'Tên' => $voucher->name,
                    'Mã' => $history->code,
                    'Ngày bắt đầu' => $voucher->started_at,
                    'Ngày kết thúc' => $voucher->ended_at,
                    'Đơn hàng tối thiểu' => number_format($voucher->min_order_amount) . ' đ',
                    'Giảm giá' => $voucher->percent . '%',
                    'Giảm tối đa' => number_format($voucher->max) . ' đ',
                ];
            }
        }

        return Excel::create($fileName, function ($excel) use ($data) {
            $excel->sheet('Vouchers', function ($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        });
    }
}
